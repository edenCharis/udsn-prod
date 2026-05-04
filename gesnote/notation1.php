<?php
include '../php/connexion.php';
include '../php/lib.php';
session_start();

if ($_SESSION['id'] != session_id() || $_SESSION['role'] != "gesnote") {
    header("location: ../deconnexion1");
    exit();
}

// ── Suppression d'une note individuelle ──────────────────────────────────────
if (isset($_GET['sup'])) {
    $id      = intval($_GET['sup']);
    $ecue    = $_GET['ecue'];
    $annee   = $_GET['annee'];
    $anonyme = $_GET['code'];
    $type    = $_GET['examen'];
    $nature  = $_GET['nature'];
    $etab    = $_SESSION['etablissement'];

    $connexion->begin_transaction();

    try {
        // 1. Supprimer de ligne1
        $del = $connexion->prepare("DELETE FROM ligne1 WHERE id = ?");
        if (!$del) throw new Exception("Préparation suppression: " . $connexion->error);
        $del->bind_param('i', $id);
        if (!$del->execute()) throw new Exception("Suppression: " . $del->error);
        if ($del->affected_rows == 0) throw new Exception("Aucune note trouvée avec cet ID");
        $del->close();

        // 2. Récupérer semestre et classe depuis anonymat
        $semestre = getSemestreByAnonymat($nature, $type, $anonyme, $ecue, $annee, $connexion, $etab);
        if (!$semestre) throw new Exception("Semestre introuvable pour cet anonymat");

        $classe = getClasseByAnonymat($nature, $type, $anonyme, $ecue, $annee, $semestre, $connexion, $etab);
        if (!$classe) throw new Exception("Classe introuvable pour cet anonymat");

        $etudiant = getIdInscriptionFromAnonymat($nature, $type, $anonyme, $annee, $ecue, $semestre, $connexion, $etab);
        if (!$etudiant) throw new Exception("Inscription étudiant introuvable");

        // 3. Recalculer la moyenne restante dans ligne1
        $id_notation = verifierInscriptionNotation($connexion, $etudiant, $ecue, $semestre, $classe, $annee,$etab);

        if ($id_notation !== null) {
            $sql_avg = "
                SELECT AVG(l.note) AS moyenne
                FROM ligne1 l
                JOIN anonymat a ON l.anonymat = a.numero
                WHERE l.semestre    = ?
                  AND l.annee       = ?
                  AND l.type_examen = ?
                  AND l.etab        = ?
                  AND l.code_ecue   = ?
                  AND a.etudiant    = ?
            ";
            $avgStmt = $connexion->prepare($sql_avg);
            $avgStmt->bind_param("sssssi", $semestre, $annee, $type, $etab, $ecue, $etudiant);
            $avgStmt->execute();
            $avgRow = $avgStmt->get_result()->fetch_assoc();
            $avgStmt->close();
            $avg = $avgRow['moyenne'] ?? null;

            if ($type == "Session Ordinaire") {
                $upd = $connexion->prepare("UPDATE notation SET moyEx = ? WHERE id = ?");
            } else {
                $upd = $connexion->prepare("UPDATE notation SET session_rappel = ? WHERE id = ?");
            }
            if (!$upd) throw new Exception("Préparation update notation: " . $connexion->error);
            $upd->bind_param('di', $avg, $id_notation);
            if (!$upd->execute()) throw new Exception("Update notation: " . $upd->error);
            $upd->close();
        }

        // 4. Logger
        logUserAction(
            $connexion, $_SESSION['id_user'],
            "Suppression d'une note",
            date("Y-m-d H:i:s"),
            $_SERVER['REMOTE_ADDR'],
            "ID supprimé: $id, Anonymat: $anonyme, ECUE: $ecue, Type: $type"
        );

        $connexion->commit();
        header("location: notation1?sucess=" . urlencode("Suppression effectuée avec succès"));
        exit();

    } catch (Exception $e) {
        $connexion->rollback();
        error_log("Erreur suppression note ID $id: " . $e->getMessage());
        header("location: notation1?erreur=" . urlencode("Erreur: " . $e->getMessage()));
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $_SESSION['univ']; ?> - Scolarité de <?php echo $_SESSION['etablissement']; ?></title>
    <link rel="icon" type="image/png" sizes="16x16" href="../administrateur/<?php echo $_SESSION['logo_univ']; ?>">
    <link rel="stylesheet" href="../vendor/bootstrap-select/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="../vendor/select2/css/select2.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/skin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .bulk-entry-form { background:#f8f9fa; padding:20px; border-radius:8px; margin-bottom:20px; }
        .grade-input { width:80px; text-align:center; }
        .student-row { transition:background-color .2s; }
        .student-row:hover { background-color:#f1f1f1; }
        .grade-saved { background-color:#d4edda !important; }
        .filter-section { display:flex; gap:15px; flex-wrap:wrap; align-items:end; }
        .filter-group { flex:1; min-width:200px; }
        #students-table-wrapper { max-height:600px; overflow-y:auto; }
        .sticky-header { position:sticky; top:0; background:white; z-index:10; box-shadow:0 2px 4px rgba(0,0,0,.1); }
        #step-indicator { font-size:.85rem; color:#666; }
    </style>
</head>
<body>

<div id="preloader">
    <div class="sk-three-bounce">
        <div class="sk-child sk-bounce1"></div>
        <div class="sk-child sk-bounce2"></div>
        <div class="sk-child sk-bounce3"></div>
    </div>
</div>

<div id="main-wrapper">
    <?php include "header.php"; ?>
    <?php include 'nav.html'; ?>

    <div class="content-body">
        <div class="container-fluid">

            <div class="row page-titles mx-0">
                <div class="col-sm-6 p-md-0">
                    <div class="welcome-text">
                        <h3>Gestion des notes des étudiants</h3>
                    </div>
                </div>
                <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="../gesnote/">Gesnote</a></li>
                        <li class="breadcrumb-item active"><a href="javascript:void(0);">Note</a></li>
                    </ol>
                </div>
            </div>

            <!-- SAISIE EN MASSE -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title"><i class="fas fa-list"></i> Saisie en masse des notes</h4>
                        </div>
                        <div class="card-body">

                            <div class="bulk-entry-form">
                                <form id="filter-form">
                                    <div class="filter-section">

                                        <div class="filter-group">
                                            <label>Classe</label>
                                            <select class="form-control form-control-lg disabling-options" id="filter-classe" name="classe" required>
                                                <option value="">-- Sélectionner --</option>
                                                <?php
                                                $sql = "SELECT DISTINCT classe FROM repartition_enseignant";
                                                $result = $connexion->query($sql);
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<option value='" . htmlspecialchars($row['classe']) . "'>" . htmlspecialchars($row['classe']) . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="filter-group">
                                            <label>ECUE</label>
                                            <select class="form-control form-control-lg disabling-options" id="filter-ecue" name="ecue" required>
                                                <option value="">-- Sélectionner --</option>
                                                <?php
                                                $sql = "SELECT * FROM ecue";
                                                $result = $connexion->query($sql);
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<option value='" . htmlspecialchars($row['code_ecue']) . "' data-libelle='" . htmlspecialchars(str_replace("'", "+", $row['libelle'])) . "'>"
                                                        . htmlspecialchars(str_replace("+", "'", $row['libelle'])) . " - " . htmlspecialchars($row['code_ecue']) . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="filter-group">
                                            <label>Semestre</label>
                                            <select class="form-control" id="filter-semestre" name="semestre" required>
                                                <option value=""></option>
                                                <?php
                                                $sql = "SELECT * FROM semestre WHERE libelle IN (SELECT semestre FROM repartition_enseignant)";
                                                $resultat = $connexion->query($sql);
                                                while ($s = $resultat->fetch_assoc()) {
                                                    echo "<option>" . htmlspecialchars(str_replace("+", "'", $s['libelle'])) . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="filter-group">
                                            <label>Année académique</label>
                                            <select class="form-control form-control-lg" id="filter-annee" name="annee" required>
                                                <option value="">-- Sélectionner --</option>
                                                <?php
                                                $sql = "SELECT * FROM annee ORDER BY libelle DESC";
                                                $result = $connexion->query($sql);
                                                while ($row = $result->fetch_assoc()) {
                                                    $val = str_replace("+", "'", $row['libelle']);
                                                    echo "<option value='" . htmlspecialchars($val) . "'>" . htmlspecialchars($val) . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="filter-group">
                                            <label>Type d'examen</label>
                                            <select class="form-control" id="filter-type" name="type_examen" required>
                                                <option value="">-- Sélectionner --</option>
                                                <option value="Session Ordinaire">Session Ordinaire</option>
                                                <option value="Session de Rappel">Session de Rappel</option>
                                            </select>
                                        </div>

                                        <div class="filter-group">
                                            <label>Nature</label>
                                            <select class="form-control" id="filter-nature" name="nature" required>
                                                <option value="">-- Sélectionner --</option>
                                                <option value="Examen Theorique" selected>Examen Theorique</option>
                                                <option value="Examen Pratique">Examen Pratique</option>
                                            </select>
                                        </div>

                                        <div class="filter-group">
                                            <button type="button" class="btn btn-primary btn-sm" id="load-students">
                                                <i class="fas fa-search"></i> Charger les étudiants
                                            </button>
                                        </div>

                                    </div>
                                </form>
                            </div>

                            <!-- LISTE DES ÉTUDIANTS -->
                            <div id="students-list" style="display:none;">

                                <!-- Champs cachés pour la soumission -->
                                <input type="hidden" id="save-classe">
                                <input type="hidden" id="save-ecue">
                                <input type="hidden" id="save-ecue-libelle">
                                <input type="hidden" id="save-semestre">
                                <input type="hidden" id="save-annee">
                                <input type="hidden" id="save-type">
                                <input type="hidden" id="save-nature">

                                <div class="d-flex justify-content-between mb-2 align-items-center">
                                    <h5>Étudiants trouvés : <span id="student-count">0</span></h5>
                                    <div>
                                        <span id="step-indicator" class="mr-3"></span>
                                        <button type="button" class="btn btn-success btn-lg" id="btn-save-top">
                                            <i class="fas fa-save"></i> Enregistrer toutes les notes
                                        </button>
                                    </div>
                                </div>

                                <!-- Barre de progression (cachée au départ) -->
                                <div id="bulk-progress" style="display:none;" class="progress mb-3" style="height:20px">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                                         role="progressbar" style="width:0%">0%</div>
                                </div>

                                <!-- Zone des messages -->
                                <div id="bulk-messages"></div>

                                <div id="students-table-wrapper">
                                    <table class="table table-bordered table-striped">
                                        <thead class="sticky-header">
                                            <tr>
                                                <th width="10%">N°</th>
                                                <th width="30%">Code Anonymat</th>
                                                <th width="30%">Note (0-20)</th>
                                            </tr>
                                        </thead>
                                        <tbody id="students-tbody"></tbody>
                                    </table>
                                </div>

                                <div class="text-center mt-3">
                                    <button type="button" class="btn btn-success btn-lg" id="btn-save-bottom">
                                        <i class="fas fa-save"></i> Enregistrer toutes les notes
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>

    <!-- Modal messages -->
    <div class="modal" id="messageModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo $_SESSION['univ']; ?></h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body" id="messageBody"></div>
            </div>
        </div>
    </div>

    <div class="footer">
        <div class="copyright">
            <p>Copyright © Designed &amp; Développé par <a href="https://www.cet-up.com" target="_blank">CETUP</a> 2023</p>
        </div>
    </div>
</div>

<script src="../vendor/global/global.min.js"></script>
<script src="../vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
<script src="../js/custom.min.js"></script>
<script src="../vendor/raphael/raphael.min.js"></script>
<script src="../vendor/morris/morris.min.js"></script>
<script src="../vendor/select2/js/select2.full.min.js"></script>
<script src="../js/plugins-init/select2-init.js"></script>
<script src="../vendor/peity/jquery.peity.min.js"></script>
<script src="../js/dashboard/dashboard-2.js"></script>
<script src="../vendor/svganimation/vivus.min.js"></script>
<script src="../vendor/svganimation/svg.animation.js"></script>
<script>
$(document).ready(function () {

    // ── Affichage messages URL ────────────────────────────────────────────────
    var p = new URLSearchParams(window.location.search);
    if (p.get('erreur') || p.get('sucess')) {
        var msg = p.get('erreur')
            ? 'Erreur : ' + p.get('erreur')
            : 'Succès : ' + p.get('sucess');
        $('#messageBody').text(msg);
        $('#messageModal').modal('show');
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    // ── Chargement des étudiants ──────────────────────────────────────────────
    $('#load-students').click(function () {
        var classe   = $('#filter-classe').val();
        var ecue     = $('#filter-ecue').val();
        var semestre = $('#filter-semestre').val();
        var annee    = $('#filter-annee').val();
        var type     = $('#filter-type').val();
        var nature   = $('#filter-nature').val();

        if (!classe || !ecue || !semestre || !annee || !type || !nature) {
            alert('Veuillez remplir tous les champs');
            return;
        }

        $('#load-students').html('<i class="fas fa-spinner fa-spin"></i> Chargement...');

        $.ajax({
            url: 'ajax_load_students.php',
            method: 'POST',
            data: { classe, ecue, semestre, annee, type_examen: type, nature },
            success: function (response) {
                var data;
                try { data = JSON.parse(response); } catch(e) { alert('Réponse invalide du serveur'); return; }
                if (data.error) { alert(data.error); return; }

                // Stocker les paramètres dans les champs cachés
                $('#save-classe').val(classe);
                $('#save-ecue').val(ecue);
                $('#save-ecue-libelle').val($('#filter-ecue option:selected').data('libelle'));
                $('#save-semestre').val(semestre);
                $('#save-annee').val(annee);
                $('#save-type').val(type);
                $('#save-nature').val(nature);

                // Construire le tableau
                var tbody = '';
                $.each(data.students, function (index, student) {
                    var g = student.existing_note !== null ? student.existing_note : '';
                    tbody += '<tr class="student-row">'
                        + '<td>' + (index + 1) + '</td>'
                        + '<td>' + $('<span>').text(student.anonymat).html() + '</td>'
                        + '<td><input type="number" class="form-control grade-input"'
                        + ' data-anonymat="' + $('<span>').text(student.anonymat).html() + '"'
                        + ' min="0" max="20" step="0.01" value="' + g + '" placeholder="0-20"></td>'
                        + '</tr>';
                });
                $('#students-tbody').html(tbody);
                $('#student-count').text(data.students.length);
                $('#bulk-messages').html('');
                $('#bulk-progress').hide();
                $('#students-list').show();
                $('#load-students').html('<i class="fas fa-search"></i> Charger les étudiants');
                $('html, body').animate({ scrollTop: $('#students-list').offset().top - 100 }, 500);
            },
            error: function () {
                alert('Erreur lors du chargement des étudiants');
                $('#load-students').html('<i class="fas fa-search"></i> Charger les étudiants');
            }
        });
    });

    // ── Fonction principale : enregistrer les notes ───────────────────────────
    function saveGrades() {
        var hasGrades = $('.grade-input').toArray().some(function(el) { return el.value !== ''; });
        if (!hasGrades) {
            alert('Veuillez entrer au moins une note avant d\'enregistrer');
            return;
        }
        if (!confirm('Êtes-vous sûr de vouloir enregistrer ces notes ?')) return;

        // Construire le payload
        var grades = {};
        $('.grade-input').each(function () {
            var anonymat = $(this).data('anonymat');
            grades[anonymat] = $(this).val();
        });

        var payload = {
            classe:       $('#save-classe').val(),
            ecue:         $('#save-ecue').val(),
            ecue_libelle: $('#save-ecue-libelle').val(),
            semestre:     $('#save-semestre').val(),
            annee:        $('#save-annee').val(),
            type_examen:  $('#save-type').val(),
            nature:       $('#save-nature').val(),
            grades:       grades
        };

        // UI
        $('#btn-save-top, #btn-save-bottom').prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin"></i> Enregistrement…');
        $('#bulk-messages').html('');

        var $bar = $('#bulk-progress .progress-bar');
        $('#bulk-progress').show();
        $bar.css('width', '10%').text('Étape 1/2 : Enregistrement des notes…')
            .removeClass('bg-success bg-danger bg-warning progress-bar-animated')
            .addClass('progress-bar-animated');

        // ── AJAX 1 : save_bulk_grades.php ─────────────────────────────────────
        $.ajax({
            url:         'save_bulk_grades.php',
            method:      'POST',
            contentType: 'application/json; charset=utf-8',
            data:        JSON.stringify(payload),
            success: function (data) {
                if (!data.success) {
                    $bar.css('width','100%').removeClass('progress-bar-animated').addClass('bg-danger').text('Erreur');
                    $('#bulk-messages').html(
                        '<div class="alert alert-danger mt-2"><strong>Erreur :</strong> '
                        + $('<span>').text(data.error || 'Erreur inconnue').html() + '</div>'
                    );
                    resetButtons();
                    return;
                }

                // Étape 1 OK → passer à 50%
                $bar.css('width', '50%').text('Étape 2/2 : Mise à jour de la notation…');
                $('#step-indicator').text('Notes enregistrées ✓ — Mise à jour notation…');

                // Afficher les erreurs éventuelles de l'étape 1
                var msg1 = '';
                if (data.errors && data.errors.length > 0) {
                    msg1 = '<div class="alert alert-warning mt-2"><strong>Avertissements étape 1 :</strong><ul class="mb-0">';
                    data.errors.forEach(function(e) {
                        msg1 += '<li>' + $('<span>').text(e).html() + '</li>';
                    });
                    msg1 += '</ul></div>';
                }

                // ── AJAX 2 : update_notation.php ──────────────────────────────
                
                
                console.log("=== Lancement update_notation ===");
                $.ajax({
                    url:         'update_notation.php',
                    method:      'POST',
                    contentType: 'application/json; charset=utf-8',
                    data:        JSON.stringify({
                        classe:      payload.classe,
                        ecue:        payload.ecue,
                        semestre:    payload.semestre,
                        annee:       payload.annee,
                        type_examen: payload.type_examen,
                        nature:      payload.nature
                    }),
                    success: function (data2) {
                        $bar.css('width','100%').removeClass('progress-bar-animated');

                        var msg2 = '';
                        if (data2.success) {
                            $bar.addClass('bg-success').text('Terminé ✓');
                            $('#step-indicator').text('');

                            // Résumé global
                            msg2 = '<div class="alert alert-success mt-2">'
                                + '<strong><i class="fas fa-check-circle"></i> '
                                + $('<span>').text(data.message).html() + '</strong><br>'
                                + '<small>Notation mise à jour : '
                                + $('<span>').text(data2.message).html() + '</small>';

                            if (data2.errors && data2.errors.length > 0) {
                                msg2 += '<hr><ul class="mb-0">';
                                data2.errors.forEach(function(e) {
                                    msg2 += '<li>' + $('<span>').text(e).html() + '</li>';
                                });
                                msg2 += '</ul>';
                            }
                            msg2 += '</div>';

                            // Colorer les lignes sauvegardées
                            $('.grade-input').each(function () {
                                if ($(this).val() !== '') $(this).closest('tr').addClass('grade-saved');
                            });
                        } else {
                            $bar.addClass('bg-warning').text('Partiel');
                            msg2 = '<div class="alert alert-warning mt-2">'
                                + '<strong>Notes enregistrées</strong> mais erreur lors de la mise à jour notation :<br>'
                                + $('<span>').text(data2.error || 'Erreur inconnue').html() + '</div>';
                        }

                        $('#bulk-messages').html(msg1 + msg2);
                        resetButtons();
                    },
                    error: function (xhr) {
                        $bar.css('width','100%').removeClass('progress-bar-animated').addClass('bg-warning').text('Partiel');
                        $('#bulk-messages').html(
                            msg1 +
                            '<div class="alert alert-warning mt-2">Notes enregistrées mais erreur réseau sur update_notation.</div>'
                        );
                        resetButtons();
                    }
                });
            },
            error: function (xhr) {
                $bar.css('width','100%').removeClass('progress-bar-animated').addClass('bg-danger').text('Erreur');
                var errMsg = 'Erreur réseau ou serveur.';
                try {
                    var d = JSON.parse(xhr.responseText);
                    if (d.error) errMsg = d.error;
                } catch(_) {}
                $('#bulk-messages').html('<div class="alert alert-danger mt-2">' + $('<span>').text(errMsg).html() + '</div>');
                resetButtons();
            }
        });
    }

    function resetButtons() {
        $('#btn-save-top, #btn-save-bottom').prop('disabled', false)
            .html('<i class="fas fa-save"></i> Enregistrer toutes les notes');
    }

    // Brancher les deux boutons sur la même fonction
    $('#btn-save-top, #btn-save-bottom').click(saveGrades);
});
</script>
</body>
</html>
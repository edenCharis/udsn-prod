<?php
/**
 * anonymat_rappel.php
 * Génération automatique des codes anonymes pour la Session de Rappel
 * Identifie les étudiants ajournés par matière et génère leurs codes
 */

include '../php/connexion.php';
include '../php/lib.php';
session_start();

if (!($_SESSION['id'] == session_id() && $_SESSION['role'] == "anonymat")) {
    header("location: ../login");
    exit;
}

$etablissement = $_SESSION['etablissement'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $_SESSION['univ']; ?> – Anonymat Session de Rappel</title>
    <link rel="icon" type="image/png" sizes="16x16" href="../administrateur/<?php echo $_SESSION['logo_univ']; ?>">
    <link rel="stylesheet" href="../vendor/bootstrap-select/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="../vendor/select2/css/select2.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/skin.css">
    <link href="../vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
    <style>
        /* ── Design général ── */
        .section-label {
            font-weight: 700; color: #333; font-size: 13px;
            text-transform: uppercase; letter-spacing: .6px;
            margin-bottom: 14px; border-left: 4px solid #e74c3c; padding-left: 8px;
        }

        /* ── Carte filtre ── */
        .filter-card { border-top: 3px solid #e74c3c; }
        .filter-card .card-header { background: #e74c3c; color: #fff; }
        .filter-card .card-header h4 { color: #fff; margin: 0; }

        /* ── Stats rapides ── */
        .stats-bar {
            display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 18px;
        }
        .stat-badge {
            display: flex; flex-direction: column; align-items: center;
            background: #fff; border: 1px solid #dee2e6; border-radius: 8px;
            padding: 10px 18px; min-width: 110px; box-shadow: 0 1px 4px rgba(0,0,0,.07);
        }
        .stat-badge .stat-num { font-size: 22px; font-weight: 700; color: #e74c3c; line-height: 1; }
        .stat-badge .stat-lbl { font-size: 11px; color: #666; margin-top: 4px; text-align: center; }

        /* ── Accordéon ECUE ── */
        .ecue-block { margin-bottom: 14px; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; }
        .ecue-header {
            background: #2c3e50; color: #fff; padding: 12px 16px;
            display: flex; justify-content: space-between; align-items: center;
            cursor: pointer; user-select: none;
        }
        .ecue-header:hover { background: #34495e; }
        .ecue-title { font-weight: 600; font-size: 14px; }
        .ecue-meta { display: flex; gap: 10px; align-items: center; }
        .badge-count {
            background: #e74c3c; color: #fff; border-radius: 12px;
            padding: 2px 10px; font-size: 12px; font-weight: 700;
        }
        .badge-already {
            background: #27ae60; color: #fff; border-radius: 12px;
            padding: 2px 10px; font-size: 12px;
        }
        .badge-pending {
            background: #f39c12; color: #fff; border-radius: 12px;
            padding: 2px 10px; font-size: 12px;
        }
        .ecue-body { display: none; padding: 0; background: #fff; }
        .ecue-body.open { display: block; }

        .ecue-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .ecue-table thead tr { background: #ecf0f1; }
        .ecue-table th, .ecue-table td { padding: 8px 12px; border-bottom: 1px solid #eee; }
        .ecue-table th { font-weight: 600; font-size: 12px; text-transform: uppercase; color: #555; }
        .ecue-table tbody tr:hover { background: #fafafa; }
        .ecue-table .col-code { font-weight: 700; color: #e74c3c; text-align: center; }
        .ecue-table .col-moy  { text-align: center; }
        .ecue-table .col-dec  { text-align: center; }

        .decision-elim { color: #c0392b; font-weight: 600; }
        .decision-moy  { color: #e67e22; font-weight: 600; }

        /* ── Bouton générer individuel par ECUE ── */
        .btn-gen-ecue {
            background: #27ae60; color: #fff; border: none;
            padding: 5px 14px; border-radius: 4px; font-size: 12px; cursor: pointer;
        }
        .btn-gen-ecue:hover { background: #219a52; }
        .btn-gen-ecue:disabled { background: #95a5a6; cursor: not-allowed; }

        /* ── Barre d'action globale ── */
        .action-bar {
            background: #fff; border: 1px solid #dee2e6;
            border-radius: 8px; padding: 16px 20px; margin-bottom: 20px;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 1px 4px rgba(0,0,0,.07);
        }
        .action-bar .left { display: flex; align-items: center; gap: 10px; }

        /* ── Toast notification ── */
        #toast-container {
            position: fixed; top: 80px; right: 20px; z-index: 9999;
        }
        .toast-msg {
            background: #2ecc71; color: #fff; padding: 12px 20px;
            border-radius: 6px; margin-bottom: 8px; font-size: 13px;
            box-shadow: 0 2px 8px rgba(0,0,0,.2); animation: fadeIn .3s ease;
            max-width: 340px;
        }
        .toast-msg.error { background: #e74c3c; }
        @keyframes fadeIn { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:translateY(0); } }

        /* ── Loader overlay ── */
        #globalLoader {
            display: none; position: fixed; top:0; left:0; width:100%; height:100%;
            background: rgba(0,0,0,.45); z-index: 9998;
            justify-content: center; align-items: center; flex-direction: column;
        }
        #globalLoader.active { display: flex; }
        .loader-box {
            background: #fff; border-radius: 10px; padding: 30px 40px;
            text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,.3);
        }
        .loader-box .spinner {
            width: 48px; height: 48px; border: 5px solid #eee;
            border-top-color: #e74c3c; border-radius: 50%;
            animation: spin .8s linear infinite; margin: 0 auto 14px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Vide ── */
        .empty-state {
            text-align: center; padding: 50px 20px; color: #aaa;
        }
        .empty-state i { font-size: 48px; display: block; margin-bottom: 12px; }
    </style>
</head>
<body>

<!-- Preloader -->
<div id="preloader">
    <div class="sk-three-bounce">
        <div class="sk-child sk-bounce1"></div>
        <div class="sk-child sk-bounce2"></div>
        <div class="sk-child sk-bounce3"></div>
    </div>
</div>

<!-- Toast notifications -->
<div id="toast-container"></div>

<!-- Loader global -->
<div id="globalLoader">
    <div class="loader-box">
        <div class="spinner"></div>
        <div id="loaderText" style="font-weight:600; color:#333;">Génération en cours…</div>
        <div id="loaderSub" style="font-size:12px; color:#888; margin-top:6px;"></div>
    </div>
</div>

<div id="main-wrapper">
    <?php include "header.php"; ?>
    <?php include 'nav.html'; ?>

    <div class="content-body">
        <div class="container-fluid">

            <!-- Breadcrumb -->
            <div class="row page-titles mx-0">
                <div class="col-sm-6 p-md-0">
                    <div class="welcome-text">
                        <h3><i class="fa fa-repeat text-danger mr-2"></i>Anonymat – Session de Rappel</h3>
                        <p class="mb-0 text-muted">Identifiez les ajournés et générez leurs codes anonymes automatiquement</p>
                    </div>
                </div>
                <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index">Anonymat</a></li>
                        <li class="breadcrumb-item active">Session de Rappel</li>
                    </ol>
                </div>
            </div>

            <!-- ══ FILTRES ══ -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card filter-card">
                        <div class="card-header">
                            <h4><i class="fa fa-filter mr-2"></i>Critères de recherche</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><strong>Classe <span class="text-danger">*</span></strong></label>
                                        <select class="form-control" id="f_classe" required>
                                            <option value="">— Sélectionner —</option>
                                            <?php
                                            $res = $connexion->query("SELECT * FROM classe WHERE etab='$etablissement' ORDER BY libelle");
                                            if ($res) while ($row = $res->fetch_assoc()) {
                                                $lib = str_replace("+", "'", $row['libelle']);
                                                echo "<option value=\"" . htmlspecialchars($lib) . "\">" . htmlspecialchars($lib) . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><strong>Semestre <span class="text-danger">*</span></strong></label>
                                        <select class="form-control" id="f_semestre" required>
                                            <option value="">— Sélectionner —</option>
                                            <?php
                                            $res = $connexion->query("SELECT * FROM semestre");
                                            if ($res) while ($row = $res->fetch_assoc()) {
                                                $lib = str_replace("+", "'", $row['libelle']);
                                                echo "<option value=\"" . htmlspecialchars($lib) . "\">" . htmlspecialchars($lib) . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><strong>Année académique <span class="text-danger">*</span></strong></label>
                                        <select class="form-control" id="f_annee" required>
                                            <option value="">— Sélectionner —</option>
                                            <?php
                                            $res = $connexion->query("SELECT * FROM annee ORDER BY libelle DESC");
                                            if ($res) while ($row = $res->fetch_assoc()) {
                                                $lib = str_replace("+", "'", $row['libelle']);
                                                echo "<option value=\"" . htmlspecialchars($lib) . "\">" . htmlspecialchars($lib) . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><strong>Nature d'examen</strong></label>
                                        <select class="form-control" id="f_nature">
                                            <option value="Examen Theorique">Examen Théorique</option>
                                            <option value="Examen Pratique">Examen Pratique</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <button class="btn btn-danger btn-lg" id="btnRechercher">
                                        <i class="fa fa-search mr-2"></i>Rechercher les ajournés
                                    </button>
                                    <button class="btn btn-secondary btn-lg ml-2" id="btnReset" style="display:none">
                                        <i class="fa fa-times mr-1"></i>Réinitialiser
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══ RÉSULTATS ══ -->
            <div id="resultsSection" style="display:none;">

                <!-- Stats rapides -->
                <div class="stats-bar" id="statsBar"></div>

                <!-- Barre d'action globale -->
                <div class="action-bar">
                    <div class="left">
                        <i class="fa fa-bolt text-warning" style="font-size:20px;"></i>
                        <span style="font-weight:600;">Action globale :</span>
                    </div>
                    <div>
                        <button class="btn btn-success btn-sm mr-2" id="btnGenAll">
                            <i class="fa fa-check-circle mr-1"></i>Générer TOUS les codes manquants
                        </button>
                        <button class="btn btn-info btn-sm" id="btnPrintAll">
                            <i class="fa fa-print mr-1"></i>Imprimer la liste complète
                        </button>
                    </div>
                </div>

                <!-- Blocs par ECUE -->
                <div id="ecueBlocks"></div>

            </div>

            <!-- ══ ÉTAT VIDE ══ -->
            <div id="emptyState" style="display:none;">
                <div class="card">
                    <div class="card-body empty-state">
                        <i class="fa fa-check-circle text-success"></i>
                        <h4 class="text-success">Aucun étudiant ajourné</h4>
                        <p class="text-muted">Tous les étudiants ont validé leur semestre pour ces critères.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="footer">
        <div class="copyright">
            <p>Copyright &copy; Designed &amp; Développé par <a href="https://www.cet-up.com" target="_blank">CETUP</a> 2023</p>
        </div>
    </div>
</div>

<!-- ══ SCRIPTS ══ -->
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
<script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
<script src="../js/plugins-init/datatables.init.js"></script>

<script>
$(document).ready(function () {

    /* ──────────────────────────────────────────────
       UTILITAIRES
    ────────────────────────────────────────────── */
    function showToast(msg, type) {
        var cls = type === 'error' ? 'toast-msg error' : 'toast-msg';
        var $t = $('<div class="' + cls + '">' + msg + '</div>');
        $('#toast-container').append($t);
        setTimeout(function () { $t.fadeOut(400, function () { $t.remove(); }); }, 4000);
    }

    function showLoader(text, sub) {
        $('#loaderText').text(text || 'Chargement…');
        $('#loaderSub').text(sub || '');
        $('#globalLoader').addClass('active');
    }

    function hideLoader() {
        $('#globalLoader').removeClass('active');
    }

    /* Données courantes */
    var currentData = null;

    /* ──────────────────────────────────────────────
       RECHERCHE DES AJOURNÉS
    ────────────────────────────────────────────── */
    $('#btnRechercher').on('click', function () {
        var classe   = $('#f_classe').val();
        var semestre = $('#f_semestre').val();
        var annee    = $('#f_annee').val();
        var nature   = $('#f_nature').val();

        if (!classe || !semestre || !annee) {
            showToast('Veuillez remplir Classe, Semestre et Année académique', 'error');
            return;
        }

        showLoader('Analyse des résultats…', 'Identification des ajournés par matière');

        $.ajax({
            url: 'get_ajournes_rappel.php',
            method: 'POST',
            data: { classe: classe, semestre: semestre, annee: annee, nature: nature },
            dataType: 'json',
            success: function (res) {
                hideLoader();
                if (!res.success) {
                    showToast(res.message || 'Erreur serveur', 'error');
                    return;
                }
                currentData = res;
                renderResults(res, nature);
                $('#btnReset').show();
            },
            error: function (xhr) {
                hideLoader();
                var msg = 'Erreur de communication avec le serveur';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch(e) {}
                showToast(msg, 'error');
            }
        });
    });

    /* ──────────────────────────────────────────────
       RENDU DES RÉSULTATS
    ────────────────────────────────────────────── */
    function renderResults(res, nature) {
        $('#resultsSection').hide();
        $('#emptyState').hide();
        $('#ecueBlocks').empty();
        $('#statsBar').empty();

        if (!res.ecues || res.ecues.length === 0) {
            $('#emptyState').show();
            return;
        }

        /* Stats */
        var totalAjournes  = 0;
        var totalDejaCode  = 0;
        var totalAGenerer  = 0;
        res.ecues.forEach(function (e) {
            totalAjournes += e.etudiants.length;
            e.etudiants.forEach(function (et) {
                if (et.code_existant) totalDejaCode++;
                else totalAGenerer++;
            });
        });

        $('#statsBar').html(
            '<div class="stat-badge"><span class="stat-num">' + res.ecues.length + '</span><span class="stat-lbl">Matières<br>concernées</span></div>' +
            '<div class="stat-badge"><span class="stat-num">' + totalAjournes + '</span><span class="stat-lbl">Ajournés<br>total</span></div>' +
            '<div class="stat-badge" style="border-color:#27ae60"><span class="stat-num" style="color:#27ae60">' + totalDejaCode + '</span><span class="stat-lbl">Codes déjà<br>générés</span></div>' +
            '<div class="stat-badge" style="border-color:#f39c12"><span class="stat-num" style="color:#f39c12">' + totalAGenerer + '</span><span class="stat-lbl">Codes à<br>générer</span></div>'
        );

        /* Blocs ECUE */
        res.ecues.forEach(function (ecue) {
            var dejaCode = ecue.etudiants.filter(function (e) { return e.code_existant; }).length;
            var aGenerer = ecue.etudiants.length - dejaCode;

            var badgeAlready = dejaCode > 0 ? '<span class="badge-already ml-2">' + dejaCode + ' déjà codés</span>' : '';
            var badgePending = aGenerer > 0  ? '<span class="badge-pending ml-2">' + aGenerer + ' à générer</span>' : '';
            var btnGen       = aGenerer > 0
                ? '<button class="btn-gen-ecue btn-gen-single" data-ecue="' + ecue.code + '" data-libelle="' + escHtml(ecue.libelle) + '"><i class="fa fa-magic mr-1"></i>Générer (' + aGenerer + ')</button>'
                : '<span style="color:#27ae60; font-size:12px;"><i class="fa fa-check-circle mr-1"></i>Tous codés</span>';

            var rows = '';
            ecue.etudiants.forEach(function (et, i) {
                var codeTd = et.code_existant
                    ? '<td class="col-code">' + et.code_existant + '</td>'
                    : '<td class="col-code text-muted">—</td>';
                var decClass = et.decision === 'Note Eliminatoire' ? 'decision-elim' : 'decision-moy';
                rows += '<tr>' +
                    '<td style="text-align:center">' + (i + 1) + '</td>' +
                    '<td>' + escHtml(et.nom) + '</td>' +
                    '<td>' + escHtml(et.prenom) + '</td>' +
                    '<td style="text-align:center">' + escHtml(et.matricule) + '</td>' +
                    '<td class="col-moy">' + et.moyenne + '</td>' +
                    '<td class="col-dec"><span class="' + decClass + '">' + escHtml(et.decision) + '</span></td>' +
                    codeTd +
                    '<td style="text-align:center">' +
                        (et.code_existant
                            ? '<span class="badge badge-success">✓</span>'
                            : '<span class="badge badge-warning">En attente</span>') +
                    '</td>' +
                    '</tr>';
            });

            var block = $('\
<div class="ecue-block" data-ecue="' + ecue.code + '">\
  <div class="ecue-header">\
    <div class="ecue-title">\
      <i class="fa fa-book mr-2"></i>[' + escHtml(ecue.code) + '] ' + escHtml(ecue.libelle) + '\
    </div>\
    <div class="ecue-meta">\
      <span class="badge-count">' + ecue.etudiants.length + ' ajourné(s)</span>\
      ' + badgeAlready + badgePending + '\
      <div style="margin-left:14px;">' + btnGen + '</div>\
      <i class="fa fa-chevron-down ml-3" style="font-size:14px;"></i>\
    </div>\
  </div>\
  <div class="ecue-body">\
    <table class="ecue-table">\
      <thead><tr>\
        <th style="width:40px;">#</th>\
        <th>Nom</th><th>Prénom</th>\
        <th style="width:120px; text-align:center;">Matricule</th>\
        <th style="width:80px; text-align:center;">Moyenne</th>\
        <th style="width:140px; text-align:center;">Décision</th>\
        <th style="width:110px; text-align:center;">Code anonyme</th>\
        <th style="width:90px; text-align:center;">Statut</th>\
      </tr></thead>\
      <tbody>' + rows + '</tbody>\
    </table>\
  </div>\
</div>');

            $('#ecueBlocks').append(block);
        });

        /* Stocker nature dans data pour réutilisation */
        $('#resultsSection').data('nature', nature);
        $('#resultsSection').show();
    }

    /* ──────────────────────────────────────────────
       ACCORDÉON
    ────────────────────────────────────────────── */
    $(document).on('click', '.ecue-header', function (e) {
        if ($(e.target).closest('.btn-gen-ecue').length) return; // ne pas toggler si clic sur bouton
        var $body = $(this).next('.ecue-body');
        $body.toggleClass('open');
        $(this).find('.fa-chevron-down, .fa-chevron-up')
            .toggleClass('fa-chevron-down fa-chevron-up');
    });

    /* ──────────────────────────────────────────────
       GÉNÉRATION PAR ECUE
    ────────────────────────────────────────────── */
    $(document).on('click', '.btn-gen-single', function () {
        var ecueCode    = $(this).data('ecue');
        var ecueLibelle = $(this).data('libelle');
        var $btn        = $(this);

        var classe   = $('#f_classe').val();
        var semestre = $('#f_semestre').val();
        var annee    = $('#f_annee').val();
        var nature   = $('#f_nature').val();

        if (!confirm('Générer les codes anonymes pour\n[' + ecueCode + '] ' + ecueLibelle + '\nClasse : ' + classe + ' | ' + semestre + ' | ' + annee + '\nSession de Rappel – ' + nature + ' ?\n\nCette action est irréversible.')) return;

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i>Génération…');
        showLoader('Génération des codes…', '[' + ecueCode + '] ' + ecueLibelle);

        $.ajax({
            url: 'traitement_bulk',
            method: 'POST',
            data: {
                ecue: ecueCode,
                classe: classe,
                semestre: semestre,
                examen: 'Session de Rappel',
                nature: nature,
                annee: annee,
                source: 'rappel'      // indicateur pour traitement_bulk
            },
            success: function (res) {
                hideLoader();
                showToast('✅ Codes générés pour [' + ecueCode + '] ' + ecueLibelle, 'success');
                // Relancer la recherche pour rafraîchir
                setTimeout(function () { $('#btnRechercher').click(); }, 800);
            },
            error: function () {
                hideLoader();
                $btn.prop('disabled', false).html('<i class="fa fa-magic mr-1"></i>Générer');
                showToast('Erreur lors de la génération pour ' + ecueCode, 'error');
            }
        });
    });

    /* ──────────────────────────────────────────────
       GÉNÉRATION GLOBALE (toutes les ECUE manquantes)
    ────────────────────────────────────────────── */
    $('#btnGenAll').on('click', function () {
        if (!currentData || !currentData.ecues) return;

        var aGenerer = currentData.ecues.filter(function (e) {
            return e.etudiants.some(function (et) { return !et.code_existant; });
        });

        if (aGenerer.length === 0) {
            showToast('Tous les codes sont déjà générés !', 'success');
            return;
        }

        if (!confirm('Générer les codes pour ' + aGenerer.length + ' matière(s) ?\nTous les ajournés sans code recevront un code pour la Session de Rappel.')) return;

        var classe   = $('#f_classe').val();
        var semestre = $('#f_semestre').val();
        var annee    = $('#f_annee').val();
        var nature   = $('#f_nature').val();

        showLoader('Génération globale…', '0 / ' + aGenerer.length + ' matières traitées');

        var idx = 0;

        function processNext() {
            if (idx >= aGenerer.length) {
                hideLoader();
                showToast('✅ Génération terminée pour ' + aGenerer.length + ' matière(s) !', 'success');
                setTimeout(function () { $('#btnRechercher').click(); }, 800);
                return;
            }
            var ecue = aGenerer[idx];
            $('#loaderSub').text((idx + 1) + ' / ' + aGenerer.length + ' – [' + ecue.code + '] ' + ecue.libelle);

            $.ajax({
                url: 'traitement_bulk',
                method: 'POST',
                data: {
                    ecue: ecue.code,
                    classe: classe,
                    semestre: semestre,
                    examen: 'Session de Rappel',
                    nature: nature,
                    annee: annee,
                    source: 'rappel'
                },
                success: function () { idx++; processNext(); },
                error: function () {
                    showToast('Erreur sur [' + ecue.code + '] – passage à la suivante', 'error');
                    idx++; processNext();
                }
            });
        }

        processNext();
    });

    /* ──────────────────────────────────────────────
       IMPRESSION GLOBALE
    ────────────────────────────────────────────── */
    $('#btnPrintAll').on('click', function () {
        if (!currentData || !currentData.ecues) return;

        var classe   = $('#f_classe').val();
        var semestre = $('#f_semestre').val();
        var annee    = $('#f_annee').val();
        var nature   = $('#f_nature').val();

        var printContent = buildPrintPage(currentData.ecues, classe, semestre, annee, nature);
        var win = window.open('', '_blank');
        win.document.write(printContent);
        win.document.close();
        win.onload = function () { win.focus(); win.print(); };
    });

    /* ──────────────────────────────────────────────
       RESET
    ────────────────────────────────────────────── */
    $('#btnReset').on('click', function () {
        $('#resultsSection').hide();
        $('#emptyState').hide();
        currentData = null;
        $('#btnReset').hide();
    });

    /* ──────────────────────────────────────────────
       CONSTRUCTION PAGE IMPRESSION
    ────────────────────────────────────────────── */
    function buildPrintPage(ecues, classe, semestre, annee, nature) {
        var rows = '';
        var total = 0;
        ecues.forEach(function (ecue) {
            ecue.etudiants.forEach(function (et, i) {
                total++;
                rows += '<tr>' +
                    '<td style="text-align:center">' + total + '</td>' +
                    '<td>' + escHtml(et.nom) + '</td>' +
                    '<td>' + escHtml(et.prenom) + '</td>' +
                    '<td>' + escHtml(et.matricule) + '</td>' +
                    '<td style="text-align:center">' + et.moyenne + '</td>' +
                    '<td>[' + escHtml(ecue.code) + '] ' + escHtml(ecue.libelle) + '</td>' +
                    '<td style="text-align:center; font-weight:bold; color:#c0392b; font-size:15px;">' +
                        (et.code_existant || '–') +
                    '</td>' +
                    '</tr>';
            });
        });

        return '<!DOCTYPE html><html><head><meta charset="UTF-8">' +
            '<title>Codes Rappel – ' + classe + '</title>' +
            '<style>body{font-family:Arial,sans-serif;margin:20px;font-size:11pt}' +
            'h1{text-align:center;font-size:16pt;margin-bottom:4px}' +
            '.info{background:#f5f5f5;padding:12px;border-radius:4px;margin-bottom:16px;font-size:10pt}' +
            'table{width:100%;border-collapse:collapse}' +
            'th{background:#2c3e50;color:#fff;padding:8px;text-align:left;font-size:10pt}' +
            'td{border:1px solid #ddd;padding:7px;font-size:10pt}' +
            'tr:nth-child(even){background:#f9f9f9}' +
            '@media print{@page{size:A4 landscape;margin:12mm}}' +
            '</style></head><body>' +
            '<h1>Liste des Codes Anonymes – Session de Rappel</h1>' +
            '<div class="info">' +
            '<strong>Classe :</strong> ' + classe + ' &nbsp;|&nbsp; ' +
            '<strong>Semestre :</strong> ' + semestre + ' &nbsp;|&nbsp; ' +
            '<strong>Année :</strong> ' + annee + ' &nbsp;|&nbsp; ' +
            '<strong>Nature :</strong> ' + nature + ' &nbsp;|&nbsp; ' +
            '<strong>Total :</strong> ' + total + ' étudiant(s)' +
            '</div>' +
            '<table><thead><tr>' +
            '<th style="width:40px">#</th><th>Nom</th><th>Prénom</th>' +
            '<th>Matricule</th><th style="width:80px">Moyenne</th>' +
            '<th>Matière (ECUE)</th><th style="width:110px">Code Anonyme</th>' +
            '</tr></thead><tbody>' + rows + '</tbody></table>' +
            '<p style="text-align:center;margin-top:20px;font-size:9pt;color:#888">' +
            'Imprimé le ' + new Date().toLocaleString('fr-FR') + ' – CETUP</p>' +
            '<script>window.addEventListener("load",function(){setTimeout(function(){window.print();},300);});<\/script>' +
            '</body></html>';
    }

    /* ──────────────────────────────────────────────
       UTILITAIRE : escape HTML
    ────────────────────────────────────────────── */
    function escHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

});
</script>

</body>
</html>
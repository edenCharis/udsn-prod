<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="gesnote"){


    if(isset($_GET['sup'])){
        $id = $_GET['sup'];

        $sql="delete from notation where id=$id";

        if($connexion->query($sql)){
            logUserAction($connexion,$_SESSION['id_user'],"supression d'une note ",date("Y-m-d H:i:s"),$userIP,"valeur suprimée :$id");
            header("location: notation?sucess=supression effectuée avec success");
        }else{
            header("location: notation?erreur=$connexion->error" );
        }
    }
    
    if(isset($_GET["voir"])){
        $classe = $_GET["classe"];
        $semestre=$_GET["semestre"];
        $annee=$_GET["annee"];
        $type =$_GET["type"];
        $ecue =$_GET["ecue"];
        $niveau = NiveauParSemestre($semestre);
        
        if( $type =="devoir"){
            header("location: voir_devoir?classe=$classe&ecue=$ecue&semestre=$semestre&type=$type&niveau=$niveau&annee=$annee&etablissement=".$_SESSION["etablissement"]);
        }else if($type =="examen"){
            header("location: voir_examen?classe=$classe&ecue=$ecue&semestre=$semestre&type=$type&niveau=$niveau&annee=$annee&etablissement=".$_SESSION["etablissement"]);
        }
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $_SESSION['univ'];?> - Scolarité de <?php echo $_SESSION['etablissement'];?></title>
    <link rel="icon" type="image/png" sizes="16x16" href="../administrateur/<?php echo $_SESSION['logo_univ']?>">
    <link rel="stylesheet" href="../vendor/bootstrap-select/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="../vendor/select2/css/select2.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/skin.css">
    <link href="../vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
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
                            <li class="breadcrumb-item"><a href="../scolarite/">Scolarité</a></li>
                            <li class="breadcrumb-item"><a href="inscription">Etudiants</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0);">Note</a></li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 text-center">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Liste des notes</h4>
                            </div>
                            <div class="card-body">

                                <!-- Formulaire de filtres -->
                                <form method="GET" action="" class="mb-4">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="filtre_classe">Filtrer par Classe</label>
                                                <select name="filtre_classe" id="filtre_classe" class="form-control disabling-options">
                                                    <option value="">-- Toutes les classes --</option>
                                                    <?php 
                                                        $sql_classes = "SELECT DISTINCT classe FROM notation WHERE etab='".$_SESSION['etablissement']."' ORDER BY classe";
                                                        $resultat_classes = $connexion->query($sql_classes);
                                                        while($classe_opt = $resultat_classes->fetch_assoc()){
                                                    ?>
                                                        <option value="<?php echo $classe_opt['classe'];?>" 
                                                            <?php echo (isset($_GET['filtre_classe']) && $_GET['filtre_classe'] == $classe_opt['classe']) ? 'selected' : ''; ?>>
                                                            <?php echo $classe_opt['classe'];?>
                                                        </option>
                                                    <?php }?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="filtre_ecue">Filtrer par ECUE</label>
                                                <select name="filtre_ecue" id="filtre_ecue" class="form-control disabling-options">
                                                    <option value="">-- Toutes les ECUE --</option>
                                                    <?php 
                                                        $sql_ecues = "SELECT DISTINCT code_ecue, ecue FROM notation WHERE etab='".$_SESSION['etablissement']."' ORDER BY ecue ASC";
                                                        $resultat_ecues = $connexion->query($sql_ecues);
                                                        while($ecue_opt = $resultat_ecues->fetch_assoc()){
                                                    ?>
                                                        <option value="<?php echo $ecue_opt['code_ecue'];?>" 
                                                            <?php echo (isset($_GET['filtre_ecue']) && $_GET['filtre_ecue'] == $ecue_opt['code_ecue']) ? 'selected' : ''; ?>>
                                                            <?php echo $ecue_opt["ecue"]." - ".$ecue_opt["code_ecue"];?>
                                                        </option>
                                                    <?php }?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="filtre_annee">Filtrer par Année</label>
                                                <select name="filtre_annee" id="filtre_annee" class="form-control disabling-options">
                                                    <option value="">-- Toutes les années --</option>
                                                    <?php 
                                                        $sql_annees = "SELECT DISTINCT annee FROM notation WHERE etab='".$_SESSION['etablissement']."' ORDER BY annee DESC";
                                                        $resultat_annees = $connexion->query($sql_annees);
                                                        while($annee_opt = $resultat_annees->fetch_assoc()){
                                                    ?>
                                                        <option value="<?php echo $annee_opt['annee'];?>" 
                                                            <?php echo (isset($_GET['filtre_annee']) && $_GET['filtre_annee'] == $annee_opt['annee']) ? 'selected' : ''; ?>>
                                                            <?php echo $annee_opt['annee'];?>
                                                        </option>
                                                    <?php }?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>&nbsp;</label><br>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fa fa-filter"></i> Filtrer
                                                </button>
                                                <a href="notation" class="btn btn-secondary">
                                                    <i class="fa fa-refresh"></i> Réinitialiser
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </form>

                                <div class="table-responsive">
                                    <table id="example3" class="display" style="min-width: 845px">
                                        <thead>
                                            <tr>
                                                <th>N°</th>
                                                <th>Etudiant</th>
                                                <th>Ecue</th>
                                                <th>Classe</th>
                                                <th>Semestre</th>
                                                <th>Moy. Devoir</th>
                                                <th>Note Examen</th>
                                                <th>Session Rappel</th>
                                                <th>Moyenne Gén.</th>
                                                <th>Année</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php 
                                            $sql = "SELECT * FROM notation WHERE etab='".$_SESSION['etablissement']."'";
                                            
                                            if(isset($_GET['filtre_classe']) && !empty($_GET['filtre_classe'])){
                                                $filtre_classe = $connexion->real_escape_string($_GET['filtre_classe']);
                                                $sql .= " AND classe = '$filtre_classe'";
                                            }
                                            if(isset($_GET['filtre_ecue']) && !empty($_GET['filtre_ecue'])){
                                                $filtre_ecue = $connexion->real_escape_string($_GET['filtre_ecue']);
                                                $sql .= " AND code_ecue = '$filtre_ecue'";
                                            }
                                            if(isset($_GET['filtre_annee']) && !empty($_GET['filtre_annee'])){
                                                $filtre_annee = $connexion->real_escape_string($_GET['filtre_annee']);
                                                $sql .= " AND annee = '$filtre_annee'";
                                            }
                                            $sql .= " ORDER BY id DESC";
                                            $resultat = $connexion->query($sql);
                                            
                                            if($resultat->num_rows > 0){
                                                while($ue = $resultat->fetch_assoc()){
                                        ?>
                                            <tr id="row-<?php echo $ue['id']; ?>">
                                                <td><?php echo $ue['id'];?></td>
                                                <td><?php echo str_replace("+","'", obtenirNomPrenom(obtenirCodeById($ue['inscription'],$connexion),$ue['annee'],$connexion));?></td>
                                                <td><?php echo str_replace("+","'",$ue['ecue']);?></td>
                                                <td><?php echo $ue['classe'];?></td>
                                                <td><?php echo $ue['semestre'];?></td>
                                                <td class="cell-moyDev"><?php echo ($ue['moyDev'] !== null) ? round($ue['moyDev'],2) : '-';?></td>
                                                <td class="cell-moyEx"><?php echo ($ue['moyEx'] !== null) ? round($ue['moyEx'],2) : '-';?></td>
                                                <td class="cell-rappel"><?php echo ($ue['session_rappel'] !== null) ? $ue['session_rappel'] : '-';?></td>
                                                <td class="cell-moyGen"><?php echo ($ue['moyGen'] !== null) ? round($ue['moyGen'],2) : '-';?></td>
                                                <td><?php echo $ue['annee'];?></td>
                                                <td>
                                                    <!-- Bouton Modifier -->
                                                    <button class="btn btn-warning btn-sm btn-modifier-note"
                                                        data-id="<?php echo $ue['id'];?>"
                                                        data-moydev="<?php echo $ue['moyDev'];?>"
                                                        data-moyex="<?php echo $ue['moyEx'];?>"
                                                        data-rappel="<?php echo $ue['session_rappel'];?>"
                                                        data-etudiant="<?php echo str_replace("+","'", obtenirNomPrenom(obtenirCodeById($ue['inscription'],$connexion),$ue['annee'],$connexion));?>"
                                                        data-ecue="<?php echo htmlspecialchars($ue['ecue']);?>"
                                                        data-classe="<?php echo $ue['classe'];?>"
                                                        data-semestre="<?php echo $ue['semestre'];?>"
                                                        data-annee="<?php echo $ue['annee'];?>">
                                                        <i class="fa fa-pencil"></i> Modifier
                                                    </button>

                                                    <?php if($ue['moyDev'] === null || $ue['moyEx'] === null) { ?>
                                                    <a class="btn btn-success btn-sm btn-actualiser-note" 
                                                        data-id="<?php echo $ue['id'];?>"
                                                        data-etudiant="<?php echo $ue['inscription'];?>"
                                                        data-ecue="<?php echo $ue['code_ecue'];?>"
                                                        data-semestre="<?php echo $ue['semestre'];?>"
                                                        data-annee="<?php echo $ue['annee'];?>"
                                                        data-classe="<?php echo $ue['classe'];?>">
                                                        <i class="fa fa-refresh"></i> Actualiser
                                                    </a>
                                                    <?php } ?>

                                                    <a href="?sup=<?php echo $ue['id'];?>" 
                                                        class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette note ?');">
                                                        <i class="fa fa-trash"></i> Supprimer
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php 
                                                }
                                            } else {
                                        ?>
                                            <tr>
                                                <td colspan="11" class="text-center">
                                                    <div class="alert alert-info">Aucune note trouvée.</div>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="footer">
            <div class="copyright">
                <p>Copyright © Designed &amp; Developpé par <a href="https://www.cet-up.com" target="_blank">CETUP</a> 2023</p>
            </div>
        </div>

    </div>

    <!-- ══════════════════════════════════════════
         MODAL MODIFICATION NOTE
    ══════════════════════════════════════════ -->
    <div class="modal fade" id="modalModifierNote" tabindex="-1" role="dialog" aria-labelledby="modalModifierNoteLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="modalModifierNoteLabel">
                        <i class="fa fa-pencil"></i> Modifier la note
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <!-- Infos lecture seule -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fa fa-user"></i> Étudiant</label>
                                <input type="text" id="modal_etudiant" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fa fa-book"></i> ECUE</label>
                                <input type="text" id="modal_ecue" class="form-control" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><i class="fa fa-users"></i> Classe</label>
                                <input type="text" id="modal_classe" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><i class="fa fa-list"></i> Semestre</label>
                                <input type="text" id="modal_semestre" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><i class="fa fa-calendar"></i> Année</label>
                                <input type="text" id="modal_annee" class="form-control" readonly>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Notes modifiables -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><i class="fa fa-pencil-square"></i> Moyenne Devoir</label>
                                <input type="number" step="0.01" min="0" max="20"
                                    id="modal_moyDev" class="form-control form-control-lg"
                                    placeholder="0.00">
                                <small class="text-muted">Entre 0 et 20</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><i class="fa fa-file-text"></i> Note Examen</label>
                                <input type="number" step="0.01" min="0" max="20"
                                    id="modal_moyEx" class="form-control form-control-lg"
                                    placeholder="0.00">
                                <small class="text-muted">Entre 0 et 20</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><i class="fa fa-repeat"></i> Session de Rappel</label>
                                <input type="number" step="0.01" min="0" max="20"
                                    id="modal_rappel" class="form-control form-control-lg"
                                    placeholder="0.00">
                                <small class="text-muted">Laisser vide si non concerné</small>
                            </div>
                        </div>
                    </div>

                    <!-- Aperçu moyenne calculée -->
                    <div class="alert alert-info mt-2" id="apercu_moyenne" style="display:none;">
                        <i class="fa fa-calculator"></i>
                        Moyenne calculée (CC + EX) / 2 = <strong id="apercu_valeur">-</strong>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times"></i> Annuler
                    </button>
                    <button type="button" class="btn btn-warning" id="btn_confirmer_modif">
                        <i class="fa fa-save"></i> Enregistrer les modifications
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- FIN MODAL -->

    <!-- Modal messages -->
    <div class="modal" id="messageModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo $_SESSION['univ'];?></h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="messageBody"></div>
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
    <script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../js/plugins-init/datatables.init.js"></script>

    <script>
    $(document).ready(function() {

        // ── Messages URL ─────────────────────────────────────
        var urlParams = new URLSearchParams(window.location.search);
        var erreur  = urlParams.get('erreur');
        var success = urlParams.get('sucess');
        if (erreur || success) {
            var message = erreur ? "Erreur : " + erreur : "Message : " + success;
            $('#messageBody').text(message);
            $('#messageModal').modal('show');
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        // ══════════════════════════════════════════════════════
        // OUVERTURE DU MODAL MODIFIER
        // ══════════════════════════════════════════════════════
        var noteIdEnCours = null;

        $(document).on('click', '.btn-modifier-note', function() {
            var btn = $(this);
            noteIdEnCours = btn.data('id');

            // Remplir les infos lecture seule
            $('#modal_etudiant').val(btn.data('etudiant'));
            $('#modal_ecue').val(btn.data('ecue'));
            $('#modal_classe').val(btn.data('classe'));
            $('#modal_semestre').val(btn.data('semestre'));
            $('#modal_annee').val(btn.data('annee'));

            // Remplir les notes modifiables
            var moyDev = btn.data('moydev');
            var moyEx  = btn.data('moyex');
            var rappel = btn.data('rappel');

            $('#modal_moyDev').val(moyDev !== '' && moyDev !== null ? moyDev : '');
            $('#modal_moyEx').val(moyEx !== '' && moyEx !== null ? moyEx : '');
            $('#modal_rappel').val(rappel !== '' && rappel !== null ? rappel : '');

            // Calcul aperçu
            calculerApercu();

            $('#modalModifierNote').modal('show');
        });

        // ── Aperçu moyenne en temps réel ─────────────────────
        function calculerApercu() {
            var cc = parseFloat($('#modal_moyDev').val());
            var ex = parseFloat($('#modal_moyEx').val());
            if (!isNaN(cc) && !isNaN(ex)) {
                var moy = Math.round(((cc + ex) / 2) * 100) / 100;
                $('#apercu_valeur').text(moy + ' / 20');
                $('#apercu_moyenne').show();
            } else {
                $('#apercu_moyenne').hide();
            }
        }

        $('#modal_moyDev, #modal_moyEx').on('input', function() {
            calculerApercu();
        });

        // ══════════════════════════════════════════════════════
        // ENREGISTREMENT VIA AJAX
        // ══════════════════════════════════════════════════════
        $('#btn_confirmer_modif').on('click', function() {
            if (!noteIdEnCours) return;

            var moyDev = $('#modal_moyDev').val();
            var moyEx  = $('#modal_moyEx').val();
            var rappel = $('#modal_rappel').val();

            // Validation basique
            if (moyDev !== '' && (parseFloat(moyDev) < 0 || parseFloat(moyDev) > 20)) {
                afficherAlerte('danger', 'La moyenne devoir doit être entre 0 et 20.');
                return;
            }
            if (moyEx !== '' && (parseFloat(moyEx) < 0 || parseFloat(moyEx) > 20)) {
                afficherAlerte('danger', 'La note examen doit être entre 0 et 20.');
                return;
            }
            if (rappel !== '' && (parseFloat(rappel) < 0 || parseFloat(rappel) > 20)) {
                afficherAlerte('danger', 'La session de rappel doit être entre 0 et 20.');
                return;
            }

            var btn = $(this);
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Enregistrement...');

            $.ajax({
                url: 'modifier_note_ajax.php',
                type: 'POST',
                data: {
                    id_note  : noteIdEnCours,
                    moyDev   : moyDev,
                    moyEx    : moyEx,
                    rappel   : rappel
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Mettre à jour la ligne du tableau sans recharger
                        var row = $('#row-' + noteIdEnCours);
                        row.find('.cell-moyDev').text(response.data.moyDev !== null ? response.data.moyDev : '-');
                        row.find('.cell-moyEx').text(response.data.moyEx !== null ? response.data.moyEx : '-');
                        row.find('.cell-rappel').text(response.data.session_rappel !== null ? response.data.session_rappel : '-');
                        row.find('.cell-moyGen').text(response.data.moyGen !== null ? response.data.moyGen : '-');

                        // Mettre à jour les data-attributes du bouton modifier
                        row.find('.btn-modifier-note')
                            .data('moydev', response.data.moyDev)
                            .data('moyex', response.data.moyEx)
                            .data('rappel', response.data.session_rappel);

                        // Effet visuel
                        row.css('background-color', '#d4edda');
                        setTimeout(function() { row.css('background-color', ''); }, 3000);

                        $('#modalModifierNote').modal('hide');
                        afficherAlerte('success', '✓ Note modifiée avec succès !<br>' +
                            'Devoir: <strong>' + (response.data.moyDev ?? '-') + '</strong> | ' +
                            'Examen: <strong>' + (response.data.moyEx ?? '-') + '</strong> | ' +
                            'Rappel: <strong>' + (response.data.session_rappel ?? '-') + '</strong> | ' +
                            'Moyenne: <strong>' + (response.data.moyGen ?? '-') + '</strong>');
                    } else {
                        afficherAlerte('danger', '✗ Erreur : ' + response.message);
                    }
                    btn.prop('disabled', false).html('<i class="fa fa-save"></i> Enregistrer les modifications');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    afficherAlerte('danger', '✗ Erreur serveur lors de la modification.');
                    btn.prop('disabled', false).html('<i class="fa fa-save"></i> Enregistrer les modifications');
                }
            });
        });

        // ══════════════════════════════════════════════════════
        // ACTUALISER NOTE
        // ══════════════════════════════════════════════════════
        $(document).on('click', '.btn-actualiser-note', function(e) {
            e.preventDefault();
            var btn = $(this);
            var originalHtml = btn.html();
            var row = btn.closest('tr');

            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Actualisation...');

            $.ajax({
                url: 'actualiser_note_ajax.php',
                type: 'POST',
                data: {
                    id_note    : btn.data('id'),
                    inscription: btn.data('etudiant'),
                    code_ecue  : btn.data('ecue'),
                    semestre   : btn.data('semestre'),
                    annee      : btn.data('annee'),
                    classe     : btn.data('classe')
                },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        row.find('.cell-moyDev').text(response.data.moyDev);
                        row.find('.cell-moyEx').text(response.data.moyEx);
                        row.find('.cell-rappel').text(response.data.session_rappel);
                        row.find('.cell-moyGen').text(response.data.moyGen);
                        row.css('background-color', '#d4edda');
                        setTimeout(function() { row.css('background-color', ''); }, 3000);
                        afficherAlerte('success', '✓ Notes actualisées avec succès.');
                    } else {
                        afficherAlerte('danger', '✗ ' + response.message);
                    }
                    btn.prop('disabled', false).html(originalHtml);
                },
                error: function() {
                    afficherAlerte('danger', '✗ Erreur serveur lors de l\'actualisation.');
                    btn.prop('disabled', false).html(originalHtml);
                }
            });
        });

        // ── Fonction utilitaire : afficher une alerte flottante ──
        function afficherAlerte(type, message) {
            var html = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert" ' +
                'style="position:fixed;top:80px;right:20px;z-index:9999;min-width:320px;">' +
                message +
                '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                '</div>';
            $('body').append(html);
            setTimeout(function() {
                $('.alert-' + type).fadeOut(function() { $(this).remove(); });
            }, 5000);
        }

    });
    </script>

</body>
</html>

<?php 
} else {
    header("location: ../login");
}
?>
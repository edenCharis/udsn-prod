<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and $_SESSION['role']=="gesnote"){

// ── Préparer l'URL du relevé si le formulaire individuel est soumis ──
$releve_url = null;
if (isset($_GET['generer1']) && isset($_GET['etudiant']) && isset($_GET['classe']) && isset($_GET['annee'])) {
    if (verifierInscription($_GET["etudiant"], $_GET["annee"], $connexion)) {
        $sql = "SELECT id FROM inscription
                WHERE classe='".$_GET['classe']."'
                  AND annee='".$_GET['annee']."'
                  AND candidat='".$_GET["etudiant"]."'
                LIMIT 1";
        $res = $connexion->query($sql);
        if ($row = $res->fetch_object()) {
            $sem_param = (isset($_GET['semestre']) && $_GET['semestre'] !== '')
                ? '&semestre='.urlencode($_GET['semestre'])
                : '';
            $releve_url = "generation?ins_=" . $row->id . $sem_param;
        }
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

    <style>
        /* Modal plein écran pour le relevé */
        #releveModal .modal-dialog {
            max-width: 96vw;
            width: 96vw;
            margin: 1vh auto;
        }
        #releveModal .modal-content {
            height: 96vh;
            display: flex;
            flex-direction: column;
        }
        #releveModal .modal-body {
            flex: 1;
            padding: 0;
            overflow: hidden;
        }
        #releveModal .modal-header {
            background: #2c3e50;
            color: #fff;
            padding: 10px 16px;
        }
        #releveModal .modal-header .close {
            color: #fff;
            opacity: 1;
            font-size: 1.5rem;
        }
        #releveModal .modal-title {
            font-size: 1rem;
            font-weight: 600;
        }
        #releve-iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        .btn-imprimer-releve {
            background: #27ae60;
            color: #fff;
            border: none;
            padding: 5px 18px;
            border-radius: 4px;
            font-size: 0.9rem;
            cursor: pointer;
            margin-left: 10px;
        }
        .btn-imprimer-releve:hover { background: #1e8449; color:#fff; }
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
                        <div class="welcome-text"><h3>Relevés de note</h3></div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../scolarite/">Scolarité</a></li>
                            <li class="breadcrumb-item"><a href="inscription">Etudiants</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0);">Relevés</a></li>
                        </ol>
                    </div>
                </div>

                <div class="row">

                    <!-- ═══════════════════════════════════════
                         FORMULAIRE 1 : Liste d'admission (collectif)
                    ═══════════════════════════════════════ -->
                 

                    <!-- ═══════════════════════════════════════
                         FORMULAIRE 2 : Relevé individuel
                         → Ouvre directement le relevé en modal
                    ═══════════════════════════════════════ -->
                    <div class="col-md-12 text-center">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Relevé individuel</h4>
                            </div>
                            <div class="card-body">
                                <div class="basic-form">
                                    <form method="get">
                                        <div class="form-row">
                                            <div class="form-group col-sm-3">
                                                <label>Etudiant</label>
                                                <select class="form-control form-control-lg disabling-options" name="etudiant" required>
                                                    <option selected=""></option>
                                                    <?php
                                                    $sql = "SELECT * FROM inscription WHERE etab='".$_SESSION['etablissement']."' ORDER BY annee DESC";
                                                    $r   = $connexion->query($sql);
                                                    while ($x = $r->fetch_assoc()):
                                                    ?><option value="<?php echo $x['candidat']; ?>">
                                                        <?php echo str_replace("+","'", obtenirNomPrenom($x['candidat'], $x['annee'], $connexion)); ?>
                                                    </option><?php
                                                    endwhile; ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-sm-3">
                                                <label>Classe</label>
                                                <select class="form-control form-control-lg disabling-options" name="classe" required>
                                                    <option selected=""></option>
                                                    <?php
                                                    $sql = "SELECT * FROM classe WHERE etab='".$_SESSION['etablissement']."'";
                                                    $r   = $connexion->query($sql);
                                                    while ($x = $r->fetch_assoc()):
                                                    ?><option><?php echo str_replace("+","'",$x['libelle']); ?></option><?php
                                                    endwhile; ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-sm-3">
                                                <label>Semestre</label>
                                                <select class="form-control form-control-lg disabling-options" name="semestre">
                                                    <option value="">-- Tous --</option>
                                                    <?php
                                                    $sql = "SELECT * FROM semestre";
                                                    $r   = $connexion->query($sql);
                                                    while ($x = $r->fetch_assoc()):
                                                    ?><option><?php echo str_replace("+","'",$x['libelle']); ?></option><?php
                                                    endwhile; ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-sm-3">
                                                <label>Année académique</label>
                                                <select class="form-control form-control-lg disabling-options" name="annee" required>
                                                    <option selected=""></option>
                                                    <?php
                                                    $sql = "SELECT * FROM annee";
                                                    $r   = $connexion->query($sql);
                                                    while ($x = $r->fetch_assoc()):
                                                    ?><option><?php echo str_replace("+","'",$x['libelle']); ?></option><?php
                                                    endwhile; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-success" name="generer1">
                                            <i class="la la-file-text"></i> GENERER LE RELEVÉ
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div><!-- /row -->

                <!-- ═══════════════════════════════════════
                     RÉSULTATS FORMULAIRE 1 (liste collective)
                ═══════════════════════════════════════ -->
                <?php if (isset($_GET['generer']) && isset($_GET['classe']) && isset($_GET['semestre']) && isset($_GET['specialite']) && isset($_GET['annee'])): ?>
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">
                                <?php echo htmlspecialchars($_GET['specialite']); ?> –
                                <?php echo htmlspecialchars($_GET['semestre']); ?> –
                                <?php echo htmlspecialchars($_GET['annee']); ?>
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="example3" class="table table-bordered table-responsive-sm">
                                    <thead>
                                        <tr>
                                            <th>Matricule</th>
                                            <th>Etudiant</th>
                                            <th>Classe</th>
                                            <th>Moy. du <?php echo htmlspecialchars($_GET['semestre']); ?></th>
                                            <th>Observation</th>
                                            <th>Année</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $sql = "SELECT * FROM inscription WHERE classe='".$_GET['classe']."' AND annee='".$_GET['annee']."'";
                                    $req = $connexion->query($sql);
                                    while ($data = $req->fetch_object()):
                                        $statut  = "";
                                        $moyenne = calcul_moyenne($data->id, $_GET['semestre'], $_GET['annee'], $_SESSION['etablissement'], $connexion);
                                        if ($moyenne !== "-") $statut = statutSoutenance($moyenne);
                                        $ecues = rechercher_notes_eliminatoires($data->id, $_GET['semestre'], $_GET['annee'], $connexion);
                                        $_SESSION['ecue_non_valide'] = $ecues;
                                        $url_releve = "generation?ins_=".$data->id."&semestre=".urlencode($_GET['semestre']);
                                    ?>
                                    <tr>
                                        <th><?php echo getCandidatCodeByInscription($data->id, $connexion); ?></th>
                                        <td><?php echo obtenirNomPrenom(getCandidatCodeByInscription($data->id, $connexion), $_GET['annee'], $connexion); ?></td>
                                        <td><?php echo htmlspecialchars($_GET['classe']); ?></td>
                                        <td><?php echo $moyenne; ?></td>
                                        <td>
                                            <?php if ($statut != "") echo '<span class="badge badge-info">'.$statut.'</span> '; ?>
                                            <?php echo ($ecues == null) ? "" : "passe en rattrapage"; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($_GET['annee']); ?></td>
                                        <td>
                                            <!-- Bouton ouvre le relevé en modal -->
                                            <button class="btn btn-success btn-sm btn-open-releve"
                                                    data-url="<?php echo htmlspecialchars($url_releve); ?>">
                                                <i class="la la-file-text"></i> Relevé individuel
                                            </button>
                                            <a href="releve-classe?classe=<?php echo urlencode($_GET['classe']); ?>&annee=<?php echo urlencode($_GET['annee']); ?>&semestre=<?php echo urlencode($_GET['semestre']); ?>"
                                               class="btn btn-warning btn-sm" target="_blank">
                                                <i class="la la-list"></i> Relevé collectif
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php
                // Message si étudiant non trouvé (formulaire 2)
                if (isset($_GET['generer1']) && isset($_GET['etudiant']) && isset($_GET['classe']) && isset($_GET['annee'])) {
                    if (!verifierInscription($_GET["etudiant"], $_GET["annee"], $connexion)) {
                        echo '<div class="col-lg-12"><div class="alert alert-danger">L\'étudiant n\'a pas été trouvé pour cette classe et cette année.</div></div>';
                    }
                }
                ?>

            </div><!-- /container-fluid -->
        </div><!-- /content-body -->

        <!-- ═══════════════════════════════════════════════
             MODAL RELEVÉ  –  iframe plein écran
             S'ouvre automatiquement si $releve_url est défini
             Peut aussi être ouvert via .btn-open-releve
        ═══════════════════════════════════════════════ -->
        <div class="modal fade" id="releveModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="la la-file-text"></i>&nbsp; Relevé de Notes
                        </h5>
                        <button type="button"
                                class="btn-imprimer-releve"
                                onclick="document.getElementById('releve-iframe').contentWindow.print()">
                            🖨️ Imprimer
                        </button>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <iframe id="releve-iframe" src="about:blank"></iframe>
                    </div>
                </div>
            </div>
        </div>

        <!-- Autres modals -->
        <div class="modal fade" id="modifierModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">ECUEs non validés</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <?php if (isset($_SESSION['ecue_non_valide'])): foreach ((array)$_SESSION['ecue_non_valide'] as $e): echo $e."<br>"; endforeach; endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer">
            <div class="copyright">
                <p>Copyright © Conçu &amp; Développé par <a href="https://www.cet-up.com" target="_blank">CETUP</a> 2023</p>
            </div>
        </div>
    </div><!-- /main-wrapper -->

    <!-- Scripts -->
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

        // ── Fonction centrale : charger le relevé dans le modal ──
        function ouvrirReleve(url) {
            // Désactiver l'auto-print dans l'iframe pour ne pas imprimer automatiquement
            $('#releve-iframe').attr('src', url + '&no_autoprint=1');
            $('#releveModal').modal('show');
        }

        // ── Ouverture depuis la liste collective ──
        $(document).on('click', '.btn-open-releve', function () {
            ouvrirReleve($(this).data('url'));
        });

        // ── Ouverture automatique depuis le formulaire individuel ──
        <?php if ($releve_url): ?>
        ouvrirReleve("<?php echo addslashes($releve_url); ?>");
        <?php endif; ?>

        // ── Vider l'iframe à la fermeture du modal ──
        $('#releveModal').on('hidden.bs.modal', function () {
            $('#releve-iframe').attr('src', 'about:blank');
        });

        // ── Messages URL ──
        var urlParams = new URLSearchParams(window.location.search);
        var erreur  = urlParams.get('erreur');
        var success = urlParams.get('sucess');
        if (erreur || success) {
            alert(erreur ? "Erreur : " + erreur : success);
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });
    </script>

</body>
</html>

<?php } else { header("location: ../login"); } ?>
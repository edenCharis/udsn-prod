<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if($_SESSION['id'] == session_id() and $_SESSION['role']=="gesnote"){
    
    // Vérifier si l'ID est passé en paramètre
    if(!isset($_GET['id']) || empty($_GET['id'])){
        header("location: index?erreur=ID de note non spécifié");
        exit();
    }
    
    $id_note = $connexion->real_escape_string($_GET['id']);
    
    // Récupérer les informations de la note
    $sql = "SELECT * FROM notation WHERE id='$id_note' AND etab='".$_SESSION['etablissement']."'";
    $resultat = $connexion->query($sql);
    
    if($resultat->num_rows == 0){
        header("location: index?erreur=Note introuvable");
        exit();
    }
    
    $note = $resultat->fetch_assoc();
    
    // Récupérer les informations de l'étudiant
    $codeCandidat = obtenirCodeById($note['inscription'], $connexion);
    $nomEtudiant = obtenirNomPrenom($codeCandidat, $note['annee'], $connexion);
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $_SESSION['univ'];?> - Scolarité de  <?php echo $_SESSION['etablissement'];?> </title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../administrateur/<?php echo $_SESSION['logo_univ']?>">
    <link rel="stylesheet" href="../vendor/bootstrap-select/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="../vendor/select2/css/select2.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/skin.css">
</head>
<body>

    <!--*******************
        Preloader start
    ********************-->
    <div id="preloader">
        <div class="sk-three-bounce">
            <div class="sk-child sk-bounce1"></div>
            <div class="sk-child sk-bounce2"></div>
            <div class="sk-child sk-bounce3"></div>
        </div>
    </div>
    <!--*******************
        Preloader end
    ********************-->

    <!--**********************************
        Main wrapper start
    ***********************************-->
    <div id="main-wrapper">

        <!--**********************************
            Nav header start
        ***********************************-->
    
        <!--**********************************
            Nav header end
        ***********************************-->

        <!--**********************************
            Header start
        ***********************************-->
        <?php include "header.php"; ?>
        <!--**********************************
            Header end ti-comment-alt
        ***********************************-->

        <!--**********************************
            Sidebar start
        ***********************************-->
        <?php include 'nav.html'; ?>
        <!--**********************************
            Sidebar end
        ***********************************-->
        
        <!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <!-- row -->
            <div class="container-fluid">
                
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h3>Modification de note</h3>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../scolarite/">Scolarité</a></li>
                            <li class="breadcrumb-item"><a href="index">Notes</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0);">Modifier</a></li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Modifier les informations de la note</h4>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="traitement_modification">
                                    <input type="hidden" name="id_note" value="<?php echo $note['id'];?>">
                                    
                                    <!-- Informations de l'étudiant (lecture seule) -->
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <label class="input-group-text">Étudiant</label>
                                        </div>
                                        <input type="text" class="form-control form-control-lg" value="<?php echo str_replace('+', "'", $nomEtudiant);?>" readonly>
                                    </div>

                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <label class="input-group-text">Année Académique</label>
                                        </div>
                                        <input type="text" class="form-control form-control-lg" value="<?php echo $note['annee'];?>" readonly>
                                    </div>

                                    <!-- Classe -->
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <label class="input-group-text">Classe </label>
                                        </div>
                                        <select name="classe" class="disabling-options form-control form-control-lg" required>
                                            <option value="">-- Sélectionner --</option>
                                            <?php 
                                                $sql_classes = "SELECT * FROM classe WHERE etab='".$_SESSION['etablissement']."'";
                                                $resultat_classes = $connexion->query($sql_classes);
                                                while($classe = $resultat_classes->fetch_assoc()){
                                            ?>
                                                <option value="<?php echo $classe['libelle'];?>" 
                                                    <?php echo ($note['classe'] == $classe['libelle']) ? 'selected' : ''; ?>>
                                                    <?php echo str_replace("+","'",$classe['libelle']);?>
                                                </option>
                                            <?php }?>
                                        </select>
                                    </div>
                                    
                                    <!-- ECUE -->
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <label class="input-group-text">Ecue </label>
                                        </div>
                                        <select name="ecue" class="disabling-options form-control form-control-lg" required>
                                            <option value="">-- Sélectionner --</option>
                                            <?php 
                                                $sql_ecues = "SELECT * FROM ecue WHERE etab='".$_SESSION['etablissement']."'";
                                                $resultat_ecues = $connexion->query($sql_ecues);
                                                while($ecue = $resultat_ecues->fetch_assoc()){
                                            ?>
                                                <option value="<?php echo $ecue['libelle'];?>" 
                                                    <?php echo ($note['ecue'] == $ecue['libelle']) ? 'selected' : ''; ?>>
                                                    <?php echo str_replace("+","'",$ecue['libelle']);?>
                                                </option>
                                            <?php }?>
                                        </select>
                                    </div>

                                    <!-- Semestre -->
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <label class="input-group-text">Semestre </label>
                                        </div>
                                        <select name="semestre" class="disabling-options form-control form-control-lg" required>
                                            <option value="">-- Sélectionner --</option>
                                            <?php 
                                                $sql_semestres = "SELECT * FROM semestre";
                                                $resultat_semestres = $connexion->query($sql_semestres);
                                                while($semestre = $resultat_semestres->fetch_assoc()){
                                            ?>
                                                <option value="<?php echo $semestre['libelle'];?>" 
                                                    <?php echo ($note['semestre'] == $semestre['libelle']) ? 'selected' : ''; ?>>
                                                    <?php echo str_replace("+","'",$semestre['libelle']);?>
                                                </option>
                                            <?php }?>
                                        </select>
                                    </div>

                                    <!-- Moyenne Devoir -->
                                    <div class="form-group">
                                        <label for="moyDev">Moyenne Générale Devoir</label>
                                        <input type="number" step="0.01" min="0" max="20" class="form-control" 
                                               id="moyDev" name="moyDev" value="<?php echo $note['moyDev'];?>">
                                    </div>
                                    
                                    <!-- Note Examen -->
                                    <div class="form-group">
                                        <label for="moyEx">Note Examen</label>
                                        <input type="number" step="0.01" min="0" max="20" class="form-control" 
                                               id="moyEx" name="moyEx" value="<?php echo $note['moyEx'];?>">
                                    </div>
                                    
                                    <!-- Session de Rappel -->
                                    <div class="form-group">
                                        <label for="session_rappel">Session de Rappel</label>
                                        <input type="number" step="0.01" min="0" max="20" class="form-control" 
                                               id="session_rappel" name="session_rappel" value="<?php echo $note['session_rappel'];?>">
                                    </div>

                                    <!-- Boutons -->
                                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                                    <a href="index" class="btn btn-danger">Annuler</a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        <!--**********************************
            Content body end
        ***********************************-->

        <!-- Modal pour les messages -->
        <div class="modal" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="messageModalLabel"><?php echo $_SESSION['univ'];?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="messageBody">
                        <!-- Contenu du message -->
                    </div>
                </div>
            </div>
        </div>

        <!--**********************************
            Footer start
        ***********************************-->
        <div class="footer">
            <div class="copyright">
                <p>Copyright © Designed &amp; Developpé par <a href="htpps:/www.cet-up.com" target="_blank">CETUP</a> 2023</p>
            </div>
        </div>
        <!--**********************************
            Footer end
        ***********************************-->

    </div>
    <!--**********************************
        Main wrapper end
    ***********************************-->

    <!--**********************************
        Scripts
    ***********************************-->
    <!-- Required vendors -->
    <script src="../vendor/global/global.min.js"></script>
    <script src="../vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="../js/custom.min.js"></script>
    
    <!-- Chart Morris plugin files -->
    <script src="../vendor/raphael/raphael.min.js"></script>
    <script src="../vendor/morris/morris.min.js"></script>
    
    <script src="../vendor/select2/js/select2.full.min.js"></script>
    <script src="../js/plugins-init/select2-init.js"></script>
    <!-- Chart piety plugin files -->
    <script src="../vendor/peity/jquery.peity.min.js"></script>
    
    <!-- Demo scripts -->
    <script src="../js/dashboard/dashboard-2.js"></script>
    
    <!-- Svganimation scripts -->
    <script src="../vendor/svganimation/vivus.min.js"></script>
    <script src="../vendor/svganimation/svg.animation.js"></script>

    <script>
        $(document).ready(function() {
            // Vérifier si les attributs "erreur" ou "success" sont présents dans l'URL
            var urlParams = new URLSearchParams(window.location.search);
            var erreur = urlParams.get('erreur');
            var success = urlParams.get('sucess');

            // Afficher le modal si l'un des attributs est présent
            if (erreur || success) {
                var message = erreur ? "Erreur : " + erreur : "Message : " + success;
                $('#messageBody').text(message);
                $('#messageModal').modal('show');

                // Effacer les attributs de l'URL
                window.history.replaceState({}, document.title, window.location.pathname);
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
<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if($_SESSION['id'] == session_id() and $_SESSION['role'] == "scolarité") {
    
    $message_erreur = '';
    $message_succes = '';
    
    // ========== TRAITEMENT DE LA MODIFICATION ==========
    if(isset($_GET['action']) && $_GET['action'] == 'modifier') {
        
        try {
            // Récupérer et valider les données
            $ecueId = trim($_GET['ecueId'] ?? '');
            $nouveauCode = trim($_GET['nouveauCode'] ?? '');
            $nouveauEcue = trim($_GET['nouveauEcue'] ?? '');
            $nouveauvhcm = $_GET['nouveauvhcm'] ?? '';
            $nouveauvhtd = $_GET['nouveauvhtd'] ?? '';
            $nouveauvhtp = $_GET['nouveauvhtp'] ?? '';
            $nouveaucredit = $_GET['nouveaucredit'] ?? '';
            $nouveauUe = trim($_GET['nouveauUe'] ?? '');
            
           
                // Convertir en float
                $nouveauvhcm = floatval($nouveauvhcm);
                $nouveauvhtd = floatval($nouveauvhtd);
                $nouveauvhtp = floatval($nouveauvhtp);
                $nouveaucredit = floatval($nouveaucredit);
                
                
                

                        
                        // Mettre à jour l'ECUE
                        $update_stmt = $connexion->prepare("
                            UPDATE ecue 
                            SET code_ecue = ?, 
                                libelle = ?, 
                                vhcm = ?, 
                                vhtd = ?, 
                                vhtp = ?, 
                                credit = ?, 
                                ue = ? 
                            WHERE id = ? 
                            AND etab = ?
                        ");
                        
                        $update_stmt->bind_param(
                            'ssddddsis',
                            $nouveauCode,
                            $nouveauEcue,
                            $nouveauvhcm,
                            $nouveauvhtd,
                            $nouveauvhtp,
                            $nouveaucredit,
                            $nouveauUe,
                            $ecueId,
                            $_SESSION['etablissement']
                        );
                        
                        if($update_stmt->execute()) {
                            // Logger l'action
                            $userIP = $_SERVER['REMOTE_ADDR'];
                            $action = "Modification ECUE: $nouveauCode - $nouveauEcue";
                            logUserAction($connexion, $_SESSION['id_user'], $action, date("Y-m-d H:i:s"), $userIP, "ECUE ID: $ecueId");
                            
                            $message_succes = "ECUE modifié avec succès";
                            
                            // Redirection après 2 secondes
                            echo "<script>
                                setTimeout(function() {
                                    window.location.href = 'ecue';
                                }, 2000);
                            </script>";
                            
                        } else {
                            $message_erreur = "Erreur lors de la modification: " . $update_stmt->error;
                        }
                        
                        $update_stmt->close();
                    
                
            
            
        } catch (Exception $e) {
            $message_erreur = "Erreur: " . $e->getMessage();
        }
    }
    // ========== FIN DU TRAITEMENT ==========
    
    if(isset($_GET["id"])) {
?>

<!DOCTYPE html>
<html lang="en">

<head>
	
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $_SESSION['univ'];?> - Scolarité de  <?php echo $_SESSION['etablissement'];?> </title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../administrateur/<?php echo  $_SESSION['logo_univ']?>">
	<link rel="stylesheet" href="../vendor/bootstrap-select/dist/css/bootstrap-select.min.css">
	<link rel="stylesheet" href="../vendor/select2/css/select2.min.css">
    <link rel="stylesheet" href="../css/style.css">
	<link rel="stylesheet" href="../css/skin.css">
    <link href="../vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">

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
        <?php include "header.php" ;?>
        <!--**********************************
            Header end ti-comment-alt
        ***********************************-->

        <!--**********************************
            Sidebar start
        ***********************************-->
       <?php 
         include 'nav.html';
       ?>
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
                            <h3>Modifier un ECUE</h3>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../scolarite/">Scolarité</a></li>
                            <li class="breadcrumb-item"><a href="../scolarite/ue">UE</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0);">ECUES</a></li>
                        </ol>
                    </div>
                </div>
				
				<!-- Affichage des messages -->
				<?php if(!empty($message_succes)): ?>
				<div class="row">
				    <div class="col-lg-12">
				        <div class="alert alert-success alert-dismissible fade show">
				            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
				                <span aria-hidden="true">&times;</span>
				            </button>
				            <strong>Succès!</strong> <?php echo htmlspecialchars($message_succes); ?>
				        </div>
				    </div>
				</div>
				<?php endif; ?>
				
				<?php if(!empty($message_erreur)): ?>
				<div class="row">
				    <div class="col-lg-12">
				        <div class="alert alert-danger alert-dismissible fade show">
				            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
				                <span aria-hidden="true">&times;</span>
				            </button>
				            <strong>Erreur!</strong> <?php echo htmlspecialchars($message_erreur); ?>
				        </div>
				    </div>
				</div>
				<?php endif; ?>
				
				<div class="row">
					<div class="col-lg-12">
						<div class="card">
							<div class="card-header">
							</div>
							<div class="card-body">
							    <form method="get" action="">
                                    <!-- Champ action caché pour identifier la soumission -->
                                    <input type="hidden" name="action" value="modifier">
                                    <input type="hidden" name="ecueId" value="<?php echo htmlspecialchars($_GET['id'] ?? ''); ?>">
                                       <input type="hidden" name="ue" value="<?php echo htmlspecialchars($_GET['ue'] ?? ''); ?>">
                                    
                                    <div class="row">
                                        <div class="form-group col-md-4">
                                            <label for="nouveauCode">Code ECUE:</label>
                                            <input type="text" class="form-control" id="nouveauCode" name="nouveauCode" value="<?php echo htmlspecialchars($_GET['code_ecue'] ?? ''); ?>" required>
                                        </div>
<div class="form-group col-md-4">
    <label>ECUE:</label>
    <select class="form-control" name="nouveauEcue" required>
        <option value="">Sélectionnez un ECUE</option>
        <?php 
        $sql_ecue = "SELECT * FROM ecue WHERE etab='".$_SESSION['etablissement']."' ORDER BY libelle";
        $resultat_ecue = $connexion->query($sql_ecue);
        while ($ecue = $resultat_ecue->fetch_assoc()) {
            $selected = (isset($_GET['lib']) && $_GET['lib'] == $ecue['libelle']) ? 'selected' : '';
            echo "<option $selected value='".htmlspecialchars($ecue['libelle'])."'>".htmlspecialchars(str_replace("+", "'", $ecue['libelle']))."</option>";
        }
        ?>
    </select>
</div>
                                        
                                        <div class="form-group col-md-4">
                                            <label for="nouveauvhcm">VHCM :</label>
                                            <input type="number" class="form-control" id="nouveauvhcm" step="0.01" name="nouveauvhcm" value="<?php echo htmlspecialchars($_GET['vhcm'] ?? ''); ?>" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-4">
                                            <label for="nouveauvhtd">VHTD:</label>
                                            <input type="number" class="form-control" id="nouveauvhtd" step="0.01" name="nouveauvhtd" value="<?php echo htmlspecialchars($_GET['vhtd'] ?? ''); ?>" required>
                                        </div>
                                        
                                        <div class="form-group col-md-4">
                                            <label for="nouveaucredit">Credit:</label>
                                            <input type="number" class="form-control" id="nouveaucredit" step="0.01" name="nouveaucredit" value="<?php echo htmlspecialchars($_GET['cr'] ?? ''); ?>" required>
                                        </div>
                                        
                                        <div class="form-group col-md-4">
                                            <label for="nouveauvhtp">VHTP :</label>
                                            <input type="number" class="form-control" id="nouveauvhtp" step="0.01" name="nouveauvhtp" value="<?php echo htmlspecialchars($_GET['vhtp'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="form-group col-md-12">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text">UE</label>
                                            </div>
                                            <select id="nouveauUe" class="form-control disabling-options" name="nouveauUe" required>
                                                <option value="">Sélectionnez une UE</option>
                                                <?php 
                                                $sql = "SELECT * FROM ue WHERE etab='" . $_SESSION['etablissement'] . "'";
                                                $resultat = $connexion->query($sql);
                                                $ue_param = $_GET['ue'] ?? '';
                                                while ($etablissement = $resultat->fetch_assoc()) {
                                                    $option_value = htmlspecialchars($etablissement['libelle']."-".$etablissement['code']);
                                                    $option_value = str_replace("+", "'", $option_value);
                                                    $selected = ($ue_param == $etablissement['code']) ? 'selected' : '';
                                                    echo "<option $selected>$option_value</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-success">Sauvegarder</button>
                                    <button type="button" class="btn btn-danger" onclick="window.location.href='ecue'">Annuler</button>
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
        
        <div class="modal fade" id="printModal" tabindex="-1" role="dialog" aria-labelledby="printModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="printModalLabel">Informations d'impression</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="printForm">
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="parcours">UE</label>
                                <select class="disabling-options" id="ue" name="ue">
                                    <option value="">Sélectionnez une ue</option>
                                    <?php 
                                    $sql="select * from ue where etab='".$_SESSION['etablissement']."'";
                                    $resultat=$connexion->query($sql);
                                    while($etablissement =$resultat->fetch_assoc()){
                                    ?>
                                    <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                    <?php }?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="specialite">Spécialité</label>
                                <select id="specialite" class="disabling-options" name="specialite" required>
                                    <option value="">Sélectionnez une specialite</option>
                                    <?php 
                                    $sql="select * from specialite where etab='".$_SESSION['lib_etab']."'";
                                    $resultat=$connexion->query($sql);
                                    while($etablissement =$resultat->fetch_assoc()){
                                    ?>
                                    <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                    <?php }?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="semestre">Semestre</label>
                                <select class="form-control" id="semestre" name="semestre">
                                    <option value="">Sélectionnez un semestre</option>
                                    <?php 
                                    $sql="select * from semestre";
                                    $resultat=$connexion->query($sql);
                                    while($etablissement =$resultat->fetch_assoc()){
                                    ?>
                                    <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                    <?php }?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                            <button type="submit" class="btn btn-primary">Valider</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal" id="modifierModal" tabindex="-1" role="dialog" aria-labelledby="modifierModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modifierModalLabel">Modifier un ECUE</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Formulaire de modification -->
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="typeAgentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Enregistrement d'un ECUE</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="typeAgentForm" method="post" action="traitement1">
                            <div class="form-group">
                                <label for="code">Code ECUE :</label>
                                <input type="text" class="form-control" id="code" name="code" required>
                            </div>
                            <div class="form-group">
                                <label for="ecue">Libéllé ECUE :</label>
                                <input type="text" class="form-control" id="ecue" name="ecue" required>
                            </div>
                            <div class="form-group">
                                <label for="vhcm">VHCM :</label>
                                <input type="number" class="form-control" id="vhcm" step="0.01" name="vhcm" required>
                            </div>
                            <div class="form-group">
                                <label for="vhtp">VHTP :</label>
                                <input type="number" class="form-control" id="vhtp" step="0.01" name="vhtp" required>
                            </div>
                            <div class="form-group">
                                <label for="vhtd">VHTD:</label>
                                <input type="number" class="form-control" id="vhtd" step="0.01" name="vhtd" required>
                            </div>
                            <div class="form-group">
                                <label for="credit">Credit:</label>
                                <input type="number" class="form-control" id="credit" step="0.01" name="credit" required>
                            </div>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <label class="input-group-text">UE : </label>
                                </div>
                                <select id="ue" class="disabling-options" name="ue" required>
                                    <option selected=""></option>
                                    <?php 
                                    $sql="select * from ue where etab='".$_SESSION['etablissement']."'";
                                    $resultat=$connexion->query($sql);
                                    while($etablissement =$resultat->fetch_assoc()){
                                    ?>
                                    <option value="<?php echo str_replace("+","'",$etablissement["code"]) ?>"><?php echo str_replace("+","'",$etablissement['code']."  ( ".$etablissement["niveau"]."-".$etablissement["semestre"].")");?></option>
                                    <?php }?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                            <button type="button" data-dismiss="modal" class="btn btn-danger">Annuler</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

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
		
	<!-- Chart piety plugin files -->
    <script src="../vendor/peity/jquery.peity.min.js"></script>
    
    <script src="../vendor/select2/js/select2.full.min.js"></script>
    <script src="../js/plugins-init/select2-init.js"></script>
	
	<!-- Demo scripts -->
    <script src="../js/dashboard/dashboard-2.js"></script>
	
	<!-- Svganimation scripts -->
    <script src="../vendor/svganimation/vivus.min.js"></script>
    <script src="../vendor/svganimation/svg.animation.js"></script>
    <script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../js/plugins-init/datatables.init.js"></script>

    <script>
    // Gérer la soumission du formulaire d'impression
    document.getElementById('printForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const ue = document.getElementById('ue').value;
        const specialite = document.getElementById('specialite').value;
        const semestre = document.getElementById('semestre').value;

        const printUrl = `imprimer_ecue.php?ue=${ue}&specialite=${specialite}&semestre=${semestre}`;
        window.location.href = printUrl;
    });
    </script>
	
</body>
</html>

<?php 
    } else {
        header("location: ecue");
    }

} else {
    header("location: ../login");
}
?>



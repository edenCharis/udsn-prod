<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="anonymat"){


    if(isset($_GET['sup'])){
        $id = $_GET['sup'];

        $sql="delete from anonymat where id=$id";

        if($connexion->query($sql)){
            logUserAction($connexion,$_SESSION['id_user'],"supression d'une copie ",date("Y-m-d H:i:s"),$userIP,"valeur suprimée :$id");
         
            
            header("location: index?sucess=supression effectuée avec success");
        }else{
             
            header("location: index?erreur=$connexion->error" );
        }
    }

    // Handle bulk delete
    if(isset($_GET['delete_bulk'])){
        $ecue     = $_GET['ecue'];
        $classe   = $_GET['classe'];
        $semestre = $_GET['semestre'];
        $examen   = $_GET['examen'];
        $annee    = $_GET['annee'];
        $nature   = $_GET['nature'];

        $sql = "DELETE FROM anonymat 
                WHERE ecue='$ecue' 
                AND classe='$classe' 
                AND semestre='$semestre' 
                AND type='$examen' 
                AND annee='$annee' 
                AND nature='$nature'
                AND etab='".$_SESSION['etablissement']."'";
        
        if($connexion->query($sql)){
            $affected = $connexion->affected_rows;
            logUserAction($connexion,$_SESSION['id_user'],"suppression en masse de $affected copies",date("Y-m-d H:i:s"),$userIP,"ECUE:$ecue, Classe:$classe, Nature:$nature");
            header("location: index?sucess=$affected copies supprimées avec succès");
        }else{
            header("location: index?erreur=$connexion->error");
        }
    }



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
                            <h3>Anonymer les copies d'examens</h3>
                           
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                           
                            <li class="breadcrumb-item"><a href="index">Gestion des copies d'examens</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0);">Anonymer</a></li>
                        </ol>
                    </div>
                </div>
				
				<div class="row">
					<div class="col-lg-12">
						<div class="card">
							<div class="card-header">
								<h4 class="card-title">Anonymisation en masse</h4>
							</div>
                           
							<div class="card-body">
                                <form id="bulkAnonymizationForm" method="post" action="traitement_bulk">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>ECUE</label>
                                                <select class="form-control disabling-options" name="ecue" id="filter_ecue" required>
                                                    <option value="">Sélectionner...</option>
                                                    <?php 
                                                        $sql="select * from ecue where etab='".$_SESSION['etablissement']."'";
                                                        $resultat=$connexion->query($sql);
                                                        while($ecue =$resultat->fetch_assoc()){
                                                    ?>
                                                    <option value="<?php echo $ecue["code_ecue"];?>"><?php echo str_replace("+","'",$ecue['libelle']."[".$ecue["code_ecue"]."]");?></option>
                                                    <?php }?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Classe</label>
                                                <select class="form-control disabling-options" name="classe" id="filter_classe" required>
                                                    <option value="">Sélectionner...</option>
                                                    <?php 
                                                        $sql="select * from classe where etab='".$_SESSION['etablissement']."'";
                                                        $resultat=$connexion->query($sql);
                                                        while($classe =$resultat->fetch_assoc()){
                                                    ?>
                                                    <option><?php echo str_replace("+","'",$classe['libelle']);?></option>
                                                    <?php }?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Semestre</label>
                                                <select class="form-control disabling-options" name="semestre" id="filter_semestre" required>
                                                    <option value="">Sélectionner...</option>
                                                    <?php 
                                                        $sql="select * from semestre";
                                                        $resultat=$connexion->query($sql);
                                                        while($semestre =$resultat->fetch_assoc()){
                                                    ?>
                                                    <option><?php echo str_replace("+","'",$semestre['libelle']);?></option>
                                                    <?php }?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Type d'examen</label>
                                                <select class="form-control disabling-options" name="examen" id="filter_examen" required>
                                                    <option value="">Sélectionner...</option>
                                                    <option value="Session Ordinaire">Session Ordinaire</option>
                                                    <option value="Session de Rappel">Session de Rappel</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Type d'evaluation</label>
                                                <select class="form-control disabling-options" name="nature" id="filter_nature" required>
                                                    <option value="">Sélectionner...</option>
                                                    <option value="Examen Theorique" selected>Examen Theorique</option>
                                                    <option value="Examen Pratique">Examen Pratique</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Année académique</label>
                                                <select class="form-control disabling-options" name="annee" id="filter_annee" required>
                                                    <option value="">Sélectionner...</option>
                                                    <?php 
                                                        $sql="select * from annee ORDER BY libelle DESC ";
                                                        $resultat=$connexion->query($sql);
                                                        while($annee =$resultat->fetch_assoc()){
                                                    ?>
                                                    <option><?php echo str_replace("+","'",$annee['libelle']);?></option>
                                                    <?php }?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <div>
                                                    <button type="button" id="previewBtn" class="btn btn-info btn-sm mr-2">
                                                        <i class="la la-eye"></i> Prévisualiser la liste
                                                    </button>
                                                    <button type="button" id="bulkDeleteBtn" class="btn btn-danger btn-sm">
                                                        <i class="la la-trash-o"></i> Supprimer en masse
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="ecue_classe_alert" style="display:none;" class="alert alert-danger mb-3">
                                        <span style="font-size:1.25em;font-weight:bold;letter-spacing:1px;"><i class="fa fa-exclamation-triangle"></i> ERREUR</span>
                                        &mdash; <span id="ecue_classe_alert_msg"></span>
                                    </div>

                                    <div id="previewSection" style="display:none; margin-top: 20px;">
                                        <hr>
                                        <h5>Étudiants à anonymiser: <span id="studentCount" class="badge badge-primary">0</span></h5>
                                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th><input type="checkbox" id="selectAll" checked></th>
                                                        <th class="text-center">Matricule</th>
                                                        <th>Nom(s) et Prénom(s)</th>
                                                        <th class="text-center">Code à générer</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="studentPreviewBody">
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="mt-3">
                                            <button type="submit" class="btn btn-success btn-lg">
                                                <i class="fa fa-check"></i> Anonymiser les étudiants sélectionnés
                                            </button>
                                            <button type="button" class="btn btn-secondary btn-lg ml-2" onclick="$('#previewSection').hide()">
                                                Annuler
                                            </button>
                                        </div>
                                    </div>
                                    
                                </form>

							</div>
						</div>
					</div>
				</div>

                <div class="row">
					<div class="col-lg-12">
						<div class="card">
							<div class="card-header">
								<h4 class="card-title">Liste des copies anonymées</h4>
								 <a href="#" class="btn btn-success mr-2" data-toggle="modal" data-target="#printModal">
                                    <i class="fa fa-print"></i> Imprimer les codes
                                </a>
								<a href="#" class="btn btn-primary" data-toggle="modal" data-target="#typeAgentModal">+ Ajouter individuellement</a>
							</div>
                           
							<div class="card-body">
                                <!-- Formulaire de filtres -->
                                <form method="GET" action="" class="mb-4">
                                    <input type="hidden" name="list_filter" value="1">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>ECUE</label>
                                                <select name="list_ecue" class="form-control disabling-options">
                                                    <option value="">-- Toutes les ECUE --</option>
                                                    <?php
                                                        $sql_f = "SELECT code_ecue, libelle FROM ecue WHERE etab='".$_SESSION['etablissement']."' ORDER BY libelle";
                                                        $res_f = $connexion->query($sql_f);
                                                        while($r_f = $res_f->fetch_assoc()){
                                                    ?>
                                                    <option value="<?php echo $r_f['code_ecue'];?>" <?php echo (isset($_GET['list_ecue']) && $_GET['list_ecue']==$r_f['code_ecue']) ? 'selected' : ''; ?>>
                                                        <?php echo str_replace("+","'",$r_f['libelle'])." [".$r_f['code_ecue']."]";?>
                                                    </option>
                                                    <?php }?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Classe</label>
                                                <select name="list_classe" class="form-control disabling-options">
                                                    <option value="">-- Toutes les classes --</option>
                                                    <?php
                                                        $sql_f = "SELECT libelle FROM classe WHERE etab='".$_SESSION['etablissement']."' ORDER BY libelle";
                                                        $res_f = $connexion->query($sql_f);
                                                        while($r_f = $res_f->fetch_assoc()){
                                                    ?>
                                                    <option <?php echo (isset($_GET['list_classe']) && $_GET['list_classe']==$r_f['libelle']) ? 'selected' : ''; ?>>
                                                        <?php echo str_replace("+","'",$r_f['libelle']);?>
                                                    </option>
                                                    <?php }?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Année académique</label>
                                                <select name="list_annee" class="form-control disabling-options">
                                                    <option value="">-- Toutes les années --</option>
                                                    <?php
                                                        $sql_f = "SELECT libelle FROM annee ORDER BY libelle DESC";
                                                        $res_f = $connexion->query($sql_f);
                                                        while($r_f = $res_f->fetch_assoc()){
                                                    ?>
                                                    <option <?php echo (isset($_GET['list_annee']) && $_GET['list_annee']==$r_f['libelle']) ? 'selected' : ''; ?>>
                                                        <?php echo $r_f['libelle'];?>
                                                    </option>
                                                    <?php }?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Type d'examen</label>
                                                <select name="list_examen" class="form-control">
                                                    <option value="">-- Tous --</option>
                                                    <option value="Session Ordinaire" <?php echo (isset($_GET['list_examen']) && $_GET['list_examen']=='Session Ordinaire') ? 'selected' : ''; ?>>Session Ordinaire</option>
                                                    <option value="Session de Rappel" <?php echo (isset($_GET['list_examen']) && $_GET['list_examen']=='Session de Rappel') ? 'selected' : ''; ?>>Session de Rappel</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Nature</label>
                                                <select name="list_nature" class="form-control">
                                                    <option value="">-- Toutes --</option>
                                                    <option value="Examen Theorique" <?php echo (isset($_GET['list_nature']) && $_GET['list_nature']=='Examen Theorique') ? 'selected' : ''; ?>>Examen Theorique</option>
                                                    <option value="Examen Pratique" <?php echo (isset($_GET['list_nature']) && $_GET['list_nature']=='Examen Pratique') ? 'selected' : ''; ?>>Examen Pratique</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>&nbsp;</label><br>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fa fa-filter"></i> Filtrer
                                                </button>
                                                <a href="index" class="btn btn-secondary">
                                                    <i class="fa fa-refresh"></i> Réinitialiser
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </form>

                                <?php if(isset($_GET['list_filter'])): ?>
								<div class="table-responsive">
									<table class="table table-bordered table-striped table-hover" style="min-width: 845px">
										<thead>
											<tr>
												<th>N°</th>
												<th>Etudiant</th>
												<th>Code Ecue</th>
												<th>Libelle</th>
												<th>Classe</th>
                                                <th>Semestre</th>
                                                <th>Specialité</th>
                                                <th>Code Anonyme</th>
                                                <th>Examen</th>
                                                <th>Nature</th>
                                                <th>Année académique</th>
												<th>Action</th>
											</tr>
										</thead>
										<tbody>
                                          <?php
                                                 $sql = "SELECT * FROM anonymat WHERE etab='".$_SESSION['etablissement']."'";
                                                 if(!empty($_GET['list_ecue']))   $sql .= " AND ecue='".$connexion->real_escape_string($_GET['list_ecue'])."'";
                                                 if(!empty($_GET['list_classe'])) $sql .= " AND classe='".$connexion->real_escape_string($_GET['list_classe'])."'";
                                                 if(!empty($_GET['list_annee']))  $sql .= " AND annee='".$connexion->real_escape_string($_GET['list_annee'])."'";
                                                 if(!empty($_GET['list_examen'])) $sql .= " AND type='".$connexion->real_escape_string($_GET['list_examen'])."'";
                                                 if(!empty($_GET['list_nature'])) $sql .= " AND nature='".$connexion->real_escape_string($_GET['list_nature'])."'";
                                                 $sql .= " ORDER BY annee DESC, ecue, classe, numero";
                                                 $resultat = $connexion->query($sql);
                                                 $count = 1;
                                                 while($ue = $resultat->fetch_assoc()){
                                          ?>
											<tr>
												<td><?php echo $count;?></td>
												<td><?php echo str_replace("+","'", obtenirNomPrenom(obtenirCodeById($ue['etudiant'],$connexion),$ue['annee'],$connexion));?></td>
												<td><?php echo str_replace("+","'",$ue['ecue']);?></td>
												<td><?php echo str_replace("+","'",getecue($ue['ecue'], $connexion));?></td>
												<td><?php echo $ue['classe'];?></td>
                                                <td><?php echo $ue['semestre'];?></td>
                                                <td><?php echo str_replace("+","'", $ue['specialite']);?></td>
                                                <td><strong><?php echo $ue['numero'];?></strong></td>
                                                <td><?php echo $ue['type'];?></td>
                                                <td><?php echo $ue['nature'];?></td>
                                                <td><?php echo $ue['annee'];?></td>
												<td>
                                                   <a href="index?sup=<?php echo $ue['id'];?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette copie?')">
                                                       <i class="la la-trash-o"></i>
                                                   </a>
                                                </td>
											</tr>
                                            <?php $count++; }?>
										</tbody>
									</table>
								</div>
                                <?php endif; ?>
							</div>
						</div>
					</div>
				</div>
				
            </div>
        </div>
        <!--**********************************
            Content body end
        ***********************************-->


<!-- ===================== MODAL AJOUT INDIVIDUEL ===================== -->
<div class="modal fade bd-example-modal-lg" id="typeAgentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="exampleModalLabel">Formulaire d'anonymation individuelle</h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="typeAgentForm" method="post" action="traitement1">
                  
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <label class="input-group-text">Etudiant </label>
                        </div>
                        <select class="disabling-options" name="etudiant" required>
                            <option selected=""></option>
                           <?php 
                                    $sql="select * from inscription where etab='".$_SESSION['etablissement']."'";
                                    $resultat=$connexion->query($sql);
                                    while($etudiant =$resultat->fetch_assoc()){
                                        $codeCandidat= getCandidatCodeByInscription($etudiant['id'],$connexion);
                            ?>
                             <option value="<?php echo $etudiant['id'];?>"><?php echo obtenirNomPrenom($codeCandidat,$etudiant['annee'],$connexion);?></option>
                             <?php }?>
                        </select>
                    </div>

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <label class="input-group-text">ECUE</label>
                        </div>
                        <select id="single-select" class="disabling-options" name="ecue" required>
                            <option selected=""></option>
                           <?php 
                                    $sql="select * from ecue where etab='".$_SESSION['etablissement']."'";
                                    $resultat=$connexion->query($sql);
                                    while($etablissement =$resultat->fetch_assoc()){
                            ?>
                            <option value="<?php echo $etablissement["code_ecue"];?>"><?php echo str_replace("+","'",$etablissement['libelle']."[".$etablissement["code_ecue"]."]");?></option>
                             <?php }?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Type d'evaluation</label>
                            <select class="form-control disabling-options" name="nature" required>
                                <option value="">Sélectionner...</option>
                                <option value="Examen Theorique" selected>Examen Theorique</option>
                                <option value="Examen Pratique">Examen Pratique</option>
                            </select>
                        </div>
                    </div>

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <label class="input-group-text">Classe </label>
                        </div>
                        <select class="disabling-options" name="classe" id="modal_classe" required>
                            <option selected=""></option>
                           <?php
                                    $sql="select * from classe where etab='".$_SESSION['etablissement']."'";
                                    $resultat=$connexion->query($sql);
                                    while($etablissement =$resultat->fetch_assoc()){
                            ?>
                             <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                             <?php }?>
                        </select>
                    </div>

                    <div id="modal_ecue_classe_alert" style="display:none;" class="alert alert-danger mb-3">
                        <span style="font-size:1.15em;font-weight:bold;letter-spacing:1px;"><i class="fa fa-exclamation-triangle"></i> ERREUR</span>
                        &mdash; <span id="modal_ecue_classe_alert_msg"></span>
                    </div>

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <label class="input-group-text">Semestre </label>
                        </div>
                        <select class="disabling-options" name="semestre" required>
                            <option selected=""></option>
                           <?php 
                                    $sql="select * from semestre ";
                                    $resultat=$connexion->query($sql);
                                    while($etablissement =$resultat->fetch_assoc()){
                            ?>
                             <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                             <?php }?>
                        </select>
                    </div>

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <label class="input-group-text">Examen  </label>
                        </div>
                        <select class="disabling-options" name="examen">
                            <option selected=""></option>
                            <option value="Session Ordinaire">Session Ordinaire</option>
                            <option value="Session de Rappel">Session de Rappel</option>
                        </select>
                    </div>
                
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <label class="input-group-text">Année académique  </label>
                        </div>
                        <select class="disabling-options" name="annee">
                            <option selected=""></option>
                           <?php 
                                    $sql="select * from annee ";
                                    $resultat=$connexion->query($sql);
                                    while($etablissement =$resultat->fetch_assoc()){
                            ?>
                             <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
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
<!-- ================================================================= -->


<!-- ===================== MODAL MESSAGE ===================== -->
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
            </div>
        </div>
    </div>
</div>
<!-- ========================================================= -->


<!-- ===================== MODAL IMPRESSION ===================== -->
<div class="modal fade bd-example-modal-lg" id="printModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Imprimer les codes anonymes</h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="printForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ECUE</label>
                                <select class="form-control disabling-options" id="print_ecue" required>
                                    <option value="">Sélectionner...</option>
                                    <?php 
                                        $sql="select * from ecue where etab='".$_SESSION['etablissement']."'";
                                        $resultat=$connexion->query($sql);
                                        while($ecue =$resultat->fetch_assoc()){
                                    ?>
                                    <option value="<?php echo $ecue["code_ecue"];?>"><?php echo str_replace("+","'",$ecue['libelle']."[".$ecue["code_ecue"]."]");?></option>
                                    <?php }?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Classe</label>
                                <select class="form-control disabling-options" id="print_classe" required>
                                    <option value="">Sélectionner...</option>
                                    <?php 
                                        $sql="select * from classe where etab='".$_SESSION['etablissement']."'";
                                        $resultat=$connexion->query($sql);
                                        while($classe =$resultat->fetch_assoc()){
                                    ?>
                                    <option><?php echo str_replace("+","'",$classe['libelle']);?></option>
                                    <?php }?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Semestre</label>
                                <select class="form-control" id="print_semestre" required>
                                    <option value="">Sélectionner...</option>
                                    <?php 
                                        $sql="select * from semestre";
                                        $resultat=$connexion->query($sql);
                                        while($semestre =$resultat->fetch_assoc()){
                                    ?>
                                    <option><?php echo str_replace("+","'",$semestre['libelle']);?></option>
                                    <?php }?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Type d'examen</label>
                                <select class="form-control" id="print_examen" required>
                                    <option value="">Sélectionner...</option>
                                    <option value="Session Ordinaire">Session Ordinaire</option>
                                    <option value="Session de Rappel">Session de Rappel</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Type d'evaluation</label>
                                <select class="form-control disabling-options" id="print_nature" required>
                                    <option value="">Sélectionner...</option>
                                    <option value="Examen Theorique" selected>Examen Theorique</option>
                                    <option value="Examen Pratique">Examen Pratique</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Année académique</label>
                                <select class="form-control disabling-options" id="print_annee" required>
                                    <option value="">Sélectionner...</option>
                                    <?php 
                                        $sql="select * from annee";
                                        $resultat=$connexion->query($sql);
                                        while($annee =$resultat->fetch_assoc()){
                                    ?>
                                    <option><?php echo str_replace("+","'",$annee['libelle']);?></option>
                                    <?php }?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="generatePrintBtn" class="btn btn-success btn-lg">
                        <i class="fa fa-print"></i> Générer et Imprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- ============================================================ -->


<!-- Print Preview Area (hidden) -->
<div id="printArea" style="display:none;"></div>


        <!--**********************************
            Footer start
        ***********************************-->
         <div class="footer">
            <div class="copyright">
                <p>Copyright © Designed &amp; Developpé  par <a href="htpps:/www.cet-up.com" target="_blank">CETUP</a> 2023</p>
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
    $(document).ready(function() {

        // -------------------------------------------------------
        // Affichage du message (succès / erreur) via URL
        // -------------------------------------------------------
        var urlParams = new URLSearchParams(window.location.search);
        var erreur  = urlParams.get('erreur');
        var success = urlParams.get('sucess');

        if (erreur) {
            var errLower = erreur.toLowerCase();
            var isEcueError = errLower.indexOf('ecue') !== -1 || errLower.indexOf('classe') !== -1 || errLower.indexOf('semestre') !== -1;
            if (isEcueError) {
                var alertHtml =
                    '<div class="alert alert-danger alert-dismissible mt-3" id="ecue_url_erreur_banner" role="alert">' +
                        '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>' +
                        '<h4 style="font-size:1.3em;letter-spacing:1px;"><i class="fa fa-exclamation-triangle"></i> <strong>ERREUR</strong></h4>' +
                        '<p style="font-size:1.05em;margin-bottom:0;">' + $('<span>').text(erreur).html() + '</p>' +
                    '</div>';
                $('.page-titles').after(alertHtml);
            } else {
                $('#messageBody').text('Erreur : ' + erreur);
                $('#messageModal').modal('show');
            }
            window.history.replaceState({}, document.title, window.location.pathname);
        }
        if (success) {
            $('#messageBody').text('Message : ' + success);
            $('#messageModal').modal('show');
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        // -------------------------------------------------------
        // Validation temps réel ECUE / Classe (formulaire en masse)
        // -------------------------------------------------------
        function checkEcueClasse(ecue, classe, semestre, alertId, msgId) {
            if (!ecue || !classe) { $('#' + alertId).hide(); return; }
            $.ajax({
                url: 'check_ecue_classe.php',
                method: 'POST',
                data: { ecue: ecue, classe: classe, semestre: semestre },
                dataType: 'json',
                success: function(r) {
                    if (r.error) { return; }
                    if (r.valid === null) { $('#' + alertId).hide(); return; }
                    if (!r.valid_classe) {
                        $('#' + msgId).text("L'ECUE sélectionnée n'est pas enseignée dans la classe « " + classe + " ».");
                        $('#' + alertId).show();
                    } else if (semestre && !r.valid_semestre) {
                        $('#' + msgId).text("L'ECUE sélectionnée n'est pas enseignée dans ce semestre pour la classe « " + classe + " ».");
                        $('#' + alertId).show();
                    } else {
                        $('#' + alertId).hide();
                    }
                }
            });
        }

        // Bulk form validation
        $('#filter_ecue, #filter_classe').on('change', function() {
            checkEcueClasse($('#filter_ecue').val(), $('#filter_classe').val(), $('#filter_semestre').val(), 'ecue_classe_alert', 'ecue_classe_alert_msg');
        });
        $('#filter_semestre').on('change', function() {
            if ($('#filter_ecue').val() && $('#filter_classe').val()) {
                checkEcueClasse($('#filter_ecue').val(), $('#filter_classe').val(), $(this).val(), 'ecue_classe_alert', 'ecue_classe_alert_msg');
            }
        });

        // Modal (individual) form validation
        $('#single-select, #modal_classe').on('change', function() {
            checkEcueClasse($('#single-select').val(), $('#modal_classe').val(), '', 'modal_ecue_classe_alert', 'modal_ecue_classe_alert_msg');
        });

        // Block individual form submission if ECUE/Classe mismatch
        $('#typeAgentForm').on('submit', function(e) {
            if ($('#modal_ecue_classe_alert').is(':visible')) {
                e.preventDefault();
                showErrorModal('<i class="fa fa-exclamation-triangle"></i> ERREUR de configuration',
                    '<strong style="font-size:1.2em;">ERREUR</strong> &mdash; ' + $('#modal_ecue_classe_alert_msg').text());
            }
        });

        // -------------------------------------------------------
        // Fonction utilitaire : modal d'erreur
        // -------------------------------------------------------
        function showErrorModal(title, message) {
            var modalHtml =
                '<div class="modal fade" id="errorModal" tabindex="-1" role="dialog">' +
                    '<div class="modal-dialog modal-dialog-centered" role="document">' +
                        '<div class="modal-content">' +
                            '<div class="modal-header bg-danger text-white">' +
                                '<h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> ' + title + '</h5>' +
                                '<button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>' +
                            '</div>' +
                            '<div class="modal-body">' + message + '</div>' +
                            '<div class="modal-footer">' +
                                '<button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>';

            $('#errorModal').remove();
            $('body').append(modalHtml);
            $('#errorModal').modal('show');
            $('#errorModal').on('hidden.bs.modal', function() { $(this).remove(); });
        }

        // -------------------------------------------------------
        // Prévisualisation des étudiants
        // -------------------------------------------------------
        $('#previewBtn').click(function() {
            var ecue     = $('#filter_ecue').val();
            var classe   = $('#filter_classe').val();
            var semestre = $('#filter_semestre').val();
            var examen   = $('#filter_examen').val();
            var annee    = $('#filter_annee').val();
            var nature   = $('#filter_nature').val();

            if (!ecue || !classe || !semestre || !examen || !annee || !nature) {
                showErrorModal('Champs manquants', 'Veuillez remplir tous les champs avant de prévisualiser');
                return;
            }

            if ($('#ecue_classe_alert').is(':visible')) {
                showErrorModal('<i class="fa fa-exclamation-triangle"></i> ERREUR de configuration',
                    '<strong style="font-size:1.2em;">ERREUR</strong> &mdash; ' + $('#ecue_classe_alert_msg').text());
                return;
            }

            $.ajax({
                url: 'get_students_for_anonymization.php',
                method: 'POST',
                data: { ecue: ecue, classe: classe, semestre: semestre, examen: examen, annee: annee, nature: nature },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#studentPreviewBody').empty();
                        $('#studentCount').text(response.students.length);

                        if (response.students.length === 0) {
                            $('#studentPreviewBody').append('<tr><td colspan="4" class="text-center">Aucun étudiant trouvé pour ces critères</td></tr>');
                        } else {
                            $.each(response.students, function(index, student) {
                                var row =
                                    '<tr>' +
                                        '<td><input type="checkbox" name="selected_students[]" value="' + student.id + '" checked class="student-checkbox"></td>' +
                                        '<td>' + student.matricule + '</td>' +
                                        '<td>' + student.nom_prenom + '</td>' +
                                        '<td><strong>' + student.code_anonyme + '</strong></td>' +
                                    '</tr>';
                                $('#studentPreviewBody').append(row);
                            });
                        }

                        $('#previewSection').show();
                    } else {
                        showErrorModal('Erreur de traitement', response.message || 'Une erreur est survenue');
                    }
                },
                error: function(xhr, status, error) {
                    var details = '<div class="error-details">';
                    details += '<p><strong>Statut HTTP:</strong> ' + (xhr.status || 'Inconnu') + '</p>';
                    details += '<p><strong>Type d\'erreur:</strong> ' + (status || 'Inconnu') + '</p>';
                    details += '<p><strong>Message:</strong> ' + (error || 'Aucun message') + '</p>';
                    if (xhr.responseText) {
                        try {
                            var jsonResponse = JSON.parse(xhr.responseText);
                            details += '<p><strong>Réponse du serveur:</strong></p><pre>' + JSON.stringify(jsonResponse, null, 2) + '</pre>';
                        } catch(e) {
                            details += '<p><strong>Réponse brute:</strong></p><pre>' + xhr.responseText.substring(0, 500) + '</pre>';
                        }
                    }
                    details += '</div>';
                    showErrorModal('Erreur de récupération des étudiants', details);
                }
            });
        });

        // -------------------------------------------------------
        // Select all / Deselect all
        // -------------------------------------------------------
        $('#selectAll').change(function() {
            $('.student-checkbox').prop('checked', $(this).prop('checked'));
        });

        $(document).on('change', '.student-checkbox', function() {
            var total   = $('.student-checkbox').length;
            var checked = $('.student-checkbox:checked').length;
            $('#selectAll').prop('checked', total === checked);
        });

        // -------------------------------------------------------
        // ===== SUPPRESSION EN MASSE =====
        // -------------------------------------------------------
        $('#bulkDeleteBtn').click(function() {
            var ecue     = $('#filter_ecue').val();
            var classe   = $('#filter_classe').val();
            var semestre = $('#filter_semestre').val();
            var examen   = $('#filter_examen').val();
            var annee    = $('#filter_annee').val();
            var nature   = $('#filter_nature').val();
            var count    = $('#studentCount').text();

            // Récupère le libellé de l'ECUE sélectionné
            var ecueLabel = $('#filter_ecue option:selected').text();

            var confirmHtml =
                '<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog">' +
                    '<div class="modal-dialog modal-dialog-centered" role="document">' +
                        '<div class="modal-content">' +
                            '<div class="modal-header bg-danger text-white">' +
                                '<h5 class="modal-title">' +
                                    '<i class="la la-exclamation-triangle"></i> Confirmer la suppression en masse' +
                                '</h5>' +
                                '<button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>' +
                            '</div>' +
                            '<div class="modal-body">' +
                                '<p>Vous êtes sur le point de supprimer <strong class="text-danger">' + count + ' copie(s) anonymée(s)</strong> correspondant aux critères suivants :</p>' +
                                '<table class="table table-bordered table-sm mt-2">' +
                                    '<tr><th>ECUE</th><td>' + ecueLabel + '</td></tr>' +
                                    '<tr><th>Classe</th><td>' + classe + '</td></tr>' +
                                    '<tr><th>Semestre</th><td>' + semestre + '</td></tr>' +
                                    '<tr><th>Type d\'examen</th><td>' + examen + '</td></tr>' +
                                    '<tr><th>Nature</th><td>' + nature + '</td></tr>' +
                                    '<tr><th>Année académique</th><td>' + annee + '</td></tr>' +
                                '</table>' +
                                '<div class="alert alert-danger mt-3 mb-0">' +
                                    '<i class="la la-warning"></i> ' +
                                    '<strong>Attention ! Cette action est irréversible.</strong><br>' +
                                    'Toutes les copies anonymées correspondant à ces critères seront définitivement supprimées de la base de données.' +
                                '</div>' +
                            '</div>' +
                            '<div class="modal-footer">' +
                                '<button type="button" class="btn btn-secondary" data-dismiss="modal">' +
                                    '<i class="la la-times"></i> Annuler' +
                                '</button>' +
                                '<button type="button" id="confirmDeleteYes" class="btn btn-danger">' +
                                    '<i class="la la-trash-o"></i> Oui, supprimer définitivement' +
                                '</button>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>';

            $('#confirmDeleteModal').remove();
            $('body').append(confirmHtml);
            $('#confirmDeleteModal').modal('show');

            // Confirmation → redirection vers handler PHP
            $('#confirmDeleteYes').off('click').on('click', function() {
                $('#confirmDeleteModal').modal('hide');
                window.location.href = 'index?delete_bulk=1'
                    + '&ecue='     + encodeURIComponent(ecue)
                    + '&classe='   + encodeURIComponent(classe)
                    + '&semestre=' + encodeURIComponent(semestre)
                    + '&examen='   + encodeURIComponent(examen)
                    + '&annee='    + encodeURIComponent(annee)
                    + '&nature='   + encodeURIComponent(nature);
            });

            $('#confirmDeleteModal').on('hidden.bs.modal', function() {
                $(this).remove();
            });
        });
        // -------------------------------------------------------

    }); // end document.ready
    </script>


    <script>
    $(document).ready(function() {

        // -------------------------------------------------------
        // Impression des codes anonymes
        // -------------------------------------------------------
        $('#generatePrintBtn').click(function() {
            var ecue     = $('#print_ecue').val();
            var classe   = $('#print_classe').val();
            var semestre = $('#print_semestre').val();
            var examen   = $('#print_examen').val();
            var annee    = $('#print_annee').val();
            var nature   = $('#print_nature').val();

            if (!ecue || !classe || !semestre || !examen || !annee || !nature) {
                alert('Veuillez remplir tous les champs');
                return;
            }

            $.ajax({
                url: 'get_codes_for_print.php',
                method: 'POST',
                data: { ecue: ecue, classe: classe, semestre: semestre, examen: examen, annee: annee, nature: nature },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.codes.length > 0) {
                        generatePrintDocument(response.codes, {
                            ecue: ecue, classe: classe, semestre: semestre,
                            examen: examen, annee: annee, nature: nature
                        });
                    } else {
                        alert('Aucun code anonyme trouvé pour ces critères');
                    }
                },
                error: function() {
                    alert('Erreur lors de la récupération des codes');
                }
            });
        });

        function generatePrintDocument(codes, filters) {
            $.ajax({
                url: 'get_ecue_libelle.php',
                method: 'POST',
                data: { code_ecue: filters.ecue },
                dataType: 'json',
                success: function(response) {
                    var libelleEcue = response.libelle || "";
                    createPrintContent(codes, filters, libelleEcue);
                },
                error: function() {
                    createPrintContent(codes, filters, filters.ecue);
                }
            });
        }

        function createPrintContent(codes, filters, libelleEcue) {
            var printContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Codes Anonymes - Impression</title>
                    <style>
                        @page { size: A4; margin: 15mm; }
                        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; }
                        .header h1 { margin: 0; font-size: 24px; color: #333; }
                        .header h2 { margin: 5px 0; font-size: 18px; color: #666; }
                        .info-section { margin-bottom: 20px; background: #f5f5f5; padding: 15px; border-radius: 5px; }
                        .info-section p { margin: 5px 0; font-size: 14px; }
                        .info-section strong { color: #333; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th, td { border: 1px solid #333; padding: 5px 8px; text-align: left; }
                        th { background-color: #333; color: white; font-weight: bold; }
                        tr:nth-child(even) { background-color: #f9f9f9; }
                        .col-center { text-align: center; }
                        .code-cell { font-weight: bold; font-size: 14px; color: #d9534f; text-align: center; }
                        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #ccc; padding-top: 10px; }
                        @media print { .no-print { display: none; } body { padding: 0; } }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>Liste des Codes Anonymes</h1>
                        <h2>Examen - ${filters.examen}</h2>
                    </div>
                    <div class="info-section">
                        <p><strong>Nature Examen:</strong> ${filters.nature}</p>
                        <p><strong>Code ECUE:</strong> ${filters.ecue}</p>
                        <p><strong>ECUE:</strong> ${libelleEcue}</p>
                        <p><strong>Classe:</strong> ${filters.classe}</p>
                        <p><strong>Semestre:</strong> ${filters.semestre}</p>
                        <p><strong>Type d'examen:</strong> ${filters.examen}</p>
                        <p><strong>Année académique:</strong> ${filters.annee}</p>
                        <p><strong>Nombre total de copies:</strong> ${codes.length}</p>
                        <p><strong>Date d'impression:</strong> ${new Date().toLocaleDateString('fr-FR')}</p>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th class="col-center" style="width: 45px;">N°</th>
                                <th class="col-center" style="width: 110px;">Matricule</th>
                                <th>Nom(s) et Prénom(s)</th>
                                <th class="col-center" style="width: 140px;">Code Anonyme</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            codes.forEach(function(item, index) {
                printContent += `
                    <tr>
                        <td class="col-center">${index + 1}</td>
                        <td class="col-center">${item.matricule}</td>
                        <td>${item.nom_prenom}</td>
                        <td class="code-cell">${item.code_anonyme}</td>
                    </tr>
                `;
            });

            printContent += `
                        </tbody>
                    </table>
                    <div class="footer">
                        <p>Document généré le ${new Date().toLocaleString('fr-FR')}</p>
                        <p>CETUP - Système de Gestion Scolaire</p>
                    </div>
                </body>
                </html>
            `;

            var printWindow = window.open('', '_blank');
            printWindow.document.write(printContent);
            printWindow.document.close();
            printWindow.onload = function() {
                printWindow.focus();
                printWindow.print();
            };

            $('#printModal').modal('hide');
        }

    }); // end document.ready
    </script>


<style>
.error-details {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-top: 10px;
}
.error-details p {
    margin-bottom: 10px;
}
.error-details pre {
    background-color: #fff;
    padding: 10px;
    border: 1px solid #dee2e6;
    border-radius: 3px;
    max-height: 200px;
    overflow-y: auto;
    font-size: 12px;
}
</style>

</body>
</html>

<?php 

}else{
    header("location: ../login");
}
?>
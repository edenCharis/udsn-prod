<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="doyen"){

    if(isset($_POST["imprimer"]) and isset($_POST["deb"])){

        header("location: p?p=".$_POST["deb"]."");

        if(isset($_SESSION["deliberation"])){
            unset($_SESSION["deliberation"]);
        }
      
        
    }


    if( isset($_POST["enregistrer"]) and isset($_POST["semestre"]) and isset($_POST["annee"]) and  isset($_POST["note"])){


          $numero = genererCodeDeliberation();
          $semestres=$_POST["semestre"];
          $annee=$_POST["annee"];
          $type=$_POST["note"];



          if($type == "pvd"){


            $sql="INSERT INTO deliberation(numero,type_semestre,annee,date_signature,etab) values('$numero','$semestres','$annee','".date('Y-m-d H:i:s')."','".$_SESSION["etablissement"]."')";
            if($connexion->query($sql)){

               // $_SESSION["deliberation"] = $numero;
                $userIP = $_SERVER['REMOTE_ADDR'];
        
                logUserAction($connexion,$_SESSION['id_user'],"edition d'une note de service pvd",date("Y-m-d H:i:s"),$userIP,"valeur : $semestres+$numero+$annee+$type");
        
                header("location: note?sucess=Enregistrement effectuée avec succèss");
        
            }else{
                  header("location: note?erreur=$connexion->error");
              }
          }

    }


    if(isset($_POST["ajouter"]) )
    {
        
        if($_POST["president"] !== "" and  isset($_POST["parcours"]) and isset($_POST["deb1"]) )
        {
            $president = $_POST["president"];
          
           $parcours =$_POST["parcours"];
           $numero = $_POST["deb1"];

           enregistrerDansJDP($parcours,$numero,"Président",getGradeById($president,$connexion),$president,$_SESSION["etablissement"],$connexion);
          
        }

        if( $_POST["rapporteur"] !== "" and isset($_POST["parcours"]) and isset($_POST["deb1"]) )
        {
            
            $rapporteur=$_POST["rapporteur"];
            $parcours =$_POST["parcours"];
            $numero = $_POST["deb1"];

            enregistrerDansJDP($parcours,$numero,"Rapporteur",getGradeById($rapporteur,$connexion),$rapporteur,$_SESSION["etablissement"],$connexion);
          
        }
        if( $_POST["membres"] !== "" and isset($_POST["parcours"]) and isset($_POST["deb1"])  ){
                         

           
           $parcours =$_POST["parcours"];
           $numero = $_POST["deb1"];

          
             $membres =$_POST["membres"];

             foreach ($membres as $membre)
            {
                enregistrerDansJDP($parcours,$numero,"Membre",getGradeById($membre,$connexion),$membre,$_SESSION["etablissement"],$connexion);
           
            }}

            $parcours =$_POST["parcours"];

            logUserAction($connexion,$_SESSION['id_user'],"edition d'une note de service pvd",date("Y-m-d H:i:s"),$userIP,"edition des membres du jury du parcours: $parcours");
        
                header("location: note?sucess=Enregistrement effectuée avec succes");
        
            
        
    }

    if(isset($_GET["jdp"])){

        $id = $_GET['jdp'];

        $sql="delete from jdp where id=$id";

        if($connexion->query($sql)){
            logUserAction($connexion,$_SESSION['id_user'],"supression d'un jury ",date("Y-m-d H:i:s"),$userIP,"valeur suprimée :$id");
         
            
            header("location: note?sucess=supression effectuée avec success");
        }else{
             
            header("location: note?erreur=$connexion->error" );
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
    <link rel="icon" type="image/png" sizes="16x16"  href="../administrateur/<?php echo  $_SESSION['logo_univ']?>">
	<link rel="stylesheet" href="../vendor/bootstrap-select/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="../css/style.css">
	<link rel="stylesheet" href="../css/skin.css">
    <link rel="stylesheet" href="../vendor/select2/css/select2.min.css">

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
                            <h3>Edition d'une note de service</h3>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../scolarite/">Scolarité</a></li> 
                        </ol>
                    </div>
                </div>
				
				<div class="row">
                <div class="col-md-4 text-center">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">  <h3> <i class="la la-edit"></i>Note De Service</a> </h3>
                            </div>
                     <div class="card-body">
                            <form  method="post" action="note">
                  
                            <div class="input-group mb-3">
                                          <div class="input-group-prepend">
                                              <label class="input-group-text">Type de note de service </label>
                                          </div>
                                          <select   class="disabling-options" class="form-control form-control-lg" name="note"   recquired>
                                              
                                               <option value=""></option>
                                               <option value="pvd">Procès Verbal de Délibération</option>
                                          </select>
                  </div> 
                  <div class="input-group mb-3">
                                          <div class="input-group-prepend">
                                              <label class="input-group-text">Type semestre </label>
                                          </div>
                                          <select   class="disabling-options" class="form-control form-control-lg" name="semestre"   recquired>
                                              
                                               <option value=""></option>
                                               <option value="pair">Semestre Pair</option>
                                               <option value="impair">Semestre Impair</option>
                                          </select>
                  </div>   
                     <div class="input-group mb-3">
                                          <div class="input-group-prepend">
                                              <label class="input-group-text">Année académique  </label>
                                          </div>
                                          <select  class="disabling-options" class="form-control form-control-lg" name="annee" >
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
                  <input  type="submit" name="enregistrer" value="Enregistrer" class="btn btn-primary">
            
            </form>

       
    

                            </div>
                        </div>

                       
					</div>
                    <div class="col-md-8 text-center">
                            <div class="card">
                                <div class="card-body">
                                             
                                                     <form action="note" method="post">
                                                     <div class="input-group mb-3">
                                          <div class="input-group-prepend">
                                              <label class="input-group-text">Déliberation: </label>
                                          </div>
                                          <select   class="disabling-options" class="form-control form-control-lg" name="deb1" >
                                              
                                              <option value=""></option>
                                           
                                                    


                                                   
                                                     <?php 
                                                     $sql="select * from deliberation where etab='".$_SESSION["etablissement"]."' ORDER BY date_signature DESC ";
                                                     $resultat=$connexion->query($sql);
                                                     while($d =$resultat->fetch_object()){
                                             ?>
                                              <option value="<?php echo $d->numero; ?>"><?php echo $d->type_semestre."/".$d->annee."/ Note de service"."/".$d->date_signature  ;?></option>
                                              <?php }?>
                                         </select>
                                                  
                                    <div class="input-group mb-3">
                                          <div class="input-group-prepend">
                                              <label class="input-group-text">Parcours </label>
                                          </div>
                                          <select   class="disabling-options" class="form-control form-control-lg" name="parcours" recquired>
                                              
                                               <option value=""></option>
                                               <?php 
                                                      $sql="select * from parcours where etab='".$_SESSION["lib_etab"]."'";
                                                      $resultat=$connexion->query($sql);
                                                      while($etablissement =$resultat->fetch_assoc()){
                                              ?>
                                               <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                               <?php }?>
                                          </select>
                  </div> 
                  <h4>Président : </h4>
                
                  <div class="input-group mb-3">
                                          <div class="input-group-prepend">
                                              <label class="input-group-text">Nom(s) & Prénom(s)</label>
                                          </div>
                                          <select   class="disabling-options" class="form-control form-control-lg" name="president"  recquired>
                                              
                                               <option value=""></option>
                                               <?php 
                                                      $sql="select * from enseignant where etab='".$_SESSION["etablissement"]."'";
                                                      $resultat=$connexion->query($sql);
                                                      while($etablissement =$resultat->fetch_assoc()){
                                              ?>
                                               <option value="<?php echo $etablissement["id"]; ?>"><?php echo  (($etablissement["grade"] !== "Aucun") ? $etablissement["grade"] : "")." ".str_replace("+","'",$etablissement['nom']." ".$etablissement["prenom"]);?></option>
                                               <?php }?>
                                          </select>
                  </div>    
                  <h4>Rapporteur : </h4>
                   
                  <div class="input-group mb-3">
                                          <div class="input-group-prepend">
                                              <label class="input-group-text">Nom(s) & Prénom(s)</label>
                                          </div>
                                          <select   class="disabling-options" class="form-control form-control-lg" name="rapporteur"  recquired>
                                              
                                              <option value=""></option>
                                              <?php 
                                                     $sql="select * from enseignant where etab='".$_SESSION["etablissement"]."'";
                                                     $resultat=$connexion->query($sql);
                                                     while($etablissement =$resultat->fetch_assoc()){
                                             ?>
                                              <option value="<?php echo $etablissement["id"]; ?>"><?php echo   (($etablissement["grade"] !== "Aucun") ? $etablissement["grade"] : "")." ".str_replace("+","'",$etablissement['nom']." ".$etablissement["prenom"]);?></option>
                                              <?php }?>
                                         </select>
                  </div> 
                  <h4>Membres : </h4>
                
                  <div class="input-group mb-3">
                                          
                                          <label>Selectionnez les membres (Appuyez shift pour selectionner plus qu'un membre)</label>
                                    <select multiple class="disabling-options" class="form-control form-control-lg" id="sel2"  name="membres[]">
                                                <option value=""></option>
                                                <?php 
                                                      $sql="select * from enseignant where etab='".$_SESSION["etablissement"]."'";
                                                      $resultat=$connexion->query($sql);
                                                      while($etablissement =$resultat->fetch_assoc()){
                                              ?>
                                               <option  value="<?php echo $etablissement['id'];?>"><?php echo   (($etablissement["grade"] !== "Aucun") ? $etablissement["grade"] : "")." ".str_replace("+","'",$etablissement['nom']." ".$etablissement["prenom"]);?></option>
                                               <?php }?>
                                               <?php 
                                                      $sql="select * from agent where etab='".$_SESSION["etablissement"]."'";
                                                      $resultat=$connexion->query($sql);
                                                      while($etablissement =$resultat->fetch_assoc()){
                                              ?>
                                               <option value="<?php echo $etablissement['code'];?>"><?php echo str_replace("+","'",$etablissement['nom']." ".$etablissement["prenom"]);?></option>
                                               <?php }?>
                                            </select>
                  </div> 

               <button  type="submit" name="ajouter"  class="btn btn-warning">  <i class="la la-plus"></i> Ajouter</button>
               <div class="input-group mb-3">
                                          <div class="input-group-prepend">
                                              <label class="input-group-text">Déliberation: </label>
                                          </div>
                                          <select   class="disabling-options" class="form-control form-control-lg" name="deb" >
                                              
                                              <option value=""></option>
                                           
                                                    


                                                     <form action="note" method="post">
                                                     <?php 
                                                     $sql="select * from deliberation where etab='".$_SESSION["etablissement"]."' ORDER BY date_signature DESC";
                                                     $resultat=$connexion->query($sql);
                                                     while($d =$resultat->fetch_object()){
                                             ?>
                                              <option value="<?php echo $d->numero; ?>"><?php echo $d->type_semestre."/".$d->annee."/ Note de service"  ;?></option>
                                              <?php }?>
                                         </select>
               <button  type="submit" name="imprimer"  class="btn btn-success">  <i class="la la-print"></i> Imprimer</button>
                
                                                     </form>

                                </div>
                            </div>

                        </div>
                </div>

                         
				
            </div>
            <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">

                        <div class="table-responsive">
            <table id="example3"  class="table table-striped table-responsive-sm">
                <thead>
                    <tr>
                        <th>Note de service</th>
                        <th>Parcours</th>
                        <th>Role</th>
                    
                        <th>Enseignant</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                       $sql="select * from jdp where etab='".$_SESSION["etablissement"]."' ";
                       $requete=$connexion->query($sql);
                       while($data = $requete->fetch_object()){

                       

                    
                    ?>
                    <tr>
                        <th><?php echo $data->deliberation;?></th>
                        <td><?php echo $data->parcours;?></td>
                        <td><?php echo $data->role;?></td>  
                        <td><?php echo mettrePremieresLettresMajuscules(getNomPrenomEnseignant(getCodeEnseignant($data->membre,$connexion),$connexion));?></td>
                  
                        <td class="color-primary"> <a href="note?jdp=<?php echo $data->id;?>"  class="btn btn-danger" name="generer"><i class="la la-trash"></i></a>
                      </tr>
                    <?php }?>
                  
                </tbody>
            </table>
        </div>
                            
                        </div>
                    </div>
                </div>   
        </div>
        <!--**********************************
            Content body end
        ***********************************-->




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
                <p>Copyright © Designed &amp; Developpé  par <a href="htpps:/www.cet-up.com" target="_blank">CETUP</a> 2023</p>
            </div>
        </div>
        <!--**********************************
            Footer end
        ***********************************-->

		<!--**********************************
           Support ticket button start
        ***********************************-->

        <!--**********************************
           Support ticket button end
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
    <script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../js/plugins-init/datatables.init.js"></script>

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
<script>
    $(document).ready(function() {
        // Gérer le clic sur le bouton "Modifier"
        $('.btn-primary').click(function() {
            // Récupérer les données de la ligne cliquée
            var id = $(this).data('id');
            var lib = $(this).data('lib');
            var niv= $(this).data('niv');
            var  spec = $(this).data('spec');
            

            // Pré-remplir le formulaire modal avec les données actuelles
            $('#classeId').val(id);
            $('#nouveauClasse').val(lib);
            $('#nouveauniv').val(niv);
            $('#nouveauspec').val(spec);
          
        });
    });
</script>
	
</body>
</html>

<?php 



}else{
    header("location: ../login");
}?>
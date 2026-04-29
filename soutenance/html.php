<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();
$_SESSION['total']=0;

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="soutenance"){

   if(isset($_GET["soutenance"])){

    $soutenance = $_GET["soutenance"];

     if($soutenance !=="" ){
?>


<!DOCTYPE html>
<html lang="en">

<head>
	
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $_SESSION['univ'];?> - Scolarité de  <?php echo $_SESSION['etablissement'];?> </title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../images/univ.png">
	<link rel="stylesheet" href="../vendor/bootstrap-select/dist/css/bootstrap-select.min.css">
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

   
<div class="content h-50" id="contenu-a-imprimer">
            <!-- row -->
            <div class="container-fluid">
            <div class="row">
    <div class="col-md-12">
      <div class="d-flex justify-content-between align-items-center">
        <!-- Université Denis Sassou Nguesso -->
        <div class="p-2"> <h3>Université Denis Sassou Nguesso</h3></div>
        <div class="p-2">
          <img src="../images/univ.png" alt="Logo de l'université" style="max-width: 100px;">
        </div>
        <!-- Devise -->
        <div class="p-2">  <h3>Rigueur*Excellence*Lumiere</h3></div>
    
        <!-- Logo -->
     
      </div>
    </div>
  </div>
				
				<div class="row page-titles justify-content-center align-items-center">
                   
                            <h4>Fiche de Notation de la soutenance du rapport de stage de fin de formation</h4>
                       
                </div>
				
         <div class="row h-100 justify-content-center align-items-center">
               <div class="col-md-12 text-center">

                <?php 
                
                
                         $sql="select * from soutenance join inscription on soutenance.impetrant=inscription.id where soutenance.code='$soutenance'";
                         $resultat=$connexion->query($sql);
                         while($etudiant = $resultat->fetch_assoc()){
                ?>
                <form>
                    <div class="card">
                         <div class="card-header d-block">
                                <h2 class="card-title">RENSEIGNEMENT SUR L'ETUDIANT</h2>
                                
                            </div>
                        <div class="card-body">
                        <div class="row">
                        <div class="col-lg-6">
               
                                     
                                        <div class="form-group row">
                                        <label class="col-sm-4 col-form-label">Nom(s)</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="nom" name="nom" value="<?php echo strtoupper($etudiant['nom']);?>">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                        <label class="col-sm-4 col-form-label">Prénom(s)</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control"   id="prenom" name="prenom" value="<?php echo strtoupper($etudiant['prenom']);?>">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                        <label class="col-sm-4 col-form-label">Matricule</label>
                                            <div class="col-sm-8">
                                                <input type="text"     id="matricule" class="form-control" name="matricule" value="<?php echo strtoupper($etudiant['candidat']);?>" disabled>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-4 col-form-label">Date de naissance</label>
                                            <div class="col-sm-8">
                                                <input type="date" class="form-control" name="datenaissance" value="<?php echo $etudiant['date_naissance'];?>" disabled>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-4 col-form-label">Lieu de naissance</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" name="lieunaissance" value="<?php echo strtoupper($etudiant['lieu de naissance']);?>" disabled>
                                            </div>
                                        </div>
                                   
                                        
                        </div>
                        <div class="col-lg-6">
                                      
                                        <div class="form-group row">
                                            <label class="col-sm-4 col-form-label">Niveau</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" name="niveau" value="<?php echo strtoupper($etudiant['niveau']);?>" disabled>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-4 col-form-label">Departement</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" name="departement" value="<?php echo strtoupper($etudiant['departement']);?>" disabled>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-4 col-form-label">Specialité</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" name="specialite" value="<?php echo strtoupper($etudiant['specialite']);?>" disabled>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-4 col-form-label">Titre du sujet</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" name="titre_du_sujet" value="<?php echo strtoupper(replace($etudiant['theme']));?>" >
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-4 col-form-label">Année académique</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" name="annee_acad" value="<?php echo strtoupper($etudiant['annee_acad']);?>" disabled>
                                            </div>
                                       
                                      </div>
                        </div>
                    </div>
                        </div>
                    </div>
                    <?php 
                       $note_soutenance =$etudiant['note'];
                     }?>
           
                </form>

              </div>

              <?php 
              
                   $sql="select * from type_element ";
                   $resultat=$connexion->query($sql);
                   while($type_element = $resultat->fetch_assoc()){

                    $total =0;
              
              ?>
         <div class="col-lg-6">
                <div class="card">
                            <div class="card-header">
                                <h4 class="card-title"><?php echo strtoupper($type_element['libelle_type']);?></h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped verticle-middle table-responsive-sm">
                                        <thead>
                                            <tr>
                                                <th scope="col">Element d'appreciation</th>
                                              
                                                <th scope="col" width="30%" >Note</th>
                                                <th scope="col">Bareme</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php 

                                                 $somme = 0;
                                                $sql="select * from element where type_element='".$type_element['libelle_type']."'";
                                                $result = $connexion->query($sql);
                                                while ($element = $result->fetch_assoc()){
                                                    $somme = $somme + getNoteElement($soutenance,$element['libelle_element'],$_SESSION['etablissement'],$connexion);
                                                     $_SESSION['total']= $_SESSION['total'] + getNoteElement($soutenance,$element['libelle_element'],$_SESSION['etablissement'],$connexion);
                                        ?> 
                                            <tr>
                                        
                                                <td> <?php echo strtolower(str_replace("+","'",$element['libelle_element']));?></td>
                                              
                                
                                               
                                        <td>  

                                            <?php
                                                if(getNoteElement($soutenance,$element['libelle_element'],$_SESSION['etablissement'],$connexion) == null) {
                                                    echo "";
                                            ?>
                                               
                                                <?php }else{?>
                                                    
                                                          <?php echo getNoteElement($soutenance,$element['libelle_element'],$_SESSION['etablissement'],$connexion);?>
                                                        
                                                      
                                                <?php }?>


                                        </td>
                                                <td><?php echo $element['note_max'];?>
                                                </td>
                                             
                                            </tr>
                                        
                                         <?php }?>
                                       
                                        </tbody>
                                    </table>


                                    <p>TOTAL SUR <?php echo $type_element['note_max'];?> POINTS :  <?php echo $somme;  ;?></p>
                                </div>
                            </div>

                           
                </div>
                   
		 </div>
         <?php
            
        
           }?>  
<div class="col-md-12 text-center">
<div class="card">
<div class="card-header d-block">
                                <h4 class="card-title">RECAPITULATIF DES RESULTATS DES SIX SEMESTRES</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-responsive-sm">
                                        <thead>
                                            <tr>
                                                <th>Semestre</th>
                                                <th>Moyenne Semestrielle</th>
                                                <th>Session de validation</th>
                                                <th>Decision du jury</th>
                                                <th>Mention</th>
                                                <th>Année accadémique</th>

                                                
                                            </tr>
                                        </thead>
                                        <tbody>

                                        <?php 

                                                  $tableau_moyenne = array();                                  
                                                  $sql1="select inscription,semestre,v.annee,v.etab from vue_moyenne_ue as v join inscription on v.inscription= inscription.id join soutenance on soutenance.impetrant=inscription.id where soutenance.code='$soutenance' ";
                                                    $resultat = $connexion->query($sql1);

                                                    while($recap=$resultat->fetch_assoc() ){
                                        ?>
                                            <tr>
                                                <th><?php echo $recap['semestre'];?></th>
                                                <td><?php  echo round(calcul_moyenne($recap['inscription'],$recap['semestre'],$recap['annee'],$recap['etab'],$connexion),2);?></td>
                                                <td><span class="badge badge-primary"></span>
                                                </td>
                                                <td></td>
                                                <td><?php echo mentionParmoyenne(round(calcul_moyenne($recap['inscription'],$recap['semestre'],$recap['annee'],$recap['etab'],$connexion),2));?></td>
                                                <td class="color-primary"><?php echo $recap['annee'];?></td>
                                            </tr>
                                        
                                         <?php
                                                    $tableau_moyenne[] = round(calcul_moyenne($recap['inscription'],$recap['semestre'],$recap['annee'],$recap['etab'],$connexion),2);
                                        
                                        }?>
                                          
                                           
                                        </tbody>
                                    </table>

                                    
                                   
                                </div>
                            </div>
                            <form method="post" action="traitement">    
                                                    <div class="row no-gutters" >
                                                        <div class="col-md-6">

                                                         <?php 

                                                              $m=0;
                                                              $i =0;
                                                              foreach ($tableau_moyenne as $moy){
                                                                 $m=$m + $moy;
                                                                 $i++;
                                                              }

                                                              $m = $moy /$i;
                                                         ?>
                                                        <p>Moyenne Générale : <?php  echo $moy;?>  </p>
                                                        <p>Statut : <?php  echo statutSoutenance($moy);?> </p>
                                                        
                                                        <p>Total des points : <?php echo $_SESSION['total'];?>/100</p>
                                                       
                                                        <input type="hidden" name="soutenance" value="<?php echo $soutenance;?>">
                                                        </div>
                                                    <div class="col-md-6 no-gutters">
                                                      
                                                        <div class="row">
                                                            <div class="col-6 mx-auto mb-0">
                                                            <label for="">Note soutenance</label>
                                                             <?php echo $note_soutenance;?>
                                                              <br>
                                                              <p> Mention : <?php echo mentionParmoyenne($note_soutenance);?></p>
                                                            </div>  
                                                             
                                                     
                                                       </div>
                                                            
                                                   </div>
                                                    </div>
                                                </form>
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
         <div class="col-6 mx-auto">
                                                        <button onclick="convertirEnPDF()"  class="btn btn-light" type="button"> <i class="fa fa-print"></i> Imprimer </button>
                                                        </div>
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
    <script src="../js/dlabnav-init.js"></script>
		
    <!-- Chart Morris plugin files -->
   
		

		<!-- Demo scripts -->
    <script src="../js/dashboard/dashboard-1.js"></script>
	
	<!-- Svganimation scripts -->
    <script src="../vendor/svganimation/vivus.min.js"></script>
    <script src="../vendor/svganimation/svg.animation.js"></script>
    <script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../js/plugins-init/datatables.init.js"></script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script src="..js/test.js"></script>

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
         //   window.history.replaceState({}, document.title, window.location.pathname);
        }
    });
</script>



<script>
    $(document).ready(function() {
        // Gérer le clic sur le bouton "Modifier"
        $('.btn btn-primary element').click(function() {
            // Récupérer les données de la ligne cliquée
            var a = $(this).data('id');
            // Pré-remplir le formulaire modal avec les données actuelles
            $('#element1').val(a);

        });
    });
</script>

	
</body>
</html>

<?php 
     }
   }
}else{
    header("location: ../login");
}?>
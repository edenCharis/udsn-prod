<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="inscription"){

   


    if(isset($_GET["code"]) and isset($_GET["annee"]) and isset($_GET["specialite"]))
    {

    $code =$_GET["code"];
    $annee=$_GET["annee"];
    $specialite =$_GET["specialite"];
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
        <?php include "header.php" ;?>
        <!--**********************************
            Header start
        ***********************************-->
        
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
                            <h4>Modification des données personnelles de l'Etudiant </h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                          
                            <li class="breadcrumb-item active"><a href="index"> <?php echo $_SESSION['etablissement'];?></a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0);"> Mon profil</a></li>
                        </ol>
                    </div>
                </div>
				 
              
                    <div class="card">
                        
                    <div class="card-body ">
  <form method="post" action="modifier">
    <input type="hidden" name="code" value="<?php echo $code;?>">
 

  <?php 
     $sql="select * from candidat join inscription on candidat.code = inscription.candidat  where inscription.candidat='$code' and inscription.annee='$annee' and specialite='$specialite'";

     $resultat =$connexion->query($sql);

     while($modifier = $resultat->fetch_object()){
  
  ?>
    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <label for="nom">Nom </label>
          <input type="text" class="form-control" id="nom" name="nom" value="<?php echo $modifier->nom;?>">
        </div>
        <div class="form-group">
          <label for="prenom">Prénom </label>
          <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo $modifier->prenom;?>">
        </div>
        <div class="form-group">
          <label for="dateNaissance">Date de naissance </label>
          <input type="date" class="form-control" id="dateNaissance" name="datenaissance" value="<?php echo $modifier->date_nais;?>">
        </div>
        <div class="form-group">
          <label for="lieuNaissance">Lieu de naissance </label>
          <input type="text" class="form-control" id="lieuNaissance" name="lieunaissance" value="<?php echo $modifier->lieu_nais;?>">
        </div>
           <div class="form-group">
          <label for="lieuNaissance">Sexe</label>
          <input type="text" class="form-control" id="sexe" name="sexe" value="<?php echo $modifier->sexe;?>">
        </div>
           <div class="form-group">
          <label for="lieuNaissance">Telephone </label>
          <input type="text" class="form-control" id="tel" name="tel" value="<?php echo ($modifier->tel !== null) ? $modifier->tel : '';?>">
        </div>
        
           <div class="form-group">
          <label for="lieuNaissance">Email </label>
          <input type="text" class="form-control" id="email" name="email" value="<?php echo ($modifier->email !== null) ? $modifier->email : '';?>">
        </div>
      </div>
      <div class="col-md-6">
               <div class="form-group">
          <label for="specialite">specialite</label>
          <select class="form-control" id="specialite" name="specialite">
            <option value="<?php echo $modifier->specialite;?>"><?php echo $modifier->specialite;?></option>
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
          <label for="serieBac">Classe</label>
          <select class="form-control" id="classe" name="classe">
            <option value="<?php echo $modifier->classe;?>"><?php echo $modifier->classe;?></option>
            <?php 
                                                        $sql="select * from classe where etab='".$_SESSION['etablissement']."'";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option value="<?php echo $etablissement['libelle']?>"><?php echo str_replace("+","'",$etablissement['libelle']."-".$etablissement['specialite']);?></option>
                                                 <?php }?>
          
          </select>
        </div>
        <div class="form-group">
          <label for="moyenneBac">Moyenne au Bac :</label>
          <input type="number" step="0.01" class="form-control" id="moyenneBac" name="moyennebac" value="<?php echo $modifier->moyenneBac;?>">
        </div>
        <div class="form-group">
          <label for="serieBac">Série du Bac </label>
          <select class="form-control" id="serieBac" name="bac">
          <option value="<?php echo $modifier->bac;?>"><?php echo $modifier->bac;?></option>
            <option value="A">A</option>
            <option value="C">C</option>
            <option value="D">D</option>
            <option value="E">E</option>
         
          </select>
        </div>
        <div class="form-group">
          <label for="annee">Année académique</label>
          <select class="form-control" id="annee" name="annee">
            <option value="<?php echo $modifier->annee;?>"><?php echo $modifier->annee;?></option>
            <?php 
                                                        $sql="select * from annee ";
                                                        $resultat=$connexion->query($sql);
                                                        while($etablissement =$resultat->fetch_assoc()){
                                                ?>
                                                 <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                                 <?php }?>
          
            <!-- Ajoutez d'autres options selon vos besoins -->
          </select>
        </div>
      </div>
    </div>
    <button type="submit" name="done" class="btn btn-primary">Modifier</button>
  </form>
  <?php }?>
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

    <script>
    // Récupérer l'élément select
    var selectAnnee = document.getElementById('anneeSelect');

    // Ajouter les années de 2000 à 2023 au sélecteur
    for (var annee = 2000; annee <= 2023; annee++) {
        var option = document.createElement('option');
        option.value = annee;
        option.text = annee;
        selectAnnee.appendChild(option);
    }
</script>
		
    <!-- Chart Morris plugin files -->
    <script src="../vendor/raphael/raphael.min.js"></script>
    <script src="../vendor/morris/morris.min.js"></script>
		
	
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
            var classe = $(this).data('classe');
            var ecue= $(this).data('ecue');
            var  sem = $(this).data('sem');
            var annee = $(this).data('annee');

            // Pré-remplir le formulaire modal avec les données actuelles
            $('#affId').val(id);
            $('#nouveauClasse').val(classe);
            $('#nouveauEcue').val(ecue);
            $('#nouveauSem').val(sem);
            $('#nouveauAnnee').val(annee);



        });
    });
</script>
	
</body>
</html>

<?php 
    }else{
    if(isset($_POST['done'])){
        
        // Récupération des données du formulaire
        $code = $_POST['code'];
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $dateNaissance = $_POST['datenaissance'];
        $lieuNaissance = $_POST['lieunaissance'];
        $specialite = $_POST['classe'];
        $moyenneBac = $_POST['moyenneBac'];
        $serieBac = $_POST['bac'];
        $annee = $_POST['annee'];
        $classe = $_POST['classe'];
        $tel = $_POST['tel'];
        $email = $_POST['email'];
        
        
        
        // Vérification des champs modifiés et construction de la requête
        $sql = "UPDATE candidat SET ";
        $updates = array();
        
        if (!empty($nom)) {
            $updates[] = "nom = '$nom'";
        }
        if (!empty($prenom)) {
            $updates[] = "prenom = '$prenom'";
        }
        if (!empty($dateNaissance)) {
            $updates[] = "date_nais = '$dateNaissance'";
        }
        if (!empty($lieuNaissance)) {
            $updates[] = "lieu_nais = '$lieuNaissance'";
        }
          if (!empty($tel)) {
            $updates[] = "tel = '$tel'";
        }
        if (!empty($email)) {
            $updates[] = "email = '$email'";
        }
        if (!empty($moyenneBac)) {
            $updates[] = "moyenneBac = '$moyenneBac'";
        }
        if (!empty($serieBac)) {
            $updates[] = "bac = '$serieBac'";
        }
        if (!empty($annee)) {
            $updates[] = "annee = '$annee'";
        }
        
        if (!empty($updates)) {
            // Démarrage de la transaction
            $connexion->begin_transaction();
            
            try {
                // Première requête : mise à jour du candidat
                $sql .= implode(", ", $updates);
                $sql .= " WHERE code ='$code'";
                
                if (!$connexion->query($sql)) {
                    throw new Exception("Erreur lors de la mise à jour du candidat: " . $connexion->error);
                }
                
                // Deuxième requête : mise à jour de l'inscription
                $requete = "UPDATE inscription SET classe='".$classe."' WHERE candidat='$code'";
                
                if (!$connexion->query($requete)) {
                    throw new Exception("Erreur lors de la mise à jour de l'inscription: " . $connexion->error);
                }
                
                // Si tout s'est bien passé, valider la transaction
                $connexion->commit();
                
                // Log de l'action
                $userIP = $_SERVER['REMOTE_ADDR'];
                logUserAction($connexion, $_SESSION['id_user'], "modification des données de l'etudiant $code", date("Y-m-d H:i:s"), $userIP, "valeur modifié : ".$code);
                
                header("location: donnees?sucess=Modification effectuée avec succès");
                
            } catch (Exception $e) {
                // En cas d'erreur, annuler la transaction
                $connexion->rollback();
                header("location: donnees?erreur=" . urlencode($e->getMessage()));
            }
            
        } else {
            header("location: donnees?sucess=Aucune modification à effectuer");
        }
    }
}

}else{
    header("location: ../login");
}?>
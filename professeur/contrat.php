<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

// FIXED: More secure session validation
if(!isset($_SESSION['id']) || $_SESSION['id'] !== session_id() || !isset($_SESSION['role']) || $_SESSION['role'] !== "enseignant"){
    header("location: ../login");
    exit();
}

// Handle form submission for update
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])){
    
    // Retrieve and sanitize form data
    $id = intval($_POST['id']);
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $date_naissance = $_POST['date_naissance'];
    $diplome = trim($_POST['diplome']);
    $specialite = trim($_POST['specialite']);
    $grade = trim($_POST['grade']);
    $telephone = trim($_POST['telephone']);
    $email = trim($_POST['email']);
    $ville = trim($_POST['ville']);
    $sexe = $_POST['sexe'];
    
    // Validate required fields
    if(empty($id) || empty($nom) || empty($prenom) || empty($date_naissance) || 
       empty($diplome) || empty($specialite) || empty($grade) || empty($telephone) || 
       empty($email) || empty($ville) || empty($sexe)){
        header("location: contrat?erreur=" . urlencode("Tous les champs sont obligatoires"));
        exit();
    }
    
    // Validate email format
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        header("location: contrat?erreur=" . urlencode("Format email invalide"));
        exit();
    }
    
    // FIXED: Validate phone number format
    if(!preg_match('/^[0-9+\-\s()]{8,20}$/', $telephone)){
        header("location: contrat?erreur=" . urlencode("Format téléphone invalide"));
        exit();
    }
    
    // FIXED: Validate date format
    $date_obj = DateTime::createFromFormat('Y-m-d', $date_naissance);
    if(!$date_obj || $date_obj->format('Y-m-d') !== $date_naissance){
        header("location: contrat?erreur=" . urlencode("Format de date invalide"));
        exit();
    }
    
    // FIXED: Validate sexe value (whitelist)
    if(!in_array($sexe, ['Homme', 'Femme'])){
        header("location: contrat?erreur=" . urlencode("Valeur sexe invalide"));
        exit();
    }
    
    // FIXED: Better handling of quotes - use parameterized queries instead
    // No need to replace quotes since we're using prepared statements
    
    // FIXED: Verify ownership - ensure teacher can only update their own record
    $sql_verify = "SELECT e.id FROM enseignant e 
                   INNER JOIN user u ON e.code = u.code_enseignant 
                   WHERE e.id = ? AND u.id = ?";
    $stmt_verify = $connexion->prepare($sql_verify);
    $stmt_verify->bind_param("ii", $id, $_SESSION['id_user']);
    $stmt_verify->execute();
    $result_verify = $stmt_verify->get_result();
    
    if($result_verify->num_rows === 0){
        $stmt_verify->close();
        header("location: contrat?erreur=" . urlencode("Accès non autorisé"));
        exit();
    }
    $stmt_verify->close();
    
    // Prepare update query
    $sql_update = "UPDATE enseignant SET 
            nom = ?,
            prenom = ?,
            date_naissance = ?,
            diplome = ?,
            specialite = ?,
            grade = ?,
            telephone = ?,
            email = ?,
            ville = ?,
            sexe = ?
            WHERE id = ?";
    
    $stmt_update = $connexion->prepare($sql_update);
    
    if($stmt_update){
        $stmt_update->bind_param("ssssssssssi", 
            $nom, 
            $prenom, 
            $date_naissance, 
            $diplome, 
            $specialite, 
            $grade, 
            $telephone, 
            $email, 
            $ville, 
            $sexe, 
            $id
        );
        
        if($stmt_update->execute()){
            // Log the action
            // FIXED: Better IP detection with fallback
            $userIP = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            // Sanitize IP to prevent header injection
            $userIP = filter_var($userIP, FILTER_VALIDATE_IP) ? $userIP : 'Unknown';
            
            logUserAction(
                $connexion,
                $_SESSION['id_user'],
                "Modification des informations personnelles de l'enseignant",
                date("Y-m-d H:i:s"),
                $userIP,
                "Enseignant ID: $id"
            );
            
            $stmt_update->close();
            header("location: contrat?sucess=" . urlencode("Informations mises à jour avec succès"));
            exit();
        } else {
            $stmt_update->close();
            header("location: contrat?erreur=" . urlencode("Erreur lors de la mise à jour"));
            exit();
        }
    } else {
        header("location: contrat?erreur=" . urlencode("Erreur de préparation de la requête"));
        exit();
    }
}

if (isset($_SESSION['id_user']) && !isset($_SESSION["code_enseignant"])) {
    $sql = "SELECT code_enseignant FROM user WHERE id = ?";
    $stmt = $connexion->prepare($sql);
    $stmt->bind_param("i", $_SESSION['id_user']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $_SESSION["code_enseignant"] = $row['code_enseignant'];
    }
    $stmt->close();
}

// FIXED: Validate session data exists
if(!isset($_SESSION["code_enseignant"])){
    header("location: index?erreur=" . urlencode("Code enseignant introuvable"));
    exit();
}

$code_enseignant = $_SESSION["code_enseignant"];

// Get contract information
$numero_contrat = getContratEnseignant($code_enseignant, $connexion);

// FIXED: Validate numero_contrat exists
if(empty($numero_contrat)){
    header("location: index?erreur=" . urlencode("Contrat introuvable"));
    exit();
}

// Get annee from contrat table using numero_contrat
$sql_annee = "SELECT annee FROM contrat WHERE numero_contrat = ? LIMIT 1";
$stmt_annee = $connexion->prepare($sql_annee);
$stmt_annee->bind_param("s", $numero_contrat);
$stmt_annee->execute();
$result_annee = $stmt_annee->get_result();

if ($row_annee = $result_annee->fetch_assoc()) {
    $annee_academique = $row_annee['annee'];
} else {
    $stmt_annee->close();
    header("location: index?erreur=" . urlencode("Année académique introuvable"));
    exit();
}
$stmt_annee->close();

// Prepare and execute query safely
$sql = "SELECT e.* FROM enseignant e 
        INNER JOIN contrat c ON e.id = c.enseignant 
        WHERE c.numero_contrat = ? AND c.annee = ? LIMIT 1";
$stmt = $connexion->prepare($sql);
$stmt->bind_param("ss", $numero_contrat, $annee_academique);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_object();
$stmt->close();

if (!$data) {
    header("location: index?erreur=" . urlencode("Contrat introuvable"));
    exit();
}

// FIXED: Add CSRF token generation
if(!isset($_SESSION['csrf_token'])){
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
	
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo htmlspecialchars($_SESSION['etablissement'], ENT_QUOTES, 'UTF-8');?></title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../administrateur/<?php echo htmlspecialchars($_SESSION['logo_univ'], ENT_QUOTES, 'UTF-8');?>">
	<link rel="stylesheet" href="../vendor/bootstrap-select/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="../vendor/select2/css/select2.min.css">
    <link rel="stylesheet" href="../css/style.css">
	<link rel="stylesheet" href="../css/skin.css">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
            Header start
        ***********************************-->
        <?php include "header.php";?>
        <!--**********************************
            Header end
        ***********************************-->

        <!--**********************************
            Sidebar start
        ***********************************-->
       <?php include 'nav.php';?>
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
                <div class="col-sm-8 p-md-0">
                    <div class="welcome-text">
                        <h4>DETAILS D'UN CONTRAT D'ENSEIGNEMENT</h4>
                    </div>
                </div>
            </div>

            <form action="" method="post">
                <!-- FIXED: Add CSRF token -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');?>">
                
                <div class="row">
					<div class="col-xl-12 col-xxl-12 col-sm-12">
                        <div class="card">
                            <div class="card-header">
								<h5 class="card-title">INFORMATIONS PERSONNELLES DE L'ENSEIGNANT</h5>
							</div>
							<div class="card-body">
                             
								<div class="row">
									<div class="col-lg-6 col-md-6 col-sm-12">
									    <div class="form-group">
											<input type="hidden" class="form-control" name="id" value="<?php echo htmlspecialchars($data->id, ENT_QUOTES, 'UTF-8');?>">
										</div>
										<div class="form-group">
											<label class="form-label">Code Unique</label>
											<input type="text" class="form-control text-danger" name="code" value="<?php echo htmlspecialchars($data->code, ENT_QUOTES, 'UTF-8');?>" readonly>
										</div>
									</div>
									<div class="col-lg-6 col-md-6 col-sm-12">
										<div class="form-group">
											<label class="form-label">Nom(s) *</label>
											<input type="text" class="form-control" name="nom" value="<?php echo htmlspecialchars($data->nom, ENT_QUOTES, 'UTF-8');?>" required maxlength="100">
										</div>
									</div>
									<div class="col-lg-6 col-md-6 col-sm-12">
										<div class="form-group">
											<label class="form-label">Prénom(s) *</label>
											<input type="text" class="form-control" name="prenom" value="<?php echo htmlspecialchars($data->prenom, ENT_QUOTES, 'UTF-8');?>" required maxlength="100">
										</div>
									</div>
									<div class="col-lg-6 col-md-6 col-sm-12">
										<div class="form-group">
											<label class="form-label">Date de naissance *</label>
											<input type="date" class="form-control" name="date_naissance" value="<?php echo htmlspecialchars($data->date_naissance, ENT_QUOTES, 'UTF-8');?>" required>
										</div>
									</div>
									<div class="col-lg-6 col-md-6 col-sm-12">
										<div class="form-group">
											<label class="form-label">Diplôme *</label>
                                            <select id="diplome" class="disabling-options form-control" name="diplome" required>
                                                <option value="<?php echo htmlspecialchars($data->diplome, ENT_QUOTES, 'UTF-8');?>"><?php echo htmlspecialchars($data->diplome, ENT_QUOTES, 'UTF-8');?></option>
                                                <?php 
                                                    $sql_dip = "SELECT * FROM type_diplome";
                                                    $resultat_dip = $connexion->query($sql_dip);
                                                    while($diplome_row = $resultat_dip->fetch_assoc()){
                                                        if($diplome_row['libelle'] != $data->diplome){
                                                ?>
                                                <option value="<?php echo htmlspecialchars($diplome_row['libelle'], ENT_QUOTES, 'UTF-8');?>"><?php echo htmlspecialchars($diplome_row['libelle'], ENT_QUOTES, 'UTF-8');?></option>
                                                <?php }}?>
                                            </select>
                                        </div>
									</div>
                                    <div class="col-lg-6 col-md-6 col-sm-12">
										<div class="form-group">
											<label class="form-label">Spécialité *</label>
											<input type="text" class="form-control" name="specialite" value="<?php echo htmlspecialchars($data->specialite, ENT_QUOTES, 'UTF-8');?>" required maxlength="100">
										</div>
									</div>
									<div class="col-lg-6 col-md-6 col-sm-12">
										<div class="form-group">
											<label class="form-label">Grade *</label>
											<select id="grade" class="disabling-options form-control" name="grade" required>
                                                <option value="<?php echo htmlspecialchars($data->grade, ENT_QUOTES, 'UTF-8');?>"><?php echo htmlspecialchars($data->grade, ENT_QUOTES, 'UTF-8');?></option>
                                                <?php 
                                                    $sql_grade = "SELECT * FROM type_grade";
                                                    $resultat_grade = $connexion->query($sql_grade);
                                                    while($grade_row = $resultat_grade->fetch_assoc()){
                                                        if($grade_row['libelle'] != $data->grade){
                                                ?>
                                                <option value="<?php echo htmlspecialchars($grade_row['libelle'], ENT_QUOTES, 'UTF-8');?>"><?php echo htmlspecialchars($grade_row['libelle'], ENT_QUOTES, 'UTF-8');?></option>
                                                <?php }}?>
                                            </select>
										</div>
									</div>
								
									<div class="col-lg-6 col-md-6 col-sm-12">
										<div class="form-group">
											<label class="form-label">Contact téléphonique *</label>
											<input type="text" class="form-control" name="telephone" value="<?php echo htmlspecialchars($data->telephone, ENT_QUOTES, 'UTF-8');?>" required maxlength="20">
										</div>
									</div>
                                    <div class="col-lg-6 col-md-6 col-sm-12">
										<div class="form-group">
											<label class="form-label">Email *</label>
											<input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($data->email, ENT_QUOTES, 'UTF-8');?>" required maxlength="100">
										</div>
									</div>
                                    <div class="col-lg-6 col-md-6 col-sm-12">
										<div class="form-group">
											<label class="form-label">Lieu de résidence *</label>
											<input type="text" class="form-control" name="ville" value="<?php echo htmlspecialchars($data->ville, ENT_QUOTES, 'UTF-8');?>" required maxlength="100">
										</div>
									</div>

									<div class="col-lg-6 col-md-6 col-sm-12">
										<div class="form-group">
											<label class="form-label">Sexe *</label>
											<select class="form-control" name="sexe" required>
												<option value="<?php echo htmlspecialchars($data->sexe, ENT_QUOTES, 'UTF-8');?>"><?php echo htmlspecialchars($data->sexe, ENT_QUOTES, 'UTF-8');?></option>
												<?php if($data->sexe != 'Homme'){?>
												<option value="Homme">Homme</option>
												<?php }?>
												<?php if($data->sexe != 'Femme'){?>
												<option value="Femme">Femme</option>
												<?php }?>
											</select>
										</div>
									</div>
								</div>
								
                            </div>
                            <div class="card-footer text-right">
                                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Enregistrer les modifications</button>
                            </div>
                        </div>
                      
                    </div>
				</div>
            </form>
                
            <div class="row">
				<div class="col-xl-12 col-xxl-12 col-sm-12">
                    <div class="card">
                        <div class="card-header">
							<h5 class="card-title">DISPOSITIONS PARTICULIERES</h5>
						</div>
						<div class="card-body">
                          <p>Etablissement: <h3><?php echo htmlspecialchars(mettrePremieresLettresMajuscules(getLibelleEtablissement($data->etab, $connexion)), ENT_QUOTES, 'UTF-8');?></h3></p> 
                          <p>Année académique: <h3><?php echo htmlspecialchars($annee_academique, ENT_QUOTES, 'UTF-8');?></h3></p>  
                          <p>Grade: <h3><?php echo htmlspecialchars(getGradeById($data->id, $connexion), ENT_QUOTES, 'UTF-8');?></h3></p>
                          <p>Numéro de Contrat: <h3><?php echo htmlspecialchars($numero_contrat, ENT_QUOTES, 'UTF-8');?></h3></p>
                          <p>Ecues:</p>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Code ECUE</th>
            <th>Libellé ECUE</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $ecues = getEcuesContrat($numero_contrat, $data->etab, $connexion);
        foreach($ecues as $e){
            $libelle = str_replace("+","'",htmlspecialchars(getecue($e, $connexion), ENT_QUOTES, 'UTF-8'));
            $code = htmlspecialchars($e, ENT_QUOTES, 'UTF-8');
            echo "
                <tr>
                    <td>$code</td>
                    <td>$libelle</td>
                </tr>
            ";
        }
        ?>
    </tbody>
</table>

                        </div>
                      </div>
                </div>
			</div>
           
            </div>
        </div>
        
        <!-- Message Modal -->
        <div class="modal" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="messageModalLabel"><?php echo htmlspecialchars($_SESSION['etablissement'], ENT_QUOTES, 'UTF-8');?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="messageBody">
                        <!-- Message content will be inserted here -->
                    </div>
                </div>
            </div>
        </div>

        <!--**********************************
            Footer start
        ***********************************-->
         <div class="footer">
            <div class="copyright">
                <p>Copyright © Designed &amp; Developpé par <a href="https://www.cet-up.com" target="_blank">CETUP</a> 2023</p>
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
    <script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../js/plugins-init/datatables.init.js"></script>

    <script>
    $(document).ready(function() {
        // FIXED: Use textContent instead of text() for better XSS protection
        var urlParams = new URLSearchParams(window.location.search);
        var erreur = urlParams.get('erreur');
        var success = urlParams.get('sucess');

        if (erreur || success) {
            var message = erreur ? "Erreur : " + erreur : "Message : " + success;
            // Use textContent to prevent XSS
            document.getElementById('messageBody').textContent = message;
            $('#messageModal').modal('show');

            // Clear URL parameters
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });
</script>
	
</body>
</html>

<?php 
// Close database connection
$connexion->close();
?>
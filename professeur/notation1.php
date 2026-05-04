<?php 

include '../php/connexion.php';
include '../php/lib.php';
session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="enseignant"){

    /*Handle bulk grade submission
   if(isset($_POST['bulk_save_grades'])){
    $classe = $_POST['classe'];
    $ecue = $_POST['ecue'];
    $semestre = $_POST['semestre'];
    $annee = $_POST['annee'];
    $type_examen = $_POST['type_examen'];
    
    $success_count = 0;
    $error_count = 0;
    $errors = [];
    
   
   $etablissement = $_SESSION['etablissement'];
        $verify_stmt = $connexion->prepare("SELECT * FROM vue_repartition WHERE code_ecue = ? AND semestre = ? AND etab = ? and classe=?");
        $verify_stmt->bind_param('ssss', $ecue, $semestre, $etablissement,$classe);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();
        
        if($verify_result->num_rows == 0) {
              header("location: notation1?erreur=Configuration introuvable pour cette classe/ECUE");
        exit();
        }
    
    // Start transaction
    $connexion->begin_transaction();
    
    try {
        // Vérifier si l'ECUE est enseigné dans le semestre choisi
       
      
        
        // Process each grade
        foreach($_POST['grades'] as $anonymat => $note) {
            if(empty($note) || $note === '') continue;
            
            // Validate grade (0-20)
            if($note < 0 || $note > 20) {
                $errors[] = "Note invalide pour $anonymat: $note";
                $error_count++;
                continue;
            }
            
            // Check if grade already exists in ligne1
            $check_stmt = $connexion->prepare("SELECT id FROM ligne1 WHERE anonymat = ? AND code_ecue = ? AND type_examen = ? AND annee = ?");
            $check_stmt->bind_param('ssss', $anonymat, $ecue, $type_examen, $annee);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if($check_result->num_rows > 0) {
                // UPDATE existing grade in ligne1
                $row = $check_result->fetch_assoc();
                $update_stmt = $connexion->prepare("UPDATE ligne1 SET note = ?, user_id = ? WHERE id = ?");
                $update_stmt->bind_param('dii', $note, $_SESSION['id_user'], $row['id']);
                
                if($update_stmt->execute()) {
                    $success_count++;
                } else {
                    $error_count++;
                    $errors[] = "Erreur mise à jour $anonymat";
                }
                $update_stmt->close();
            } else {
                // NSERT new grade in ligne1
                $ecue_libelle = str_replace("'", "+", $_POST['ecue_libelle']);
                $insert_stmt = $connexion->prepare("INSERT INTO ligne1 (anonymat, code_ecue, ecue, semestre, type_examen, note, annee, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $insert_stmt->bind_param('sssssdsi', $anonymat, $ecue, $ecue_libelle, $semestre, $type_examen, $note, $annee, $_SESSION['id_user']);
                
                if($insert_stmt->execute()) {
                    $success_count++;
                } else {
                    $error_count++;
                    $errors[] = "Erreur insertion $anonymat";
                }
                $insert_stmt->close();
            }
            $check_stmt->close();
            
      
            // Récupérer l'ID inscription depuis l'anonymat
            $etudiant_id = getIdInscriptionFromAnonymat($anonymat, $annee, $ecue, $semestre, $connexion);
            
            if($etudiant_id) {
                // Appeler la fonction centralisée
                mettreAJourNotationExamen($connexion, $etudiant_id, $ecue, $semestre, $classe, $annee, $type_examen, $note, $_SESSION['id_user'],$etablissement);
            } else {
                $errors[] = "Étudiant introuvable pour anonymat: $anonymat";
                $error_count++;
            }
        }
        
        // Log the action
        $userIP = $_SERVER['REMOTE_ADDR'];
        logUserAction($connexion, $_SESSION['id_user'], "Saisie en masse de notes - $success_count notes", date("Y-m-d H:i:s"), $userIP, "Classe: $classe, ECUE: $ecue, Type: $type_examen");
        
        $connexion->commit();
        
        if($error_count > 0) {
            header("location: notation1?sucess=" . urlencode("$success_count notes enregistrées, $error_count erreurs"));
        } else {
            header("location: notation1?sucess=" . urlencode("$success_count notes enregistrées avec succès"));
        }
        exit();
        
    } catch (Exception $e) {
        $connexion->rollback();
        header("location: notation1?erreur=" . urlencode($e->getMessage()));
        exit();
    }
}*/

    // Handle individual deletion (keep existing functionality)
   if(isset($_GET['sup'])){
    $id = intval($_GET['sup']); // Sécurisation
    $ecue = $_GET['ecue'];
    $annee = $_GET['annee'];
    $anonyme = $_GET['code'];
    $type = $_GET['examen'];
    $nature = $_GET['nature'];
    $etab = $_SESSION['etablissement'];
    
    // Démarrer la transaction
    $connexion->begin_transaction();
    
    try {
        // 1. Supprimer de ligne1 avec requête préparée
        $delete_stmt = $connexion->prepare("DELETE FROM ligne1 WHERE id = ?");
        if(!$delete_stmt){
            throw new Exception("Erreur de préparation de la requête de suppression: " . $connexion->error);
        }
        
        $delete_stmt->bind_param('i', $id);
        if(!$delete_stmt->execute()){
            throw new Exception("Erreur lors de la suppression: " . $delete_stmt->error);
        }
        
        if($delete_stmt->affected_rows == 0){
            throw new Exception("Aucune note trouvée avec cet ID");
        }
        $delete_stmt->close();
        
        // 2. Récupérer les informations nécessaires
        $semestre = getSemestreByAnonymat($nature,$type,$anonyme, $ecue, $annee, $connexion,$_SESSION['etablissement']);
        if(!$semestre){
            throw new Exception("Semestre introuvable pour cet anonymat");
        }
        
        $classe = getClasseByAnonymat($nature,$type,$anonyme, $ecue, $annee, $semestre, $connexion,$_SESSION['etablissement']);
        if(!$classe){
            throw new Exception("Classe introuvable pour cet anonymat");
        }
        
        $etudiant = getIdInscriptionFromAnonymat($nature,$type,$anonyme,$annee,$ecue,$semestre,$connexion,$_SESSION['etablissement']);
        if(!$etudiant){
            throw new Exception("Inscription étudiant introuvable");
        }
        
        // 3. Vérifier l'inscription notation
        $id_notation = verifierInscriptionNotation($connexion, $etudiant, $ecue, $semestre, $classe, $annee);
        
        if($id_notation !== null){
            // 4. Mettre à jour la table notation avec requête préparée
            if($type == "Session Ordinaire"){
                
                      $sql_avg = "
    SELECT AVG(l.note) AS moyenne
    FROM ligne1 l
    JOIN anonymat a ON l.anonymat = a.numero
    WHERE l.semestre = ?
      AND l.annee = ?
      AND l.type_examen = ?
      AND l.etab = ?
      AND l.code_ecue = ?
      AND a.etudiant = ?
   ";

$stmt = $connexion->prepare($sql_avg);
$stmt->bind_param(
    "sssssi",
    $semestre,
    $annee,
    $type,
    $etab,
    $ecue,
    $etudiant
);

$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$avg = ($row['moyenne']) !== null ? $row['moyenne'] : null ; // ← 
                
                $update_stmt = $connexion->prepare("UPDATE notation SET moyEx =? WHERE id = ?");
            } else {
                $update_stmt = $connexion->prepare("UPDATE notation SET session_rappel = ? WHERE id = ?");
            }
            
            if(!$update_stmt){
                throw new Exception("Erreur de préparation de la mise à jour notation: " . $connexion->error);
            }
            
            $update_stmt->bind_param('di',$avg, $id_notation);
            if(!$update_stmt->execute()){
                throw new Exception("Erreur lors de la mise à jour notation: " . $update_stmt->error);
            }
            $update_stmt->close();
        }
        
        // 5. Logger l'action
        $userIP = $_SERVER['REMOTE_ADDR'];
        logUserAction(
            $connexion,
            $_SESSION['id_user'],
            "Suppression d'une note",
            date("Y-m-d H:i:s"),
            $userIP,
            "ID supprimé: $id, Anonymat: $anonyme, ECUE: $ecue, Type: $type"
        );
        
        // 6. Valider la transaction
        $connexion->commit();
        
        header("location: notation1?sucess=" . urlencode("Suppression effectuée avec succès"));
        exit();
        
    } catch (Exception $e) {
        // Annuler la transaction en cas d'erreur
        $connexion->rollback();
        
        // Logger l'erreur
        error_log("Erreur suppression note ID $id: " . $e->getMessage());
        
        header("location: notation1?erreur=" . urlencode("Erreur: " . $e->getMessage()));
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $_SESSION['univ'];?> - Scolarité de  <?php echo $_SESSION['etablissement'];?> </title>
    <link rel="icon" type="image/png" sizes="16x16" href="../administrateur/<?php echo  $_SESSION['logo_univ']?>">
	<link rel="stylesheet" href="../vendor/bootstrap-select/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="../vendor/select2/css/select2.min.css">
    <link rel="stylesheet" href="../css/style.css">
	<link rel="stylesheet" href="../css/skin.css">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .bulk-entry-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .grade-input {
            width: 80px;
            text-align: center;
        }
        .student-row {
            transition: background-color 0.2s;
        }
        .student-row:hover {
            background-color: #f1f1f1;
        }
        .grade-saved {
            background-color: #d4edda !important;
        }
        .filter-section {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: end;
        }
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        #students-table-wrapper {
            max-height: 600px;
            overflow-y: auto;
        }
        .sticky-header {
            position: sticky;
            top: 0;
            background: white;
            z-index: 10;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
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
        <?php include "header.php" ;?>
        <?php include 'nav.php'; ?>
		
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
                            <li class="breadcrumb-item"><a href="../gesnote/">Gesnote</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0);">Note</a></li>
                        </ol>
                    </div>
                </div>

                <!-- BULK GRADE ENTRY SECTION -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title"><i class="fas fa-list"></i> Saisie en masse des notes</h4>
                            </div>
                            <div class="card-body">
                                <div class="bulk-entry-form">
                                    <form id="filter-form">
                                        <div class="filter-section">
                                            <div class="filter-group">
                                                <label>Classe</label>
                                                <select class="disabling-options" class="form-control form-control-lg" id="filter-classe" name="classe" required>
                                                    <option value="">-- Sélectionner --</option>
                                                    <?php 
                                                        $sql = "SELECT DISTINCT classe FROM repartition_enseignant WHERE code='".$_SESSION["code_enseignant"]."'";
                                                        $result = $connexion->query($sql);
                                                        while($row = $result->fetch_assoc()){
                                                            echo "<option value='".$row['classe']."'>".$row['classe']."</option>";
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                            
                                            <div class="filter-group">
                                                <label>ECUE</label>
                                                <select class="disabling-options" class="form-control form-control-lg" id="filter-ecue" name="ecue" required>
                                                    <option value="">-- Sélectionner --</option>
                                                    <?php 
                                                        $sql = "SELECT * FROM ecue WHERE code_ecue IN (SELECT code_ecue FROM repartition_enseignant WHERE code='".$_SESSION["code_enseignant"]."')";
                                                        $result = $connexion->query($sql);
                                                        while($row = $result->fetch_assoc()){
                                                            echo "<option value='".$row['code_ecue']."' data-libelle='".str_replace("'","+",$row['libelle'])."'>".str_replace("+","'",$row['libelle'])." - ".$row['code_ecue']."</option>";
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                            
                                            <div class="filter-group">
                                                   <label>Semestre </label>
                          
                                <select class="disabling-options" class="form-control" id="filter-semestre" name="semestre" required >
                                    <option selected=""></option>
                                    <?php 
                                    $sql="select * from semestre where libelle in (select semestre from repartition_enseignant where code='".$_SESSION["code_enseignant"]."')";
                                    $resultat=$connexion->query($sql);
                                    while($etablissement =$resultat->fetch_assoc()){
                                    ?>
                                    <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                    <?php }?>
                                </select>
                       
                                            </div>
                                            
                                            <div class="filter-group">
                                                <label>Année académique</label>
                                                <select class="disabling-options" class="form-control form-control-lg"  id="filter-annee" name="annee" required>
                                                    <option value="">-- Sélectionner --</option>
                                                    <?php 
                                                        $sql = "SELECT * FROM annee ORDER BY libelle DESC";
                                                        $result = $connexion->query($sql);
                                                        while($row = $result->fetch_assoc()){
                                                            echo "<option value='".str_replace("+","'",$row['libelle'])."'>".str_replace("+","'",$row['libelle'])."</option>";
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                            
                                            <div class="filter-group">
                                                <label>Type d'examen</label>
                                                <select class="form-control" id="filter-type" name="type_examen" required>
                                                    <option value="">-- Sélectionner --</option>
                                                    <option value="Session Ordinaire">Session Ordinaire</option>
                                                    <option value="Session de Rappel">Session de Rappel</option>
                                                </select>
                                            </div>
                                            
                                              <div class="filter-group">
                                                <label>Nature</label>
                                                <select class="form-control" id="filter-nature" name="nature" required>
                                                    <option value="">-- Sélectionner --</option>
                                                    <option value="Examen Theorique" selected="">Examen Theorique</option>
                                                    <option value="Examen Pratique">Examen Pratique</option>
                                                </select>
                                            </div>
                                            
                                            <div class="filter-group">
                                                <button type="button" class="btn btn-primary btn-sm" id="load-students">
                                                    <i class="fas fa-search"></i> Charger les étudiants
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                
                                <div id="students-list" style="display:none;">
                                    <form id="grades-form" method="POST" action="save_bulk_grades.php">
                                       
                                        <input type="hidden" name="classe" id="save-classe">
                                        <input type="hidden" name="ecue" id="save-ecue">
                                        <input type="hidden" name="ecue_libelle" id="save-ecue-libelle">
                                        <input type="hidden" name="semestre" id="save-semestre">
                                        <input type="hidden" name="annee" id="save-annee">
                                        <input type="hidden" name="type_examen" id="save-type">
                                         <input type="text" name="nature" id="save-nature">
                                        
                                        <div class="d-flex justify-content-between mb-3">
                                            <h5>Étudiants trouvés: <span id="student-count">0</span></h5>
                                            <button type="submit" class="btn btn-success btn-lg">
                                                <i class="fas fa-save"></i> Enregistrer toutes les notes
                                            </button>
                                        </div>
                                        
                                        <div id="students-table-wrapper">
                                            <table class="table table-bordered table-striped">
                                                <thead class="sticky-header">
                                                    <tr>
                                                        <th width="10%">N°</th>
                                                        <th width="30%">Code Anonymat</th>
                                                        <th width="30%">Note (0-20)</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="students-tbody">
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                        <div class="text-center mt-3">
                                            <button type="submit" class="btn btn-success btn-lg">
                                                <i class="fas fa-save"></i> Enregistrer toutes les notes
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>

        <!-- Message Modal -->
        <div class="modal" id="messageModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><?php echo $_SESSION['univ'];?></h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="messageBody"></div>
                </div>
            </div>
        </div>

        <div class="footer">
            <div class="copyright">
                <p>Copyright © Designed &amp; Developpé  par <a href="htpps:/www.cet-up.com" target="_blank">CETUP</a> 2023</p>
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
    <script>
    $(document).ready(function() {
        // Show message modal if needed
        var urlParams = new URLSearchParams(window.location.search);
        var erreur = urlParams.get('erreur');
        var success = urlParams.get('sucess');

        if (erreur || success) {
            var message = erreur ? "Erreur : " + erreur : "Succès : " + success;
            $('#messageBody').text(message);
            $('#messageModal').modal('show');
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        // Load students based on filters
        $('#load-students').click(function() {
            var classe = $('#filter-classe').val();
            var ecue = $('#filter-ecue').val();
            var semestre = $('#filter-semestre').val();
            var annee = $('#filter-annee').val();
            var type = $('#filter-type').val();
           var nature = $('#filter-nature').val();

            if(!classe || !ecue || !semestre || !annee || !type || !nature) {
                alert('Veuillez remplir tous les champs');
                return;
            }

            // Show loading
            $('#load-students').html('<i class="fas fa-spinner fa-spin"></i> Chargement...');

            // AJAX call to load students
            $.ajax({
                url: 'ajax_load_students.php',
                method: 'POST',
                data: {
                    classe: classe,
                    ecue: ecue,
                    semestre: semestre,
                    annee: annee,
                    type_examen: type,
                    nature : nature
                },
                success: function(response) {
                    var data = JSON.parse(response);
                    
                    if(data.error) {
                        alert(data.error);
                        return;
                    }

                    // Populate hidden fields
                    $('#save-classe').val(classe);
                    $('#save-ecue').val(ecue);
                    $('#save-ecue-libelle').val($('#filter-ecue option:selected').data('libelle'));
                    $('#save-semestre').val(semestre);
                    $('#save-annee').val(annee);
                    $('#save-type').val(type);
                     $('#save-nature').val(nature);

                    // Build table
                    var tbody = '';
                    $.each(data.students, function(index, student) {
                        var existingGrade = student.existing_note || '';
                        tbody += '<tr class="student-row">';
                        tbody += '<td>' + (index + 1) + '</td>';
                        tbody += '<td>' + student.anonymat + '</td>';
                        tbody += '<td><input type="number" class="form-control grade-input" name="grades[' + student.anonymat + ']" min="0" max="20" step="0.01" value="' + existingGrade + '" placeholder="0-20"></td>';
                        tbody += '</tr>';
                    });

                    $('#students-tbody').html(tbody);
                    $('#student-count').text(data.students.length);
                    $('#students-list').show();
                    $('#load-students').html('<i class="fas fa-search"></i> Charger les étudiants');

                    // Scroll to students list
                    $('html, body').animate({
                        scrollTop: $('#students-list').offset().top - 100
                    }, 500);
                },
                error: function() {
                    alert('Erreur lors du chargement des étudiants');
                    $('#load-students').html('<i class="fas fa-search"></i> Charger les étudiants');
                }
            });
        });

        // Form validation before submit
        $('#grades-form').submit(function(e) {
            var hasGrades = false;
            $('.grade-input').each(function() {
                if($(this).val() !== '') {
                    hasGrades = true;
                    return false;
                }
            });

            if(!hasGrades) {
                e.preventDefault();
                alert('Veuillez entrer au moins une note avant d\'enregistrer');
                return false;
            }

            return confirm('Êtes-vous sûr de vouloir enregistrer ces notes?');
        });
    });
    </script>
	
</body>
</html>

<?php 
}else{
    header("location: ../deconnexion1");
}
?>
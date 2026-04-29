<?php 
session_start();

include '../php/connexion.php';
include '../php/lib.php';




$userIP = $_SERVER['REMOTE_ADDR'];


if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="enseignant"){
 
 $enseignant =  strtoupper(getNomPrenomEnseignant($_SESSION["code_enseignant"], $connexion));
 
    
    // Traitement de la soumission des notes AVEC MOYENNES
if($_POST && isset($_POST['sauvegarder_notes'])) {
    $ecue = $_POST['ecue'];
    $classe = $_POST['classe'];
    $semestre = $_POST['semestre'];
    $annee = $_POST['annee'];
    $type_evaluation = $_POST['type_evaluation'];
    
    $erreurs = [];
    $succes = 0;
    $debug_messages = [] ;
    
    
    if(VerifierSemestre($connexion, $semestre, $annee, $_SESSION['etablissement']))
    {
         header("location: notation2?erreur=Acces Interdit.");
         exit;
  
    }
        
    
    
    
    
    
    
    // ✅ Pour stocker les messages de debug
    
    // DÉMARRER LA TRANSACTION
    $connexion->begin_transaction();
    
    try {
        foreach($_POST['moyennes'] as $etudiant_id => $moyenne) {
            if(!empty($moyenne) && is_numeric($moyenne) && $moyenne >= 0 && $moyenne <= 20) {
                
                // Modifier si une note existe déjà (PREPARED STATEMENT)
                $stmt_check = $connexion->prepare("SELECT id FROM ligne2 WHERE etudiant=? AND code_ecue=? AND semestre=? AND annee=? AND type=? AND etab=?");
                $stmt_check->bind_param("ssssss", $etudiant_id, $ecue, $semestre, $annee, $type_evaluation, $_SESSION['etablissement']);
                $stmt_check->execute();
                $result_check = $stmt_check->get_result();
                
                if($result_check->num_rows > 0) {
                    // MISE À JOUR (PREPARED STATEMENT)
                    $row = $result_check->fetch_assoc();
                    $stmt_update = $connexion->prepare("UPDATE ligne2 SET note=?, user_id=? WHERE id=?");
                    $stmt_update->bind_param("dii", $moyenne, $_SESSION['id_user'], $row['id']);
                    
                    if($stmt_update->execute()) {
                        $succes++;
                        // ✅ Capturer les messages de debug
                        $result_notation = mettreAJourNotation($connexion, $etudiant_id, $ecue, $semestre, $classe, $annee, $_SESSION['id_user'],$_SESSION['etablissement']);
                        if($result_notation) {
                            $debug_messages[] = $result_notation;
                        }
                    } else {
                        throw new Exception("Erreur mise à jour étudiant ID: $etudiant_id");
                    }
                    $stmt_update->close();
                    
                } else {
                    // Récupérer infos ECUE (PREPARED STATEMENT)
                    $stmt_ecue = $connexion->prepare("SELECT ecue.libelle, specialite FROM ecue JOIN ue ON ecue.code_ue = ue.code WHERE ecue.code_ecue=? AND ecue.etab=?");
                    $stmt_ecue->bind_param("ss", $ecue, $_SESSION['etablissement']);
                    $stmt_ecue->execute();
                    $ecue_info = $stmt_ecue->get_result()->fetch_assoc();
                    $stmt_ecue->close();
                    
                    if(!$ecue_info) {
                        throw new Exception("ECUE introuvable pour le code: $ecue");
                    }
                    
                    // INSERTION (PREPARED STATEMENT)
                    $stmt_insert = $connexion->prepare("INSERT INTO ligne2 (etudiant, ecue, code_ecue, classe, specialite, type, note, semestre, annee, etab, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt_insert->bind_param("ssssssdsssi", 
                        $etudiant_id, 
                        $ecue_info['libelle'], 
                        $ecue, 
                        $classe, 
                        $ecue_info['specialite'], 
                        $type_evaluation, 
                        $moyenne, 
                        $semestre, 
                        $annee, 
                        $_SESSION['etablissement'], 
                        $_SESSION['id_user']
                    );
                    
                    if($stmt_insert->execute()) {
                        $succes++;
                        // ✅ Capturer les messages de debug
                        $result_notation = mettreAJourNotation($connexion, $etudiant_id, $ecue, $semestre, $classe, $annee, $_SESSION['id_user'],$_SESSION["etablissement"]);
                        if($result_notation) {
                            $debug_messages[] = $result_notation;
                        }
                        logUserAction($connexion, $_SESSION['id_user'], "ajout d'une note de devoir", date("Y-m-d H:i:s"), $userIP, "étudiant: $etudiant_id, moyenne: $moyenne");
                    } else {
                        throw new Exception("Erreur insertion étudiant ID: $etudiant_id");
                    }
                    $stmt_insert->close();
                }
                
                $stmt_check->close();
            }
        }
        
        // VALIDER LA TRANSACTION
        $connexion->commit();
        
        if($succes > 0) {
            $message = "$succes notes enregistrées avec succès";
           
            header("location: notation2?classe=".$classe."&annee=".$annee."&sucess=" . urlencode($message));
        } else {
            header("location: notation2?classe=".$classe."&annee=".$annee."&erreur=" . urlencode("Aucune note valide à enregistrer"));
        }
        
    } catch(Exception $e) {
        // ANNULER LA TRANSACTION EN CAS D'ERREUR
        $connexion->rollback();
        $erreurs[] = $e->getMessage();
        // ✅ Ajouter les messages de debug aux erreurs
        if(count($debug_messages) > 0) {
            $erreurs[] = "DEBUG: " . implode(" | ", $debug_messages);
        }
        header("location: notation2?classe=".$classe."&annee=".$annee."&erreur=" . urlencode("Erreur: " . implode(", ", $erreurs)));
    }
    
    exit();
}

 
    // Traitement de suppression
  if(isset($_GET['sup'])){
    $id = $_GET['sup'];
    $ecue = $_GET['ecue'];
    $semestre = $_GET['semestre'];
    $annee = $_GET['annee'];
    $etudiant = $_GET['etudiant'];
    $classe = $_GET['classe'];
    
    // ✅ TRANSACTION pour garantir la cohérence
    $connexion->begin_transaction();
    
    try {
        // ✅ DELETE avec PREPARED STATEMENT (protection SQL injection)
        $stmt_delete = $connexion->prepare("DELETE FROM ligne2 WHERE id=?");
        $stmt_delete->bind_param("i", $id);
        
        if(!$stmt_delete->execute()) {
            throw new Exception("Erreur lors de la suppression: " . $stmt_delete->error);
        }
        
        // ✅ Vérifier si une ligne a bien été supprimée
        if($stmt_delete->affected_rows == 0) {
            throw new Exception("Aucune note trouvée avec cet ID");
        }
        
        $stmt_delete->close();
        
        // Log de l'action
        logUserAction($connexion, $_SESSION['id_user'], "suppression d'une note de devoir", date("Y-m-d H:i:s"), $userIP, "ID supprimé: $id");
        
        // ✅ Recalculer la moyenne après suppression
        $nouvelleNote = moyenneGeneraleDevoirs($connexion, $etudiant, $ecue, $semestre, $annee);
        $id_notation = verifierInscriptionNotation($connexion, $etudiant, $ecue, $semestre, $classe, $annee);
        
        if($id_notation !== null && $id_notation > 0) {
            // ✅ UPDATE avec PREPARED STATEMENT
            $stmt_update = $connexion->prepare("UPDATE notation SET moyDev=?, user_id=? WHERE id=?");
            $stmt_update->bind_param("dii", $nouvelleNote, $_SESSION['id_user'], $id_notation);
            
            if(!$stmt_update->execute()) {
                throw new Exception("Erreur mise à jour notation: " . $stmt_update->error);
            }
            
            $stmt_update->close();
        } else {
            // ✅ Si aucune ligne notation n'existe, ce n'est pas une erreur
            // (cas où c'était la seule note et il n'y a plus rien à calculer)
        }
        
        // ✅ VALIDER LA TRANSACTION
        $connexion->commit();
        
        header("location: notation2?classe=" . urlencode($classe) . "&annee=" . urlencode($annee) . "&sucess=" . urlencode("Suppression effectuée avec succès"));
        
    } catch(Exception $e) {
        // ✅ ANNULER en cas d'erreur
        $connexion->rollback();
        header("location: notation2?classe=" . urlencode($classe) . "&annee=" . urlencode($annee) . "&erreur=" . urlencode("Erreur: " . $e->getMessage()));
    }
    
    exit();
}
    
    // Récupération des classes de l'enseignant
    $classes = getClasseEnseignant($_SESSION["code_enseignant"],$_SESSION["annee"],$_SESSION["etablissement"],$connexion);
    
    // Variables pour le système bullmark
    $etudiants = [];
    $notes_existantes = [];
    $show_table = false;
    $show_old_table = true;
    
    // Si tous les critères sont sélectionnés, afficher le tableau de saisie massive
    if(isset($_GET['ecue_bulk']) && isset($_GET['classe_bulk']) && isset($_GET['semestre_bulk']) && isset($_GET['annee_bulk']) && isset($_GET['type_evaluation'])) {
        $ecue = $_GET['ecue_bulk'];
        $classe = $_GET['classe_bulk'];
        $semestre = $_GET['semestre_bulk'];
        $annee = $_GET['annee_bulk'];
        $type_evaluation = $_GET['type_evaluation'];
        $show_table = true;
        $show_old_table = false;
        
        
          if(VerifierSemestre($connexion, $semestre, $annee, $_SESSION['etablissement'])) {
        header("location: notation2?erreur=" . urlencode("Accès interdit : ce semestre est clôturé."));
        exit;
    }
        
        // Récupérer les étudiants de la classe pour l'année donnée
        $sql_etudiants = "SELECT i.id, i.candidat FROM inscription i JOIN candidat c on i.candidat=c.code
                         WHERE i.classe='$classe' AND i.annee='$annee' AND i.etab='".$_SESSION['etablissement']."' 
                         ORDER BY c.nom, c.prenom";
        $result_etudiants = $connexion->query($sql_etudiants);
        
        while($etudiant = $result_etudiants->fetch_assoc()) {
            $nom_prenom = obtenirNomPrenom($etudiant['candidat'], $annee, $connexion);
            $etudiants[] = [
                'id' => $etudiant['id'],
                'nom_prenom' => str_replace("+", "'", $nom_prenom)
            ];
        }
        
       
        // Récupérer les notes existantes
        $sql_notes = "SELECT etudiant, note FROM ligne2 
                     WHERE code_ecue='$ecue' AND classe='$classe' AND semestre='$semestre' AND annee='$annee' 
                     AND type='$type_evaluation' AND etab='".$_SESSION['etablissement']."'";
        $result_notes = $connexion->query($sql_notes);
        
        while($note = $result_notes->fetch_assoc()) {
            $notes_existantes[$note['etudiant']] = $note['note'];
        }
    }
    
    // Récupérer classe et année depuis l'URL pour l'ancien système
    $classe_filter = isset($_GET["classe"]) ? $_GET["classe"] : '';
    $annee_filter = isset($_GET["annee"]) ? $_GET["annee"] : '';
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
         include 'nav.php';
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
                            <h3>Gestion des notes des étudiants</h3>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../enseignant/">Enseignant</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0);">Note</a></li>
                        </ol>
                    </div>
                </div>

                <!-- Formulaire de sélection des critères pour saisie massive -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                             
                            </div>
                            <div class="card-body">
                                <form method="GET" action="">
                                    <div class="row">
                                        <div class="col-md-2">
                                            
                                            
                                            
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <label class="input-group-text">Classe</label>
                                                </div>
                                                <select name="classe_bulk" class="disabling-options" required>
                                                    <option value="">Sélectionner</option>
                                                    <?php 
                                                    $sql = "SELECT DISTINCT classe FROM repartition_enseignant WHERE code='".$_SESSION["code_enseignant"]."' AND etab='".$_SESSION['etablissement']."'";
                                                    $resultat = $connexion->query($sql);
                                                    while($classe_item = $resultat->fetch_assoc()){
                                                    ?>
                                                    <option value="<?php echo $classe_item['classe'];?>" 
                                                        <?php echo (isset($_GET['classe_bulk']) && $_GET['classe_bulk'] == $classe_item['classe']) ? 'selected' : ''; ?>>
                                                        <?php echo str_replace("+","'",$classe_item['classe']);?>
                                                    </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <label class="input-group-text">Ecue</label>
                                                </div>
                                                <select name="ecue_bulk" class="disabling-options" required>
                                                    <option value="">Sélectionner</option>
                                                    <?php 
                                                    $sql = "SELECT DISTINCT e.code_ecue, e.libelle FROM ecue e 
                                                           JOIN repartition_enseignant r ON e.code_ecue = r.code_ecue 
                                                           WHERE r.code='".$_SESSION["code_enseignant"]."' AND e.etab='".$_SESSION['etablissement']."'";
                                                    $resultat = $connexion->query($sql);
                                                    while($ecue_item = $resultat->fetch_assoc()){
                                                    ?>
                                                    <option value="<?php echo $ecue_item['code_ecue'];?>"
                                                        <?php echo (isset($_GET['ecue_bulk']) && $_GET['ecue_bulk'] == $ecue_item['code_ecue']) ? 'selected' : ''; ?>>
                                                        <?php echo str_replace("+","'",$ecue_item['libelle']."[".$ecue_item["code_ecue"]."]");?>
                                                    </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <label class="input-group-text">Semestre</label>
                                                </div>
                                                <select name="semestre_bulk" class="disabling-options" required>
                                                    <option value="">Sélectionner</option>
                                                    <?php 
                                                    $sql = "SELECT DISTINCT semestre FROM repartition_enseignant WHERE code='".$_SESSION["code_enseignant"]."'";
                                                    $resultat = $connexion->query($sql);
                                                    while($semestre_item = $resultat->fetch_assoc()){
                                                    ?>
                                                    <option value="<?php echo $semestre_item['semestre'];?>"
                                                        <?php echo (isset($_GET['semestre_bulk']) && $_GET['semestre_bulk'] == $semestre_item['semestre']) ? 'selected' : ''; ?>>
                                                        <?php echo str_replace("+","'",$semestre_item['semestre']);?>
                                                    </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <label class="input-group-text">Evaluation</label>
                                                </div>
                                                <select name="type_evaluation" class="disabling-options" required>
                                                    <option value="">Sélectionner</option>
                                                   
                                                    <option value="Devoir de classe" <?php echo (isset($_GET['type_evaluation']) && $_GET['type_evaluation'] == 'Devoir de classe') ? 'selected' : ''; ?>>Devoir de classe</option>
                                                    
                                                     <option value="Devoir pratique" <?php echo (isset($_GET['type_evaluation']) && $_GET['type_evaluation'] == 'Devoir pratique') ? 'selected' : ''; ?>>Devoir pratique</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <label class="input-group-text">Année</label>
                                                </div>
                                                <select name="annee_bulk" class="disabling-options" required>
                                                    <option value="">Sélectionner</option>
                                                    <?php 
                                                    $sql = "SELECT DISTINCT annee FROM repartition_enseignant WHERE code='".$_SESSION["code_enseignant"]."'";
                                                    $resultat = $connexion->query($sql);
                                                    while($annee_item = $resultat->fetch_assoc()){
                                                    ?>
                                                    <option value="<?php echo $annee_item['annee'];?>"
                                                        <?php echo (isset($_GET['annee_bulk']) && $_GET['annee_bulk'] == $annee_item['annee']) ? 'selected' : ''; ?>>
                                                        <?php echo str_replace("+","'",$annee_item['annee']);?>
                                                    </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-1">
                                            
                                            <input type="hidden" name="prof" value="<?php echo $enseignant; ?>">
                                            <button type="submit" class="btn btn-primary" style="margin-top: 8px;">
                                                <i class="la la-search"></i> Charger
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if($show_table && count($etudiants) > 0): ?>
                <!-- Tableau de saisie des notes -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">
                                    
                                    
                                      <div class="float-left">
                                   <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#ajouterDevoirModal">
                                        <i class="la la-plus"></i> Ajouter un devoir
                                    </button>
                                    
                               <button type="button" class="btn btn-sm btn-info ml-2" onclick="imprimerReleveNotes()">
    <i class="la la-print"></i> Imprimer le relevé
</button>


<button class="btn btn-sm btn-danger"> <span id="compteur-notes">0</span> / <?php echo count($etudiants); ?> étudiants notés
                               </button>

                                        
                                   
                                </div>
                                   
                             
                            </div>
                            <div class="card-body">
                                <form method="POST" action="" id="formNotes">
                                    <input type="hidden" name="sauvegarder_notes" value="1">
                                    <input type="hidden" name="ecue" value="<?php echo $ecue; ?>">
                                    <input type="hidden" name="classe" value="<?php echo $classe; ?>">
                                    <input type="hidden" name="semestre" value="<?php echo $semestre; ?>">
                                    <input type="hidden" name="annee" value="<?php echo $annee; ?>">
                                    <input type="hidden" name="type_evaluation" value="<?php echo $type_evaluation; ?>">

                                    <div class="table-responsive">
                                        <table class="display" id="tableNotes" style="min-width: 845px">
                                            <thead>
                                                <tr id="headerRow">
                                                    <th>N°</th>
                                                    <th>Etudiant</th>
                                                    <th class="note-column">Devoir 1 (/20)</th>
                                                    <th>Statut</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tableBody">
                                                <?php 
                                                $count = 1;
                                                foreach($etudiants as $etudiant): 
                                                    $note_actuelle = isset($notes_existantes[$etudiant['id']]) ? $notes_existantes[$etudiant['id']] : '';
                                                    $statut_class = !empty($note_actuelle) ? 'btn-success' : 'btn-warning';
                                                    $statut_text = !empty($note_actuelle) ? 'Noté' : 'Non noté';
                                                ?>
                                                <tr data-etudiant-id="<?php echo $etudiant['id']; ?>">
                                                    <td><?php echo $count; ?></td>
                                                    <td><strong><?php echo $etudiant['nom_prenom']; ?></strong></td>
                                                    <td class="note-cell" data-devoir-id="1">
                                                        <input type="number" 
                                                               name="notes_details[<?php echo $etudiant['id']; ?>][1]" 
                                                               class="form-control note-input" 
                                                               min="0" 
                                                               max="20" 
                                                               step="0.25" 
                                                               value="<?php echo $note_actuelle; ?>"
                                                               placeholder="0.00"
                                                               style="width: 120px;"
                                                               oninput="updateMoyenne(<?php echo $etudiant['id']; ?>)">
                                                    </td>
                                                    <td>
                                                        <span class="btn btn-sm <?php echo $statut_class; ?> statut-badge" id="statut-<?php echo $etudiant['id']; ?>">
                                                            <?php echo $statut_text; ?>
                                                        </span>
                                                        <!-- CHAMP CACHÉ POUR LA MOYENNE -->
                                                        <input type="hidden" name="moyennes[<?php echo $etudiant['id']; ?>]" id="moyenne-<?php echo $etudiant['id']; ?>" value="<?php echo $note_actuelle; ?>">
                                                    </td>
                                                </tr>
                                                <?php 
                                                $count++;
                                                endforeach; 
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="row mt-4">
                                        <div class="col-md-8">
                                            <div class="alert alert-secondary">
                                                <strong><i class="la la-info-circle"></i> Instructions:</strong>
                                                Saisissez les notes sur 20 • Décimales autorisées (ex: 15.25) • Laissez vide si absent • La moyenne est calculée automatiquement
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-right">
                                            <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#viderNotesModal">
                                                <i class="la la-eraser"></i> Vider tout
                                            </button>
                                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#sauvegarderModal">
                                                <i class="la la-save"></i> Sauvegarder toutes les notes
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Ajouter Devoir -->
                <div class="modal fade" id="ajouterDevoirModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="la la-plus-circle"></i> Ajouter un nouveau devoir</h5>
                                <button type="button" class="close" data-dismiss="modal">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p>Voulez-vous ajouter une nouvelle colonne de notes pour ce devoir ?</p>
                                <p class="text-muted">Une colonne sera ajoutée pour tous les étudiants.</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                <button type="button" class="btn btn-success" onclick="ajouterNouveauDevoir()">
                                    <i class="la la-check"></i> Confirmer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                
                
                 

                <!-- Modal Vider Notes -->
                <div class="modal fade" id="viderNotesModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header bg-warning">
                                <h5 class="modal-title"><i class="la la-exclamation-triangle"></i> Vider toutes les notes</h5>
                                <button type="button" class="close" data-dismiss="modal">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p><strong>Attention !</strong> Cette action supprimera toutes les notes saisies.</p>
                                <p class="text-muted">Êtes-vous sûr de vouloir continuer ?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                <button type="button" class="btn btn-warning" onclick="viderToutesNotes()">
                                    <i class="la la-eraser"></i> Vider tout
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Sauvegarder -->
                <div class="modal fade" id="sauvegarderModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title"><i class="la la-save"></i> Sauvegarder les notes</h5>
                                <button type="button" class="close text-white" data-dismiss="modal">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p>Êtes-vous sûr de vouloir sauvegarder toutes les notes ?</p>
                                <p class="text-muted">Les moyennes seront calculées et enregistrées dans la base de données.</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                <button type="button" class="btn btn-success" onclick="sauvegarderNotes()">
                                    <i class="la la-check"></i> Confirmer la sauvegarde
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Supprimer Devoir -->
                <div class="modal fade" id="supprimerDevoirModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title"><i class="la la-trash"></i> Supprimer le devoir</h5>
                                <button type="button" class="close text-white" data-dismiss="modal">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p>Êtes-vous sûr de vouloir supprimer ce devoir ?</p>
                                <p class="text-muted">Toutes les notes associées seront supprimées.</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                <button type="button" class="btn btn-danger" onclick="confirmerSuppressionDevoir()">
                                    <i class="la la-trash"></i> Supprimer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if($show_old_table): ?>
                <!-- Ancien tableau de consultation -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">
                                    <?php if($classe_filter && $annee_filter): ?>
                                        Classe : <?php echo $classe_filter;?> | Année-Académique : <?php echo $annee_filter;?>
                                    <?php else: ?>
                                        Evaluations continues
                                    <?php endif; ?>
                                </h4>
                                <?php if ($_SESSION['statut'] == 1) {?>
                                    <a href="add-library.html" class="btn btn-primary" data-toggle="modal" data-target="#typeAgentModal">+ Nouveau</a>
                                <?php } ?>
                                
                                
                                </h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="example3" class="display" style="min-width: 845px">
                                        <thead>
                                            <tr>
                                                <th>N°</th>
                                                <th>Etudiant</th>
                                                <th>Ecue</th>
                                                <th>Specialité</th>
                                                <th>Evaluation</th>
                                                <th>Note</th>
                                                <th>Semestre</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $sql = "SELECT * FROM ligne2 WHERE classe IN (SELECT classe FROM repartition_enseignant WHERE code='".$_SESSION["code_enseignant"]."') AND user_id=".$_SESSION["id_user"];
                                            
                                            if($classe_filter && $annee_filter) {
                                                $sql .= " AND classe='$classe_filter' AND annee='$annee_filter'";
                                            }
                                            
                                            $resultat = $connexion->query($sql);
                                            $count = 1;
                                            while($ue = $resultat->fetch_assoc()){
                                            ?>
                                            <tr heigth="15%">
                                                <td><?php echo $count;?></td>
                                                <td><?php echo str_replace("+","'", obtenirNomPrenom(obtenirCodeById($ue['etudiant'],$connexion),$ue['annee'],$connexion));?></td>
                                                <td><?php echo str_replace("+","'",$ue['ecue']);?></td>
                                                <td><?php echo $ue['specialite'];?></td>
                                                <td><?php echo $ue['type'];?></td>
                                                <td><?php echo $ue['note'];?></td>	
                                                <td><?php echo $ue['semestre'];?></td>		
                                                <td>			
                                                    <a href="notation2?sup=<?php echo $ue['id'];?>&classe=<?php echo $ue['classe']?>&etudiant=<?php echo $ue['etudiant']?>&semestre=<?php echo $ue['semestre']?>&ecue=<?php echo $ue['code_ecue']?>&annee=<?php echo $ue['annee']?>" class="btn btn-sm btn-danger"><i class="la la-trash-o"></i>supprimer</a>
                                                </td>												
                                            </tr>
                                            <?php $count++; }?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
				
            </div>
        </div>
        <!--**********************************
            Content body end
        ***********************************-->

        <!-- Modal Modification (ancien système) -->
        <div class="modal" id="modifierModal" tabindex="-1" role="dialog" aria-labelledby="modifierModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modifierModalLabel">Modification</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Formulaire de modification -->
                        <form  method="post" action="traitement">
                            <input type="hidden" id="noteId" name="noteId">

                            <div class="form-group">
                                <label for="nouveauMoyDev">Moyenne Devoir</label>
                                <input type="number" min="0" step="0.25" max="20" class="form-control" id="nouveauMoyDev" name="nouveauMoyDev" >
                            </div>

                            <div class="form-group">
                                <label for="nouveauMoyEx">Moyenne Examen</label>
                                <input type="number" min="0"  step="0.25" max="20" class="form-control" id="nouveauMoyEx" name="nouveauMoyEx" >
                            </div>
                            <div class="form-group">
                                <label for="sessionrappel">Session de rappel</label>
                                <input type="number" min="0" step="0.25" max="20" class="form-control" id="sessionrappel" name="nouveausession" >
                            </div>
                            <button type="submit" class="btn btn-success">Sauvegarder</button>
                            <button  class="btn btn-danger">Annuler</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Ajout individuel (ancien système) -->
        <div class="modal fade bd-example-modal-lg" id="typeAgentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title" id="exampleModalLabel">FORMULAIRE D'ENREGISTREMENT D'UNE EVALUATION</h3>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Formulaire pour enregistrer le type d'agent -->
                        <form id="typeAgentForm" method="post" action="traitement1">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <label class="input-group-text">Etudiant </label>
                                </div>
                                <select  class="disabling-options"  name="etudiant" required>
                                    <option selected=""></option>
                                    <?php 
                                    if($classe_filter && $annee_filter) {
                                        $sql="select * from inscription where classe='$classe_filter' and annee='$annee_filter'";
                                    } else {
                                        $sql="select * from inscription where etab='".$_SESSION['etablissement']."'";
                                    }
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
                                    <label class="input-group-text">Classe </label>
                                </div>
                                <select id="classe" class="disabling-options" class="form-control form-control-lg" name="classe" required>
                                    <option selected=""></option>
                                    <?php 
                                    $sql="select * from classe where libelle IN (SELECT DISTINCT classe FROM repartition_enseignant WHERE code='".$_SESSION["code_enseignant"]."')";
                                    $resultat=$connexion->query($sql);
                                    while($etablissement =$resultat->fetch_assoc()){
                                    ?>
                                    <option><?php echo str_replace("+","'",$etablissement['libelle']);?></option>
                                    <?php }?>
                                </select>
                            </div>

                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <label class="input-group-text">Ecue </label>
                                </div>
                                <select class="disabling-options" class="form-control form-control-lg" name="ecue" required >
                                    <option selected=""></option>
                                    <?php 
                                    $sql="select * from ecue where code_ecue in (select code_ecue from repartition_enseignant where code='".$_SESSION["code_enseignant"]."')";
                                    $resultat=$connexion->query($sql);
                                    while($etablissement =$resultat->fetch_assoc()){
                                    ?>
                                    <option value="<?php echo $etablissement["code_ecue"];?>"><?php echo str_replace("+","'",$etablissement['libelle'])."-".$etablissement["code_ecue"];;?></option>
                                    <?php }?>
                                </select>
                            </div>

                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <label class="input-group-text">Semestre </label>
                                </div>
                                <select class="disabling-options" class="form-control form-control-lg" name="semestre" required >
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

                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <label class="input-group-text">Evaluation  </label>
                                </div>
                                <select class="disabling-options" class="form-control form-control-lg" name="examen" >
                                    <option selected=""></option>
                                    <option value="Evaluation Continue">Evaluation Continue</option>
                                  
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="note">Note </label>
                                <input type="number" min="0" step="0.25" max="20" class="form-control" id="note" name="note" required>
                            </div>
                        
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <label class="input-group-text">Année académique  </label>
                                </div>
                                <select class="disabling-options" class="form-control form-control-lg" name="annee" >
                                    <option selected=""></option>
                                    <?php 
                                    $sql="select * from annee where libelle in (select annee from repartition_enseignant where code='".$_SESSION["code_enseignant"]."')"; 
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

        <!-- Modal Message -->
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
    function imprimerReleveNotes() {
    // Récupérer les paramètres depuis l'URL
    const urlParams = new URLSearchParams(window.location.search);
    const classe = urlParams.get('classe_bulk');
    const ecue = urlParams.get('ecue_bulk');
    const semestre = urlParams.get('semestre_bulk');
    const typeEvaluation = urlParams.get('type_evaluation');
    const annee = urlParams.get('annee_bulk');
    const prof = urlParams.get('prof');
    
    // DEBUG: Afficher les paramètres récupérés
    console.log('Paramètres récupérés:', {
        classe: classe,
        ecue: ecue,
        semestre: semestre,
        typeEvaluation: typeEvaluation,
        annee: annee
    });
    
    // Validation
    if (!classe || !ecue || !semestre || !typeEvaluation || !annee) {
        alert('Veuillez d\'abord charger les étudiants en sélectionnant tous les critères !');
        return;
    }
    
    // Récupérer le libellé de l'ECUE depuis le DOM
    const ecueInfo = document.querySelector('.card-header .float-right small');
    let libelleEcue = ecue;
    if (ecueInfo) {
        const text = ecueInfo.textContent;
        const match = text.match(/^([^|]+)/);
        if (match) {
            libelleEcue = match[1].trim();
        }
    }
    
    // Préparer les filtres
    const filters = {
        classe: classe,
        ecue: ecue,
        semestre: semestre,
        examen: typeEvaluation,
        annee: annee,
        nature: typeEvaluation
    };
    
    // Faire une requête AJAX pour récupérer les données
    $.ajax({
        url: 'get_notes_print.php',
        type: 'GET',
        data: {
            classe: classe,
            ecue: ecue,
            semestre: semestre,
            type_evaluation: typeEvaluation,
            annee: annee
        },
        dataType: 'json',
        success: function(data) {
            console.log('Réponse reçue:', data); // DEBUG
            if (data.success && data.notes && data.notes.length > 0) {
                createPrintContentNotes(data.notes, filters, libelleEcue,prof);
            } else {
                console.error('Données reçues:', data); // DEBUG
                alert('Aucune note trouvée pour ces critères. Message: ' + (data.message || 'Aucun message'));
            }
        },
        error: function(xhr, status, error) {
            console.error('Erreur AJAX:', {xhr: xhr, status: status, error: error});
            console.error('Réponse brute:', xhr.responseText); // DEBUG
            alert('Erreur lors de la récupération des données: ' + error);
        }
    });
}

function createPrintContentNotes(notes, filters, libelleEcue, prof) {
    var printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Relevé de Notes - Impression</title>
            <style>
                @page {
                    size: A4;
                    margin: 15mm;
                }
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 20px;
                }
                .header-univ {
                    display: flex;
                    justify-content: space-between;
                    align-items: flex-start;
                    margin-bottom: 30px;
                    padding-bottom: 15px;
                    border-bottom: 2px solid #333;
                }
                .header-left, .header-right {
                    flex: 1;
                }
                .header-center {
                    text-align: center;
                    padding: 0 20px;
                }
                .header-center img {
                    max-width: 100px;
                    height: auto;
                }
                .custom-font {
                    font-weight: bold;
                    margin: 5px 0;
                }
                .header-left h4, .header-right h4 {
                    font-size: 14px;
                    margin: 5px 0;
                }
                .header-left h5 {
                    font-size: 12px;
                    margin: 5px 0;
                }
                .header-left p, .header-right p {
                    font-size: 11px;
                    margin: 3px 0;
                }
                .header-title {
                    text-align: center;
                    margin: 20px 0 30px 0;
                }
                .header-title h1 {
                    margin: 0;
                    font-size: 24px;
                    color: #333;
                }
                .header-title h2 {
                    margin: 5px 0;
                    font-size: 18px;
                    color: #666;
                }
                .info-section {
                    margin-bottom: 20px;
                    background: #f5f5f5;
                    padding: 15px;
                    border-radius: 5px;
                }
                .info-section p {
                    margin: 5px 0;
                    font-size: 14px;
                }
                .info-section strong {
                    color: #333;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }
                th, td {
                    border: 1px solid #333;
                    padding: 10px;
                    text-align: left;
                }
                th {
                    background-color: #333;
                    color: white;
                    font-weight: bold;
                }
                tr:nth-child(even) {
                    background-color: #f9f9f9;
                }
                .note-cell {
                    font-weight: bold;
                    font-size: 16px;
                    text-align: center;
                }
                .footer {
                    margin-top: 30px;
                    text-align: center;
                    font-size: 12px;
                    color: #666;
                    border-top: 1px solid #ccc;
                    padding-top: 10px;
                }
                @media print {
                    .no-print {
                        display: none;
                    }
                    body {
                        padding: 0;
                    }
                }
            </style>
        </head>
        <body>
            <div class="header-univ">
                <div class="header-left">
                    <h4 class="custom-font">UNIVERSITÉ DENIS SASSOU-N'GUESSO</h4>
                    <h5 class="custom-font">DIRECTION DE LA SCOLARITÉ ET DES EXAMENS</h5>
                    <p>SERVICE DE LA SCOLARITÉ ET DES EXAMENS</p>
                </div>
                <div class="header-center">
                    <img src="../images/univ.png" alt="Logo de l'université">
                </div>
                <div class="header-right">
                    <h4 class="custom-font">Rigueur-Excellence-Lumières</h4>
                    <p>${filters.etablissement || ''}</p>
                    ${filters.type_etablissement === 'faculté' ? '<p class="custom-font">VICE-DÉCANAT</p>' : ''}
                </div>
            </div>
            
            <div class="header-title">
                <h1>Relevé de Notes</h1>
                <h2>${filters.examen}</h2>
            </div>
            
            <div class="info-section">
                <p><strong>ECUE:</strong> ${libelleEcue}</p>
                <p><strong>Classe:</strong> ${filters.classe}</p>
                <p><strong>Semestre:</strong> ${filters.semestre}</p>
                <p><strong>Type d'évaluation:</strong> ${filters.examen}</p>
                <p><strong>Année académique:</strong> ${filters.annee}</p>
                <p><strong>Date d'impression:</strong> ${new Date().toLocaleDateString('fr-FR')}</p>
                <p><strong>Enseignant :</strong> ${prof}</p>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">N°</th>
                        <th>Matricule</th>
                        <th>Nom et Prénom</th>
                        <th style="width: 120px;">Note (/20)</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    // Add rows
    notes.forEach(function(item, index) {
        const note = parseFloat(item.note);
        
        printContent += `
            <tr>
                <td style="text-align: center;">${index + 1}</td>
                <td>${item.matricule || 'N/A'}</td>
                <td>${item.nom_prenom}</td>
                <td class="note-cell">${note.toFixed(2)}</td>
            </tr>
        `;
    });
    
    printContent += `
                </tbody>
            </table>
            
            <div class="footer">
                <p>Document généré le ${new Date().toLocaleString('fr-FR')}</p>
                <p>UDSN - Système de Gestion Scolaire</p>
            </div>
        </body>
        </html>
    `;
    
    // Open print window
    var printWindow = window.open('', '_blank');
    printWindow.document.write(printContent);
    printWindow.document.close();
    
    // Wait for content to load then print
    printWindow.onload = function() {
        printWindow.focus();
        printWindow.print();
    };
}

    </script>

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

        // Initialiser le compteur
        updateCompteur();
    });

    // Gérer le clic sur le bouton "Modifier" (ancien système)
    $('.btn-primary').click(function() {
        var id = $(this).data('id');
        var x = $(this).data('moydev');
        var y= $(this).data('moyex');
        var z = $(this).data('note');

        $('#noteId').val(id);
        $('#nouveauMoyDev').val(x);
        $('#nouveauMoyEx').val(y);
        $('#sessionrappel').val(z);
    });

    // FONCTION POUR CALCULER LA MOYENNE D'UN ÉTUDIANT
    function calculerMoyenne(etudiantId) {
        const row = document.querySelector(`tr[data-etudiant-id="${etudiantId}"]`);
        const inputs = row.querySelectorAll('.note-input');
        
        let total = 0;
        let count = 0;
        
        inputs.forEach(input => {
            const note = parseFloat(input.value);
            if (!isNaN(note) && input.value !== '') {
                total += note;
                count++;
            }
        });
        
        // Si aucune note, retourner null
        if (count === 0) return null;
        
        // Calculer et arrondir à 2 décimales
        const moyenne = (total / count).toFixed(2);
        return moyenne;
    }

    // FONCTION POUR METTRE À JOUR LE CHAMP CACHÉ DE MOYENNE
    function updateMoyenne(etudiantId) {
        const moyenne = calculerMoyenne(etudiantId);
        const moyenneInput = document.getElementById(`moyenne-${etudiantId}`);
        
        if (moyenne !== null) {
            moyenneInput.value = moyenne;
        } else {
            moyenneInput.value = '';
        }
        
        // Mettre à jour le statut
        updateStatut(etudiantId);
    }

    // FONCTION POUR METTRE À JOUR LE STATUT (avec moyenne affichée)
    function updateStatut(etudiantId) {
        const row = document.querySelector(`tr[data-etudiant-id="${etudiantId}"]`);
        const inputs = row.querySelectorAll('.note-input');
        const statutBadge = document.getElementById(`statut-${etudiantId}`);
        const moyenne = calculerMoyenne(etudiantId);
        
        let hasNote = false;
        inputs.forEach(input => {
            if (input.value !== '') {
                hasNote = true;
            }
        });
        
        if (hasNote && moyenne !== null) {
            statutBadge.className = 'btn btn-sm btn-success statut-badge';
            statutBadge.innerHTML = `Noté<br><small>Moy: ${moyenne}/20</small>`;
        } else {
            statutBadge.className = 'btn btn-sm btn-warning statut-badge';
            statutBadge.textContent = 'Non noté';
        }
        
        updateCompteur();
    }

    function updateCompteur() {
        const rows = document.querySelectorAll('#tableBody tr');
        let counted = 0;
        
        rows.forEach(row => {
            const inputs = row.querySelectorAll('.note-input');
            let hasNote = false;
            inputs.forEach(input => {
                if (input.value !== '') {
                    hasNote = true;
                }
            });
            if (hasNote) counted++;
        });
        
        const compteur = document.getElementById('compteur-notes');
        if(compteur) {
            compteur.textContent = counted;
        }
    }

    function viderToutesNotes() {
        const inputs = document.querySelectorAll('.note-input');
        inputs.forEach(input => {
            input.value = '';
        });
        
        // Mettre à jour tous les statuts et moyennes
        const rows = document.querySelectorAll('#tableBody tr');
        rows.forEach(row => {
            const etudiantId = row.getAttribute('data-etudiant-id');
            updateMoyenne(etudiantId);
        });
        
        // Fermer le modal
        $('#viderNotesModal').modal('hide');
        
        showToast('info', 'Toutes les notes ont été vidées');
    }

    // FONCTION POUR CALCULER TOUTES LES MOYENNES AVANT SAUVEGARDE
    function calculerToutesMoyennes() {
        const rows = document.querySelectorAll('#tableBody tr');
        rows.forEach(row => {
            const etudiantId = row.getAttribute('data-etudiant-id');
            updateMoyenne(etudiantId);
        });
    }

    // FONCTION POUR SAUVEGARDER (calcule les moyennes puis soumet)
    function sauvegarderNotes() {
        // Calculer toutes les moyennes avant envoi
        calculerToutesMoyennes();
        
        // Vérifier qu'il y a au moins une note
        let hasAnyNote = false;
        document.querySelectorAll('.note-input').forEach(input => {
            if (input.value !== '') hasAnyNote = true;
        });
        
        if (!hasAnyNote) {
            alert('Aucune note n\'a été saisie !');
            $('#sauvegarderModal').modal('hide');
            return;
        }
        
        // Fermer le modal
        $('#sauvegarderModal').modal('hide');
        
        // Soumettre le formulaire
        document.getElementById('formNotes').submit();
    }

    function showToast(type, message) {
        const bgColor = type === 'success' ? '#28a745' : type === 'warning' ? '#ffc107' : '#17a2b8';
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${bgColor};
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            z-index: 9999;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        `;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.transition = 'opacity 0.5s';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    }

    let devoirIdCounter = 1;
    let devoirNumCounter = 1;
    let devoirIdToDelete = null;

    function ajouterNouveauDevoir() {
        devoirIdCounter++;
        devoirNumCounter++;
        
        const devoirId = devoirIdCounter;
        const devoirNum = devoirNumCounter;
        
        // Ajouter une nouvelle colonne dans le header
        const headerRow = document.getElementById('headerRow');
        const statutHeader = headerRow.querySelector('th:last-child');
        
        const newHeader = document.createElement('th');
        newHeader.className = 'note-column';
        newHeader.innerHTML = `Devoir ${devoirNum} (/20) <button type="button" class="btn btn-sm btn-danger ml-2" onclick="preparerSuppressionDevoir(${devoirId})" title="Supprimer ce devoir"><i class="la la-times"></i></button>`;
        newHeader.setAttribute('data-devoir-id', devoirId);
        newHeader.setAttribute('data-devoir-num', devoirNum);
        
        headerRow.insertBefore(newHeader, statutHeader);
        
        // Ajouter une nouvelle cellule de note dans chaque ligne
        const rows = document.querySelectorAll('#tableBody tr');
        rows.forEach(row => {
            const etudiantId = row.getAttribute('data-etudiant-id');
            const statutCell = row.querySelector('td:last-child');
            
            const newCell = document.createElement('td');
            newCell.className = 'note-cell';
            newCell.setAttribute('data-devoir-id', devoirId);
            newCell.innerHTML = `
                <input type="number" 
                       name="notes_details[${etudiantId}][${devoirId}]" 
                       class="form-control note-input" 
                       min="0" 
                       max="20" 
                       step="0.25" 
                       placeholder="0.00"
                       style="width: 120px;"
                       oninput="updateMoyenne(${etudiantId})">
            `;
            
            row.insertBefore(newCell, statutCell);
        });
        
        // Fermer le modal
        $('#ajouterDevoirModal').modal('hide');
        
        // Mettre à jour toutes les moyennes
        const rowsAll = document.querySelectorAll('#tableBody tr');
        rowsAll.forEach(row => {
            const etudiantId = row.getAttribute('data-etudiant-id');
            updateMoyenne(etudiantId);
        });
        
        // Afficher un message de succès
        showToast('success', `Devoir ${devoirNum} ajouté avec succès !`);
    }

    function preparerSuppressionDevoir(devoirId) {
        devoirIdToDelete = devoirId;
        $('#supprimerDevoirModal').modal('show');
    }

    function confirmerSuppressionDevoir() {
        if (devoirIdToDelete === null) return;
        
        const devoirId = devoirIdToDelete;
        
        // Récupérer le numéro du devoir avant suppression
        const header = document.querySelector(`#headerRow th[data-devoir-id="${devoirId}"]`);
        const devoirNum = header ? header.getAttribute('data-devoir-num') : '';
        
        // Supprimer le header
        if (header) {
            header.remove();
        }
        
        // Supprimer toutes les cellules correspondantes
        const cells = document.querySelectorAll(`#tableBody td[data-devoir-id="${devoirId}"]`);
        cells.forEach(cell => cell.remove());
        
        // Renuméroter les devoirs restants
        renumeroterDevoirs();
        
        // Recalculer toutes les moyennes
        const rows = document.querySelectorAll('#tableBody tr');
        rows.forEach(row => {
            const etudiantId = row.getAttribute('data-etudiant-id');
            updateMoyenne(etudiantId);
        });
        
        // Fermer le modal
        $('#supprimerDevoirModal').modal('hide');
        
        // Afficher un message de succès
        showToast('warning', `Devoir ${devoirNum} supprimé`);
        
        devoirIdToDelete = null;
    }

    function renumeroterDevoirs() {
        const headers = document.querySelectorAll('#headerRow th.note-column');
        devoirNumCounter = headers.length;
        
        headers.forEach((header, index) => {
            const nouveauNum = index + 1;
            header.setAttribute('data-devoir-num', nouveauNum);
            
            const devoirId = header.getAttribute('data-devoir-id');
            header.innerHTML = `Devoir ${nouveauNum} (/20) <button type="button" class="btn btn-sm btn-danger ml-2" onclick="preparerSuppressionDevoir(${devoirId})" title="Supprimer ce devoir"><i class="la la-times"></i></button>`;
        });
    }
    </script>
	
</body>
</html>

<?php 
} else {
    header("location: ../deconnexion");
}
?>
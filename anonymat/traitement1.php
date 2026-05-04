<?php 

include '../php/connexion.php';
include '../php/lib.php';
session_start();

/**
 * Generate anonymous codes in format: Letter(A-Z) + Number(0-9)
 * Maximum 260 codes possible (26 letters × 10 digits)
 */
function generateAnonymousCodes($existing_codes) {
    $codes = [];
    
    for($letter = ord('A'); $letter <= ord('Z'); $letter++) {
        for($number = 0; $number <= 9; $number++) {
            $code = chr($letter) . $number;
            if(!in_array($code, $existing_codes)) {
                $codes[] = $code;
            }
        }
    }
    
    return $codes;
}

if(isset($_POST['etudiant']) && isset($_POST['ecue']) && isset($_POST['classe']) && 
   isset($_POST['semestre']) && isset($_POST['examen']) && isset($_POST['annee'])) {
    
    $etudiant = $_POST['etudiant'];
    $ecue = $_POST['ecue'];
       $nature = $_POST['nature'];
    $classe = $_POST['classe'];
    $semestre = $_POST['semestre'];
    $examen = $_POST['examen'];
    $annee = $_POST['annee'];
    $nature = isset($_POST['nature']) ? $_POST['nature'] : 'Examen Theorique';
    $etab = $_SESSION['etablissement'];
    $agent = $_SESSION['id_user'];
    
    // Check if student is enrolled in the class
    if(!estInscritDansClasse($etudiant, $classe, $annee, $connexion)) {
        header("location: index?erreur=L'etudiant n'est pas inscrit");
        exit;
    }
    
    // Verify ECUE is taught in this class
    if(!verifierEcueClasse($ecue, $classe, $_SESSION['etablissement'], $connexion)) {
        header("location: index?erreur=L' ECUE $ecue n'est pas enseignée dans cette classe $classe");
        exit;
    }
    
    // Verify ECUE is taught in this semester for this class
    if(!verifierEcueClasseSemestre($ecue, $classe, $semestre, $_SESSION['etablissement'], $connexion)) {
        header("location: index?erreur=L' ECUE $ecue n'est pas enseignée dans ce semestre pour la classe $classe");
        exit;
    }
    
    // Validate classe assignment for current user
    if(isset($_SESSION["classe"])) {
        if($_SESSION["classe"] !== "") {
            if($_SESSION["classe"] !== $classe) {
                header("location: index?erreur=Vous n'avez pas été attribué cette classe");
                exit;
            }
        }
    }
    
    // Validate ECUE assignment for current user
    if(isset($_SESSION["ecue"])) {
        if($_SESSION["ecue"] !== "") {
            if($_SESSION["ecue"] !== $ecue) {
                header("location: index?erreur=ERREUR : Vous n'avez pas été attribué cette ECUE");
                exit;
            }
        }
    }
    
    // Check if this student already has an anonymous code for this exam
    $check_existing = "SELECT numero FROM anonymat
                      WHERE etudiant = '$etudiant'
                      AND code_ecue = '$ecue'
                      AND classe = '$classe'
                      AND semestre = '$semestre'
                      AND type = '$examen'
                      AND annee = '$annee'
                      AND etab = '$etab'";
    
    if($nature !== '') {
        $check_existing .= " AND nature = '$nature'";
    }
    
    $existing_result = $connexion->query($check_existing);
    
    if($existing_result && $existing_result->num_rows > 0) {
        $existing_data = $existing_result->fetch_assoc();
        header("location: index?erreur=Cet étudiant a déjà un code anonymat pour cet examen : " . $existing_data['numero']);
        exit;
    }
    
    // Get ECUE details for specialite and libelle
    $sql_ecue = "SELECT specialite, e.libelle FROM ecue e JOIN ue u ON e.code_ue = u.code
                 WHERE e.code_ecue = '$ecue' AND e.etab = '$etab'";
    $result_ecue = $connexion->query($sql_ecue);
    
    if(!$result_ecue || $result_ecue->num_rows == 0) {
        header("location: index?erreur=ECUE introuvable");
        exit;
    }
    
    $ecue_data    = $result_ecue->fetch_assoc();
    $specialite   = $ecue_data['specialite'];
    $ecue_libelle = $ecue_data['libelle'] ?? $ecue;

    // Get existing codes to generate new ones
    $sql_codes = "SELECT numero FROM anonymat
                  WHERE code_ecue = '$ecue'
                  AND classe = '$classe'
                  AND semestre = '$semestre'
                  AND type = '$examen'
                  AND annee = '$annee'
                  AND etab = '$etab'";
    $resultat_codes = $connexion->query($sql_codes);
    $existing_codes = [];
    while($row = $resultat_codes->fetch_assoc()) {
        $existing_codes[] = $row['numero'];
    }
    
    // Generate available codes
    $available_codes = generateAnonymousCodes($existing_codes);
    
    if(count($available_codes) == 0) {
        header("location: index?erreur=Pas assez de codes disponibles. Maximum 260 étudiants par ECUE/Examen.");
        exit;
    }
    
    // Get the first available code
    $code = $available_codes[0];
    
    // Insert anonymat record
    $sql = "INSERT INTO anonymat (etudiant, classe, specialite, numero, type, ecue, code_ecue, user, annee, semestre, etab";

    if ($nature !== '') {
        $sql .= ", nature";
    }

    $sql .= ") VALUES ('$etudiant', '$classe', '$specialite', '$code', '$examen', '$ecue_libelle', '$ecue', '$agent', '$annee', '$semestre', '$etab'";

    if ($nature !== '') {
        $sql .= ", '$nature'";
    }

    $sql .= ")";
    
    if($connexion->query($sql)) {
        $userIP = $_SERVER['REMOTE_ADDR'];
        
        logUserAction(
            $connexion,
            $_SESSION['id_user'],
            "anonymation d'une copie d'examen",
            date("Y-m-d H:i:s"),
            $userIP,
            "valeur enregistrée : $code+$examen+$ecue+$annee+$classe"
        );
        
        header("location: index?sucess=Opération effectuée avec succès. CODE ANONYMAT : $code");
    } else {
        header("location: index?erreur=" . $connexion->error);
    }
}

?>
<?php 
// Désactiver l'affichage des erreurs pour éviter de casser le JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Capturer les erreurs dans un log
ini_set('log_errors', 1);


// S'assurer que la réponse est en JSON
header('Content-Type: application/json');

try {
    include '../php/connexion.php';
    include '../php/lib.php';

    session_start();

    if(!isset($_SESSION['id']) || $_SESSION['id'] != session_id() || $_SESSION['role'] != "gesnote"){
        echo json_encode(array('success' => false, 'message' => 'Accès non autorisé'));
        exit();
    }
    
    if(!isset($_POST['id_note'])){
        echo json_encode(array('success' => false, 'message' => 'Données incomplètes - id_note manquant'));
        exit();
    }
    
    $id_note = $connexion->real_escape_string($_POST['id_note']);
    $etudiant_id = $connexion->real_escape_string($_POST['inscription']);
    $code_ecue = $connexion->real_escape_string($_POST['code_ecue']);
    $semestre = $connexion->real_escape_string($_POST['semestre']);
    $annee = $connexion->real_escape_string($_POST['annee']);
    $classe = $connexion->real_escape_string($_POST['classe']);
    $etab = $_SESSION['etablissement'];
    
  
    
    // Variables pour stocker les notes
    $moyDev = null;
    $moyEx = null;
    $session_rappel = null;
    
    // =========================================================
    // 1. MOYENNE DES DEVOIRS depuis ligne2
    // =========================================================
    $sql_ligne2 = "SELECT AVG(note) as moyenne_devoirs 
                   FROM ligne2 
                   WHERE etudiant='$etudiant_id' 
                   AND TRIM(code_ecue)=TRIM('$code_ecue')
                   AND semestre='$semestre' 
                   AND annee='$annee' 
                   AND etab='$etab'";
    
    $result_ligne2 = $connexion->query($sql_ligne2);
    
    if(!$result_ligne2){
        throw new Exception("Erreur requête ligne2: " . $connexion->error);
    }
    
    if($result_ligne2->num_rows > 0){
        $row_ligne2 = $result_ligne2->fetch_assoc();
        if($row_ligne2['moyenne_devoirs'] !== null){
            $moyDev = round($row_ligne2['moyenne_devoirs'], 2);
        }
    }
    
    // =========================================================
    // 2. MOYENNE DES EXAMENS (Théoriques + Pratiques) 
    //    Session Ordinaire depuis ligne1 via anonymat
    // =========================================================
    
    // Récupérer TOUS les numéros d'anonymat pour Session Ordinaire
    $sql_get_numeros = "SELECT DISTINCT numero, nature
                        FROM anonymat 
                        WHERE etudiant='$etudiant_id' 
                        AND ecue='$code_ecue'
                        AND semestre='$semestre' 
                        AND annee='$annee' 
                        AND etab='$etab'
                        AND type='Session Ordinaire'";
    
    $result_get_numeros = $connexion->query($sql_get_numeros);
    
    if(!$result_get_numeros){
        throw new Exception("Erreur requête anonymat ordinaire: " . $connexion->error);
    }
    
    if($result_get_numeros->num_rows > 0){
        $numeros_anonymat = array();
        while($row_numero = $result_get_numeros->fetch_assoc()){
            $numeros_anonymat[] = "'" . $connexion->real_escape_string($row_numero['numero']) . "'";
        }
        
        if(count($numeros_anonymat) > 0){
            // Récupérer la moyenne de TOUS les examens
            $numeros_list = implode(',', $numeros_anonymat);
            
            $sql_ligne1 = "SELECT AVG(note) as moyenne_examens 
                           FROM ligne1 
                           WHERE anonymat IN ($numeros_list)
                           AND code_ecue='$code_ecue'
                           AND semestre='$semestre' 
                           AND annee='$annee' 
                           AND etab='$etab'
                           AND type_examen='Session Ordinaire'";
            
            $result_ligne1 = $connexion->query($sql_ligne1);
            
            if(!$result_ligne1){
                throw new Exception("Erreur requête ligne1 ordinaire: " . $connexion->error);
            }
            
            if($result_ligne1->num_rows > 0){
                $row_ligne1 = $result_ligne1->fetch_assoc();
                if($row_ligne1['moyenne_examens'] !== null){
                    $moyEx = round($row_ligne1['moyenne_examens'], 2);
                }
            }
        }
    }
    
    // =========================================================
    // 3. SESSION DE RAPPEL (Théoriques + Pratiques si existe)
    // =========================================================
    
    // Récupérer TOUS les numéros d'anonymat pour Session Rappel
    $sql_get_numeros_rappel = "SELECT DISTINCT numero, nature
                               FROM anonymat 
                               WHERE etudiant='$etudiant_id' 
                               AND ecue='$code_ecue'
                               AND semestre='$semestre' 
                               AND annee='$annee' 
                               AND etab='$etab'
                               AND type='Session Rappel'";
    
    $result_get_numeros_rappel = $connexion->query($sql_get_numeros_rappel);
    
    if(!$result_get_numeros_rappel){
        throw new Exception("Erreur requête anonymat rappel: " . $connexion->error);
    }
    
    if($result_get_numeros_rappel->num_rows > 0){
        $numeros_anonymat_rappel = array();
        while($row_numero_rappel = $result_get_numeros_rappel->fetch_assoc()){
            $numeros_anonymat_rappel[] = "'" . $connexion->real_escape_string($row_numero_rappel['numero']) . "'";
        }
        
        if(count($numeros_anonymat_rappel) > 0){
            // Récupérer la moyenne de TOUS les examens de rappel
            $numeros_rappel_list = implode(',', $numeros_anonymat_rappel);
            
            $sql_ligne1_rappel = "SELECT AVG(note) as moyenne_rappel 
                                  FROM ligne1 
                                  WHERE anonymat IN ($numeros_rappel_list)
                                  AND code_ecue='$code_ecue'
                                  AND semestre='$semestre' 
                                  AND annee='$annee' 
                                  AND etab='$etab'
                                  AND type_examen='Session Rappel'";
            
            $result_ligne1_rappel = $connexion->query($sql_ligne1_rappel);
            
            if(!$result_ligne1_rappel){
                throw new Exception("Erreur requête ligne1 rappel: " . $connexion->error);
            }
            
            if($result_ligne1_rappel->num_rows > 0){
                $row_ligne1_rappel = $result_ligne1_rappel->fetch_assoc();
                if($row_ligne1_rappel['moyenne_rappel'] !== null){
                    $session_rappel = round($row_ligne1_rappel['moyenne_rappel'], 2);
                }
            }
        }
    }
    
    // =========================================================
    // 4. CALCUL DE LA MOYENNE GÉNÉRALE (logique des triggers)
    // =========================================================
    $moyGen = null;
    if($moyDev !== null && $moyEx !== null){
        // Si pas de session rappel OU session rappel <= moyEx
        if($session_rappel === null || $session_rappel <= $moyEx){
            $moyGen = round(($moyDev + $moyEx) / 2, 2);
        } else {
            // Session rappel > moyEx, on utilise session_rappel
            $moyGen = round(($moyDev + $session_rappel) / 2, 2);
        }
    }
    
    // =========================================================
    // 5. MISE À JOUR DE LA TABLE NOTATION
    // =========================================================
    $sql_update = "UPDATE notation SET 
                   moyDev = " . ($moyDev !== null ? "'$moyDev'" : "NULL") . ",
                   moyEx = " . ($moyEx !== null ? "'$moyEx'" : "NULL") . ",
                   session_rappel = " . ($session_rappel !== null ? "'$session_rappel'" : "NULL") . ",
                   moyGen = " . ($moyGen !== null ? "'$moyGen'" : "NULL") . "
                   WHERE id = '$id_note' AND etab = '$etab'";
    
    if(!$connexion->query($sql_update)){
        throw new Exception("Erreur UPDATE notation: " . $connexion->error);
    }
    
    // Log de l'action
    logUserAction(
        $connexion,
        $_SESSION['id_user'],
        "Actualisation d'une note",
        date("Y-m-d H:i:s"),
        $_SERVER['REMOTE_ADDR'],
        "ID note: $id_note - Etudiant: $etudiant_id - Devoir: $moyDev - Examen: $moyEx - Rappel: $session_rappel - Moyenne: $moyGen"
    );
    
    $response = array(
        'success' => true,
        'message' => 'Notes actualisées avec succès',
        'data' => array(
            'etudiant_id' => $etudiant_id,
            'moyDev' => $moyDev !== null ? $moyDev : 'N/A',
            'moyEx' => $moyEx !== null ? $moyEx : 'N/A',
            'session_rappel' => $session_rappel !== null ? $session_rappel : 'N/A',
            'moyGen' => $moyGen !== null ? $moyGen : 'N/A'
        )
    );
    
    echo json_encode($response);
    
} catch(Exception $e) {
    // Logger l'erreur
    error_log("Erreur actualiser_note: " . $e->getMessage());
    
    // Retourner une erreur JSON valide
    echo json_encode(array(
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage(),
        'debug' => array(
            'file' => $e->getFile(),
            'line' => $e->getLine()
        )
    ));
}
?>
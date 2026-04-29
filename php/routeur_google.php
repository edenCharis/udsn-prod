<?php

require_once('connexion.php');
require_once('lib.php');
session_start();

// Inclure les fichiers nécessaires de Google API Client
require_once '../assets/vendor/autoload.php';

$client = new Google_Client(['client_id' => '750618913312-09qo79ctgrbntupbq2ekrhqk02sblhf5.apps.googleusercontent.com']);
$id_token = file_get_contents('php://input');
$data = json_decode($id_token, true);
$id_token = $data['token'];

try {
    $payload = $client->verifyIdToken($id_token);
    if ($payload) {
        $userid = $payload['sub'];
            $query = "SELECT id,nom,login, role,mdp,etab,univ,img FROM utilisateur WHERE  email=?";
    $stmt = $connexion->prepare($query);
    $stmt->bind_param("s",$payload['email']);
    $stmt->execute();
    $stmt->bind_result($id,$nom,$userName, $userRole,$mdp,$etablissement,$univ,$img);
    $stmt->fetch();
    $stmt->close();

    if ($id !== null) {

        $statut = getstatut($id,$connexion);
        // Authentification réussie, rediriger en fonction du rôle
       if ($userRole === 'candidat') {

            $_SESSION['id']=session_id();
            $_SESSION['id_user'] = $id;
            $_SESSION['login'] = $payload['name'];
            $_SESSION['role'] = $userRole;
           // $_SESSION['mdp'] =$mdp;
           

            if(idCandidatExiste1($id,$connexion)){
                $_SESSION['statut'] =getStatutCandidat($id,$connexion);
                $_SESSION['code'] = getCodeCandidat($id,$connexion);
                $_SESSION['img'] = getImgCandidat($id,$connexion);
                $_SESSION['annee'] =getAnneeCandidat($id,$connexion);

            }

            logUserAction($connexion,$id,"connexion candidat",date("Y-m-d H:i:s"),$userIP,"valeur :  $id");
            header("Location: ../candidat");
        }else {
        // Authentification échouée, rediriger vers la page de connexion avec un message d'erreur
        header("Location: ../connexion?erreur=Utilisateur inconnu. Essayez à nouveau");
    }
    }



      

        // Exemple d'affichage des informations utilisateur
        echo json_encode([
            'status' => 'success',
            'userid' => $userid,
            'email' => $payload['email'],
            'name' => $payload['name']
        ]);
    } else {
        // Token invalide
        echo json_encode(['status' => 'error', 'message' => 'Invalid ID token']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>

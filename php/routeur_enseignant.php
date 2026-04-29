
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


// Inclure la configuration de la base de données
include('connexion.php');
include('lib.php');
session_start();



    if(isset($_POST['username']) and isset($_POST['password'])){

    
    // Récupérer les valeurs du formulaire
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Requête pour vérifier les informations d'authentification
    $query = "SELECT id,nom,login, role,mdp,etab,univ,img,classe,ecue,parcours,annee,semestre,examen FROM utilisateur WHERE login = ? AND mdp = ?";
    $stmt = $connexion->prepare($query);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $stmt->bind_result($id,$nom,$userName, $userRole,$mdp,$etablissement,$univ,$img,$classe,$ecue,$parcours,$annee,$semestre,$examen);
    $stmt->fetch();
    $stmt->close();

    if ($id !== null) {

        $statut = getstatut($id,$connexion);
        if($statut == 'O')
        {
            if ($userRole === 'administrateur') {
                $_SESSION['id']=session_id();
                $_SESSION['nom_user'] = $nom;
                $_SESSION['id_user'] = $id;
                $_SESSION['login'] = $userName;
                $_SESSION['role'] = $userRole;
                $_SESSION['mdp'] =$mdp;
                $userIP = $_SERVER['REMOTE_ADDR'];
    
                logUserAction($connexion,$id,"connexion",date("Y-m-d H:i:s"),$userIP,null);
               
                header("Location: ../administrateur");
            } else if ($userRole === 'candidat') {
    
                $_SESSION['id']=session_id();
                $_SESSION['id_user'] = $id;
                $_SESSION['login'] = $userName;
                $_SESSION['role'] = $userRole;
                $_SESSION['mdp'] =$mdp;
                $_SESSION['logo_univ'] = getlogo($id,$connexion);
                $_SESSION['email'] = $username;
               
    
                if(idCandidatExiste1($id,$connexion)){
                    $_SESSION['statut'] =getStatutCandidat($id,$connexion);
                    $_SESSION['code'] = getCodeCandidat($id,$connexion);
                    $_SESSION['img'] = getImgCandidat($id,$connexion);
                    $_SESSION['annee'] =getAnneeCandidat($id,$connexion);
    
                }
    
                logUserAction($connexion,$id,"connexion candidat",date("Y-m-d H:i:s"),$userIP,"valeur :  $id");
                header("Location: ../candidat");
            } else if($userRole === "scolarité") {
                $_SESSION['id']=session_id();
                $_SESSION['nom_user'] = $nom;
                $_SESSION['id_user'] = $id;
                $_SESSION['login'] = $userName;
                $_SESSION['role'] = $userRole;
                $_SESSION['mdp'] =$mdp;
                $_SESSION['img'] =$img;
                $_SESSION['etablissement']=$etablissement;
                $_SESSION['lib_etab']=getLibelleEtablissement(  $_SESSION['etablissement'],$connexion);
                $userIP = $_SERVER['REMOTE_ADDR'];
                $_SESSION['univ']=$univ;
                $_SESSION['statut'] =getstatut($id,$connexion);
                $_SESSION['logo_univ'] = getlogo($id,$connexion);
                
                $_SESSION['img'] = getimguser($id,$connexion);
                logUserAction($connexion,$id,"connexion",date("Y-m-d H:i:s"),$userIP,null);
                header("Location: ../scolarite");
            } else if($userRole === "caisse") {
                $_SESSION['id']=session_id();
                $_SESSION['nom_user'] = $nom;
                $_SESSION['id_user'] = $id;
                $_SESSION['login'] = $userName;
                $_SESSION['role'] = $userRole;
                $_SESSION['mdp'] =$mdp;
                $_SESSION['img'] =$img;
                $_SESSION['etablissement']=$etablissement;
                $_SESSION['lib_etab']=getLibelleEtablissement($_SESSION['etablissement'],$connexion);
                $_SESSION['univ']=$univ;
                    $_SESSION['statut'] =getstatut($id,$connexion);
                    $_SESSION['logo_univ'] = getlogo($id,$connexion);
                    
                    $_SESSION['img'] = getimguser($id,$connexion);
                $userIP = $_SERVER['REMOTE_ADDR'];
    
                logUserAction($connexion,$id,"connexion",date("Y-m-d H:i:s"),$userIP,null);
               
                header("Location: ../caisse");
            }
            else if($userRole === "cours") {
                $_SESSION['id']=session_id();
                $_SESSION['nom_user'] = $nom;
                $_SESSION['id_user'] = $id;
                $_SESSION['login'] = $userName;
                $_SESSION['role'] = $userRole;
                $_SESSION['mdp'] =$mdp;
                $_SESSION['img'] =$img;
                $_SESSION['etablissement']=$etablissement;
                $_SESSION['lib_etab']=getLibelleEtablissement($_SESSION['etablissement'],$connexion);
                $_SESSION['univ']=$univ;
                    $_SESSION['statut'] =getstatut($id,$connexion);
                    $_SESSION['logo_univ'] = getlogo($id,$connexion);
                    $_SESSION['img'] = getimguser($id,$connexion);
                $userIP = $_SERVER['REMOTE_ADDR'];
    
                logUserAction($connexion,$id,"connexion",date("Y-m-d H:i:s"),$userIP,null);
               
                header("Location: ../cours");
            }
            else if($userRole === "gesnote") {
                $_SESSION['id']=session_id();
                $_SESSION['nom_user'] = $nom;
                $_SESSION['id_user'] = $id;
                $_SESSION['login'] = $userName;
                $_SESSION['role'] = $userRole;
                $_SESSION['mdp'] =$mdp;
                $_SESSION['img'] =$img;
                $_SESSION['etablissement']=$etablissement;
                $_SESSION['lib_etab']=getLibelleEtablissement($_SESSION['etablissement'],$connexion);
                $_SESSION['univ']=$univ;

                if($classe !== "" and $ecue !== ""){
                    $_SESSION["classe"] = $classe;
                    $_SESSION["ecue"] = $ecue;
                }
                
                    $_SESSION['statut'] =getstatut($id,$connexion);
                    $_SESSION['logo_univ'] = getlogo($id,$connexion);
                    
                    $_SESSION['img'] = getimguser($id,$connexion);
                $userIP = $_SERVER['REMOTE_ADDR'];
    
                logUserAction($connexion,$id,"connexion",date("Y-m-d H:i:s"),$userIP,null);
               
                header("Location: ../gesnote");
            }else if ($userRole === 'Administrateur' and $univ != null) {
    
                $_SESSION['id']=session_id();
                $_SESSION['id_user'] = $id;
                $_SESSION['login'] = $userName;
                $_SESSION['role'] = $userRole;
                $_SESSION['mdp'] =$mdp;
                $_SESSION['univ']=$univ;
                $_SESSION['nom_user'] = $nom;
                
                    $_SESSION['statut'] =getstatut($id,$connexion);
                    $_SESSION['logo_univ'] = getlogo($id,$connexion);
                    
                    $_SESSION['img'] = getimguser($id,$connexion);
                    
                
    
                logUserAction($connexion,$id,"connexion utilisateur",date("Y-m-d H:i:s"),$userIP,"valeur :  $id");
                header("Location: ../admin");
            } else if ($userRole === 'soutenance' ) {
    
                $_SESSION['id']=session_id();
                $_SESSION['nom_user'] = $nom;
                $_SESSION['id_user'] = $id;
                $_SESSION['login'] = $userName;
                $_SESSION['role'] = $userRole;
                $_SESSION['mdp'] =$mdp;
                $_SESSION['img'] =$img;
                $_SESSION['etablissement']=$etablissement;
                $_SESSION['lib_etab']=getLibelleEtablissement($_SESSION['etablissement'],$connexion);
                $_SESSION['univ']=$univ;
             
                
                    $_SESSION['statut'] =getstatut($id,$connexion);
                    $_SESSION['logo_univ'] = getlogo($id,$connexion);
                    
                    $_SESSION['img'] = getimguser($id,$connexion);
                $userIP = $_SERVER['REMOTE_ADDR'];
    
                logUserAction($connexion,$id,"connexion",date("Y-m-d H:i:s"),$userIP,null);
               
                header("Location: ../soutenance"); 
        }
        else if ($userRole === 'inscription' ) {
    
            $_SESSION['id']=session_id();
            $_SESSION['nom_user'] = $nom;
            $_SESSION['id_user'] = $id;
            $_SESSION['login'] = $userName;
            $_SESSION['role'] = $userRole;
            $_SESSION['mdp'] =$mdp;
            $_SESSION['img'] =$img;
            $_SESSION['etablissement']=$etablissement;
            $_SESSION['lib_etab']=getLibelleEtablissement($_SESSION['etablissement'],$connexion);
            $_SESSION['univ']=$univ;
         
            
                $_SESSION['statut'] =getstatut($id,$connexion);
                $_SESSION['logo_univ'] = getlogo($id,$connexion);
                
                $_SESSION['img'] = getimguser($id,$connexion);
            $userIP = $_SERVER['REMOTE_ADDR'];
    
            logUserAction($connexion,$id,"connexion",date("Y-m-d H:i:s"),$userIP,null);
           
            header("Location: ../inscription"); 
    } else if ($userRole === 'pvd' ) {
    
        $_SESSION['id']=session_id();
        $_SESSION['nom_user'] = $nom;
        $_SESSION['id_user'] = $id;
        $_SESSION['login'] = $userName;
        $_SESSION['role'] = $userRole;
        $_SESSION['img'] =$img;
        $_SESSION['etablissement']=$etablissement;
        $_SESSION['lib_etab']=getLibelleEtablissement($_SESSION['etablissement'],$connexion);
        $_SESSION['univ']=$univ;
        
        $_SESSION['parcours']=$parcours;
       $_SESSION['semestre']=$semestre;
         $_SESSION['annee']=$annee;
           $_SESSION['examen']=$examen;
     
     
     
        
            $_SESSION['statut'] =getstatut($id,$connexion);
            $_SESSION['logo_univ'] = getlogo($id,$connexion);
            
            $_SESSION['img'] = getimguser($id,$connexion);
        $userIP = $_SERVER['REMOTE_ADDR'];

        logUserAction($connexion,$id,"connexion",date("Y-m-d H:i:s"),$userIP,null);
       
        header("Location: ../pvd"); 
}
else if ($userRole === 'daarhspe' ) {
    
    $_SESSION['id']=session_id();
    $_SESSION['nom_user'] = $nom;
    $_SESSION['id_user'] = $id;
    $_SESSION['login'] = $userName;
    $_SESSION['role'] = $userRole;
    $_SESSION['mdp'] =$mdp;
    $_SESSION['img'] =$img;
    $_SESSION['etablissement']=$etablissement;
    //$_SESSION['lib_etab']=getLibelleEtablissement($_SESSION['etablissement'],$connexion);
   // $_SESSION['univ']=$univ;
 
    
        $_SESSION['statut'] =getstatut($id,$connexion);
        $_SESSION['logo_univ'] = getlogo($id,$connexion);
        
        $_SESSION['img'] = getimguser($id,$connexion);
    $userIP = $_SERVER['REMOTE_ADDR'];

    logUserAction($connexion,$id,"connexion",date("Y-m-d H:i:s"),$userIP,null);
   
    header("Location: ../DAARHSPE"); 
}
        else if ($userRole === 'anonymat' ) {
    
            $_SESSION['id']=session_id();
            $_SESSION['nom_user'] = $nom;
            $_SESSION['id_user'] = $id;
            $_SESSION['login'] = $userName;
            $_SESSION['role'] = $userRole;
            $_SESSION['mdp'] =$mdp;
            $_SESSION['img'] =$img;
            $_SESSION['etablissement']=$etablissement;
            $_SESSION['lib_etab']=getLibelleEtablissement($_SESSION['etablissement'],$connexion);
            $_SESSION['univ']=$univ;

            if($classe !== "" and $ecue !== ""){
                $_SESSION["classe"] = $classe;
                $_SESSION["ecue"] = $ecue;
            }
         
            
                $_SESSION['statut'] =getstatut($id,$connexion);
                $_SESSION['logo_univ'] = getlogo($id,$connexion);
                
                $_SESSION['img'] = getimguser($id,$connexion);
            $userIP = $_SERVER['REMOTE_ADDR'];
    
            logUserAction($connexion,$id,"connexion",date("Y-m-d H:i:s"),$userIP,null);
           
            header("Location: ../anonymat"); 
    }else {
            // Authentification échouée, rediriger vers la page de connexion avec un message d'erreur
            header("Location: ../login?erreur=Utilisateur inconnu. Essayez à nouveau");
        }
    
    
    

        }else{
            header("Location: ../login?erreur=Votre compte a été desactivé ! Veuillez contactez l'administrateur");
        }
        // Authentification réussie, rediriger en fonction du rôle
       

} 

else {
    // Rediriger vers la page de connexion si l'accès direct à ce fichier est tenté
    header("Location: ../login?erreur=Échec de la connexion. Veuillez vérifier vos informations d'identification");
}


}


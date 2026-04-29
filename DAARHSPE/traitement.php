<?php 

include '../php/connexion.php';
include '../php/lib.php';
session_start();

 error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST"){



    $nom = clean_input($_POST["nom"]);
    $prenom = clean_input($_POST["prenom"]);
    $date_naissance = clean_input($_POST["date_naissance"]);
    $diplome = clean_input($_POST["diplome"]);
    $specialite = clean_input($_POST["specialite"]);
    $grade = clean_input($_POST["grade"]);   
    $telephone = clean_input($_POST["telephone"]);
    $email =  clean_input($_POST["email"]);

    $ville =  clean_input($_POST["ville"]);

    $sexe = clean_input($_POST["sexe"]);
    $annee = $_POST["annee"];
    $code_enseignant = genererMatriculeEnseignant($prenom,$nom);

    $etab = clean_input($_POST["etab"]);
    $ecues =$_POST["ecues"];
    $statut_enseignant = $_POST["etat"];
    
$th=null;
    $code_contrat = generateCodeContrat();
    $th=getThByGrade($connexion,$grade);


    $sql = " INSERT into enseignant(code,nom,prenom,email,telephone,sexe,grade,diplome,etab,date_naissance,ville,specialite,th,statut) values ('$code_enseignant','$nom','$prenom','$email','$telephone','$sexe','$grade','$diplome','$etab','$date_naissance','$ville','$specialite',$th,'$statut_enseignant')";

    $userIP = $_SERVER['REMOTE_ADDR'];
   if($connexion->query($sql)){

           $enseignant_id=$connexion->insert_id;
             

           $requete = "INSERT into contrat(numero_contrat,code_unique,enseignant,etab,annee) values ('$code_contrat','$code_enseignant',$enseignant_id,'$etab','$annee')";


           if($connexion->query($requete) and $connexion->query("update enseignant set contrat='$code_contrat' where id=$enseignant_id"))
           {
                 foreach($ecues as $e){


                        $st="INSERT into contrat_couverture(contrat,ecue,etab) values ('$code_contrat','$e','$etab')";
                        $connexion->query($st);
                 }
       

                 logUserAction($connexion,$_SESSION['id_user'],"enregistrement d'un contrat ",date("Y-m-d H:i:s"),$userIP,"code contrat : $code_contrat");

                 header("location: contrat?sucess=Opération effectuée avec succèss");
           }else{

            header("location: contrat?erreur=$connexion->error");


           }
          
       
    }else{
        
        header("location: index?erreur=$connexion->error");

    }




}



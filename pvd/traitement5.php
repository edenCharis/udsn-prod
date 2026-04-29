<?php 
include '../php/connexion.php';
include '../php/lib.php';
session_start();

if($_SERVER["REQUEST_METHOD"] == "POST" and $_POST["note"] !== ""){
    $semestre=  getSemestreUser($_SESSION["id_user"],$connexion);
    $parcours =getParcoursUser($_SESSION["id_user"],$connexion);
    $annee=getAnneeUser($_SESSION["id_user"],$connexion);
    $examen=getExamenUser($_SESSION["id_user"],$connexion);
    $niveau=NiveauParSemestre($semestre);


    $ue =$_POST["ue"];
    $ecue =$_POST["ecue"];
    $etudiant =$_POST["etudiant"];
    $note=$_POST["note"];


    $resultat = modifierNoteDevoir($etudiant,$ecue,$semestre,$annee,$_SESSION["etablissement"],$note,$connexion);
 if( $resultat == true)
{
   logUserAction($connexion,$_SESSION['id_user'],"modification note par jury ",date("Y-m-d H:i:s"),$userIP,"valeur : $etudiant+$semestre+$annee+$ecue+$note");
    
header("location: index?sucess=Opération effectuée avec succès");

}else{

header("location: index?erreur=SQL error, $connexion->error.");


}



}
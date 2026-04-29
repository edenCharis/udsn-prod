<?php
include '../php/connexion.php';
include '../php/lib.php';

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Nettoyage des entrées
    $enseignant_id = clean_input($_POST["enseignant"]);
    $annee = clean_input($_POST["annee"]);
    $etab = clean_input($_POST["etab"]);
    $ecues = $_POST["ecues"];

    // Étape 1 : Récupérer les informations de l'enseignant
    $sql_ens = "SELECT nom, prenom FROM enseignant WHERE id = $enseignant_id";
    $result_ens = $connexion->query($sql_ens);

    if ($result_ens && $result_ens->num_rows > 0) {
        $enseignant = $result_ens->fetch_assoc();
        $nom = $enseignant['nom'];
        $prenom = $enseignant['prenom'];

        // Étape 2 : Générer le code unique de l'enseignant
        $code_unique = genererMatriculeEnseignant($prenom, $nom);

        // Étape 3 : Mettre à jour le code de l'enseignant dans la base
        $update_ens = "UPDATE enseignant SET code = '$code_unique' WHERE id = $enseignant_id";
        if (!$connexion->query($update_ens)) {
            header("location: contrat?erreur=Erreur lors de la mise à jour du code enseignant : " . $connexion->error);
            exit();
        }

        // Étape 4 : Générer un code de contrat unique
        $code_contrat = generateCodeContrat();

        // Étape 5 : Insérer le contrat dans la base
        $requete_contrat = "INSERT INTO contrat (numero_contrat, enseignant, etab, annee, code_unique)
                            VALUES ('$code_contrat', $enseignant_id, '$etab', '$annee', '$code_unique')";

        if ($connexion->query($requete_contrat)) {

            // Étape 6 : Mettre à jour le contrat dans la table enseignant
            $connexion->query("UPDATE enseignant SET contrat = '$code_contrat' WHERE id = $enseignant_id");

            // Étape 7 : Lier les ECUEs au contrat
            foreach ($ecues as $e) {
                $st = "INSERT INTO contrat_couverture (contrat, ecue, etab)
                       VALUES ('$code_contrat', '$e', '$etab')";
                $connexion->query($st);
            }

            // Étape 8 : Journaliser l’action
            $userIP = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
            logUserAction(
                $connexion,
                $_SESSION['id_user'],
                "Enregistrement d'un contrat",
                date("Y-m-d H:i:s"),
                $userIP,
                "Code contrat : $code_contrat"
            );

            // Étape 9 : Redirection succès
            header("location: contrat?sucess=Opération effectuée avec succès");
        } else {
            header("location: contrat?erreur=Erreur lors de l'insertion du contrat : " . $connexion->error);
        }

    } else {
        header("location: contrat?erreur=Enseignant introuvable");
    }
}
?>

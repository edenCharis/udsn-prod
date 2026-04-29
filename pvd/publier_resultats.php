<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo json_encode(['success' => false, 'message' => "Erreur PHP: $errstr dans $errfile à la ligne $errline"]);
    exit;
});

set_exception_handler(function($exception) {
    echo json_encode(['success' => false, 'message' => "Exception: " . $exception->getMessage()]);
    exit;
});

include '../php/connexion.php';
include '../php/lib.php';

session_start();

if (!$connexion) {
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données: ' . mysqli_connect_error()]);
    exit;
}

// ================================================================
// HELPER : Vérifie si un étudiant doit passer en rattrapage
// (logique identique à pvd.php)
// ================================================================
function etudiantDoitRattraper($etudiant_id, $semestre, $annee, $etablissement, $connexion) {
    $sql = "SELECT etudiant
            FROM recap
            WHERE etudiant = ?
              AND semestre = ?
              AND annee = ?
              AND etab = ?
              AND examen = 'ordinaire'
              AND decision <> 'Validé'
            LIMIT 1";

    $stmt = $connexion->prepare($sql);
    $stmt->bind_param("isss", $etudiant_id, $semestre, $annee, $etablissement);
    $stmt->execute();
    $stmt->store_result();
    $doit_rattraper = ($stmt->num_rows > 0);
    $stmt->close();

    $ecues_eliminatoires   = rechercher_notes_eliminatoires($etudiant_id, $semestre, $annee, $connexion);
    $a_notes_eliminatoires = !empty($ecues_eliminatoires);

    return ($doit_rattraper);
}

if ($_SESSION['id'] == session_id() && $_SESSION['role'] == "pvd" || $_SESSION['role'] == "scolarité") {

    if (isset($_POST['semestre']) && isset($_POST['specialite']) && isset($_POST['annee']) && isset($_POST['examen'])) {

        try {
            $semestre      = mysqli_real_escape_string($connexion, $_POST["semestre"]);
            $specialite    = str_replace("'", "+", $_POST["specialite"]);
            $annee         = mysqli_real_escape_string($connexion, $_POST["annee"]);
            $examen        = mysqli_real_escape_string($connexion, $_POST["examen"]);
            $niveau        = mysqli_real_escape_string($connexion, $_POST["niveau"]);
            $classe        = mysqli_real_escape_string($connexion, $_POST["classe"]);
            $etablissement = mysqli_real_escape_string($connexion, $_POST["etablissement"]);

            $parcours = getParcours($specialite, $connexion);

            // Requête UE (réutilisée plusieurs fois dans la boucle)
            $sql_ue = "SELECT DISTINCT ue.code, libelle FROM ue
                       WHERE ue.etab='" . $etablissement . "'
                         AND specialite='" . $specialite . "'
                         AND semestre='$semestre'
                         AND niveau='$niveau'";

            // Compter le nombre d'UE (pour la décision)
            $result_ue_count_global = $connexion->query($sql_ue);
            $nb_ue_total = $result_ue_count_global ? $result_ue_count_global->num_rows : 0;

            // Liste des étudiants
            $sql_etudiants = "SELECT inscription.id, candidat.code AS candidat,
                                     candidat.nom AS nom, candidat.prenom AS prenom
                              FROM inscription
                              JOIN candidat   ON candidat.code       = inscription.candidat
                              JOIN classe     ON inscription.classe   = classe.libelle
                              JOIN specialite ON classe.specialite    = specialite.libelle
                              JOIN parcours   ON specialite.parcours  = parcours.libelle
                              WHERE inscription.classe = '$classe'
                                AND inscription.annee  = '$annee'
                                AND parcours.libelle   = '$parcours'
                              GROUP BY candidat.nom, candidat.prenom
                              ORDER BY LOWER(nom), LOWER(prenom)";

            $r = $connexion->query($sql_etudiants);
            if (!$r) {
                throw new Exception("Erreur requête étudiants: " . $connexion->error);
            }

            $compteur_publies = 0;
            $compteur_ignores = 0;
            $erreurs          = [];

            while ($etudiant = $r->fetch_object()) {

                // ════════════════════════════════════════════════════════
                // FILTRE RATTRAPAGE : exclure les étudiants déjà admis
                // ════════════════════════════════════════════════════════
                if ($examen == "rattrapage") {
                    if (!etudiantDoitRattraper(
                            $etudiant->id, $semestre, $annee, $etablissement, $connexion
                        )) {
                        $compteur_ignores++;
                        continue;
                    }
                }

                // ════════════════════════════════════════════════════════
                // CALCUL MANUEL — identique à pvd.php
                // Moyenne générale = moyenne des moyennes UE
                // Moyenne UE       = moyenne des moyennes ECUE
                // Moyenne ECUE     = (CC + EX) / 2
                // Note éliminatoire si moy_ecue < 6
                // ════════════════════════════════════════════════════════

                $toutes_moyennes_ue          = [];   // une entrée par UE
                $ue_validees_count           = 0;
                $a_note_eliminatoire_globale = false;
                $total_ecues                 = 0;
                $ecues_with_data             = 0;

                $result_ue = $connexion->query($sql_ue);
                if (!$result_ue) {
                    throw new Exception("Erreur requête UE: " . $connexion->error);
                }

                while ($data = $result_ue->fetch_object()) {

                    $sql_ecue    = "SELECT * FROM ecue WHERE code_ue='" . $data->code . "'";
                    $result_ecue = $connexion->query($sql_ecue);

                    $moyennes_ecue_ue       = [];   // moyennes ECUE de cette UE
                    $a_note_eliminatoire_ue = false;

                    while ($ecue = $result_ecue->fetch_object()) {
                        $total_ecues++;

                        // ── Récupération CC ──────────────────────────────────
                        $a = getEtudiantCC(
                                $etudiant->id, $connexion, $etablissement,
                                $semestre, $ecue->code_ecue, $annee
                             );

                        // ── Récupération EX selon mode ───────────────────────
                        if ($examen == "rattrapage") {
                            // Même règle que pvd.php :
                            // si une note de rattrapage existe → on l'utilise
                            // sinon → on garde la note d'examen ordinaire
                            $note_ratt = getEtudiantRattrapage(
                                             $etudiant->id, $connexion, $etablissement,
                                             $semestre, $ecue->code_ecue, $annee
                                         );
                            if ($note_ratt !== "-" && $note_ratt !== null && $note_ratt !== "") {
                                $b = $note_ratt;
                            } else {
                                $b = getEtudiantEXT(
                                         $etudiant->id, $connexion, $etablissement,
                                         $semestre, $ecue->code_ecue, $annee
                                     );
                            }
                        } else {
                            $b = getEtudiantEXT(
                                     $etudiant->id, $connexion, $etablissement,
                                     $semestre, $ecue->code_ecue, $annee
                                 );
                        }

                        // ── Moyenne ECUE ──────────────────────────────────────
                        if ($a !== "-" && $b !== "-") {
                            $ecues_with_data++;
                            $moy_ecue = round(($a + $b) / 2, 2);
                            $moyennes_ecue_ue[] = $moy_ecue;

                            // Note éliminatoire si moy_ecue < 6
                            if ($moy_ecue < 6) {
                                $a_note_eliminatoire_ue      = true;
                                $a_note_eliminatoire_globale = true;
                            }
                        }
                    } // fin while ECUE

                    // ── Moyenne UE ────────────────────────────────────────────
                    if (count($moyennes_ecue_ue) > 0) {
                        $ue_moy = round(array_sum($moyennes_ecue_ue) / count($moyennes_ecue_ue), 2);
                       
                        if ($ue_moy >= 10 && !$a_note_eliminatoire_ue) {
                            $ue_validees_count++;
                        }
                        
                         $toutes_moyennes_ue[] = $ue_moy;

                    }

                } // fin while UE

                // ════════════════════════════════════════════════════════
                // Seuil de complétude : 90 % minimum pour publier
                // ════════════════════════════════════════════════════════
                $percentage_complete = ($total_ecues > 0)
                    ? ($ecues_with_data / $total_ecues) * 100
                    : 0;

                if ($percentage_complete < 90) {
                    $compteur_ignores++;
                    continue;
                }

                // ════════════════════════════════════════════════════════
                // Moyenne générale = moyenne des moyennes UE
                // (identique à la logique d'affichage dans pvd.php)
                // ════════════════════════════════════════════════════════
                if (count($toutes_moyennes_ue) > 0) {
                    $tt = round(array_sum($toutes_moyennes_ue) / count($toutes_moyennes_ue), 2);
                } else {
                    $tt = "-";
                }

                // ════════════════════════════════════════════════════════
                // Décision du jury (identique à pvd.php)
                // ════════════════════════════════════════════════════════
                $decision = "-";
                if ($tt !== "-") {
                    $statut = "";
                    if ($nb_ue_total >= 1) {
                        $statut = statutSoutenance($tt);
                    }

                    if ($examen == "rattrapage") {
                        $ecues_elim_ratt = rechercher_notes_eliminatoires_rattrapage(
                            $etudiant->id, $semestre, $annee, $connexion
                        );

                        if (!empty($ecues_elim_ratt)) {
                            $decision = "Note Eliminatoire";
                        } elseif ($a_note_eliminatoire_globale) {
                            $decision = "Note Eliminatoire";
                        } else {
                            $decision = $statut;
                        }
                    } else {
                        if ($a_note_eliminatoire_globale) {
                            if (stripos($statut, 'Validé') !== false) {
                                $decision = "Note Eliminatoire";
                            } elseif (stripos($statut, 'Ajourné') !== false || stripos($statut, 'Ajourne') !== false) {
                                $decision = $statut;
                            } else {
                                $decision = "Note Eliminatoire";
                            }
                        } else {
                            $decision = $statut;
                        }
                    }
                }

                // ════════════════════════════════════════════════════════
                // INSERT ou UPDATE dans recap
                // ════════════════════════════════════════════════════════
                $check_stmt = $connexion->prepare(
                    "SELECT id FROM recap
                     WHERE etudiant = ? AND semestre = ? AND annee = ? AND examen = ? AND etab = ?"
                );

                if (!$check_stmt) {
                    $erreurs[] = "Erreur préparation vérification pour étudiant ID " . $etudiant->id . ": " . $connexion->error;
                    continue;
                }

                $check_stmt->bind_param("issss", $etudiant->id, $semestre, $annee, $examen, $etablissement);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows > 0) {
                    $update_stmt = $connexion->prepare(
                        "UPDATE recap SET moy = ?, decision = ?, specialite = ?
                         WHERE etudiant = ? AND semestre = ? AND annee = ? AND examen = ? AND etab = ?"
                    );
                    if (!$update_stmt) {
                        $erreurs[] = "Erreur préparation UPDATE pour étudiant ID " . $etudiant->id . ": " . $connexion->error;
                        $check_stmt->close();
                        continue;
                    }
                    $update_stmt->bind_param("sssissss", $tt, $decision, $specialite, $etudiant->id, $semestre, $annee, $examen, $etablissement);
                    if (!$update_stmt->execute()) {
                        $erreurs[] = "Erreur UPDATE pour étudiant ID " . $etudiant->id . ": " . $update_stmt->error;
                        $update_stmt->close();
                        $check_stmt->close();
                        continue;
                    }
                    $update_stmt->close();
                } else {
                    $insert_stmt = $connexion->prepare(
                        "INSERT INTO recap (etudiant, semestre, annee, examen, moy, decision, etab, specialite)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
                    );
                    if (!$insert_stmt) {
                        $erreurs[] = "Erreur préparation INSERT pour étudiant ID " . $etudiant->id . ": " . $connexion->error;
                        $check_stmt->close();
                        continue;
                    }
                    $insert_stmt->bind_param("isssssss", $etudiant->id, $semestre, $annee, $examen, $tt, $decision, $etablissement, $specialite);
                    if (!$insert_stmt->execute()) {
                        $erreurs[] = "Erreur INSERT pour étudiant ID " . $etudiant->id . ": " . $insert_stmt->error;
                        $insert_stmt->close();
                        $check_stmt->close();
                        continue;
                    }
                    $insert_stmt->close();
                }

                $check_stmt->close();
                $compteur_publies++;

            } // fin while étudiants

            $message = "$compteur_publies résultat(s) publié(s) avec succès. $compteur_ignores étudiant(s) ignoré(s) (déjà admis ou notes incomplètes).";
            if (count($erreurs) > 0) {
                $message .= "\n\nErreurs rencontrées:\n" . implode("\n", $erreurs);
            }

            echo json_encode([
                'success' => true,
                'message' => $message,
                'details' => [
                    'publies' => $compteur_publies,
                    'ignores' => $compteur_ignores,
                    'erreurs' => $erreurs
                ]
            ]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Exception: ' . $e->getMessage()]);
        }

    } else {
        echo json_encode(['success' => false, 'message' => 'Paramètres manquants', 'received' => $_POST]);
    }

} else {
    echo json_encode([
        'success' => false,
        'message' => 'Non autorisé - Session: ' . ($_SESSION['id'] ?? 'non défini') . ', Role: ' . ($_SESSION['role'] ?? 'non défini')
    ]);
}
?>
<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include '../php/connexion.php';
include '../php/lib.php';

session_start();

// Vérifier la session
if($_SESSION['id'] != session_id() || $_SESSION['role'] != "scolarité") {
    echo '<div class="alert alert-danger">Session invalide</div>';
    exit;
}

// Récupérer les paramètres POST
$semestre = isset($_POST['semestre']) ? $_POST['semestre'] : '';
$specialite = isset($_POST['specialite']) ? $_POST['specialite'] : '';
$annee = isset($_POST['annee']) ? $_POST['annee'] : '';
$examen = isset($_POST['examen']) ? $_POST['examen'] : '';
$niveau = isset($_POST['niveau']) ? $_POST['niveau'] : '';
$classe = isset($_POST['classe']) ? $_POST['classe'] : '';
$etablissement = isset($_POST['etablissement']) ? $_POST['etablissement'] : '';

// Échapper les valeurs pour sécurité SQL
$semestre = mysqli_real_escape_string($connexion, $semestre);
$specialite = str_replace("'", "+", $specialite);
$annee = mysqli_real_escape_string($connexion, $annee);
$examen = mysqli_real_escape_string($connexion, $examen);
$niveau = mysqli_real_escape_string($connexion, $niveau);
$classe = mysqli_real_escape_string($connexion, $classe);
$etablissement = mysqli_real_escape_string($connexion, $etablissement);
$parcours = getParcours($specialite,$connexion);

$etablissement_libelle = getLibelleEtablissement($etablissement, $connexion);

// Vérifier que tous les paramètres sont présents
if(empty($semestre) || empty($specialite) || empty($annee) || empty($classe)) {
    echo '<div class="alert alert-danger">Paramètres manquants</div>';
    exit;
}

// Récupérer les UE
$sql = "SELECT DISTINCT ue.code, libelle 
        FROM ue 
        WHERE ue.etab='".$etablissement."' 
        AND specialite='".$specialite."' 
        AND semestre='$semestre' 
        AND niveau='$niveau'";
?>

<div class="pv-header">
    <h5><strong>Spécialité :</strong> <?php echo str_replace("+", "'", $specialite); ?></h5>
    <h5><strong>Semestre :</strong> <?php echo $semestre; ?></h5>
    <h5><strong>Année universitaire :</strong> <?php echo $annee; ?></h5>
    <h5><strong>Examen :</strong> <?php echo ucfirst($examen); ?></h5>
    <h5><strong>Classe :</strong> <?php echo $classe; ?></h5>
    
    <!-- Légende des couleurs -->
    
</div>

<div class="table-responsive">
    <table class="table table-bordered table-striped" style="font-size: 9px;" id="pvTable" >
        <thead>
            <tr>
                <th rowspan="3">Nom(s) et prénom(s)</th>
                
                <?php 
                $result_ue = $connexion->query($sql);
                $ue_index = 0;
                while($data = $result_ue->fetch_object()) {
                    $ue_index++;
                    $sql_ = "SELECT * FROM ecue WHERE code_ue='".$data->code."'";
                    $result_ecue = $connexion->query($sql_);
                    $colspan = ($result_ecue->num_rows > 0) ? ($result_ecue->num_rows * 3) + 1 : 1;
                ?>
                <th colspan="<?php echo $colspan; ?>"><?php echo str_replace("+", "'", $data->libelle); ?></th>
                <?php } ?>
                
                <th rowspan="3">UE validées sur <?php echo $result_ue->num_rows; ?></th>
                <th rowspan="3">Moyenne Generale</th>
                <th rowspan="3">Appréciation</th>
                <th rowspan="3">Decision du jury</th>
            </tr>
            <tr>
                <?php 
                $result_ue = $connexion->query($sql);
                while($data = $result_ue->fetch_object()) {
                    $sql_ = "SELECT * FROM ecue WHERE code_ue='".$data->code."'";
                    $result_ecue = $connexion->query($sql_);
                    $i = 0;
                    while($ecue = $result_ecue->fetch_object()) {
                        $i++;
                ?>
                <th colspan="3"><?php echo str_replace("+", "'", $ecue->libelle); ?></th>
                <?php 
                        if($i == $result_ecue->num_rows) { 
                ?>
                <th rowspan="2">Moy UE</th>
                <?php 
                        }
                    } 
                } 
                ?>
            </tr>
            <tr>
                <?php 
                $result_ue = $connexion->query($sql);
                while($data = $result_ue->fetch_object()) {
                    $sql_ = "SELECT * FROM ecue WHERE code_ue='".$data->code."'";
                    $result_ecue = $connexion->query($sql_);
                    while($ecue = $result_ecue->fetch_object()) {
                ?>
                <th style="width: 40px;">CC</th>
                <th style="width: 40px;">EX.T</th>
                <th style="width: 40px;">MOY. ECUE</th>
                <?php 
                    }
                } 
                ?>
            </tr>
        </thead>
        <tbody>
            <?php 
            $sql1 = "SELECT
                inscription.id,
                candidat.code AS candidat,
                candidat.nom AS nom,
                candidat.prenom AS prenom
            FROM inscription
            JOIN candidat ON candidat.code = inscription.candidat
            JOIN classe ON inscription.classe = classe.libelle
            JOIN specialite ON classe.specialite = specialite.libelle
            JOIN parcours ON specialite.parcours = parcours.libelle
            WHERE classe.libelle = '$classe'
              AND parcours.libelle = '$parcours'
              AND inscription.annee='$annee'
            GROUP BY candidat.nom, candidat.prenom
            ORDER BY LOWER(nom), LOWER(prenom)";
            
            $r = $connexion->query($sql1);
            $num = 0;
            
            while($etudiant = $r->fetch_object()) {
                $num++;
            ?>
            <tr>
                <th><?php echo mettrePremieresLettresMajuscules(getNomEtudiant(getCandidatCodeByInscription($etudiant->id, $connexion), $connexion, $etablissement_libelle) . "  " . getPrenomEtudiant(getCandidatCodeByInscription($etudiant->id, $connexion), $connexion, $etablissement_libelle)); ?></th>
                
                <?php 
                // Tableau pour stocker les UE validées
                $ue_validees_count = 0;
                $a_note_eliminatoire_globale = false;
                
                $result_ue = $connexion->query($sql);
                
                while($data = $result_ue->fetch_object()) {
                    $sql_ = "SELECT * FROM ecue WHERE code_ue='".$data->code."'";
                    $result_ecue = $connexion->query($sql_);
                    
                    // Tableaux pour cette UE spécifique
                    $moyennes_ecue_ue = array();
                    $a_note_eliminatoire_ue = false;
                    $i = 0;
                    
                    while($ecue = $result_ecue->fetch_object()) {
                        $i++;
                        
                        $a = getEtudiantCC($etudiant->id, $connexion, $etablissement, $semestre, $ecue->code_ecue, $annee);
                        
                        if($examen == "ordinaire") {
                            $b = getEtudiantEXT($etudiant->id, $connexion, $etablissement, $semestre, $ecue->code_ecue, $annee);
                        } else {
                            $b = getEtudiantRattrapage($etudiant->id, $connexion, $etablissement, $semestre, $ecue->code_ecue, $annee);
                        }
                        
                        // Calculer la moyenne de l'ECUE
                        $moy_ecue = 0;
                        
                        if($a !== "-" && $b !== "-") {
                            $moy_ecue = ($a !== 0 || $b !== 0) ? round(($a + $b) / 2, 2) : 0;
                            $moyennes_ecue_ue[] = $moy_ecue;
                            
                            // Vérifier si c'est une note éliminatoire (< 6)
                            if($moy_ecue < 6) {
                                $a_note_eliminatoire_ue = true;
                                $a_note_eliminatoire_globale = true;
                            }
                        }
                ?>
                <th style="width: 40px;"><?php echo ($a !== "-") ? $a : "-"; ?></th>
                <th class="text-danger" style="width: 40px;"><?php echo ($b !== "-") ? $b : "-"; ?></th>
                <th class="text-primary" style="width: 40px;"><?php echo $moy_ecue; ?></th>
                
                <?php 
                        if($i == $result_ecue->num_rows) {
                            // Calculer la moyenne de l'UE
                            $ue_moy = "-";
                            
                            if(count($moyennes_ecue_ue) > 0) {
                                $somme_ecue = array_sum($moyennes_ecue_ue);
                                $nb_ecue = count($moyennes_ecue_ue);
                                $ue_moy = round($somme_ecue / $nb_ecue, 2);
                                
                                // Valider l'UE si moyenne >= 10 et aucune note éliminatoire
                                if($ue_moy >= 10 && !$a_note_eliminatoire_ue) {
                                    $ue_validees_count++;
                                }
                            }
                ?>
                <th class="text-secondary" style="width: 40px;"><?php echo $ue_moy; ?></th>
                <?php 
                        }
                    }
                }
                ?>
                
                <th style="width: 40px;"><?php echo $ue_validees_count; ?></th>
                
                <th class="<?php 
                    // Count total ECUEs and ECUEs with data
                    $total_ecues = 0;
                    $ecues_with_data = 0;
                    
                    $result_ue_count = $connexion->query($sql);
                    while($data_count = $result_ue_count->fetch_object()) {
                        $sql_count = "SELECT * FROM ecue WHERE code_ue='".$data_count->code."'";
                        $result_ecue_count = $connexion->query($sql_count);
                        
                        while($ecue_count = $result_ecue_count->fetch_object()) {
                            $total_ecues++;
                            $cc = getEtudiantCC($etudiant->id, $connexion, $etablissement, $semestre, $ecue_count->libelle, $annee);
                            
                            if($examen == "ordinaire") {
                                $ex = getEtudiantEXT($etudiant->id, $connexion, $etablissement, $semestre, $ecue_count->libelle, $annee);
                            } else {
                                $ex = getEtudiantRattrapage($etudiant->id, $connexion, $etablissement, $semestre, $ecue_count->libelle, $annee);
                            }
                            
                            if($cc !== "-" && $ex !== "-") {
                                $ecues_with_data++;
                            }
                        }
                    }
                    
                    $percentage_complete = ($total_ecues > 0) ? ($ecues_with_data / $total_ecues) * 100 : 0;
                    
                    if($ecues_with_data > 0) {
                        $tt = calcul_moyenne($etudiant->id, $semestre, $annee, $etablissement, $connexion);
                        
                        if($percentage_complete == 100) {
                            echo 'text-success';
                        } elseif($percentage_complete >= 80) {
                            echo 'text-info';
                        } else {
                            echo 'text-danger';
                        }
                    } else {
                        $tt = "-";
                        echo 'text-dark';
                    }
                ?>" style="width: 50px;">
                    <?php 
                    if($ecues_with_data > 0) {
                        echo $tt;
                        if($percentage_complete < 100) {
                            echo '<br><small style="font-size:8px;">(' . round($percentage_complete) . '%)</small>';
                        }
                    } else {
                        echo "-";
                    }
                    ?>
                </th>
                
                <th class="text-primary" style="width: 60px;">
                    <?php 
                    if($tt !== "-") {
                        echo mentionParmoyenne($tt, 2);
                    } else {
                        echo "-";
                    }
                    ?>
                </th>
                
                <th style="width: 80px;">
                    <?php
                    $result = "-";
                    
                    if($tt !== "-") {
                        $statut = "";
                        
                        if($result_ue->num_rows - 1 >= 1) {
                            $statut = statutSoutenance(round($tt, 2));
                        }
                        
                        // Logique de note éliminatoire
                        if($a_note_eliminatoire_globale) {
                            if(stripos($statut, 'Admis') !== false) {
                                $result = "<span class='badge badge-danger'>Note Éliminatoire</span>";
                            } elseif(stripos($statut, 'Ajourné') !== false || stripos($statut, 'Ajourne') !== false) {
                                $result = $statut;
                            } else {
                                $result = "<span class='badge badge-danger'>Note Éliminatoire</span>";
                            }
                        } else {
                            $result = $statut;
                        }
                    }
                    
                    echo $result;
                    ?>
                </th>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
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

$result_ue_count_total = $connexion->query($sql);
$nb_ue_total = $result_ue_count_total->num_rows;
?>

<div class="pv-header">
    <h5><strong>Spécialité :</strong> <?php echo str_replace("+", "'", $specialite); ?></h5>
    <h5><strong>Semestre :</strong> <?php echo ucfirst($semestre); ?></h5>
    <h5><strong>Année universitaire :</strong> <?php echo $annee; ?></h5>
    <h5><strong>Type de session :</strong> <?php echo ($examen == 'ordinaire') ? 'Session Ordinaire' : 'Session de Rattrapage'; ?></h5>
    <h5><strong>Classe :</strong> <?php echo $classe; ?></h5>
    
    <!-- Légende des couleurs -->
    
</div>

<div class="table-responsive">
    <table class="table table-bordered table-striped" style="font-size: 9px;" id="pvTable" >
        <thead>
            <tr>
                <th rowspan="3">N°</th>
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
                
                <th rowspan="3">UE validées sur <?php echo $nb_ue_total; ?></th>
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
                <th><?php echo $num; ?></th>
                <th><?php echo mettrePremieresLettresMajuscules(getNomEtudiant(getCandidatCodeByInscription($etudiant->id, $connexion), $connexion, $etablissement_libelle) . "  " . getPrenomEtudiant(getCandidatCodeByInscription($etudiant->id, $connexion), $connexion, $etablissement_libelle)); ?></th>
                
                <?php 
                $toutes_moyennes_ue          = [];
                $ue_validees_count           = 0;
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

                        if ($examen == "ordinaire") {
                            $b = getEtudiantEXT($etudiant->id, $connexion, $etablissement, $semestre, $ecue->code_ecue, $annee);
                        } else {
                            $note_ratt = getEtudiantRattrapage($etudiant->id, $connexion, $etablissement, $semestre, $ecue->code_ecue, $annee);
                            $b = ($note_ratt !== "-" && $note_ratt !== null && $note_ratt !== "")
                                ? $note_ratt
                                : getEtudiantEXT($etudiant->id, $connexion, $etablissement, $semestre, $ecue->code_ecue, $annee);
                        }
                        
                        // Calculer la moyenne de l'ECUE
                        $moy_ecue   = 0;
                        $color_ecue = '';

                        if($a !== "-" && $b !== "-") {
                            $moy_ecue = ($a !== 0 || $b !== 0) ? round(($a + $b) / 2, 2) : 0;
                            $moyennes_ecue_ue[] = $moy_ecue;

                            if($moy_ecue < 6) {
                                $a_note_eliminatoire_ue      = true;
                                $a_note_eliminatoire_globale = true;
                                $color_ecue = 'text-danger';
                            } elseif($moy_ecue >= 10) {
                                $color_ecue = 'text-success';
                            } else {
                                $color_ecue = 'text-warning';
                            }
                        }
                ?>
                <th style="width: 40px;"><?php echo ($a !== "-") ? $a : "-"; ?></th>
                <th style="width: 40px;"><?php echo ($b !== "-") ? $b : "-"; ?></th>
                <th class="<?php echo $color_ecue; ?>" style="width: 40px;"><?php echo $moy_ecue; ?></th>
                
                <?php 
                        if($i == $result_ecue->num_rows) {
                            $ue_moy = "-";
                            if (count($moyennes_ecue_ue) > 0) {
                                $ue_moy = round(array_sum($moyennes_ecue_ue) / count($moyennes_ecue_ue), 2);
                                if ($ue_moy >= 10 && !$a_note_eliminatoire_ue) {
                                    $ue_validees_count++;
                                }
                                $toutes_moyennes_ue[] = $ue_moy;
                            }
                ?>
                <th class="text-primary" style="width: 40px;"><?php echo $ue_moy; ?></th>
                <?php 
                        }
                    }
                }
                ?>
                
                <th style="width: 40px;"><?php echo $ue_validees_count; ?></th>
                
                <?php
                $color_moy = 'text-dark';
                $tt        = "-";
                if (count($toutes_moyennes_ue) > 0) {
                    $tt = round(array_sum($toutes_moyennes_ue) / count($toutes_moyennes_ue), 2);
                    if ($tt >= 10)     $color_moy = 'text-success';
                    elseif ($tt < 6)   $color_moy = 'text-danger';
                    else               $color_moy = 'text-warning';
                }
                ?>
                <th class="<?php echo $color_moy; ?>" style="width: 50px;">
                    <?php echo ($tt !== "-") ? $tt : "-"; ?>
                </th>

                <th class="text-primary" style="width: 60px;">
                    <?php echo ($tt !== "-") ? mentionParmoyenne($tt, 2) : "-"; ?>
                </th>

                <th style="width: 80px;">
                    <?php
                    $result_decision = "-";
                    if ($tt !== "-") {
                        if ($tt >= 10 && $a_note_eliminatoire_globale) {
                            $result_decision = "<span class='badge badge-danger'>Note Éliminatoire</span>";
                        } elseif ($tt >= 10) {
                            $result_decision = "<span class='badge badge-success'>Validé(e)</span>";
                        } else {
                            $result_decision = "<span class='badge badge-warning'>Ajourné(e)</span>";
                        }
                    }
                    echo $result_decision;
                    ?>
                </th>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
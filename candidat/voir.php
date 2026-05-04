<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id()){

    if(isset($_GET["etudiant"]) and isset($_GET["annee"]) and isset($_GET["examen"]) and isset($_GET["semestre"])){

        $etudiant      = $_GET["etudiant"];
        $annee         = $_GET["annee"];
        $semestre      = $_GET["semestre"];
        $examen        = $_GET["examen"];
        $etab          = $_GET["etablissement"];
        $classe        = $_GET["classe"];
        $specialite    = getSpecialiteClasse($connexion, $classe);
        $etablissement = $_GET["etablissement"];
        $examen_type   = strtolower(trim($examen)); // "ordinaire" ou "rattrapage"
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>UDSN - Espace étudiant</title>
    <link rel="icon" type="image/png" sizes="16x16" href="../administrateur/logo/favicon.png">
    <link rel="stylesheet" href="../vendor/bootstrap-select/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../vendor/pickadate/themes/default.css">
    <link rel="stylesheet" href="../vendor/pickadate/themes/default.date.css">
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
</head>

<body>

    <div id="main-wrapper">

        <div class="nav-header">
            <a href="#" class="brand-logo">
                <h3><b style="color: white;">UDSN</b></h3>
                <img class="logo-abbr" src="../images/univ.png" alt="">
            </a>
            <div class="nav-control">
                <div class="hamburger">
                    <span class="line"></span><span class="line"></span><span class="line"></span>
                </div>
            </div>
        </div>

        <div class="header">
            <div class="header-content">
                <nav class="navbar navbar-expand">
                    <div class="collapse navbar-collapse justify-content-between">
                        <ul class="navbar-nav header-right">
                            <li class="nav-item dropdown notification_dropdown"></li>
                            <?php if(isset($_SESSION['img'])): ?>
                            <a class="nav-link" href="#" role="button" data-toggle="dropdown">
                                <img src="<?php echo $_SESSION['img']; ?>" width="70" alt="">
                            </a>
                            <?php endif; ?>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a href="app-profile.html" class="dropdown-item ai-icon">
                                    <svg id="icon-user1" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                         viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                         stroke-linecap="round" stroke-linejoin="round" class="feather feather-user">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    <span class="ml-2">Mon Profil</span>
                                </a>
                                <a href="../connexion" class="dropdown-item ai-icon">
                                    <svg id="icon-logout" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                         viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                         stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-out">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                        <polyline points="16 17 21 12 16 7"></polyline>
                                        <line x1="21" y1="12" x2="9" y2="12"></line>
                                    </svg>
                                    <span class="ml-3">Déconnexion</span>
                                </a>
                            </div>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>

        <?php include('nav.html'); ?>

        <div class="content-body">
            <div class="container-fluid">

                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>Résultat</h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active"><a href="index">SGUDSN</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0);">Mon profil</a></li>
                        </ol>
                    </div>
                </div>

                <!-- ═══════════════════════════════════════════
                     CARTE RÉSUMÉ RÉSULTAT
                ═══════════════════════════════════════════ -->
                <div class="row">
                    <div class="col-xl-12 col-xxl-12 col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Classe : <?php echo $classe; ?></h5>
                                <h5 class="card-title">Examen : <?php echo $examen; ?></h5>
                                <h5 class="card-title">Semestre : <?php echo $semestre; ?></h5>
                                <h5 class="card-title">Année universitaire : <?php echo $annee; ?></h5>
                            </div>

                            <div class="card-body">
                            <?php
                            // ── 1. Récupérer toutes les ECUE du semestre ──────────────────────────────
                            $sql_all_ecue = "SELECT DISTINCT ecue.code_ecue, ecue.libelle
                                             FROM ecue
                                             JOIN ue ON ecue.code_ue = ue.code
                                             WHERE ue.specialite = '$specialite'
                                               AND ue.semestre   = '$semestre'
                                               AND ue.etab       = '$etablissement'";
                            $result_all_ecue = $connexion->query($sql_all_ecue);

                            $ecues_eliminatoires_details = [];
                            $ecues_inferieures_10        = [];
                            $ecues_rappel_composees      = [];

                            while ($ecue_row = $result_all_ecue->fetch_assoc()) {

                                $code_ecue = $ecue_row['code_ecue'];
                                $libelle   = str_replace("+", "'", $ecue_row['libelle']);

                                // Note CC (devoir)
                                $devoir = getNoteDevoir($connexion, $etudiant, $semestre, $annee, $code_ecue);

                                // Note EX : rattrapage si disponible, sinon ordinaire
                                if ($examen_type === 'rattrapage') {
                                    $note_ratt = getEtudiantRattrapage(
                                        $etudiant, $connexion, $etablissement,
                                        $semestre, $code_ecue, $annee
                                    );
                                    if ($note_ratt !== "-" && $note_ratt !== null && $note_ratt !== "") {
                                        $exam      = $note_ratt;
                                        $a_compose = true;
                                    } else {
                                        $exam      = getNoteExamen($connexion, $etudiant, $semestre, $annee, $code_ecue);
                                        $a_compose = false;
                                    }
                                } else {
                                    $exam      = getNoteExamen($connexion, $etudiant, $semestre, $annee, $code_ecue);
                                    $a_compose = false;
                                }

                                if ($devoir !== "-" && $exam !== "-") {
                                    $moy_ecue = round(($devoir + $exam) / 2, 2);

                                    // Matières effectivement composées au rattrapage
                                    if ($examen_type === 'rattrapage' && $a_compose) {
                                        $ecues_rappel_composees[] = [
                                            'libelle' => $libelle,
                                            'moyenne' => $moy_ecue,
                                            'devoir'  => $devoir,
                                            'exam'    => $exam,
                                        ];
                                    }

                                    // Classement par seuil
                                    if ($moy_ecue < 6) {
                                        $ecues_eliminatoires_details[] = [
                                            'libelle' => $libelle,
                                            'moyenne' => $moy_ecue,
                                        ];
                                    } elseif ($moy_ecue < 10) {
                                        $ecues_inferieures_10[] = [
                                            'libelle' => $libelle,
                                            'moyenne' => $moy_ecue,
                                        ];
                                    }
                                }
                            }

                            // ── 2. Calcul MANUEL de la moyenne générale (identique au PV) ─────────────
                            $sql_ue_moy     = "SELECT ue.code
                                               FROM ue
                                               WHERE specialite = '$specialite'
                                                 AND semestre   = '$semestre'
                                                 AND etab       = '$etablissement'";
                            $result_ue_moy      = $connexion->query($sql_ue_moy);
                            $toutes_moyennes_ue = [];

                            while ($row_ue_moy = $result_ue_moy->fetch_assoc()) {
                                $code_ue_moy  = $row_ue_moy['code'];
                                $sql_ecue_moy = "SELECT ecue.code_ecue
                                                 FROM ecue
                                                 JOIN vue_repartition
                                                   ON ecue.code_ecue = vue_repartition.code_ecue
                                                 WHERE ecue.code_ue  = '$code_ue_moy'
                                                   AND specialite    = '$specialite'
                                                   AND semestre      = '$semestre'
                                                   AND classe        = '$classe'";
                                $result_ecue_moy  = $connexion->query($sql_ecue_moy);
                                $moyennes_ecue_ue = [];

                                while ($row_ecue_moy = $result_ecue_moy->fetch_assoc()) {
                                    $ce   = $row_ecue_moy['code_ecue'];
                                    $cc_v = getNoteDevoir($connexion, $etudiant, $semestre, $annee, $ce);

                                    if ($examen_type === 'rattrapage') {
                                        $ratt_v = getEtudiantRattrapage(
                                            $etudiant, $connexion, $etablissement, $semestre, $ce, $annee
                                        );
                                        $ex_v = ($ratt_v !== "-" && $ratt_v !== null && $ratt_v !== "")
                                                ? $ratt_v
                                                : getNoteExamen($connexion, $etudiant, $semestre, $annee, $ce);
                                    } else {
                                        $ex_v = getNoteExamen($connexion, $etudiant, $semestre, $annee, $ce);
                                    }

                                    if ($cc_v !== "-" && $ex_v !== "-") {
                                        $moyennes_ecue_ue[] = ($cc_v + $ex_v) / 2;
                                    }
                                }

                                if (count($moyennes_ecue_ue) > 0) {
                                    $toutes_moyennes_ue[] = array_sum($moyennes_ecue_ue) / count($moyennes_ecue_ue);
                                }
                            }

                            $moyenne_manuelle = (count($toutes_moyennes_ue) > 0)
                                ? round(array_sum($toutes_moyennes_ue) / count($toutes_moyennes_ue), 2)
                                : 0;

                            // ── 3. Moyenne générale affichée (obtenirMoyenne inchangée) ──────────────
                            $moyenne_generale = round(
                                obtenirMoyenne($connexion, $etudiant, $semestre, $examen, $annee, $etab),
                                2
                            );

                            // ── 4. Décision & mention basées sur le calcul MANUEL ────────────────────
                            $a_notes_eliminatoires = !empty($ecues_eliminatoires_details);

                            $decision_originale = statutSoutenance($moyenne_manuelle);

                            $decision = $decision_originale;
                            if ($a_notes_eliminatoires && $moyenne_manuelle >= 10) {
                                $decision = "Note éliminatoire";
                            }

                            $est_ajourn = (
                                stripos($decision_originale, 'Ajourné') !== false ||
                                stripos($decision_originale, 'Ajourne') !== false
                            );
                            ?>

                            <!-- Moyenne générale -->
                            <p>Moyenne générale :
                                <b class="text-success"><?php echo $moyenne_generale; ?>/20</b>
                            </p>

                            <?php
                            // ── 5. Bloc RATTRAPAGE : matières composées ───────────────────────────────
                            if ($examen_type === 'rattrapage' && !empty($ecues_rappel_composees)):
                            ?>
                            <div class="alert alert-success">
                                <b><i class="fa fa-pencil-square-o"></i>
                                   Ecue(s) composée(s) au rattrapage :</b>
                                <ul class="mb-0 mt-2">
                                <?php foreach ($ecues_rappel_composees as $ec):
                                    if ($ec['moyenne'] >= 10) {
                                        $cls   = 'text-success';
                                        $icone = '✔';
                                    } elseif ($ec['moyenne'] < 6) {
                                        $cls   = 'text-danger';
                                        $icone = '✘';
                                    } else {
                                        $cls   = 'text-warning';
                                        $icone = '⚠';
                                    }
                                ?>
                                    <li>
                                        <?php echo $icone; ?>
                                        <b><?php echo $ec['libelle']; ?></b>
                                    </li>
                                <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>

                            <?php
                            // ── 6. Bloc ECUE encore < 10 (toujours en échec) ────────────────────────
                            $toutes_ecues_rattrapage = array_merge(
                                $ecues_eliminatoires_details,
                                $ecues_inferieures_10
                            );

                            if ($examen_type !== 'rattrapage' && !empty($toutes_ecues_rattrapage) && ($a_notes_eliminatoires || $est_ajourn)):
                            ?>
                            <p class="alert alert-warning">
                                <b>Revient au rattrapage</b><br>
                                ECUE avec moyenne &lt; 10 :
                                <b class="text-danger">
                                <?php foreach ($toutes_ecues_rattrapage as $ecue):
                                    echo $ecue['libelle'] . " (" . $ecue['moyenne'] . "/20), ";
                                endforeach; ?>
                                </b>
                            </p>
                            <?php endif; ?>

                            <!-- Décision du jury -->
                            <p>Décision du jury :
                                <b><i><?php echo $decision; ?></i></b>
                            </p>

                            <!-- Mention (uniquement si admis) -->
                            <?php
                            if (!$a_notes_eliminatoires && !$est_ajourn):
                                echo '<p>Mention : <b><i>' . mentionParmoyenne($moyenne_manuelle) . '</i></b></p>';
                            else:
                                echo '<p>Mention : <b><i>-</i></b></p>';
                            endif;
                            ?>

                            </div><!-- /.card-body -->
                        </div><!-- /.card -->
                    </div>
                </div>

                <!-- ═══════════════════════════════════════════
                     TABLEAU DÉTAIL DES RÉSULTATS
                ═══════════════════════════════════════════ -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Détails des résultats</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>N°</th>
                                                <th>Code UE</th>
                                                <th>Unité d'enseignement</th>
                                                <th>Moy. UE</th>
                                                <th>ECUE</th>
                                                <th>CC (Devoir)</th>
                                                <?php if ($examen_type === 'rattrapage'): ?>
                                                <th>EX ordinaire</th>
                                                <th>EX rattrapage</th>
                                                <?php else: ?>
                                                <th>EXT (Examen)</th>
                                                <?php endif; ?>
                                                <th>Moyenne ECUE</th>
                                                <th>Crédit</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php

                                        // ── Pré-chargement devoirs individuels (ligne2) ──
                                        $devoirs_par_ecue = [];
                                        $etudiant_int = intval($etudiant);
                                        $semestre_esc = mysqli_real_escape_string($connexion, $semestre);
                                        $annee_esc    = mysqli_real_escape_string($connexion, $annee);
                                        $etab_esc     = mysqli_real_escape_string($connexion, $etablissement);
                                        $res_dv = $connexion->query(
                                            "SELECT code_ecue, note FROM ligne2
                                             WHERE etudiant=$etudiant_int AND semestre='$semestre_esc' AND annee='$annee_esc'
                                             ORDER BY id ASC"
                                        );
                                        if ($res_dv) {
                                            while ($dv = $res_dv->fetch_assoc()) {
                                                $devoirs_par_ecue[$dv['code_ecue']][] = $dv['note'];
                                            }
                                            $res_dv->free();
                                        }

                                        // ── Pré-chargement notes examen (ligne1 via anonymat) ──
                                        $examen_par_ecue = [];
                                        $res_an = $connexion->query(
                                            "SELECT a.code_ecue AS code_ecue, a.nature, a.type AS session_type, l.note
                                             FROM anonymat a
                                             LEFT JOIN ligne1 l ON l.anonymat=a.numero AND l.code_ecue=a.code_ecue AND l.etab=a.etab
                                                                AND l.type_examen=IF(a.type='Session Ordinaire','Session Ordinaire','Session de Rappel')
                                             WHERE a.etudiant=$etudiant_int AND a.semestre='$semestre_esc' AND a.annee='$annee_esc' AND a.etab='$etab_esc'"
                                        );
                                        if ($res_an) {
                                            while ($an = $res_an->fetch_assoc()) {
                                                $note_v = ($an['note'] !== null) ? round($an['note'], 2) : '-';
                                                $examen_par_ecue[$an['code_ecue']][$an['session_type']][$an['nature']] = $note_v;
                                            }
                                            $res_an->free();
                                        }

                                        $modals_data  = [];
                                        $modal_counter = 0;

                                        $sql_ue = "SELECT ue.libelle, code
                                                   FROM ue
                                                   WHERE specialite = '$specialite'
                                                     AND semestre   = '$semestre'
                                                     AND etab       = '$etablissement'";
                                        $result_ue = $connexion->query($sql_ue);

                                        if ($result_ue->num_rows > 0):
                                            $count = 1;

                                            while ($row_ue = $result_ue->fetch_assoc()):
                                                $ue_nom  = $row_ue["libelle"];
                                                $code_ue = $row_ue["code"];

                                                $sql_ecue = "SELECT ecue.code_ecue, ecue.libelle, ecue.credit
                                                             FROM ecue
                                                             JOIN vue_repartition
                                                               ON ecue.code_ecue = vue_repartition.code_ecue
                                                             WHERE ecue.code_ue    = '$code_ue'
                                                               AND specialite      = '$specialite'
                                                               AND semestre        = '$semestre'
                                                               AND classe          = '$classe'";
                                                $result_ecue = $connexion->query($sql_ecue);
                                                if (!$result_ecue) { echo '<tr><td colspan="11" style="background:#fee;color:red;padding:4px;font-size:11px">ECUE ERROR: '.htmlspecialchars($connexion->error).'</td></tr>'; }
                                                $rowspan = ($result_ecue && $result_ecue->num_rows > 0)
                                                           ? $result_ecue->num_rows : 1;

                                                // Collecter les données ECUE et calculer Moy UE manuellement
                                                // (même logique que h.php / pvd/proces.php — cohérence avec le PV)
                                                $ecue_rows         = [];
                                                $ecue_moyennes_ue  = [];
                                                if ($result_ecue) {
                                                    while ($row_ecue_pre = $result_ecue->fetch_assoc()) {
                                                        $ce      = $row_ecue_pre['code_ecue'];
                                                        $cc_pre  = getNoteDevoir($connexion, $etudiant, $semestre, $annee, $ce);
                                                        if ($examen_type === 'rattrapage') {
                                                            $ratt_pre = getEtudiantRattrapage($etudiant, $connexion, $etablissement, $semestre, $ce, $annee);
                                                            $ex_pre   = ($ratt_pre !== "-" && $ratt_pre !== null && $ratt_pre !== "")
                                                                        ? $ratt_pre
                                                                        : getNoteExamen($connexion, $etudiant, $semestre, $annee, $ce);
                                                        } else {
                                                            $ex_pre = getNoteExamen($connexion, $etudiant, $semestre, $annee, $ce);
                                                        }
                                                        if ($cc_pre !== "-" && $ex_pre !== "-") {
                                                            $ecue_moyennes_ue[] = ($cc_pre + $ex_pre) / 2;
                                                        }
                                                        $ecue_rows[] = $row_ecue_pre;
                                                    }
                                                }
                                                $m = (count($ecue_moyennes_ue) > 0)
                                                     ? round(array_sum($ecue_moyennes_ue) / count($ecue_moyennes_ue), 2)
                                                     : "-";

                                                echo "<tr>";
                                                echo "<td rowspan='$rowspan'>$count</td>";
                                                echo "<td rowspan='$rowspan'>" . str_replace("+", "'", $code_ue) . "</td>";
                                                echo "<td rowspan='$rowspan'>" . str_replace("+", "'", $ue_nom) . "</td>";
                                                echo "<td rowspan='$rowspan'>$m</td>";

                                                if (!empty($ecue_rows)):
                                                    foreach ($ecue_rows as $row_ecue):

                                                        $code_ecue_detail = $row_ecue["code_ecue"];

                                                        // CC
                                                        $devoir_brut = getNoteDevoir(
                                                            $connexion, $etudiant, $semestre,
                                                            $annee, $code_ecue_detail
                                                        );
                                                        $notes_cc = ($devoir_brut !== "-")
                                                                    ? round($devoir_brut, 2)
                                                                    : "-";

                                                        // EX ordinaire (toujours affiché)
                                                        $ex_ordinaire_brut = getNoteExamen(
                                                            $connexion, $etudiant, $semestre,
                                                            $annee, $code_ecue_detail
                                                        );
                                                        $notes_ex_ord = ($ex_ordinaire_brut !== "-")
                                                                        ? round($ex_ordinaire_brut, 2)
                                                                        : "-";

                                                        // EX rattrapage
                                                        $notes_ex_ratt  = "-";
                                                        $ex_pour_calcul = $ex_ordinaire_brut;

                                                        if ($examen_type === 'rattrapage') {
                                                            $ratt_brut = getEtudiantRattrapage(
                                                                $etudiant, $connexion, $etablissement,
                                                                $semestre, $code_ecue_detail, $annee
                                                            );
                                                            if ($ratt_brut !== "-" && $ratt_brut !== null && $ratt_brut !== "") {
                                                                $notes_ex_ratt  = round($ratt_brut, 2);
                                                                $ex_pour_calcul = $ratt_brut;
                                                            }
                                                        }

                                                        // Calcul moyenne ECUE finale
                                                        $moyenne_ecue   = "-";
                                                        $classe_couleur = "";

                                                        if ($devoir_brut !== "-" && $ex_pour_calcul !== "-") {
                                                            $moyenne_ecue = round(
                                                                ($devoir_brut + $ex_pour_calcul) / 2, 2
                                                            );

                                                            if ($moyenne_ecue < 6) {
                                                                $classe_couleur = "text-danger font-weight-bold";
                                                            } elseif ($moyenne_ecue < 10) {
                                                                $classe_couleur = "text-warning font-weight-bold";
                                                            } else {
                                                                $classe_couleur = "text-success";
                                                            }
                                                        }

                                                        // Badge "composé au ratt"
                                                        $badge_ratt = ($examen_type === 'rattrapage' && $notes_ex_ratt !== "-")
                                                            ? ' <span class="badge badge-info" title="Note de rattrapage utilisée">Ratt.</span>'
                                                            : '';

                                                        echo "<td>" . str_replace("+", "'", $row_ecue["libelle"]) . $badge_ratt . "</td>";
                                                        echo "<td>$notes_cc</td>";

                                                        if ($examen_type === 'rattrapage') {
                                                            $style_ord = ($notes_ex_ratt !== "-")
                                                                         ? 'style="color:#aaa;text-decoration:line-through;"'
                                                                         : '';
                                                            echo "<td $style_ord>$notes_ex_ord</td>";
                                                            $cls_ratt = ($notes_ex_ratt !== "-") ? 'text-primary font-weight-bold' : '';
                                                            echo "<td class='$cls_ratt'>$notes_ex_ratt</td>";
                                                        } else {
                                                            echo "<td>$notes_ex_ord</td>";
                                                        }

                                                        echo "<td class='$classe_couleur'>$moyenne_ecue</td>";
                                                        echo "<td>" . $row_ecue["credit"] . "</td>";

                                                        // ── Bouton Détails (données collectées, modale rendue après la table) ──
                                                        $modal_counter++;
                                                        $mid = 'detailModal' . $modal_counter;
                                                        echo "<td class='text-center'><button class='btn btn-xs btn-outline-info' data-toggle='modal' data-target='#$mid' title='Voir le détail'><i class='fa fa-search-plus'></i></button></td>";

                                                        $ec_exams = $examen_par_ecue[$code_ecue_detail] ?? [];
                                                        $modals_data[] = [
                                                            'mid'        => $mid,
                                                            'lbl'        => htmlspecialchars(str_replace("+", "'", $row_ecue["libelle"])),
                                                            'devoirs'    => $devoirs_par_ecue[$code_ecue_detail] ?? [],
                                                            'th_ord'     => $ec_exams['Session Ordinaire']['Examen Theorique'] ?? '-',
                                                            'pr_ord'     => $ec_exams['Session Ordinaire']['Examen Pratique']  ?? '-',
                                                            'th_ratt'    => $ec_exams['Session de Rappel']['Examen Theorique']  ?? '-',
                                                            'pr_ratt'    => $ec_exams['Session de Rappel']['Examen Pratique']   ?? '-',
                                                        ];

                                                        echo "</tr><tr>";

                                                    endforeach;
                                                else:
                                                    echo "<td colspan='7'>Aucune ECUE trouvée</td></tr>";
                                                endif;

                                                $count++;
                                            endwhile;
                                        else:
                                            echo "<tr><td colspan='11'>Aucune UE trouvée</td></tr>";
                                        endif;
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- /.container-fluid -->
        </div><!-- /.content-body -->

        <div class="footer">
            <div class="copyright">
                <p>Copyright © Conçu &amp; Développé par
                    <a href="https://www.cet-up.com" target="_blank">CETUP</a> 2023
                </p>
            </div>
        </div>

    </div><!-- /#main-wrapper -->

    <?php foreach ($modals_data ?? [] as $md):
        $fmt = function($v) { return ($v !== '-') ? "<b>$v/20</b>" : '<span class="text-muted">—</span>'; };
    ?>
    <div class="modal fade" id="<?php echo $md['mid']; ?>" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header text-white" style="background:#2c3e53;">
            <h6 class="modal-title"><i class="fa fa-list mr-2"></i><?php echo $md['lbl']; ?></h6>
            <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
          </div>
          <div class="modal-body p-3">

            <p class="font-weight-bold mb-1" style="border-left:4px solid #f39c12;padding-left:8px;">Contrôles Continus (CC)</p>
            <table class="table table-sm table-bordered mb-3">
              <?php if (empty($md['devoirs'])): ?>
              <tr><td colspan="2" class="text-center text-muted small">Aucun devoir enregistré</td></tr>
              <?php else: ?>
              <?php foreach ($md['devoirs'] as $i => $dn): ?>
              <tr><td>Devoir <?php echo $i + 1; ?></td><td><b><?php echo round($dn, 2); ?>/20</b></td></tr>
              <?php endforeach; ?>
              <tr class="table-warning">
                <td><b>Moyenne CC</b></td>
                <td><b><?php echo round(array_sum($md['devoirs']) / count($md['devoirs']), 2); ?>/20</b></td>
              </tr>
              <?php endif; ?>
            </table>

            <p class="font-weight-bold mb-1" style="border-left:4px solid #3498db;padding-left:8px;">Session Ordinaire</p>
            <table class="table table-sm table-bordered mb-3">
              <tr><td>Examen Théorique</td><td><?php echo $fmt($md['th_ord']); ?></td></tr>
              <tr><td>Examen Pratique</td><td><?php echo $fmt($md['pr_ord']); ?></td></tr>
            </table>

            <?php if ($examen_type === 'rattrapage'): ?>
            <p class="font-weight-bold mb-1" style="border-left:4px solid #e74c3c;padding-left:8px;">Session de Rattrapage</p>
            <table class="table table-sm table-bordered mb-0">
              <tr><td>Examen Théorique</td><td><?php echo $fmt($md['th_ratt']); ?></td></tr>
              <tr><td>Examen Pratique</td><td><?php echo $fmt($md['pr_ratt']); ?></td></tr>
            </table>
            <?php endif; ?>

          </div>
          <div class="modal-footer py-2">
            <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Fermer</button>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>

    <!-- Modal messages -->
    <div class="modal" id="messageModal" tabindex="-1" role="dialog"
         aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="messageModalLabel">SGUDSN</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="messageBody"></div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../vendor/global/global.min.js"></script>
    <script src="../vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="../js/custom.min.js"></script>
    <script src="../js/dlabnav-init.js"></script>
    <script src="../vendor/svganimation/vivus.min.js"></script>
    <script src="../vendor/svganimation/svg.animation.js"></script>
    <script src="../vendor/pickadate/picker.js"></script>
    <script src="../vendor/pickadate/picker.time.js"></script>
    <script src="../vendor/pickadate/picker.date.js"></script>
    <script src="../js/plugins-init/pickadate-init.js"></script>

    <script>
        $(document).ready(function () {
            var urlParams = new URLSearchParams(window.location.search);
            var erreur  = urlParams.get('erreur');
            var success = urlParams.get('sucess');

            if (erreur || success) {
                var message = erreur ? "Erreur : " + erreur : "Message : " + success;
                $('#messageBody').text(message);
                $('#messageModal').modal('show');
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });
    </script>

</body>
</html>
<?php
    } // fin isset GET
} else {
    header("location: ../connexion");
}
?>
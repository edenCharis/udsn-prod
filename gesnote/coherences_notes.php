<?php
include '../php/connexion.php';
include '../php/lib.php';
session_start();

$rolesAutorises = ['gesnote', 'admin', 'anonymat', 'direction'];
if (!($_SESSION['id'] == session_id() && in_array($_SESSION['role'], $rolesAutorises))) {
    header("location: ../deconnexion1"); exit();
}

$canCorrect = in_array($_SESSION['role'], ['admin', 'gesnote', 'direction']);

// ─── Export CSV ────────────────────────────────────────────────────────────────
if (isset($_GET['export_csv'])) {
    $ecue     = $_GET['ecue']     ?? '';
    $classe   = $_GET['classe']   ?? '';
    $semestre = $_GET['semestre'] ?? '';
    $annee    = $_GET['annee']    ?? '';
    $etab     = $_SESSION['etablissement'];

    $whereConditions = ["i.etab = ?"];
    $params = [$etab]; $types = 's';
    if ($ecue)     { $whereConditions[] = 'ord_grp.ecue = ?';     $params[] = $ecue;     $types .= 's'; }
    if ($classe)   { $whereConditions[] = 'ord_grp.classe = ?';   $params[] = $classe;   $types .= 's'; }
    if ($semestre) { $whereConditions[] = 'ord_grp.semestre = ?'; $params[] = $semestre; $types .= 's'; }
    if ($annee)    { $whereConditions[] = 'i.annee = ?';          $params[] = $annee;    $types .= 's'; }
    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

    $sql = "
        SELECT i.candidat, CONCAT(c.nom,' ',c.prenom) AS nom_prenom,
               ord_grp.ecue AS code_ecue, e.libelle AS libelle_ecue,
               ord_grp.classe, ord_grp.semestre, i.annee,
               ord_grp.anon_th AS anon_ord_th, ord_grp.note_th AS note_ord_th,
               ord_grp.anon_pr AS anon_ord_pr, ord_grp.note_pr AS note_ord_pr,
               rap_grp.anon_th AS anon_rap_th, rap_grp.note_th AS note_rap_th,
               rap_grp.anon_pr AS anon_rap_pr, rap_grp.note_pr AS note_rap_pr,
               n.moyDev AS not_moy_dev, n.moyEx AS not_moy_ex,
               n.session_rappel AS not_session_rappel,
               n.moyGen AS not_moy_gen, n.moyenGenRattrapage AS not_moy_gen_rat
        FROM inscription i
        JOIN candidat c ON c.code = i.candidat
        JOIN (
            SELECT a.etudiant, a.ecue, a.classe, a.semestre, a.annee, a.etab,
                MAX(CASE WHEN a.nature='Examen Theorique' THEN a.numero END) AS anon_th,
                MAX(CASE WHEN a.nature='Examen Pratique'  THEN a.numero END) AS anon_pr,
                MAX(CASE WHEN a.nature='Examen Theorique' THEN l.note  END) AS note_th,
                MAX(CASE WHEN a.nature='Examen Pratique'  THEN l.note  END) AS note_pr
            FROM anonymat a
            LEFT JOIN ligne1 l ON l.anonymat=a.numero AND l.code_ecue=a.ecue AND l.type_examen='Session Ordinaire' AND l.etab=a.etab
            WHERE a.type='Session Ordinaire'
            GROUP BY a.etudiant, a.ecue, a.classe, a.semestre, a.annee, a.etab
        ) ord_grp ON ord_grp.etudiant=i.id AND ord_grp.annee=i.annee AND ord_grp.etab=i.etab
        JOIN ecue e ON e.code_ecue=ord_grp.ecue AND e.etab=i.etab
        LEFT JOIN (
            SELECT a.etudiant, a.ecue, a.semestre, a.annee, a.etab,
                MAX(CASE WHEN a.nature='Examen Theorique' THEN a.numero END) AS anon_th,
                MAX(CASE WHEN a.nature='Examen Pratique'  THEN a.numero END) AS anon_pr,
                MAX(CASE WHEN a.nature='Examen Theorique' THEN l.note  END) AS note_th,
                MAX(CASE WHEN a.nature='Examen Pratique'  THEN l.note  END) AS note_pr
            FROM anonymat a
            LEFT JOIN ligne1 l ON l.anonymat=a.numero AND l.code_ecue=a.ecue AND l.type_examen='Session de Rappel' AND l.etab=a.etab
            WHERE a.type='Session de Rappel'
            GROUP BY a.etudiant, a.ecue, a.semestre, a.annee, a.etab
        ) rap_grp ON rap_grp.etudiant=i.id AND rap_grp.ecue=ord_grp.ecue AND rap_grp.semestre=ord_grp.semestre AND rap_grp.annee=i.annee AND rap_grp.etab=i.etab
        LEFT JOIN notation n ON n.inscription=i.id AND n.code_ecue=ord_grp.ecue AND n.annee=i.annee AND n.semestre=ord_grp.semestre AND n.etab=i.etab
        $whereClause
        ORDER BY ord_grp.classe, ord_grp.ecue, c.nom, c.prenom
    ";

    $stmt = $connexion->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="coherence_notes_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($out, [
        'Matricule','Nom & Prénom','Code ECUE','Libellé ECUE','Classe','Semestre','Année',
        'Anon Ord Théo','Note Ord Théo','Anon Ord Prat','Note Ord Prat','MoyEx Calculée',
        'Anon Rap Théo','Note Rap Théo','Anon Rap Prat','Note Rap Prat','SessRap Calculée',
        'MoyDev (notation)','MoyEx (notation)','Session Rappel (notation)',
        'MoyGen (notation)','MoyGen Ratt. (notation)',
        'Cohérence MoyEx','Cohérence SessRap'
    ], ';');
    while ($row = $result->fetch_assoc()) {
        $row['libelle_ecue'] = str_replace('+', "'", $row['libelle_ecue']);
        $row['nom_prenom']   = str_replace('+', "'", $row['nom_prenom']);
        $nth = $row['note_ord_th']!==null ? (float)$row['note_ord_th'] : null;
        $npr = $row['note_ord_pr']!==null ? (float)$row['note_ord_pr'] : null;
        $moyEx_calc = ($nth!==null&&$npr!==null) ? round(($nth+$npr)/2,2) : ($nth??$npr);
        $nth_r = $row['note_rap_th']!==null ? (float)$row['note_rap_th'] : null;
        $npr_r = $row['note_rap_pr']!==null ? (float)$row['note_rap_pr'] : null;
        $rap_calc = ($nth_r!==null&&$npr_r!==null) ? round(($nth_r+$npr_r)/2,2) : ($nth_r??$npr_r);
        $notME = $row['not_moy_ex']!==null?(float)$row['not_moy_ex']:null;
        $notSR = $row['not_session_rappel']!==null?(float)$row['not_session_rappel']:null;
        $cohME = ($moyEx_calc!==null&&$notME!==null) ? (abs($moyEx_calc-$notME)<=0.009?'OK':'INCOHÉRENT') : ($moyEx_calc!==null&&$notME===null?'MANQUANT':'ND');
        $cohSR = ($rap_calc!==null&&$notSR!==null)   ? (abs($rap_calc-$notSR)<=0.009?'OK':'INCOHÉRENT')   : ($rap_calc!==null&&$notSR===null?'MANQUANT':'ND');
        fputcsv($out, [
            $row['candidat'], $row['nom_prenom'], $row['code_ecue'], $row['libelle_ecue'],
            $row['classe'], $row['semestre'], $row['annee'],
            $row['anon_ord_th']??'', $row['note_ord_th']??'',
            $row['anon_ord_pr']??'', $row['note_ord_pr']??'', $moyEx_calc??'',
            $row['anon_rap_th']??'', $row['note_rap_th']??'',
            $row['anon_rap_pr']??'', $row['note_rap_pr']??'', $rap_calc??'',
            $row['not_moy_dev']??'', $row['not_moy_ex']??'',
            $row['not_session_rappel']??'', $row['not_moy_gen']??'',
            $row['not_moy_gen_rat']??'', $cohME, $cohSR
        ], ';');
    }
    fclose($out);
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $_SESSION['univ']; ?> - Scolarité de <?php echo $_SESSION['etablissement']; ?></title>
    <link rel="icon" type="image/png" sizes="16x16" href="../administrateur/<?php echo $_SESSION['logo_univ']; ?>">
    <link rel="stylesheet" href="../vendor/bootstrap-select/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="../vendor/select2/css/select2.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/skin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">

    <style>
        /* ── Print ─────────────────────────────────────────────── */
        @media print {
            .no-print, nav, .header, .footer, .breadcrumb, .btn,
            .card-header .btn, #filters-card { display: none !important; }
            .print-title { display: block !important; }
        }
        .print-title { display: none; }

        /* ── Stats bar — même style que comparatif_notes ────────── */
        #stats-row .badge {
            font-size: .85rem;
            padding: .45em .85em;
            border-radius: 20px;
            margin-right: 6px;
            margin-bottom: 6px;
            display: inline-block;
        }

        /* ── Filtre étudiant — copié à l'identique ───────────────── */
        #student-filter-wrap {
            display: none;
            margin-bottom: 16px;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        #student-filter-wrap input {
            flex: 1;
            min-width: 220px;
            max-width: 380px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: .85rem;
            outline: none;
        }
        #student-filter-wrap input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,.12);
        }
        #student-filter-count { font-size: .78rem; color: #6c757d; }

        /* ── Cellules de statut cohérence ────────────────────────── */
        td.cell-ok      { background: #d1fae5 !important; color: #065f46; font-weight: 700; text-align: center; white-space: nowrap; }
        td.cell-bad     { background: #fee2e2 !important; color: #991b1b; font-weight: 700; text-align: center; white-space: nowrap; }
        td.cell-missing { background: #fef9c3 !important; color: #854d0e; font-weight: 700; text-align: center; white-space: nowrap; }
        td.cell-nd      { color: #adb5bd; text-align: center; font-size: .8rem; }
        td.cell-note    { text-align: center; font-weight: 600; }
        td.cell-calc    { text-align: center; font-weight: 700; color: #1d4ed8; }
        td.cell-anon    { text-align: center; font-size: .75rem; color: #6c757d; font-family: monospace; }
        td.note-high    { color: #065f46 !important; }
        td.note-low     { color: #991b1b !important; }

        /* bordure gauche sur les lignes incohérentes */
        tr.row-bad > td:first-child { border-left: 4px solid #ef4444 !important; }

        /* ── Bouton correction par ligne ─────────────────────────── */
        .btn-fix {
            font-size: .72rem;
            padding: 2px 8px;
            border-radius: 6px;
            white-space: nowrap;
        }
        .btn-fix:disabled { opacity: .45; }

        /* ── Progression correction globale ──────────────────────── */
        #bulk-progress { display: none; margin-top: 8px; }

        /* ── Groupes en-têtes colonnes ───────────────────────────── */
        .thead-group th {
            text-align: center;
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            padding: 5px 6px;
        }
        .grp-identity { background: #e0e7ff; color: #3730a3; }
        .grp-anon-ord { background: #dcfce7; color: #14532d; }
        .grp-anon-rap { background: #fce7f3; color: #831843; }
        .grp-notation { background: #fef3c7; color: #78350f; }
        .grp-status   { background: #f1f5f9; color: #475569; }
        .grp-action   { background: #fde8d8; color: #7c2d12; }
    </style>
</head>
<body>

    <div id="preloader">
        <div class="sk-three-bounce">
            <div class="sk-child sk-bounce1"></div>
            <div class="sk-child sk-bounce2"></div>
            <div class="sk-child sk-bounce3"></div>
        </div>
    </div>

    <div id="main-wrapper">
        <?php include "header.php"; ?>
        <?php include 'nav.html'; ?>

        <div class="content-body">
            <div class="container-fluid">

                <!-- Breadcrumb -->
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h3>Contrôle de cohérence des notes</h3>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../gesnote/">Gesnote</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0);">Cohérence Notes</a></li>
                        </ol>
                    </div>
                </div>

                <!-- ── FILTRES ──────────────────────────────────────────── -->
                <div class="row" id="filters-card">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title"><i class="fas fa-filter"></i> Filtres de recherche</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Classe</label>
                                            <select class="form-control" id="f-classe">
                                                <option value="">Toutes</option>
                                                <?php
                                                    $res = $connexion->query("SELECT DISTINCT libelle FROM classe WHERE etab='".$_SESSION['etablissement']."' ORDER BY libelle");
                                                    while ($r = $res->fetch_assoc())
                                                        echo "<option>".str_replace('+', "'", $r['libelle'])."</option>";
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>ECUE</label>
                                            <select class="form-control" id="f-ecue">
                                                <option value="">Tous</option>
                                                <?php
                                                    $res = $connexion->query("SELECT code_ecue, libelle FROM ecue WHERE etab='".$_SESSION['etablissement']."' ORDER BY libelle");
                                                    while ($r = $res->fetch_assoc())
                                                        echo "<option value='".htmlspecialchars($r['code_ecue'])."'>".str_replace('+', "'", $r['libelle'])." [".$r['code_ecue']."]</option>";
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Semestre</label>
                                            <select class="form-control" id="f-semestre">
                                                <option value="">Tous</option>
                                                <?php
                                                    $res = $connexion->query("SELECT libelle FROM semestre ORDER BY libelle");
                                                    while ($r = $res->fetch_assoc())
                                                        echo "<option>".str_replace('+', "'", $r['libelle'])."</option>";
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Année académique</label>
                                            <select class="form-control" id="f-annee">
                                                <option value="">Toutes</option>
                                                <?php
                                                    $res = $connexion->query("SELECT libelle FROM annee ORDER BY libelle DESC");
                                                    while ($r = $res->fetch_assoc())
                                                        echo "<option>".str_replace('+', "'", $r['libelle'])."</option>";
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>&nbsp;</label><br>
                                            <button type="button" class="btn btn-primary btn-sm" id="btn-search">
                                                <i class="fas fa-search"></i> Rechercher
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── RÉSULTATS ────────────────────────────────────────── -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex flex-wrap align-items-center" style="gap:10px;">
                                <h4 class="card-title mb-0">
                                    <i class="fas fa-table"></i> Cohérence : Anonymat / Ligne1 / Notation
                                </h4>

                                <?php if ($canCorrect): ?>
                                <button id="btn-bulk-fix" class="btn btn-danger btn-sm no-print" style="display:none;">
                                    <i class="fas fa-wrench"></i> Corriger toutes les incohérences
                                    <span id="bulk-badge" class="badge badge-light ml-1">0</span>
                                </button>
                                <?php endif; ?>

                                <div id="export-btns" class="ml-auto" style="display:none;">
                                    <a id="btn-export" href="#" class="btn btn-success btn-sm mr-2">
                                        <i class="fas fa-file-csv"></i> Exporter CSV
                                    </a>
                                    <button class="btn btn-warning btn-sm" onclick="window.print()">
                                        <i class="fas fa-print"></i> Imprimer
                                    </button>
                                </div>
                            </div>

                            <div class="card-body">

                                <!-- Print title -->
                                <div class="print-title mb-3">
                                    <h4><?php echo $_SESSION['univ']; ?> — Contrôle Cohérence Notes</h4>
                                    <p id="print-subtitle" class="text-muted"></p>
                                </div>

                                <!-- Légende -->
                                <div class="mb-3 no-print">
                                    <span class="badge badge-success mr-1"><i class="fas fa-check-circle mr-1"></i>OK — Notes identiques</span>
                                    <span class="badge badge-danger mr-1"><i class="fas fa-times-circle mr-1"></i>INCOHÉRENT — Valeurs différentes</span>
                                    <span class="badge badge-warning mr-1"><i class="fas fa-exclamation-triangle mr-1"></i>MANQUANT — Absent de notation</span>
                                    <span class="badge badge-secondary mr-1">ND — Pas de note saisie</span>
                                    <span class="badge" style="background:#dbeafe;color:#1d4ed8;">MoyEx calculée = (Théo + Prat) / 2</span>
                                </div>

                                <!-- Progression correction globale -->
                                <div id="bulk-progress" class="no-print">
                                    <div class="progress" style="height:20px;">
                                        <div id="bulk-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-danger"
                                             role="progressbar" style="width:0%">0%</div>
                                    </div>
                                    <small class="text-muted" id="bulk-progress-text">Correction en cours…</small>
                                </div>

                                <!-- Global stats — même pattern que comparatif_notes -->
                                <div id="stats-row" style="display:none;" class="mb-4">
                                    <span class="badge badge-primary">Total lignes : <strong id="stat-total">0</strong></span>
                                    <span class="badge badge-success">Cohérentes : <strong id="stat-ok">0</strong></span>
                                    <span class="badge badge-danger">Incohérentes : <strong id="stat-bad">0</strong></span>
                                    <span class="badge badge-warning">Manquantes en notation : <strong id="stat-missing">0</strong></span>
                                    <span class="badge badge-info">Avec session rappel : <strong id="stat-rap">0</strong></span>
                                </div>

                                <!-- Loading spinner -->
                                <div id="loading" style="display:none;" class="text-center py-5">
                                    <div class="sk-three-bounce">
                                        <div class="sk-child sk-bounce1"></div>
                                        <div class="sk-child sk-bounce2"></div>
                                        <div class="sk-child sk-bounce3"></div>
                                    </div>
                                    <p class="mt-2 text-muted">Analyse en cours…</p>
                                </div>

                                <!-- Filtre étudiant — copié à l'identique de comparatif_notes -->
                                <div id="student-filter-wrap" class="d-flex">
                                    <i class="fas fa-user-search" style="color:#6c757d;font-size:1rem;"></i>
                                    <input type="text" id="student-filter-input"
                                           placeholder="Rechercher un étudiant (nom ou matricule)…" />
                                    <button class="btn btn-sm btn-outline-secondary" id="student-filter-clear">
                                        <i class="fas fa-times"></i> Réinitialiser
                                    </button>
                                    <label class="mb-0 d-flex align-items-center" style="font-size:.82rem;cursor:pointer;gap:5px;">
                                        <input type="checkbox" id="chk-only-bad"> Incohérences uniquement
                                    </label>
                                    <span id="student-filter-count"></span>
                                </div>

                                <!-- Tableau -->
                                <div id="table-wrap" style="display:none;">
                                    <div class="table-responsive">
                                        <table id="coherence-table" class="display" style="min-width:1600px;width:100%;">
                                            <thead>
                                                <tr class="thead-group">
                                                    <th colspan="3" class="grp-identity">Identité</th>
                                                    <th colspan="2" class="grp-identity">Matière</th>
                                                    <th colspan="5" class="grp-anon-ord">Ordinaire (dépouillement)</th>
                                                    <th colspan="5" class="grp-anon-rap">Rappel (dépouillement)</th>
                                                    <th colspan="5" class="grp-notation">Table NOTATION</th>
                                                    <th colspan="2" class="grp-status">Statut cohérence</th>
                                                    <?php if ($canCorrect): ?>
                                                    <th colspan="1" class="grp-action no-print">Action</th>
                                                    <?php endif; ?>
                                                </tr>
                                                <tr>
                                                    <th>Matricule</th>
                                                    <th>Nom &amp; Prénom</th>
                                                    <th>Classe</th>
                                                    <th>ECUE</th>
                                                    <th>Semestre</th>
                                                    <!-- Ordinaire -->
                                                    <th>Anon. Théo</th>
                                                    <th>Note Théo</th>
                                                    <th>Anon. Prat</th>
                                                    <th>Note Prat</th>
                                                    <th>MoyEx <small>(calc.)</small></th>
                                                    <!-- Rappel -->
                                                    <th>Anon. Théo</th>
                                                    <th>Note Théo</th>
                                                    <th>Anon. Prat</th>
                                                    <th>Note Prat</th>
                                                    <th>SessRap <small>(calc.)</small></th>
                                                    <!-- Notation -->
                                                    <th>MoyDev</th>
                                                    <th>MoyEx</th>
                                                    <th>Sess. Rappel</th>
                                                    <th>MoyGen</th>
                                                    <th>MoyGen Ratt.</th>
                                                    <!-- Statut -->
                                                    <th>MoyEx</th>
                                                    <th>Rappel</th>
                                                    <?php if ($canCorrect): ?>
                                                    <th class="no-print">Corriger</th>
                                                    <?php endif; ?>
                                                </tr>
                                            </thead>
                                            <tbody id="coherence-tbody"></tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- État vide initial -->
                                <div id="empty-state" class="text-center py-5 text-muted">
                                    <i class="fas fa-search-minus fa-3x mb-3 d-block"></i>
                                    Utilisez les filtres ci-dessus puis cliquez sur <strong>Rechercher</strong>.
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal message -->
        <div class="modal" id="messageModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><?php echo $_SESSION['univ']; ?></h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body" id="messageBody"></div>
                </div>
            </div>
        </div>

        <div class="footer">
            <div class="copyright">
                <p>Copyright © Designed &amp; Developpé par <a href="https://www.cet-up.com" target="_blank">CETUP</a> 2023</p>
            </div>
        </div>
    </div>

    <script src="../vendor/global/global.min.js"></script>
    <script src="../vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="../js/custom.min.js"></script>
    <script src="../vendor/raphael/raphael.min.js"></script>
    <script src="../vendor/morris/morris.min.js"></script>
    <script src="../vendor/select2/js/select2.full.min.js"></script>
    <script src="../js/plugins-init/select2-init.js"></script>
    <script src="../vendor/peity/jquery.peity.min.js"></script>
    <script src="../js/dashboard/dashboard-2.js"></script>
    <script src="../vendor/svganimation/vivus.min.js"></script>
    <script src="../vendor/svganimation/svg.animation.js"></script>
    <script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../js/plugins-init/datatables.init.js"></script>

    <script>
    var CAN_CORRECT = <?php echo $canCorrect ? 'true' : 'false'; ?>;

    $(document).ready(function () {

        var urlParams = new URLSearchParams(window.location.search);
        var erreur  = urlParams.get('erreur');
        var success = urlParams.get('sucess');
        if (erreur || success) {
            $('#messageBody').text(erreur ? "Erreur : " + erreur : "Succès : " + success);
            $('#messageModal').modal('show');
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        var dtTable  = null;
        var allRows  = [];   // données brutes serveur

        // ── Helpers ───────────────────────────────────────────────────────────
        function fmtNote(v) {
            if (v === null || v === undefined || v === '') return null;
            return parseFloat(v);
        }
        function fmtDisp(v) {
            if (v === null || v === undefined || v === '') return '—';
            return parseFloat(v).toFixed(2);
        }
        function noteClass(v) {
            if (v === null) return '';
            return v >= 10 ? 'note-high' : 'note-low';
        }
        function statusCell(calc, not) {
            if (calc === null) return { label: 'ND', css: 'cell-nd' };
            if (not  === null) return { label: 'MANQUANT', css: 'cell-missing' };
            if (Math.abs(calc - not) <= 0.009) return { label: 'OK ✓', css: 'cell-ok' };
            return { label: calc.toFixed(2) + ' ≠ ' + not.toFixed(2), css: 'cell-bad' };
        }

        // ── Construction du tableau ───────────────────────────────────────────
        function buildTable(rows) {
            var onlyBad = $('#chk-only-bad').is(':checked');
            var q       = $('#student-filter-input').val().toLowerCase().trim();

            var filtered = rows.filter(function(r) {
                if (onlyBad && !r.incoherent) return false;
                if (q && r.nom_prenom.toLowerCase().indexOf(q) === -1
                      && r.matricule.toLowerCase().indexOf(q) === -1) return false;
                return true;
            });

            $('#student-filter-count').text(filtered.length + ' ligne(s) affichée(s)');

            if (dtTable) { dtTable.destroy(); dtTable = null; }

            var tbody = '';
            $.each(filtered, function(i, r) {
                var nOrdNot = fmtNote(r.not_moy_ex);
                var nRapNot = fmtNote(r.not_session_rappel);
                var meyCalc = fmtNote(r.moy_ex_calc);
                var rapCalc = fmtNote(r.sess_rap_calc);

                var stMoyEx = statusCell(meyCalc, nOrdNot);
                var stRap   = statusCell(rapCalc, nRapNot);

                // Bouton corriger visible uniquement si incohérent et notation_id connu
                var btnFix = '';
                if (CAN_CORRECT) {
                    var fixDisabled = (!r.incoherent || !r.notation_id) ? ' disabled' : '';
                    var fixTitle    = !r.notation_id ? 'Aucune ligne dans notation' : (!r.incoherent ? 'Déjà cohérent' : 'Mettre à jour notation');
                    btnFix = '<td class="no-print">' +
                        '<button class="btn btn-sm btn-danger btn-fix" ' + fixDisabled +
                        ' data-id="' + (r.notation_id||'') + '"' +
                        ' data-mex="' + (meyCalc !== null ? meyCalc : '') + '"' +
                        ' data-rap="' + (rapCalc  !== null ? rapCalc  : '') + '"' +
                        ' title="' + fixTitle + '">' +
                        '<i class="fas fa-wrench"></i> Corriger</button>' +
                        '</td>';
                }

                tbody +=
                    '<tr class="' + (r.incoherent ? 'row-bad' : '') + '" data-rowid="' + i + '">' +
                    '<td>' + r.matricule + '</td>' +
                    '<td>' + r.nom_prenom + '</td>' +
                    '<td>' + r.classe + '</td>' +
                    '<td><small title="' + r.libelle_ecue + '">' + r.code_ecue + ' — ' + r.libelle_ecue + '</small></td>' +
                    '<td>' + r.semestre + '</td>' +
                    /* Ordinaire */
                    '<td class="cell-anon">' + (r.anon_ord_th || '—') + '</td>' +
                    '<td class="cell-note ' + noteClass(fmtNote(r.note_ord_th)) + '">' + fmtDisp(r.note_ord_th) + '</td>' +
                    '<td class="cell-anon">' + (r.anon_ord_pr || '—') + '</td>' +
                    '<td class="cell-note ' + noteClass(fmtNote(r.note_ord_pr)) + '">' + fmtDisp(r.note_ord_pr) + '</td>' +
                    '<td class="cell-calc ' + noteClass(meyCalc) + '">' + fmtDisp(r.moy_ex_calc) + '</td>' +
                    /* Rappel */
                    '<td class="cell-anon">' + (r.anon_rap_th || '—') + '</td>' +
                    '<td class="cell-note ' + noteClass(fmtNote(r.note_rap_th)) + '">' + fmtDisp(r.note_rap_th) + '</td>' +
                    '<td class="cell-anon">' + (r.anon_rap_pr || '—') + '</td>' +
                    '<td class="cell-note ' + noteClass(fmtNote(r.note_rap_pr)) + '">' + fmtDisp(r.note_rap_pr) + '</td>' +
                    '<td class="cell-calc ' + noteClass(rapCalc) + '">' + fmtDisp(r.sess_rap_calc) + '</td>' +
                    /* Notation */
                    '<td class="cell-note">' + fmtDisp(r.not_moy_dev) + '</td>' +
                    '<td class="cell-note ' + noteClass(nOrdNot) + '">' + fmtDisp(r.not_moy_ex) + '</td>' +
                    '<td class="cell-note ' + noteClass(nRapNot) + '">' + fmtDisp(r.not_session_rappel) + '</td>' +
                    '<td class="cell-note">' + fmtDisp(r.not_moy_gen) + '</td>' +
                    '<td class="cell-note">' + fmtDisp(r.not_moy_gen_rat) + '</td>' +
                    /* Statuts */
                    '<td class="' + stMoyEx.css + '">' + stMoyEx.label + '</td>' +
                    '<td class="' + stRap.css   + '">' + stRap.label   + '</td>' +
                    btnFix +
                    '</tr>';
            });

            $('#coherence-tbody').html(tbody);
            dtTable = $('#coherence-table').DataTable({
                destroy: true,
                pageLength: 25,
                language: { url: '//cdn.datatables.net/plug-ins/1.10.21/i18n/French.json' },
                columnDefs: [{ orderable: false, targets: CAN_CORRECT ? [5,7,11,13,22] : [5,7,11,13] }]
            });
        }

        // ── Correction d'une ligne ────────────────────────────────────────────
        $(document).on('click', '.btn-fix', function() {
            var $btn        = $(this);
            var notation_id = $btn.data('id');
            var mex         = $btn.data('mex');
            var rap         = $btn.data('rap');

            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({
                url: 'ajax_correction.php',
                method: 'POST',
                dataType: 'json',
                data: { action: 'single', notation_id: notation_id, moy_ex: mex, sess_rap: rap },
                success: function(res) {
                    if (res.error) {
                        showModal('<strong>Erreur :</strong> ' + res.error);
                        $btn.prop('disabled', false).html('<i class="fas fa-wrench"></i> Corriger');
                        return;
                    }
                    // Mettre à jour la ligne visuellement
                    var $row = $btn.closest('tr');
                    $row.removeClass('row-bad');
                    $row.find('td:first-child').css('border-left', '');
                    // Mettre à jour les cellules notation
                    if (mex !== '') {
                        $row.find('td').eq(CAN_CORRECT ? 16 : 16).text(parseFloat(mex).toFixed(2))
                            .removeClass('note-low note-high').addClass(parseFloat(mex)>=10?'note-high':'note-low');
                    }
                    if (rap !== '') {
                        $row.find('td').eq(CAN_CORRECT ? 17 : 17).text(parseFloat(rap).toFixed(2))
                            .removeClass('note-low note-high').addClass(parseFloat(rap)>=10?'note-high':'note-low');
                    }
                    // Statuts
                    $row.find('td').eq(CAN_CORRECT ? 20 : 20).text('OK ✓').removeClass('cell-bad cell-missing cell-nd').addClass('cell-ok');
                    $row.find('td').eq(CAN_CORRECT ? 21 : 21).text('OK ✓').removeClass('cell-bad cell-missing cell-nd').addClass('cell-ok');
                    $btn.prop('disabled', true).removeClass('btn-danger').addClass('btn-success')
                        .html('<i class="fas fa-check"></i> Corrigé');

                    // Décrémenter le compteur badge
                    var cur = parseInt($('#bulk-badge').text()) - 1;
                    if (cur < 0) cur = 0;
                    $('#bulk-badge').text(cur);
                    $('#stat-bad').text(cur);
                    $('#stat-ok').text(parseInt($('#stat-ok').text()) + 1);
                },
                error: function(xhr) {
                    showModal('Erreur serveur : ' + xhr.statusText);
                    $btn.prop('disabled', false).html('<i class="fas fa-wrench"></i> Corriger');
                }
            });
        });

        // ── Correction globale ────────────────────────────────────────────────
        $('#btn-bulk-fix').on('click', function() {
            var incoherentRows = allRows.filter(function(r) { return r.incoherent && r.notation_id; });
            if (incoherentRows.length === 0) { showModal('Aucune incohérence à corriger.'); return; }

            if (!confirm('Corriger ' + incoherentRows.length + ' ligne(s) dans la table notation ?\n\nCette action est irréversible.')) return;

            var corrections = incoherentRows.map(function(r) {
                return {
                    notation_id : r.notation_id,
                    moy_ex      : r.moy_ex_calc  !== null ? r.moy_ex_calc  : '',
                    sess_rap    : r.sess_rap_calc !== null ? r.sess_rap_calc : ''
                };
            });

            $('#btn-bulk-fix').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Correction…');
            $('#bulk-progress').show();
            $('#bulk-progress-bar').css('width', '0%').text('0%');

            // Envoi par lots de 50 pour ne pas dépasser les limites POST
            var BATCH = 50;
            var total = corrections.length;
            var done  = 0;
            var errors= 0;

            function sendBatch(offset) {
                var batch = corrections.slice(offset, offset + BATCH);
                $.ajax({
                    url: 'ajax_correction.php',
                    method: 'POST',
                    dataType: 'json',
                    data: { action: 'bulk', corrections: JSON.stringify(batch) },
                    success: function(res) {
                        if (res.error) { errors++; }
                        else { done += res.corrected || 0; errors += res.errors || 0; }

                        var pct = Math.round(((offset + batch.length) / total) * 100);
                        $('#bulk-progress-bar').css('width', pct + '%').text(pct + '%');
                        $('#bulk-progress-text').text((offset + batch.length) + ' / ' + total + ' traité(e)s');

                        if (offset + BATCH < total) {
                            sendBatch(offset + BATCH);
                        } else {
                            // Terminé
                            $('#btn-bulk-fix').prop('disabled', false)
                                .html('<i class="fas fa-check-circle"></i> ' + done + ' corrigée(s)');
                            var msg = '<strong>' + done + '</strong> ligne(s) corrigée(s) dans notation.';
                            if (errors > 0) msg += '<br><span class="text-danger">' + errors + ' erreur(s).</span>';
                            msg += '<br><br><em>Relancez la recherche pour actualiser l\'affichage.</em>';
                            showModal(msg);
                            setTimeout(function(){ $('#bulk-progress').hide(); }, 3000);
                        }
                    },
                    error: function(xhr) {
                        errors++;
                        showModal('Erreur serveur : ' + xhr.statusText);
                        $('#btn-bulk-fix').prop('disabled', false).html('<i class="fas fa-wrench"></i> Corriger toutes les incohérences');
                    }
                });
            }

            sendBatch(0);
        });

        // ── Filtre étudiant live ──────────────────────────────────────────────
        $('#student-filter-input').on('input', function() { buildTable(allRows); });
        $('#chk-only-bad').on('change', function()        { buildTable(allRows); });
        $('#student-filter-clear').on('click', function() {
            $('#student-filter-input').val('');
            $('#chk-only-bad').prop('checked', false);
            buildTable(allRows);
        });

        // ── Recherche principale ──────────────────────────────────────────────
        $('#btn-search').on('click', function () {
            var classe   = $('#f-classe').val();
            var ecue     = $('#f-ecue').val();
            var semestre = $('#f-semestre').val();
            var annee    = $('#f-annee').val();

            if (dtTable) { dtTable.destroy(); dtTable = null; }
            $('#loading').show();
            $('#stats-row').hide();
            $('#export-btns').hide();
            $('#btn-bulk-fix').hide();
            $('#student-filter-wrap').hide();
            $('#student-filter-input').val('');
            $('#chk-only-bad').prop('checked', false);
            $('#table-wrap').hide();
            $('#empty-state').hide();
            $('#bulk-progress').hide();

            $.ajax({
                url: 'ajax_coherence.php',
                method: 'POST',
                dataType: 'json',
                data: { classe, ecue, semestre, annee },

                success: function (data) {
                    $('#loading').hide();

                    if (!data) { showModal('Réponse vide du serveur.'); return; }
                    if (data.error) {
                        showModal('<strong>Erreur :</strong><br><pre style="white-space:pre-wrap;font-size:12px;background:#f8f9fa;padding:10px;">'
                            + data.error + (data.sql ? '\n\nSQL:\n' + data.sql : '') + '</pre>');
                        return;
                    }
                    if (!data.rows || data.rows.length === 0) {
                        $('#empty-state').html('<i class="fas fa-inbox fa-3x mb-3 d-block"></i>Aucun résultat pour ces critères.').show();
                        return;
                    }

                    allRows = data.rows;

                    // ── Stats ─────────────────────────────────────────────
                    var total        = data.rows.length;
                    var missingCount = 0;
                    var rapCount     = 0;
                    $.each(data.rows, function(i, r) {
                        if (fmtNote(r.moy_ex_calc) !== null && fmtNote(r.not_moy_ex) === null) missingCount++;
                        if (fmtNote(r.sess_rap_calc) !== null) rapCount++;
                    });

                    $('#stat-total').text(total);
                    $('#stat-ok').text(total - data.incoherent_count);
                    $('#stat-bad').text(data.incoherent_count);
                    $('#stat-missing').text(missingCount);
                    $('#stat-rap').text(rapCount);
                    $('#stats-row').show();

                    // Bouton correction globale
                    if (CAN_CORRECT && data.incoherent_count > 0) {
                        $('#bulk-badge').text(data.incoherent_count);
                        $('#btn-bulk-fix').show()
                            .html('<i class="fas fa-wrench"></i> Corriger toutes les incohérences <span class="badge badge-light ml-1">' + data.incoherent_count + '</span>');
                    }

                    buildTable(allRows);
                    $('#table-wrap').show();

                    var uniq = [];
                    $.each(allRows, function(i,r){ if(uniq.indexOf(r.matricule)===-1) uniq.push(r.matricule); });
                    $('#student-filter-count').text(uniq.length + ' étudiant(s) — ' + total + ' ligne(s)');
                    $('#student-filter-wrap').css('display', 'flex');

                    // CSV export
                    var exportUrl = window.location.pathname + '?export_csv=1'
                        + '&ecue='     + encodeURIComponent(ecue)
                        + '&classe='   + encodeURIComponent(classe)
                        + '&semestre=' + encodeURIComponent(semestre)
                        + '&annee='    + encodeURIComponent(annee);
                    $('#btn-export').attr('href', exportUrl);
                    $('#export-btns').show();

                    var parts = [];
                    if (classe) parts.push('Classe : ' + classe);
                    if ($('#f-ecue option:selected').text() !== 'Tous') parts.push('ECUE : ' + $('#f-ecue option:selected').text());
                    if (annee) parts.push(annee);
                    $('#print-subtitle').text(parts.join(' | ') + ' — Imprimé le ' + new Date().toLocaleDateString('fr-FR'));
                },

                error: function (xhr, status, errorThrown) {
                    $('#loading').hide();
                    var detail = 'Statut HTTP : ' + xhr.status + ' — ' + errorThrown;
                    if (xhr.responseText) detail += '\n\nRéponse brute :\n' + xhr.responseText.substring(0, 1000);
                    $('#empty-state').html('<i class="fas fa-exclamation-triangle fa-3x text-danger mb-3 d-block"></i>Erreur serveur.').show();
                    showModal('<pre style="max-height:300px;overflow:auto;background:#f8f9fa;padding:10px;font-size:11px;white-space:pre-wrap;">'
                        + $('<div>').text(detail).html() + '</pre>');
                }
            });
        });

        function showModal(html) {
            $('#messageBody').html(html);
            $('#messageModal').modal('show');
        }

    });
    </script>

</body>
</html>
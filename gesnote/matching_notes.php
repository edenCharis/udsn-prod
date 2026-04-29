<?php 
include '../php/connexion.php';
include '../php/lib.php';
session_start();

$rolesAutorises = ['gesnote', 'admin', 'anonymat', 'direction'];
if (!($_SESSION['id'] == session_id() && in_array($_SESSION['role'], $rolesAutorises))) {
    header("location: ../deconnexion1");
    exit();
}

// ─── Export CSV ────────────────────────────────────────────────────────────────
if (isset($_GET['export_csv'])) {
    $ecue     = $_GET['ecue']     ?? '';
    $classe   = $_GET['classe']   ?? '';
    $semestre = $_GET['semestre'] ?? '';
    $annee    = $_GET['annee']    ?? '';
    $nature   = $_GET['nature']   ?? '';
    $etab     = $_SESSION['etablissement'];

    $whereConditions = ["a.etab = ?"];
    $params = [$etab];
    $types  = 's';

    if ($ecue)     { $whereConditions[] = 'a.ecue = ?';     $params[] = $ecue;     $types .= 's'; }
    if ($classe)   { $whereConditions[] = 'a.classe = ?';   $params[] = $classe;   $types .= 's'; }
    if ($semestre) { $whereConditions[] = 'a.semestre = ?'; $params[] = $semestre; $types .= 's'; }
    if ($annee)    { $whereConditions[] = 'a.annee = ?';    $params[] = $annee;    $types .= 's'; }
    if ($nature)   { $whereConditions[] = 'a.nature = ?';   $params[] = $nature;   $types .= 's'; }

    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

    $sql = "
        SELECT
            a.numero                        AS code_anonyme,
            i.matricule                     AS matricule,
            CONCAT(c.nom, ' ', c.prenom)    AS nom_prenom,
            a.ecue                          AS code_ecue,
            e.libelle                       AS libelle_ecue,
            a.classe,
            a.semestre,
            a.nature,
            a.annee,
            MAX(CASE WHEN l.type_examen = 'Session Ordinaire' THEN l.note END) AS note_ordinaire,
            MAX(CASE WHEN l.type_examen = 'Session de Rappel' THEN l.note END) AS note_rappel
        FROM anonymat a
        JOIN inscription i ON i.id = a.etudiant
        JOIN candidat c    ON c.code = i.candidat
        JOIN ecue e        ON e.code_ecue = a.ecue AND e.etab = a.etab
        LEFT JOIN ligne1 l ON  l.anonymat  = a.numero
                           AND l.code_ecue = a.ecue
                           AND l.annee     = a.annee
                           AND l.nature    = a.nature
                           AND l.etab      = a.etab
        $whereClause
        GROUP BY a.etudiant, i.matricule, c.nom, c.prenom,
                 a.ecue, e.libelle, a.classe, a.semestre, a.nature, a.annee, a.numero
        ORDER BY a.classe, a.ecue, c.nom, c.prenom
    ";

    $stmt = $connexion->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="comparatif_notes_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($out, ['Code Anonyme','Matricule','Nom & Prénom','Code ECUE','Libellé ECUE','Classe','Semestre','Nature','Année','Note Ordinaire /20','Note Rappel /20'], ';');
    while ($row = $result->fetch_assoc()) {
        $row['libelle_ecue'] = str_replace('+', "'", $row['libelle_ecue']);
        $row['nom_prenom']   = str_replace('+', "'", $row['nom_prenom']);
        fputcsv($out, array_values($row), ';');
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
            .ecue-grid { page-break-inside: avoid; }
        }
        .print-title { display: none; }

        /* ── Stats bar ─────────────────────────────────────────── */
        #stats-row .badge {
            font-size: .85rem;
            padding: .45em .85em;
            border-radius: 20px;
            margin-right: 6px;
            margin-bottom: 6px;
            display: inline-block;
        }

        /* ── Grid container ────────────────────────────────────── */
        #grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
            gap: 22px;
            margin-top: 10px;
        }

        /* ── ECUE card ─────────────────────────────────────────── */
        .ecue-card {
            border-radius: 14px;
            box-shadow: 0 4px 18px rgba(0,0,0,.10);
            overflow: hidden;
            background: #fff;
            transition: transform .2s, box-shadow .2s;
        }
        .ecue-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 28px rgba(0,0,0,.15);
        }

        /* Card header band – colour rotates per card */
        .ecue-card-header {
            padding: 14px 18px 12px;
            color: #fff;
            position: relative;
        }
        .ecue-card-header h6 {
            margin: 0 0 2px;
            font-size: .78rem;
            opacity: .85;
            letter-spacing: .05em;
            text-transform: uppercase;
        }
        .ecue-card-header h5 {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.3;
        }
        .ecue-card-header .badge-code {
            position: absolute;
            top: 12px; right: 14px;
            background: rgba(255,255,255,.22);
            border-radius: 8px;
            padding: 3px 9px;
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: .04em;
        }

        /* Mini stats inside header */
        .ecue-mini-stats {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            flex-wrap: wrap;
        }
        .ecue-mini-stats span {
            background: rgba(255,255,255,.2);
            border-radius: 20px;
            padding: 2px 10px;
            font-size: .72rem;
            font-weight: 600;
        }

        /* Card body – student mini-rows */
        .ecue-card-body {
            padding: 0;
            max-height: 320px;
            overflow-y: auto;
        }
        .ecue-card-body::-webkit-scrollbar { width: 5px; }
        .ecue-card-body::-webkit-scrollbar-thumb { background: #dee2e6; border-radius: 4px; }

        /* Column headers */
        .ecue-col-header {
            display: grid;
            grid-template-columns: 1fr 90px 90px;
            padding: 7px 14px;
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #6c757d;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        /* Student row */
        .student-row {
            display: grid;
            grid-template-columns: 1fr 90px 90px;
            padding: 9px 14px;
            border-bottom: 1px solid #f1f3f5;
            align-items: center;
            transition: background .15s;
        }
        .student-row:last-child { border-bottom: none; }
        .student-row:hover { background: #f8f9fa; }

        .student-name {
            font-size: .82rem;
            font-weight: 600;
            color: #343a40;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .student-mat {
            font-size: .68rem;
            color: #adb5bd;
            margin-top: 1px;
        }

        /* Note pill */
        .note-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 68px;
            height: 30px;
            border-radius: 8px;
            font-size: .82rem;
            font-weight: 700;
        }
        .note-pill.success  { background: #d1fae5; color: #065f46; }
        .note-pill.danger   { background: #fee2e2; color: #991b1b; }
        .note-pill.empty    { background: #f1f3f5; color: #9ca3af; font-size: .72rem; }

        /* Colour palette for card headers */
        .hue-0 { background: linear-gradient(135deg, #2563eb, #1d4ed8); }
        .hue-1 { background: linear-gradient(135deg, #059669, #047857); }
        .hue-2 { background: linear-gradient(135deg, #d97706, #b45309); }
        .hue-3 { background: linear-gradient(135deg, #7c3aed, #6d28d9); }
        .hue-4 { background: linear-gradient(135deg, #db2777, #be185d); }
        .hue-5 { background: linear-gradient(135deg, #0891b2, #0e7490); }
        .hue-6 { background: linear-gradient(135deg, #65a30d, #4d7c0f); }
        .hue-7 { background: linear-gradient(135deg, #ea580c, #c2410c); }

        /* Empty / loading states */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
            grid-column: 1 / -1;
        }
        .empty-state i { font-size: 3rem; margin-bottom: 12px; display: block; }

        /* View toggle */
        #toggle-view .btn { border-radius: 8px; font-size: .8rem; }
        #toggle-view .btn.active { box-shadow: inset 0 2px 6px rgba(0,0,0,.12); }

        /* Full-width table fallback */
        #table-view { display: none; }
        
        
        /* ── Filtre étudiant ───────────────────────────────────── */
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
#student-filter-count {
    font-size: .78rem;
    color: #6c757d;
}
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
                            <h3>Comparatif Notes : Ordinaire vs Rappel</h3>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../gesnote/">Gesnote</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0);">Comparatif Notes</a></li>
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
                                            <label>Nature</label>
                                            <select class="form-control" id="f-nature">
                                                <option value="">Toutes</option>
                                                <option>Examen Theorique</option>
                                                <option>Examen Pratique</option>
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
                                    <i class="fas fa-th-large"></i> Comparatif par matière
                                </h4>

                                <!-- View toggle -->
                                <div id="toggle-view" class="btn-group ml-2" style="display:none !important;" id="toggle-view-wrap">
                                    <button class="btn btn-sm btn-outline-secondary active" id="btn-grid-view" title="Vue grille">
                                        <i class="fas fa-th-large"></i> Grilles
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" id="btn-table-view" title="Vue tableau">
                                        <i class="fas fa-table"></i> Tableau
                                    </button>
                                </div>

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
                                    <h4><?php echo $_SESSION['univ']; ?> — Comparatif Notes Ordinaire / Rappel</h4>
                                    <p id="print-subtitle" class="text-muted"></p>
                                </div>

                                <!-- Global stats -->
                                <div id="stats-row" style="display:none;" class="mb-4">
                                    <span class="badge badge-primary">Total étudiants : <strong id="stat-count">0</strong></span>
                                    <span class="badge badge-info">Moy. Ordinaire : <strong id="stat-moy-ord">-</strong></span>
                                    <span class="badge badge-info">Moy. Rappel : <strong id="stat-moy-rap">-</strong></span>
                                    <span class="badge badge-success">Admis Ordinaire : <strong id="stat-admis-ord">-</strong></span>
                                    <span class="badge badge-warning">Admis Rappel : <strong id="stat-admis-rap">-</strong></span>
                                    <span class="badge badge-secondary">Avec note Rappel : <strong id="stat-has-rap">-</strong></span>
                                </div>

                                <!-- Loading spinner -->
                                <div id="loading" style="display:none;" class="text-center py-5">
                                    <div class="sk-three-bounce">
                                        <div class="sk-child sk-bounce1"></div>
                                        <div class="sk-child sk-bounce2"></div>
                                        <div class="sk-child sk-bounce3"></div>
                                    </div>
                                    <p class="mt-2 text-muted">Chargement en cours…</p>
                                </div>
                              <!-- ── FILTRE ÉTUDIANT (affiché après chargement) ── -->
<div id="student-filter-wrap" class="d-flex">
    <i class="fas fa-user-search" style="color:#6c757d;font-size:1rem;"></i>
    <input type="text" id="student-filter-input"
           placeholder="Rechercher un étudiant (nom ou matricule)…" />
    <button class="btn btn-sm btn-outline-secondary" id="student-filter-clear">
        <i class="fas fa-times"></i> Réinitialiser
    </button>
    <span id="student-filter-count"></span>
</div>
                                <!-- ── GRID VIEW ── -->
                                <div id="grid-view">
                                    <div id="grid-container">
                                        <div class="empty-state">
                                            <i class="fas fa-search-minus"></i>
                                            Utilisez les filtres ci-dessus puis cliquez sur <strong>Rechercher</strong>.
                                        </div>
                                    </div>
                                </div>

                                <!-- ── TABLE VIEW (fallback) ── -->
                                <div id="table-view">
                                    <div class="table-responsive">
                                        <table id="matching-table" class="display" style="min-width:1000px">
                                            <thead>
                                                <tr>
                                                    <th>N°</th>
                                                    <th>Matricule</th>
                                                    <th>Nom &amp; Prénom</th>
                                                    <th>ECUE</th>
                                                    <th>Classe</th>
                                                    <th>Semestre</th>
                                                    <th>Nature</th>
                                                    <th>Année</th>
                                                    <th>Code Anon. Ord.</th>
                                                    <th>Note Ordinaire</th>
                                                    <th>Code Anon. Rappel</th>
                                                    <th>Note Rappel</th>
                                                </tr>
                                            </thead>
                                            <tbody id="matching-tbody"></tbody>
                                        </table>
                                    </div>
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
    $(document).ready(function () {

        // ── URL error/success modal ───────────────────────────────────────
        var urlParams = new URLSearchParams(window.location.search);
        var erreur  = urlParams.get('erreur');
        var success = urlParams.get('sucess');
        if (erreur || success) {
            $('#messageBody').text(erreur ? "Erreur : " + erreur : "Succès : " + success);
            $('#messageModal').modal('show');
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        var dtTable   = null;
        var isGridView = true;

        // ── Helpers ───────────────────────────────────────────────────────
        var HUE_COUNT = 8;
        function hueClass(i) { return 'hue-' + (i % HUE_COUNT); }

        function noteVal(n) {
            if (n === null || n === undefined || n === '') return null;
            return parseFloat(n);
        }
        function notePill(n) {
            if (n === null) return '<span class="note-pill empty">—</span>';
            var cls = n >= 10 ? 'success' : 'danger';
            return '<span class="note-pill ' + cls + '">' + n.toFixed(2) + '</span>';
        }
        function noteValStr(n) {
            if (n === null || n === undefined || n === '') return '—';
            return parseFloat(n).toFixed(2);
        }

        // ── View toggle ───────────────────────────────────────────────────
        $('#btn-grid-view').on('click', function () {
            isGridView = true;
            $(this).addClass('active');
            $('#btn-table-view').removeClass('active');
            $('#grid-view').show();
            $('#table-view').hide();
        });
        $('#btn-table-view').on('click', function () {
            isGridView = false;
            $(this).addClass('active');
            $('#btn-grid-view').removeClass('active');
            $('#table-view').show();
            $('#grid-view').hide();
            if (dtTable) { dtTable.destroy(); dtTable = null; }
            dtTable = $('#matching-table').DataTable({ destroy: true });
        });

        // ── Main search ───────────────────────────────────────────────────
        $('#btn-search').on('click', function () {
            var classe   = $('#f-classe').val();
            var ecue     = $('#f-ecue').val();
            var semestre = $('#f-semestre').val();
            var annee    = $('#f-annee').val();
            var nature   = $('#f-nature').val();

            if (dtTable) { dtTable.destroy(); dtTable = null; }
            $('#loading').show();
            $('#stats-row').hide();
            $('#export-btns').hide();
            $('#grid-container').html('');
            $('#matching-tbody').html('');

            $.ajax({
                url: 'ajax_matching.php',
                method: 'POST',
                dataType: 'json',
                data: { classe, ecue, semestre, annee, nature },

                success: function (data) {
                    $('#loading').hide();

                    if (!data) {
                        showModal('Réponse vide du serveur.');
                        return;
                    }
                    if (data.error) {
                        showModal('<strong>Erreur :</strong><br><pre style="white-space:pre-wrap;font-size:12px;background:#f8f9fa;padding:10px;">'
                            + data.error + (data.sql ? '\n\nSQL:\n' + data.sql : '') + '</pre>');
                        return;
                    }
                    if (!data.rows || data.rows.length === 0) {
                        $('#grid-container').html('<div class="empty-state"><i class="fas fa-inbox"></i>Aucun résultat pour ces critères.</div>');
                        return;
                    }

                    // ── Global stats ──────────────────────────────────────
                    var total     = data.rows.length;
                    var hasRappel = data.rows.filter(function(r){ return r.note_rappel !== null && r.note_rappel !== ''; }).length;
                    var notesOrd  = data.rows.filter(function(r){ return r.note_ordinaire !== null && r.note_ordinaire !== ''; })
                                            .map(function(r){ return parseFloat(r.note_ordinaire); });
                    var notesRap  = data.rows.filter(function(r){ return r.note_rappel !== null && r.note_rappel !== ''; })
                                            .map(function(r){ return parseFloat(r.note_rappel); });
                    var moyOrd    = notesOrd.length ? (notesOrd.reduce(function(a,b){return a+b;},0) / notesOrd.length).toFixed(2) : '—';
                    var moyRap    = notesRap.length ? (notesRap.reduce(function(a,b){return a+b;},0) / notesRap.length).toFixed(2) : '—';
                    var admisOrd  = notesOrd.filter(function(n){ return n >= 10; }).length;
                    var admisRap  = notesRap.filter(function(n){ return n >= 10; }).length;

                    $('#stat-count').text(total);
                    $('#stat-moy-ord').text(moyOrd);
                    $('#stat-moy-rap').text(moyRap);
                    $('#stat-admis-ord').text(admisOrd + ' / ' + notesOrd.length);
                    $('#stat-admis-rap').text(admisRap + ' / ' + notesRap.length);
                    $('#stat-has-rap').text(hasRappel);
                    $('#stats-row').show();

                    // ── Group rows by ECUE ────────────────────────────────
                    var grouped = {};
                    var groupOrder = [];
                    $.each(data.rows, function(i, r) {
                        var key = r.code_ecue;
                        if (!grouped[key]) {
                            grouped[key] = { label: r.libelle_ecue || r.code_ecue, code: r.code_ecue, classe: r.classe, semestre: r.semestre, nature: r.nature, annee: r.annee, students: [] };
                            groupOrder.push(key);
                        }
                        grouped[key].students.push(r);
                    });

                    // ── Build GRID ────────────────────────────────────────
                

                    // Show toggle & export
                    $('#toggle-view-wrap').css('display', 'inline-flex');
                    // Force grid view
                    $('#grid-view').show();
                    $('#table-view').hide();
                    $('#btn-grid-view').addClass('active');
                    $('#btn-table-view').removeClass('active');
                    isGridView = true;

                    // CSV export URL
                    var exportUrl = window.location.pathname + '?export_csv=1'
                        + '&ecue='     + encodeURIComponent(ecue)
                        + '&classe='   + encodeURIComponent(classe)
                        + '&semestre=' + encodeURIComponent(semestre)
                        + '&annee='    + encodeURIComponent(annee)
                        + '&nature='   + encodeURIComponent(nature);
                    $('#btn-export').attr('href', exportUrl);
                    $('#export-btns').show();

                    // Print subtitle
                    var parts = [];
                    if (classe) parts.push('Classe : ' + classe);
                    if ($('#f-ecue option:selected').text() !== 'Tous') parts.push('ECUE : ' + $('#f-ecue option:selected').text());
                    if (nature) parts.push(nature);
                    if (annee)  parts.push(annee);
                    $('#print-subtitle').text(parts.join(' | ') + ' — Imprimé le ' + new Date().toLocaleDateString('fr-FR'));
                },

                error: function (xhr, status, errorThrown) {
                    $('#loading').hide();
                    var detail = 'Statut HTTP : ' + xhr.status + ' — ' + errorThrown;
                    if (xhr.responseText) detail += '\n\nRéponse brute :\n' + xhr.responseText.substring(0, 1000);
                    $('#grid-container').html('<div class="empty-state"><i class="fas fa-exclamation-triangle text-danger"></i>Erreur serveur.</div>');
                    showModal('<pre style="max-height:300px;overflow:auto;background:#f8f9fa;padding:10px;font-size:11px;white-space:pre-wrap;">' + $('<div>').text(detail).html() + '</pre>');
                }
            });
        });

        function showModal(html) {
            $('#messageBody').html(html);
            $('#messageModal').modal('show');
        }

    });
    
    
    
    $(document).ready(function () {

    var urlParams = new URLSearchParams(window.location.search);
    var erreur  = urlParams.get('erreur');
    var success = urlParams.get('sucess');
    if (erreur || success) {
        $('#messageBody').text(erreur ? "Erreur : " + erreur : "Succès : " + success);
        $('#messageModal').modal('show');
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    var dtTable    = null;
    var isGridView = true;
    var allRows    = [];      // ── AJOUT FILTRE ÉTUDIANT ── stocke toutes les données
    var groupedData = {};     // ── AJOUT FILTRE ÉTUDIANT ── stocke grouped par ECUE
    var groupOrderData = [];  // ── AJOUT FILTRE ÉTUDIANT ──

    var HUE_COUNT = 8;
    function hueClass(i) { return 'hue-' + (i % HUE_COUNT); }

    function noteVal(n) {
        if (n === null || n === undefined || n === '') return null;
        return parseFloat(n);
    }
    function notePill(n) {
        if (n === null) return '<span class="note-pill empty">—</span>';
        var cls = n >= 10 ? 'success' : 'danger';
        return '<span class="note-pill ' + cls + '">' + n.toFixed(2) + '</span>';
    }
    function noteValStr(n) {
        if (n === null || n === undefined || n === '') return '—';
        return parseFloat(n).toFixed(2);
    }

    // ── View toggle ───────────────────────────────────────────────────
    $('#btn-grid-view').on('click', function () {
        isGridView = true;
        $(this).addClass('active');
        $('#btn-table-view').removeClass('active');
        $('#grid-view').show();
        $('#table-view').hide();
    });
    $('#btn-table-view').on('click', function () {
        isGridView = false;
        $(this).addClass('active');
        $('#btn-grid-view').removeClass('active');
        $('#table-view').show();
        $('#grid-view').hide();
        if (dtTable) { dtTable.destroy(); dtTable = null; }
        dtTable = $('#matching-table').DataTable({ destroy: true });
    });

    // ── AJOUT FILTRE ÉTUDIANT : fonction buildGrid ────────────────────
    function buildGrid(rows) {
        if (!rows || rows.length === 0) {
            $('#grid-container').html(
                '<div class="empty-state"><i class="fas fa-user-slash"></i>Aucun étudiant ne correspond à la recherche.</div>'
            );
            $('#student-filter-count').text('0 étudiant(s)');
            return;
        }

        // Reconstruire grouped depuis les rows filtrées
        var grouped = {};
        var groupOrder = [];
        $.each(rows, function (i, r) {
            var key = r.code_ecue;
            if (!grouped[key]) {
                grouped[key] = {
                    label: r.libelle_ecue || r.code_ecue,
                    code: r.code_ecue,
                    students: []
                };
                groupOrder.push(key);
            }
            grouped[key].students.push(r);
        });

        var gridHtml = '';
        var cardIdx  = 0;
        $.each(groupOrder, function (gi, key) {
            var g = grouped[key];
            var studentRows = '';
            $.each(g.students, function (si, s) {
                var nOrd = noteVal(s.note_ordinaire);
                var nRap = noteVal(s.note_rappel);
                studentRows +=
                    '<div class="student-row">' +
                        '<div>' +
                            '<div class="student-name" title="' + s.nom_prenom + '">' + s.nom_prenom + '</div>' +
                            '<div class="student-mat">' + s.matricule + '</div>' +
                        '</div>' +
                        '<div>' + notePill(nOrd) + '</div>' +
                        '<div>' + notePill(nRap) + '</div>' +
                    '</div>';
            });

            gridHtml +=
                '<div class="ecue-card">' +
                    '<div class="ecue-card-header ' + hueClass(cardIdx) + '">' +
                        '<span class="badge-code">' + g.code + '</span>' +
                        '<h5>' + g.label + '</h5>' +
                    '</div>' +
                    '<div class="ecue-card-body">' +
                        '<div class="ecue-col-header">' +
                            '<span>Étudiant</span>' +
                            '<span>Ordinaire</span>' +
                            '<span>Rappel</span>' +
                        '</div>' +
                        studentRows +
                    '</div>' +
                '</div>';
            cardIdx++;
        });

        $('#grid-container').html(gridHtml);

        // Compter les étudiants uniques affichés
        var uniqueStudents = [];
        $.each(rows, function (i, r) {
            if (uniqueStudents.indexOf(r.matricule) === -1)
                uniqueStudents.push(r.matricule);
        });
        $('#student-filter-count').text(uniqueStudents.length + ' étudiant(s) affiché(s)');
    }

    // ── AJOUT FILTRE ÉTUDIANT : événement input ───────────────────────
    $('#student-filter-input').on('input', function () {
        var q = $(this).val().toLowerCase().trim();
        if (!q) {
            buildGrid(allRows);
            return;
        }
        var filtered = allRows.filter(function (r) {
            return r.nom_prenom.toLowerCase().indexOf(q) !== -1
                || r.matricule.toLowerCase().indexOf(q) !== -1;
        });
        buildGrid(filtered);
    });

    // ── AJOUT FILTRE ÉTUDIANT : bouton réinitialiser ──────────────────
    $('#student-filter-clear').on('click', function () {
        $('#student-filter-input').val('');
        buildGrid(allRows);
    });

    // ── Main search ───────────────────────────────────────────────────
    $('#btn-search').on('click', function () {
        var classe   = $('#f-classe').val();
        var ecue     = $('#f-ecue').val();
        var semestre = $('#f-semestre').val();
        var annee    = $('#f-annee').val();
        var nature   = $('#f-nature').val();

        if (dtTable) { dtTable.destroy(); dtTable = null; }
        $('#loading').show();
        $('#stats-row').hide();
        $('#export-btns').hide();
        $('#student-filter-wrap').hide();   // ── AJOUT FILTRE ÉTUDIANT ──
        $('#student-filter-input').val(''); // ── AJOUT FILTRE ÉTUDIANT ──
        $('#grid-container').html('');
        $('#matching-tbody').html('');

        $.ajax({
            url: 'ajax_matching.php',
            method: 'POST',
            dataType: 'json',
            data: { classe, ecue, semestre, annee, nature },

            success: function (data) {
                $('#loading').hide();

                if (!data) { showModal('Réponse vide du serveur.'); return; }
                if (data.error) {
                    showModal('<strong>Erreur :</strong><br><pre style="white-space:pre-wrap;font-size:12px;background:#f8f9fa;padding:10px;">'
                        + data.error + (data.sql ? '\n\nSQL:\n' + data.sql : '') + '</pre>');
                    return;
                }
                if (!data.rows || data.rows.length === 0) {
                    $('#grid-container').html('<div class="empty-state"><i class="fas fa-inbox"></i>Aucun résultat pour ces critères.</div>');
                    return;
                }

                // ── AJOUT FILTRE ÉTUDIANT : stocker les données ───────
                allRows = data.rows;

                // ── Global stats ──────────────────────────────────────
                var total     = data.rows.length;
                var hasRappel = data.rows.filter(function(r){ return r.note_rappel !== null && r.note_rappel !== ''; }).length;
                var notesOrd  = data.rows.filter(function(r){ return r.note_ordinaire !== null && r.note_ordinaire !== ''; })
                                        .map(function(r){ return parseFloat(r.note_ordinaire); });
                var notesRap  = data.rows.filter(function(r){ return r.note_rappel !== null && r.note_rappel !== ''; })
                                        .map(function(r){ return parseFloat(r.note_rappel); });
                var moyOrd    = notesOrd.length ? (notesOrd.reduce(function(a,b){return a+b;},0) / notesOrd.length).toFixed(2) : '—';
                var moyRap    = notesRap.length ? (notesRap.reduce(function(a,b){return a+b;},0) / notesRap.length).toFixed(2) : '—';
                var admisOrd  = notesOrd.filter(function(n){ return n >= 10; }).length;
                var admisRap  = notesRap.filter(function(n){ return n >= 10; }).length;

                $('#stat-count').text(total);
                $('#stat-moy-ord').text(moyOrd);
                $('#stat-moy-rap').text(moyRap);
                $('#stat-admis-ord').text(admisOrd + ' / ' + notesOrd.length);
                $('#stat-admis-rap').text(admisRap + ' / ' + notesRap.length);
                $('#stat-has-rap').text(hasRappel);
                $('#stats-row').show();

                // ── AJOUT FILTRE ÉTUDIANT : afficher le filtre ────────
                var uniqueStudents = [];
                $.each(allRows, function (i, r) {
                    if (uniqueStudents.indexOf(r.matricule) === -1)
                        uniqueStudents.push(r.matricule);
                });
                $('#student-filter-count').text(uniqueStudents.length + ' étudiant(s) affiché(s)');
                $('#student-filter-wrap').show();

                // ── Build GRID ────────────────────────────────────────
                buildGrid(allRows);

                // ── Build TABLE ───────────────────────────────────────
                var tbody = '';
                var rowNum = 0;
                $.each(data.rows, function(i, r) {
                    rowNum++;
                    var ecueLabel = r.libelle_ecue ? r.libelle_ecue + ' [' + r.code_ecue + ']' : r.code_ecue;
                    tbody +=
                        '<tr>' +
                        '<td>' + rowNum + '</td>' +
                        '<td>' + r.matricule + '</td>' +
                        '<td>' + r.nom_prenom + '</td>' +
                        '<td>' + ecueLabel + '</td>' +
                        '<td>' + r.classe + '</td>' +
                        '<td>' + r.semestre + '</td>' +
                        '<td>' + r.nature + '</td>' +
                        '<td>' + r.annee + '</td>' +
                        '<td><strong>' + (r.code_anonyme_ord || '—') + '</strong></td>' +
                        '<td>' + noteValStr(r.note_ordinaire) + '</td>' +
                        '<td><strong>' + (r.code_anonyme_rap || '—') + '</strong></td>' +
                        '<td>' + noteValStr(r.note_rappel) + '</td>' +
                        '</tr>';
                });
                $('#matching-tbody').html(tbody);

                // Show toggle & export
                $('#toggle-view-wrap').css('display', 'inline-flex');
                $('#grid-view').show();
                $('#table-view').hide();
                $('#btn-grid-view').addClass('active');
                $('#btn-table-view').removeClass('active');
                isGridView = true;

                var exportUrl = window.location.pathname + '?export_csv=1'
                    + '&ecue='     + encodeURIComponent(ecue)
                    + '&classe='   + encodeURIComponent(classe)
                    + '&semestre=' + encodeURIComponent(semestre)
                    + '&annee='    + encodeURIComponent(annee)
                    + '&nature='   + encodeURIComponent(nature);
                $('#btn-export').attr('href', exportUrl);
                $('#export-btns').show();

                var parts = [];
                if (classe) parts.push('Classe : ' + classe);
                if ($('#f-ecue option:selected').text() !== 'Tous') parts.push('ECUE : ' + $('#f-ecue option:selected').text());
                if (nature) parts.push(nature);
                if (annee)  parts.push(annee);
                $('#print-subtitle').text(parts.join(' | ') + ' — Imprimé le ' + new Date().toLocaleDateString('fr-FR'));
            },

            error: function (xhr, status, errorThrown) {
                $('#loading').hide();
                var detail = 'Statut HTTP : ' + xhr.status + ' — ' + errorThrown;
                if (xhr.responseText) detail += '\n\nRéponse brute :\n' + xhr.responseText.substring(0, 1000);
                $('#grid-container').html('<div class="empty-state"><i class="fas fa-exclamation-triangle text-danger"></i>Erreur serveur.</div>');
                showModal('<pre style="max-height:300px;overflow:auto;background:#f8f9fa;padding:10px;font-size:11px;white-space:pre-wrap;">' + $('<div>').text(detail).html() + '</pre>');
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
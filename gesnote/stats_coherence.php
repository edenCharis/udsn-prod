<?php
include '../php/connexion.php';
include '../php/lib.php';
session_start();

$rolesAutorises = ['gesnote', 'admin', 'anonymat', 'direction'];
if (!($_SESSION['id'] == session_id() && in_array($_SESSION['role'], $rolesAutorises))) {
    header("location: ../deconnexion1"); exit();
}
$canCorrect = in_array($_SESSION['role'], ['admin', 'gesnote', 'direction']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $_SESSION['univ']; ?> — Fiabilité des notes par classe</title>
    <link rel="icon" type="image/png" sizes="16x16" href="../administrateur/<?php echo $_SESSION['logo_univ']; ?>">
    <link rel="stylesheet" href="../vendor/bootstrap-select/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="../vendor/select2/css/select2.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/skin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">

    <style>
        :root {
            --green:  #16a34a; --bg-green:  #dcfce7;
            --red:    #dc2626; --bg-red:    #fee2e2;
            --orange: #ea580c; --bg-orange: #ffedd5;
            --yellow: #ca8a04; --bg-yellow: #fef9c3;
            --blue:   #1d4ed8; --bg-blue:   #dbeafe;
            --gray:   #64748b;
        }

        /* ── Bannière globale ──────────────────────────────────────── */
        .global-banner {
            background: linear-gradient(135deg,#0f172a 0%,#1e3a5f 100%);
            border-radius:14px; padding:22px 28px; color:#fff;
            display:flex; flex-wrap:wrap; align-items:center; gap:0;
            margin-bottom:24px;
        }
        .banner-stat {
            text-align:center; padding:6px 22px;
            border-left:1px solid rgba(255,255,255,.15);
        }
        .banner-stat:first-child { border-left:none; padding-left:0; }
        .banner-title { font-size:.78rem; opacity:.65; text-transform:uppercase; letter-spacing:.04em; margin-bottom:2px; }
        .banner-val   { font-size:2rem; font-weight:800; line-height:1.1; }
        .banner-sub   { font-size:.72rem; opacity:.5; margin-top:1px; }
        .c-green { color:#4ade80; } .c-red { color:#f87171; }
        .c-yellow{ color:#fbbf24; } .c-blue { color:#60a5fa; }

        /* ── Grille de cartes ──────────────────────────────────────── */
        .classes-grid {
            display:grid;
            grid-template-columns:repeat(auto-fill, minmax(320px,1fr));
            gap:18px; margin-bottom:28px;
        }
        .classe-card {
            background:#fff; border:1px solid #e2e8f0; border-radius:14px;
            padding:18px 20px; cursor:pointer;
            transition:transform .2s, box-shadow .2s;
            box-shadow:0 1px 4px rgba(0,0,0,.06);
            border-top:4px solid var(--accent,#94a3b8);
        }
        .classe-card:hover { transform:translateY(-3px); box-shadow:0 8px 22px rgba(0,0,0,.11); }
        .classe-card.acc-high   { --accent: var(--green); }
        .classe-card.acc-medium { --accent: var(--orange); }
        .classe-card.acc-low    { --accent: var(--red); }

        .card-name { font-size:1rem; font-weight:700; color:#1e293b; margin-bottom:3px; }
        .card-sub  { font-size:.75rem; color:var(--gray); margin-bottom:14px; }

        /* ── Jauge SVG ─────────────────────────────────────────────── */
        .gauges-row { display:flex; gap:14px; margin-bottom:14px; }
        .gauge-block { flex:1; text-align:center; }
        .gauge-lbl   { font-size:.68rem; color:var(--gray); text-transform:uppercase;
                       letter-spacing:.04em; margin-bottom:3px; }
        .gauge-svg   { width:62px; height:62px; display:block; margin:0 auto; }
        .gauge-svg circle { fill:none; stroke-width:7; }
        .gauge-bg    { stroke:#e2e8f0; }
        .gauge-fill  { stroke-linecap:round; transition:stroke-dashoffset .7s ease; }
        .gauge-fill.high   { stroke:var(--green); }
        .gauge-fill.medium { stroke:var(--orange); }
        .gauge-fill.low    { stroke:var(--red); }
        .gauge-fill.none   { stroke:#e2e8f0; }
        .gauge-text  { font-size:1rem; font-weight:800; fill:#1e293b;
                       dominant-baseline:middle; text-anchor:middle; }

        /* ── Barre horizontale ─────────────────────────────────────── */
        .mini-bar { margin-top:10px; }
        .mini-bar-head { display:flex; justify-content:space-between;
                         font-size:.72rem; color:var(--gray); margin-bottom:3px; }
        .mini-bar-track { height:5px; background:#e2e8f0; border-radius:99px; overflow:hidden; }
        .mini-bar-fill  { height:100%; border-radius:99px; transition:width .7s; }
        .mini-bar-fill.high   { background:var(--green); }
        .mini-bar-fill.medium { background:var(--orange); }
        .mini-bar-fill.low    { background:var(--red); }

        /* ── Badges compteurs ──────────────────────────────────────── */
        .badges-row { display:flex; gap:6px; flex-wrap:wrap; margin-top:10px;
                      padding-top:10px; border-top:1px solid #f1f5f9; }
        .badge-sm {
            font-size:.68rem; padding:2px 8px; border-radius:20px;
            font-weight:600; border:1px solid;
        }
        .badge-ok  { background:var(--bg-green); color:var(--green); border-color:#86efac; }
        .badge-bad { background:var(--bg-red);   color:var(--red);   border-color:#fca5a5; }
        .badge-rat { background:var(--bg-blue);  color:var(--blue);  border-color:#93c5fd; }
        .badge-dec { background:var(--bg-yellow);color:var(--yellow);border-color:#fde047; }

        .btn-voir {
            width:100%; margin-top:12px; padding:7px; border:1px solid #cbd5e1;
            border-radius:8px; background:#f8fafc; color:#475569; font-size:.8rem;
            font-weight:600; cursor:pointer; transition:all .15s;
        }
        .btn-voir:hover { background:var(--blue); color:#fff; border-color:var(--blue); }

        /* ── Modal ─────────────────────────────────────────────────── */
        #detail-modal .modal-dialog { max-width:97vw; }
        #detail-modal .modal-header { background:#0f172a; color:#fff; border-radius:4px 4px 0 0; }
        #detail-modal .modal-header .close { color:#fff; }

        /* ── Tableau détail ────────────────────────────────────────── */
        td.cell-ok      { background:#d1fae5!important; color:#065f46; font-weight:700; text-align:center; }
        td.cell-bad     { background:#fee2e2!important; color:#991b1b; font-weight:700; text-align:center; }
        td.cell-empty   { color:#d1d5db; text-align:center; font-size:.78rem; }
        td.cell-note    { text-align:center; font-weight:600; }
        td.cell-calc    { text-align:center; font-weight:700; color:var(--blue); }
        td.note-high    { color:#065f46!important; }
        td.note-low     { color:#991b1b!important; }
        tr.row-bad > td:first-child { border-left:4px solid #ef4444!important; }

        /* ── Onglets modal ─────────────────────────────────────────── */
        .modal-tabs { display:flex; gap:4px; margin-bottom:12px; flex-wrap:wrap; }
        .modal-tab  {
            padding:5px 14px; border:1px solid #e2e8f0; border-radius:7px;
            font-size:.78rem; font-weight:600; cursor:pointer;
            background:#f8fafc; color:#475569; transition:all .15s;
        }
        .modal-tab.active { background:var(--blue); color:#fff; border-color:var(--blue); }

        /* ── Filtre ────────────────────────────────────────────────── */
        .filter-bar {
            display:flex; gap:10px; align-items:center;
            flex-wrap:wrap; margin-bottom:10px;
        }
        .filter-bar input {
            border:1px solid #d1d5db; border-radius:7px;
            padding:5px 12px; font-size:.82rem; min-width:200px; flex:1;
            outline:none;
        }
        .filter-bar input:focus { border-color:var(--blue); box-shadow:0 0 0 3px rgba(29,78,216,.1); }

        /* ── Stats modal ───────────────────────────────────────────── */
        .modal-kpis { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:12px; }
        .mkpi {
            padding:4px 14px; border-radius:20px; font-size:.78rem;
            font-weight:600; border:1px solid;
        }

        /* ── Décision badges ───────────────────────────────────────── */
        .dec-valide  { color:#16a34a; font-weight:700; }
        .dec-ajourné { color:#dc2626; font-weight:700; }
        .dec-elim    { color:#92400e; font-weight:700; }

        /* ── État vide ─────────────────────────────────────────────── */
        .empty-state { text-align:center; padding:60px 20px; color:#94a3b8; }

        @media print {
            .no-print, nav, .header, .footer, #filters-card { display:none!important; }
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
                        <h3><i class="fas fa-chart-pie mr-2"></i>Fiabilité des notes — Vue par classe</h3>
                    </div>
                </div>
                <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="../gesnote/">Gesnote</a></li>
                        <li class="breadcrumb-item"><a href="coherence_notes.php">Cohérence Notes</a></li>
                        <li class="breadcrumb-item active">Stats par Classe</li>
                    </ol>
                </div>
            </div>

            <!-- ── FILTRES ─────────────────────────────────────────── -->
            <div class="row no-print" id="filters-card">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title"><i class="fas fa-filter mr-2"></i>Filtres</h4>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-end">
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label>Année académique</label>
                                        <select class="form-control" id="f-annee">
                                            <option value="">Toutes</option>
                                            <?php
                                            $res = $connexion->query("SELECT libelle FROM annee ORDER BY libelle DESC");
                                            while ($r = $res->fetch_assoc())
                                                echo "<option>".htmlspecialchars($r['libelle'])."</option>";
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label>Semestre</label>
                                        <select class="form-control" id="f-semestre">
                                            <option value="">Tous</option>
                                            <?php
                                            $res = $connexion->query("SELECT libelle FROM semestre ORDER BY libelle");
                                            while ($r = $res->fetch_assoc())
                                                echo "<option>".htmlspecialchars($r['libelle'])."</option>";
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label>&nbsp;</label><br>
                                        <button type="button" class="btn btn-primary" id="btn-search">
                                            <i class="fas fa-chart-bar mr-1"></i> Générer les statistiques
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-3 text-right">
                                    <a href="coherence_notes.php" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-table mr-1"></i> Page de cohérence détaillée
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── LÉGENDE ─────────────────────────────────────────── -->
            <div id="legend-wrap" style="display:none;" class="mb-2 no-print">
                <small class="text-muted mr-3">
                    <i class="fas fa-info-circle mr-1"></i>
                    <strong>Incohérence</strong> = les 3 champs de notation sont présents mais
                    la moyenne stockée ≠ (CC + Examen) / 2.
                    Les champs vides (NULL) sont ignorés et ne comptent pas.
                </small><br>
                <small class="text-muted mr-3"><i class="fas fa-circle" style="color:var(--green)"></i> Précision ≥ 90 %</small>
                <small class="text-muted mr-3"><i class="fas fa-circle" style="color:var(--orange)"></i> 70–90 %</small>
                <small class="text-muted"><i class="fas fa-circle" style="color:var(--red)"></i> &lt; 70 %</small>
                <small class="text-muted ml-3"><i class="fas fa-hand-pointer mr-1"></i>Cliquez sur une carte pour le détail étudiant</small>
            </div>

            <!-- ── BANNIÈRE ─────────────────────────────────────────── -->
            <div id="global-banner-wrap" style="display:none;">
                <div class="global-banner">
                    <div class="banner-stat">
                        <div class="banner-title">Précision globale — Ordinaire</div>
                        <div class="banner-val c-green" id="gb-acc-ord">—</div>
                        <div class="banner-sub" id="gb-acc-ord-sub"></div>
                    </div>
                    <div class="banner-stat">
                        <div class="banner-title">Incohérences ordinaire</div>
                        <div class="banner-val c-red" id="gb-bad-ord">—</div>
                        <div class="banner-sub">lignes incohérentes</div>
                    </div>
                    <div class="banner-stat">
                        <div class="banner-title">Précision globale — Rattrapage</div>
                        <div class="banner-val c-yellow" id="gb-acc-rat">—</div>
                        <div class="banner-sub" id="gb-acc-rat-sub"></div>
                    </div>
                    <div class="banner-stat">
                        <div class="banner-title">Incohérences rattrapage</div>
                        <div class="banner-val c-red" id="gb-bad-rat">—</div>
                        <div class="banner-sub">lignes incohérentes</div>
                    </div>
                    <div class="banner-stat">
                        <div class="banner-title">Décisions recap ≠ recalculé</div>
                        <div class="banner-val c-red" id="gb-dec-bad">—</div>
                        <div class="banner-sub">étudiants avec mauvaise décision publiée</div>
                    </div>
                    <div class="banner-stat">
                        <div class="banner-title">Classes analysées</div>
                        <div class="banner-val c-blue" id="gb-classes">—</div>
                        <div class="banner-sub">classes</div>
                    </div>
                </div>
            </div>

            <!-- ── SPINNER ─────────────────────────────────────────── -->
            <div id="loading" style="display:none;" class="text-center py-5">
                <div class="sk-three-bounce">
                    <div class="sk-child sk-bounce1"></div>
                    <div class="sk-child sk-bounce2"></div>
                    <div class="sk-child sk-bounce3"></div>
                </div>
                <p class="mt-2 text-muted">Calcul des statistiques en cours…</p>
            </div>

            <!-- ── GRILLE ───────────────────────────────────────────── -->
            <div id="classes-grid-wrap" style="display:none;">
                <div class="classes-grid" id="classes-grid"></div>
            </div>

            <!-- ── ÉTAT VIDE ────────────────────────────────────────── -->
            <div id="empty-state" class="empty-state">
                <i class="fas fa-chart-pie fa-3x mb-3 d-block"></i>
                <p>Sélectionnez une année et/ou un semestre, puis cliquez sur <strong>Générer les statistiques</strong>.</p>
            </div>

        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════
         MODAL DÉTAIL
         ══════════════════════════════════════════════════════════════ -->
    <div class="modal fade" id="detail-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" style="max-width:97vw;" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-users mr-2"></i>
                        Détail — <span id="detail-classe-name"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal"><span style="color:#fff">&times;</span></button>
                </div>

                <div class="modal-body" style="padding:18px 22px;">

                    <!-- KPIs -->
                    <div class="modal-kpis" id="modal-kpis"></div>

                    <!-- Onglets + filtre -->
                    <div class="filter-bar">
                        <i class="fas fa-search" style="color:#94a3b8;"></i>
                        <input type="text" id="modal-search" placeholder="Rechercher par nom ou matricule…">
                        <div class="modal-tabs" id="modal-tabs">
                            <button class="modal-tab active" data-filter="all">Tous</button>
                            <button class="modal-tab" data-filter="bad">Incohérences seulement</button>
                            <button class="modal-tab" data-filter="dec_bad">Décision incorrecte</button>
                            <button class="modal-tab" data-filter="ok">OK seulement</button>
                        </div>
                        <span id="modal-count" style="font-size:.75rem;color:#94a3b8;margin-left:auto;white-space:nowrap;"></span>
                    </div>

                    <!-- Spinner modal -->
                    <div id="modal-loading" class="text-center py-4" style="display:none;">
                        <div class="sk-three-bounce">
                            <div class="sk-child sk-bounce1"></div>
                            <div class="sk-child sk-bounce2"></div>
                            <div class="sk-child sk-bounce3"></div>
                        </div>
                    </div>

                    <!-- Tableau -->
                    <div id="modal-table-wrap" style="display:none;">
                        <div class="table-responsive">
                            <table id="detail-table" class="display" style="width:100%;font-size:.8rem;">
                                <thead>
                                    <tr style="background:#0f172a;color:#fff;">
                                        <th>Matricule</th>
                                        <th>Nom &amp; Prénom</th>
                                        <th>ECUE</th>
                                        <th>CC (moyDev)</th>
                                        <th>MoyEx (exam. ord.)</th>
                                        <th>MoyGen stockée</th>
                                        <th>MoyGen recalculée</th>
                                        <th title="Les 3 présents : vérification (CC+MoyEx)/2 = MoyGen">Cohér. Ord.</th>
                                        <th>Sess. Rappel (exam.)</th>
                                        <th>MoyGenRatt stockée</th>
                                        <th>MoyGenRatt recalculée</th>
                                        <th title="Les 3 présents : vérification (CC+Rappel)/2 = MoyGenRatt">Cohér. Ratt.</th>
                                        <th>Décision recalc. <small>(ord.)</small></th>
                                        <th>Recap publiée <small>(ord.)</small></th>
                                        <th>Match décision <small>(ord.)</small></th>
                                        <th>Décision recalc. <small>(ratt.)</small></th>
                                        <th>Recap publiée <small>(ratt.)</small></th>
                                        <th>Match décision <small>(ratt.)</small></th>
                                    </tr>
                                </thead>
                                <tbody id="detail-tbody"></tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <div class="modal-footer" style="background:#f8fafc;border-top:1px solid #e2e8f0;">
                    <?php if ($canCorrect): ?>
                    <a id="btn-goto-coherence" href="coherence_notes.php" class="btn btn-danger btn-sm">
                        <i class="fas fa-wrench mr-1"></i> Aller corriger dans la page cohérence
                    </a>
                    <?php endif; ?>
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Fermer</button>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal messages -->
    <div class="modal" id="messageModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
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
            <p>Copyright © Designed &amp; Développé par <a href="https://www.cet-up.com" target="_blank">CETUP</a> 2023</p>
        </div>
    </div>
</div>

<!-- Scripts -->
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

    var dtDetail   = null;
    var detailRows = [];
    var filterMode = 'all';

    /* ── Helpers ─────────────────────────────────────────────────── */
    function fmt(v)  { return (v === null || v === undefined || v === '') ? '—' : parseFloat(v).toFixed(2); }
    function fmtN(v) { return (v === null || v === undefined || v === '') ? null : parseFloat(v); }
    function noteClass(v) { return v === null ? '' : (v >= 10 ? 'note-high' : 'note-low'); }

    function accClass(pct) {
        if (pct === null) return 'none';
        if (pct >= 90) return 'high';
        if (pct >= 70) return 'medium';
        return 'low';
    }

    /* ── Jauge SVG ───────────────────────────────────────────────── */
    function gauge(pct, cls) {
        var r = 24, circ = 2 * Math.PI * r;
        var offset = pct !== null ? circ - (pct / 100) * circ : circ;
        var label  = pct !== null ? pct.toFixed(1) + '%' : 'N/D';
        return `<svg class="gauge-svg" viewBox="0 0 56 56">
          <circle class="gauge-bg"   cx="28" cy="28" r="${r}"/>
          <circle class="gauge-fill ${cls}" cx="28" cy="28" r="${r}"
            stroke-dasharray="${circ}" stroke-dashoffset="${offset}"
            transform="rotate(-90 28 28)"/>
          <text class="gauge-text" x="28" y="28">${label}</text>
        </svg>`;
    }

    /* ── Mini barre ─────────────────────────────────────────────── */
    function miniBar(pct, cls, label) {
        var w = pct !== null ? pct : 0;
        var p = pct !== null ? pct.toFixed(1) + '%' : 'N/D';
        return `<div class="mini-bar">
          <div class="mini-bar-head"><span>${label}</span><span>${p}</span></div>
          <div class="mini-bar-track"><div class="mini-bar-fill ${cls}" style="width:${w}%"></div></div>
        </div>`;
    }

    /* ── Rendu d'une carte ──────────────────────────────────────── */
    function renderCard(r) {
        var clsOrd = accClass(r.acc_ord);
        var clsRat = accClass(r.acc_rat);
        var cardCls = clsOrd === 'high' ? 'acc-high' : (clsOrd === 'medium' ? 'acc-medium' : 'acc-low');

        var badOrd = r.ord_bad || 0, okOrd = r.ord_ok || 0;
        var badRat = r.rat_bad || 0, okRat = r.rat_ok || 0;
        var decBad = (r.dec_ord_mismatch||0) + (r.dec_rat_mismatch||0);

        return `
        <div class="classe-card ${cardCls}" data-classe="${r.classe}">
            <div class="card-name"><i class="fas fa-users mr-1" style="font-size:.82rem;"></i>${r.classe}</div>
            <div class="card-sub">${r.nb_etudiants} étudiant(s) · ${r.nb_lignes} ligne(s) ECUE</div>

            <div class="gauges-row">
                <div class="gauge-block">
                    <div class="gauge-lbl">Session Ord.</div>
                    ${gauge(r.acc_ord, clsOrd)}
                </div>
                <div class="gauge-block">
                    <div class="gauge-lbl">Rattrapage</div>
                    ${gauge(r.acc_rat, clsRat)}
                </div>
                <div class="gauge-block">
                    <div class="gauge-lbl">Décisions recap</div>
                    ${gauge(r.acc_dec_ord, accClass(r.acc_dec_ord))}
                </div>
            </div>

            ${miniBar(r.acc_ord, clsOrd, 'Précision MoyGen ordinaire')}
            ${r.rat_ok + r.rat_bad > 0 ? miniBar(r.acc_rat, clsRat, 'Précision MoyGenRatt rattrapage') : ''}
            ${r.dec_ord_total > 0 ? miniBar(r.acc_dec_ord, accClass(r.acc_dec_ord), 'Décisions recap ordinaire correctes') : ''}

            <div class="badges-row">
                <span class="badge-sm badge-ok">${okOrd} ord. OK</span>
                ${badOrd > 0 ? `<span class="badge-sm badge-bad">${badOrd} ord. INCOH.</span>` : ''}
                ${okRat + badRat > 0 ? `<span class="badge-sm badge-rat">${okRat} ratt. OK</span>` : ''}
                ${badRat > 0 ? `<span class="badge-sm badge-bad">${badRat} ratt. INCOH.</span>` : ''}
                ${decBad > 0 ? `<span class="badge-sm badge-dec">${decBad} décis. incorrecte(s)</span>` : ''}
            </div>

            <button class="btn-voir" data-classe="${r.classe}">
                <i class="fas fa-search mr-1"></i> Voir le détail étudiant par étudiant
            </button>
        </div>`;
    }

    /* ── Cellule cohérence ──────────────────────────────────────── */
    function cohCell(val) {
        if (val === null || val === undefined) return '<td class="cell-empty">—</td>';
        if (val === 'ok')  return '<td class="cell-ok">OK ✓</td>';
        // bad : on montre les valeurs
        return '<td class="cell-bad">INCOH. ✗</td>';
    }

    /* ── Cellule décision ───────────────────────────────────────── */
    function decClass(d) {
        if (!d || d === '—') return '';
        if (d.indexOf('Validé')        !== -1) return 'dec-valide';
        if (d.indexOf('liminatoire')   !== -1) return 'dec-elim';
        return 'dec-ajourné';
    }

    function matchCell(val) {
        if (val === null || val === undefined) return '<td class="cell-empty">—</td>';
        if (val === 'ok')  return '<td class="cell-ok">✓ Conforme</td>';
        return '<td class="cell-bad">✗ Différent</td>';
    }

    /* ── Recherche principale ───────────────────────────────────── */
    $('#btn-search').on('click', function () {
        var annee    = $('#f-annee').val();
        var semestre = $('#f-semestre').val();

        $('#loading').show();
        $('#global-banner-wrap, #legend-wrap, #classes-grid-wrap, #empty-state').hide();
        $('#classes-grid').html('');

        $.ajax({
            url: 'ajax_stats_classe.php',
            method: 'POST', dataType: 'json',
            data: { action: 'summary', annee: annee, semestre: semestre },
            success: function (data) {
                $('#loading').hide();
                if (!data || data.error) { showModal(data ? data.error : 'Réponse vide.'); return; }
                if (!data.rows || data.rows.length === 0) {
                    $('#empty-state').html('<i class="fas fa-inbox fa-3x mb-3 d-block"></i>Aucune classe trouvée.').show();
                    return;
                }

                /* Bannière globale */
                var totOrdOk=0, totOrdBad=0, totRatOk=0, totRatBad=0, totDecBad=0;
                $.each(data.rows, function(i,r){
                    totOrdOk  += parseInt(r.ord_ok  ||0);
                    totOrdBad += parseInt(r.ord_bad ||0);
                    totRatOk  += parseInt(r.rat_ok  ||0);
                    totRatBad += parseInt(r.rat_bad ||0);
                    totDecBad += parseInt(r.dec_ord_mismatch||0) + parseInt(r.dec_rat_mismatch||0);
                });
                var totOrd = totOrdOk + totOrdBad;
                var totRat = totRatOk + totRatBad;
                var accOrd = totOrd > 0 ? (totOrdOk/totOrd*100).toFixed(1)+'%' : 'N/D';
                var accRat = totRat > 0 ? (totRatOk/totRat*100).toFixed(1)+'%' : 'N/D';

                $('#gb-acc-ord').text(accOrd);
                $('#gb-acc-ord-sub').text(totOrdOk+' ok / '+totOrd+' vérifiées');
                $('#gb-bad-ord').text(totOrdBad);
                $('#gb-acc-rat').text(accRat);
                $('#gb-acc-rat-sub').text(totRatOk+' ok / '+totRat+' vérifiées');
                $('#gb-bad-rat').text(totRatBad);
                $('#gb-dec-bad').text(totDecBad);
                $('#gb-classes').text(data.rows.length);
                $('#global-banner-wrap, #legend-wrap').show();

                var html = '';
                $.each(data.rows, function(i, r) { html += renderCard(r); });
                $('#classes-grid').html(html);
                $('#classes-grid-wrap').show();
            },
            error: function (xhr, s, e) {
                $('#loading').hide();
                showModal('Erreur serveur : ' + xhr.status + ' — ' + e);
            }
        });
    });

    /* ── Clic sur une carte ─────────────────────────────────────── */
    $(document).on('click', '.classe-card .btn-voir, .classe-card', function (e) {
        var $card  = $(this).hasClass('classe-card') ? $(this) : $(this).closest('.classe-card');
        var classe = $card.data('classe');
        if (!classe) return;
        openDetail(classe);
    });

    /* ── Chargement du détail ───────────────────────────────────── */
    function openDetail(classe) {
        detailRows = []; filterMode = 'all';
        $('#detail-classe-name').text(classe);
        $('#modal-kpis').html('');
        $('#modal-loading').show();
        $('#modal-table-wrap').hide();
        if (dtDetail) { dtDetail.destroy(); dtDetail = null; }
        $('#detail-tbody').html('');
        $('.modal-tab').removeClass('active');
        $('.modal-tab[data-filter="all"]').addClass('active');
        $('#modal-search').val('');

        var annee    = $('#f-annee').val();
        var semestre = $('#f-semestre').val();
        $('#btn-goto-coherence').attr('href',
            'coherence_notes.php?classe_init='+encodeURIComponent(classe)
            +(annee    ? '&annee_init='+encodeURIComponent(annee)    : '')
            +(semestre ? '&sem_init='  +encodeURIComponent(semestre) : ''));

        $('#detail-modal').modal('show');

        $.ajax({
            url: 'ajax_stats_classe.php',
            method: 'POST', dataType: 'json',
            data: { action:'detail', classe:classe, annee:annee, semestre:semestre },
            success: function (data) {
                $('#modal-loading').hide();
                if (!data || data.error) { showModal(data ? data.error : 'Réponse vide.'); return; }

                detailRows = data.rows || [];
                var total  = detailRows.length;
                var bad    = data.incoherent_count;
                var ok     = total - bad;
                var decBad = detailRows.filter(function(r){
                    return r.dec_ord_match==='bad' || r.dec_rat_match==='bad';
                }).length;

                var pct = total > 0 ? (ok/total*100).toFixed(1) : '—';

                $('#modal-kpis').html(`
                    <span class="mkpi" style="background:var(--bg-green);color:var(--green);border-color:#86efac;">
                        <i class="fas fa-check-circle mr-1"></i>${ok} cohérentes
                    </span>
                    <span class="mkpi" style="background:var(--bg-red);color:var(--red);border-color:#fca5a5;">
                        <i class="fas fa-times-circle mr-1"></i>${bad} incohérentes
                    </span>
                    <span class="mkpi" style="background:var(--bg-blue);color:var(--blue);border-color:#93c5fd;">
                        <i class="fas fa-percentage mr-1"></i>Précision : ${pct}%
                    </span>
                    ${decBad > 0 ? `<span class="mkpi" style="background:var(--bg-yellow);color:var(--yellow);border-color:#fde047;">
                        <i class="fas fa-exclamation-triangle mr-1"></i>${decBad} décision(s) recap incorrecte(s)
                    </span>` : ''}
                    <span class="mkpi" style="background:#f1f5f9;color:var(--gray);border-color:#cbd5e1;">
                        ${total} lignes ECUE
                    </span>
                `);

                buildDetailTable();
                $('#modal-table-wrap').show();
            },
            error: function (xhr, s, e) {
                $('#modal-loading').hide();
                showModal('Erreur serveur détail : ' + e);
            }
        });
    }

    /* ── Construction tableau détail ───────────────────────────── */
    function buildDetailTable() {
        var q = $('#modal-search').val().toLowerCase().trim();

        var filtered = detailRows.filter(function(r) {
            if (filterMode === 'bad' && !r.incoherent) return false;
            if (filterMode === 'ok'  &&  r.incoherent) return false;
            if (filterMode === 'dec_bad' &&
                r.dec_ord_match !== 'bad' && r.dec_rat_match !== 'bad') return false;
            if (q && r.nom_prenom.toLowerCase().indexOf(q) === -1
                  && r.matricule.toLowerCase().indexOf(q) === -1) return false;
            return true;
        });

        $('#modal-count').text(filtered.length + ' ligne(s)');

        if (dtDetail) { dtDetail.destroy(); dtDetail = null; }

        var tbody = '';
        $.each(filtered, function(i, r) {
            var mRecOrd = r.moy_recalc_ord !== null ? parseFloat(r.moy_recalc_ord) : null;
            var mRecRat = r.moy_recalc_rat !== null ? parseFloat(r.moy_recalc_rat) : null;
            var mDev    = fmtN(r.moyDev);
            var mEx     = fmtN(r.moyEx);
            var mGen    = fmtN(r.moyGen);
            var mRap    = fmtN(r.session_rappel);
            var mGRat   = fmtN(r.moyenGenRattrapage);

            var decOrd  = r.decision_recalc_ord || '—';
            var decRat  = r.decision_recalc_rat || '—';
            var recOrd  = r.recap_decision_ord  || '—';
            var recRat  = r.recap_decision_rat  || '—';

            tbody +=
                '<tr class="'+(r.incoherent?'row-bad':'')+'">' +
                '<td>'+r.matricule+'</td>' +
                '<td>'+r.nom_prenom+'</td>' +
                '<td><small>'+r.code_ecue+' — '+r.libelle_ecue+'</small></td>' +
                // CC / MoyEx / MoyGen
                '<td class="cell-note '+(mDev!==null?noteClass(mDev):'')+'">'+fmt(r.moyDev)+'</td>' +
                '<td class="cell-note '+(mEx!==null?noteClass(mEx):'')+'">'+fmt(r.moyEx)+'</td>' +
                '<td class="cell-note '+(mGen!==null?noteClass(mGen):'')+'">'+fmt(r.moyGen)+'</td>' +
                '<td class="cell-calc '+(mRecOrd!==null?noteClass(mRecOrd):'')+'">'+fmt(r.moy_recalc_ord)+'</td>' +
                cohCell(r.coh_ord) +
                // Rappel / MoyGenRatt
                '<td class="cell-note '+(mRap!==null?noteClass(mRap):'')+'">'+fmt(r.session_rappel)+'</td>' +
                '<td class="cell-note '+(mGRat!==null?noteClass(mGRat):'')+'">'+fmt(r.moyenGenRattrapage)+'</td>' +
                '<td class="cell-calc '+(mRecRat!==null?noteClass(mRecRat):'')+'">'+fmt(r.moy_recalc_rat)+'</td>' +
                cohCell(r.coh_rat) +
                // Décision ordinaire
                '<td class="'+decClass(decOrd)+'">'+decOrd+'</td>' +
                '<td class="'+decClass(recOrd)+'">'+recOrd+'</td>' +
                matchCell(r.dec_ord_match) +
                // Décision rattrapage
                '<td class="'+decClass(decRat)+'">'+decRat+'</td>' +
                '<td class="'+decClass(recRat)+'">'+recRat+'</td>' +
                matchCell(r.dec_rat_match) +
                '</tr>';
        });

        $('#detail-tbody').html(tbody);
        dtDetail = $('#detail-table').DataTable({
            destroy: true, pageLength: 25,
            language: { url: '//cdn.datatables.net/plug-ins/1.10.21/i18n/French.json' },
            order: [[7, 'desc'], [1, 'asc']]
        });
    }

    /* ── Onglets modal ──────────────────────────────────────────── */
    $(document).on('click', '.modal-tab', function() {
        $('.modal-tab').removeClass('active');
        $(this).addClass('active');
        filterMode = $(this).data('filter');
        if (detailRows.length) buildDetailTable();
    });
    $('#modal-search').on('input', function() {
        if (detailRows.length) buildDetailTable();
    });

    function showModal(msg) {
        $('#messageBody').html(msg);
        $('#messageModal').modal('show');
    }
});
</script>
</body>
</html>
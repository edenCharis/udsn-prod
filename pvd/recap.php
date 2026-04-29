<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if (!($_SESSION['id'] == session_id() && $_SESSION['role'] == "pvd")) {
    header("location: ../login");
    exit;
}

/* ═══════════════════════════════════════════════════════════
   MODE IMPRESSION PDF : génère uniquement la page imprimable
   ═══════════════════════════════════════════════════════════ */
if (isset($_POST['imprimer_recap'])) {

    $filtre_examen  = trim($_POST['filtre_examen']  ?? '');
    $filtre_annee   = trim($_POST['filtre_annee']   ?? '');
    $filtre_parcours= trim($_POST['filtre_parcours'] ?? '');

    $etablissement  = $_SESSION['etablissement'];
    $etab_lib       = $_SESSION['lib_etab'] ?? $etablissement;
    $univ           = $_SESSION['univ'] ?? '';

    // Construction de la requête avec filtres
    $where = ["etab='" . mysqli_real_escape_string($connexion, $etablissement) . "'"];
    if (!empty($filtre_examen))   $where[] = "examen='"   . mysqli_real_escape_string($connexion, $filtre_examen)   . "'";
    if (!empty($filtre_annee))    $where[] = "annee='"    . mysqli_real_escape_string($connexion, $filtre_annee)    . "'";

    $sql = "SELECT * FROM recap WHERE " . implode(" AND ", $where) . " ORDER BY annee DESC, semestre, decision";
    $resultat = $connexion->query($sql);

    $rows = [];
    while ($r = $resultat->fetch_object()) {
        // Filtre parcours côté PHP (car parcours n'est pas dans recap)
        if (!empty($filtre_parcours)) {
            $spec_etud = getSpecialiteDuCandidat(getCandidatCodeByInscription($r->etudiant, $connexion), $r->annee, $connexion);
            $parc_etud = getParcours($spec_etud, $connexion);
            if (strtolower($parc_etud) !== strtolower($filtre_parcours)) continue;
        }
        $rows[] = $r;
    }

    $nb_total   = count($rows);
    $nb_valides = count(array_filter($rows, fn($r) => stripos($r->decision, 'Ajourné') === false && stripos($r->decision, 'Ajourne') === false && $r->decision !== '-' && $r->decision !== 'Note Eliminatoire' && $r->decision !== ''));
    $nb_ajournes= $nb_total - $nb_valides;

    $label_examen = '';
    if ($filtre_examen === 'ordinaire')   $label_examen = 'Session Ordinaire';
    elseif ($filtre_examen === 'rattrapage') $label_examen = 'Session de Rattrapage';
    else $label_examen = 'Toutes sessions';

    ?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Récapitulatif des Résultats – <?php echo htmlspecialchars($etab_lib); ?></title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: "Times New Roman", Times, serif; font-size:10.5pt; color:#000; background:#fff; }

  .no-print {
    text-align:center; padding:12px; background:#f0f0f0; border-bottom:1px solid #ccc;
  }
  .btn-print {
    background:#c0392b; color:#fff; border:none; padding:10px 30px;
    font-size:14px; cursor:pointer; border-radius:4px; margin:4px;
  }
  .btn-close {
    background:#555; color:#fff; border:none; padding:10px 20px;
    font-size:14px; cursor:pointer; border-radius:4px; margin:4px;
  }
  .btn-print:hover { background:#a93226; }
  .btn-close:hover { background:#333; }

  .page {
    width:21cm; min-height:29.7cm;
    margin:0 auto; padding:1.8cm 2cm 1.8cm 2cm; background:#fff;
  }

  /* ── En-tête 3 colonnes (identique à generer_pv.php) ── */
  .header-row    { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px; }
  .header-left   { flex:1; text-align:left; }
  .header-center { flex:0 0 auto; text-align:center; padding:0 16px; }
  .header-right  { flex:1; text-align:right; }
  .h-univ  { font-size:12pt; font-weight:bold; text-transform:uppercase; margin-bottom:3px; }
  .h-dir   { font-size:10pt; margin-bottom:2px; }
  .h-serv  { font-size:9pt; }
  .h-devise{ font-size:12pt; margin-bottom:3px; }
  .header-line { border:none; border-top:2px solid #000; margin:8px 0 14px 0; }

  /* ── Titre ── */
  .titre-pv   { text-align:center; font-size:14pt; font-weight:bold; text-transform:uppercase; letter-spacing:1px; margin-bottom:4px; }
  .sous-titre  { text-align:center; font-size:10.5pt; margin-bottom:12px; color:#333; }

  /* ── Filtres actifs ── */
  .filtres-actifs {
    display:flex; gap:10px; flex-wrap:wrap; justify-content:center;
    margin-bottom:12px;
  }
  .badge-filtre {
    display:inline-block; padding:3px 10px; border-radius:12px;
    font-size:9pt; font-weight:bold; border:1px solid #999;
  }
  .badge-examen   { background:#eaf0fb; border-color:#3498db; color:#1a5fa8; }
  .badge-annee    { background:#fef9e7; border-color:#f0b429; color:#7d5a00; }
  .badge-parcours { background:#eafaf1; border-color:#2ecc71; color:#1a7a43; }

  /* ── Statistiques ── */
  .stats-row {
    display:flex; gap:16px; justify-content:center; margin-bottom:14px;
  }
  .stat-box {
    text-align:center; padding:8px 18px;
    border:1px solid #ddd; border-radius:6px; min-width:90px;
  }
  .stat-box .stat-nb   { font-size:18pt; font-weight:bold; line-height:1.2; }
  .stat-box .stat-label{ font-size:8pt; text-transform:uppercase; color:#555; margin-top:2px; }
  .stat-total   { border-color:#555; }
  .stat-valides { border-color:#27ae60; }
  .stat-ajournes{ border-color:#c0392b; }
  .stat-total   .stat-nb { color:#333; }
  .stat-valides .stat-nb { color:#27ae60; }
  .stat-ajournes .stat-nb{ color:#c0392b; }

  /* ── Tableau ── */
  .table-recap { width:100%; border-collapse:collapse; font-size:9.5pt; }
  .table-recap thead tr { background:#2c3e50; color:#fff; }
  .table-recap th { padding:5px 7px; text-align:center; border:1px solid #888; font-size:9pt; }
  .table-recap td { padding:4px 7px; border:1px solid #ccc; vertical-align:middle; }
  .table-recap tbody tr:nth-child(even) { background:#f7f7f7; }

  .decision-valide      { color:#1a7a43; font-weight:bold; }
  .decision-ajourné     { color:#c0392b; font-weight:bold; }
  .decision-eliminatoire{ color:#7d1a00; font-weight:bold; }

  /* ── Pied de page ── */
  .pied-page {
    margin-top:20px; display:flex; justify-content:flex-end;
    font-size:10pt;
  }
  .sig-block { text-align:center; }
  .sig-title { font-weight:bold; margin-bottom:35px; }
  .sig-name  { font-size:10.5pt; }

  @media print {
    @page { size:A4 landscape; margin:12mm 14mm; }
    .no-print { display:none !important; }
    .page { width:100%; margin:0; padding:0; min-height:auto; }
    .table-recap thead tr {
      background:#2c3e50 !important; color:#fff !important;
      -webkit-print-color-adjust:exact; print-color-adjust:exact;
    }
    .table-recap tbody tr:nth-child(even) {
      background:#f7f7f7 !important;
      -webkit-print-color-adjust:exact; print-color-adjust:exact;
    }
    .stat-valides .stat-nb  { color:#27ae60 !important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    .stat-ajournes .stat-nb { color:#c0392b !important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
  }
</style>
</head>
<body>

<div class="no-print">
  <button class="btn-print" onclick="window.print()">🖨️ Imprimer / Enregistrer en PDF</button>
  <button class="btn-close" onclick="window.close()">✕ Fermer</button>
  <br><small style="color:#666;">Utilisez <strong>Ctrl+P</strong> → <strong>« Enregistrer en PDF »</strong> pour un fichier PDF</small>
</div>

<div class="page">

  <!-- ══ EN-TÊTE (identique à generer_pv.php) ══ -->
  <div class="header-row">
    <div class="header-left">
      <div class="h-univ">UNIVERSITE DENIS SASSOU-N'GUESSO</div>
      <div class="h-dir">VICE-PRESIDENCE</div>
      <div class="h-dir"><?php echo strtoupper(htmlspecialchars($etab_lib)); ?></div>
      <div class="h-dir">Direction des examens et concours</div>
      <div class="h-serv">Service de la scolarité et des examens</div>
    </div>
    <div class="header-center">
      <img src="../images/univ.png" alt="Logo" style="max-height:80px;">
    </div>
    <div class="header-right">
      <div class="h-devise">Rigueur – Excellence – Lumières</div>
    </div>
  </div>


  <!-- ══ TITRE ══ -->
  <div class="titre-pv">Récapitulatif des Résultats</div>
  <div class="sous-titre">Document généré le <?php echo date('d/m/Y à H:i'); ?></div>
  <div class="sous-titre">Parcours :  <?php echo $filtre_parcours; ?></div>
  <div class="sous-titre">Examen :  <?php echo $filtre_examen; ?></div>
  <div class="sous-titre">Annee : <?php echo $filtre_annee; ?></div>
  <!-- ══ FILTRES ACTIFS ══ -->


  <!-- ══ STATISTIQUES ══ -->
  
  <!-- ══ TABLEAU ══ -->
  <table class="table-recap">
    <thead>
      <tr>
        <th>N°</th>
        <th>Nom(s) et Prénom(s)</th>
      
       
   
        <th>Moyenne</th>
        <th>Décision</th>
      </tr>
    </thead>
    <tbody>
    <?php if (empty($rows)): ?>
      <tr>
        <td colspan="9" style="text-align:center; padding:16px; color:#888;">
          Aucun résultat trouvé pour les filtres sélectionnés.
        </td>
      </tr>
    <?php else: $num = 0; foreach ($rows as $ue): $num++; 
        $candidat_code = getCandidatCodeByInscription($ue->etudiant, $connexion);
        $nom_complet   = mettrePremieresLettresMajuscules(
            getNomEtudiant($candidat_code, $connexion, $_SESSION["lib_etab"])
            . "  " .
            getPrenomEtudiant($candidat_code, $connexion, $_SESSION["lib_etab"])
        );
        $spec_etud = getSpecialiteDuCandidat($candidat_code, $ue->annee, $connexion);
        $parc_etud = getParcours($spec_etud, $connexion);

        // Classe CSS décision
        $dec_class = '';
        if (stripos($ue->decision, 'Validé') !== false || stripos($ue->decision, 'Valide') !== false) {
            $dec_class = 'decision-valide';
        } elseif (stripos($ue->decision, 'Eliminatoire') !== false) {
            $dec_class = 'decision-eliminatoire';
        } elseif (stripos($ue->decision, 'Ajourné') !== false || stripos($ue->decision, 'Ajourne') !== false) {
            $dec_class = 'decision-ajourné';
        }
    ?>
      <tr>
        <td style="text-align:center;"><?php echo $num; ?></td>
        <td><?php echo htmlspecialchars($nom_complet); ?></td>
       
       
        <td style="text-align:center; font-weight:bold;"><?php echo htmlspecialchars($ue->moy); ?></td>
        <td class="<?php echo $dec_class; ?>" style="text-align:center;"><?php echo htmlspecialchars($ue->decision); ?></td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>

  <!-- ══ PIED DE PAGE ══ -->
  <div class="pied-page">
    <div class="sig-block">
      <div class="sig-title">Le Président du Jury</div>
      <div class="sig-name"><?php echo htmlspecialchars(getNomUtilisateurParId($connexion, $_SESSION['id_user'])); ?></div>
    </div>
  </div>

</div><!-- /page -->

<script>
  window.addEventListener('load', function() {
    setTimeout(function() { window.print(); }, 400);
  });
</script>
</body>
</html>
<?php
    exit;
}
/* ─── FIN MODE IMPRESSION ─── */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $_SESSION['univ'];?> - Scolarité de <?php echo $_SESSION['etablissement'];?></title>
    <link rel="icon" type="image/png" sizes="16x16" href="../administrateur/<?php echo $_SESSION['logo_univ']?>">
    <link rel="stylesheet" href="../vendor/bootstrap-select/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="../vendor/select2/css/select2.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/skin.css">
    <link href="../vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
    <style>
        .filtre-bar {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 14px;
        }
        .filtre-bar .form-group { margin-bottom: 0; min-width: 180px; flex: 1; }
        .filtre-bar label       { font-size: 12px; font-weight: 600; color: #555; margin-bottom: 4px; }
        .btn-imprimer-pdf {
            background: #c0392b; color: #fff; border: none;
            padding: 8px 20px; border-radius: 4px; font-size: 13px;
            cursor: pointer; white-space: nowrap; align-self: flex-end;
            height: 38px; display:flex; align-items:center; gap:6px;
        }
        .btn-imprimer-pdf:hover { background:#a93226; color:#fff; }
        .btn-reset {
            background: #6c757d; color: #fff; border: none;
            padding: 8px 14px; border-radius: 4px; font-size: 13px;
            cursor: pointer; white-space: nowrap; align-self: flex-end;
            height: 38px;
        }
        .btn-reset:hover { background:#545b62; color:#fff; }

        /* Pastilles décision dans le tableau */
        .badge-valide      { background:#d4edda; color:#155724; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:600; }
        .badge-ajourné     { background:#f8d7da; color:#721c24; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:600; }
        .badge-eliminatoire{ background:#fff3cd; color:#856404; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:600; }
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

            <div class="row page-titles mx-0">
                <div class="col-sm-6 p-md-0">
                    <div class="welcome-text">
                        <h3>Récapitulatif des Résultats</h3>
                    </div>
                </div>
                <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="../pvd/">Scolarité</a></li>
                        <li class="breadcrumb-item active">Recap</li>
                    </ol>
                </div>
            </div>

            <!-- ══ BARRE DE FILTRES + BOUTON IMPRESSION ══ -->
            <div class="row">
                <div class="col-lg-12">
                    <form id="formFiltres" method="post">
                        <div class="filtre-bar">

                            <!-- Filtre : Type examen -->
                            <div class="form-group">
                                <label>Type d'examen</label>
                                <select class="form-control form-control-sm" name="filtre_examen" id="filtre_examen">
                                    <option value="">Tous les examens</option>
                                    <option value="ordinaire">Session Ordinaire</option>
                                    <option value="rattrapage">Session de Rattrapage</option>
                                </select>
                            </div>

                            <!-- Filtre : Année -->
                            <div class="form-group">
                                <label>Année académique</label>
                                <select class="form-control form-control-sm" name="filtre_annee" id="filtre_annee">
                                    <option value="">Toutes les années</option>
                                    <?php
                                    $res_annees = $connexion->query("SELECT DISTINCT annee FROM recap WHERE etab='" . mysqli_real_escape_string($connexion, $_SESSION['etablissement']) . "' ORDER BY annee DESC");
                                    if ($res_annees) while ($an = $res_annees->fetch_object()) {
                                        echo '<option value="' . htmlspecialchars($an->annee) . '">' . htmlspecialchars($an->annee) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- Filtre : Parcours -->
                            <div class="form-group">
                                <label>Parcours</label>
                                <select class="form-control form-control-sm" name="filtre_parcours" id="filtre_parcours">
                                    <option value="">Tous les parcours</option>
                                    <?php
                                    $res_parc = $connexion->query("SELECT DISTINCT libelle FROM parcours ORDER BY libelle");
                                    if ($res_parc) while ($p = $res_parc->fetch_object()) {
                                        echo '<option value="' . htmlspecialchars($p->libelle) . '">' . htmlspecialchars($p->libelle) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- Boutons -->
                            <button type="button" class="btn-reset" id="btnReset" title="Réinitialiser les filtres">
                                <i class="fa fa-refresh"></i> Réinitialiser
                            </button>

                            <button type="submit" name="imprimer_recap" value="1"
                                    class="btn-imprimer-pdf" id="btnImprimerPDF"
                                    formtarget="_blank">
                                <i class="fa fa-file-pdf-o"></i> Imprimer en PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- ══ FIN FILTRES ══ -->

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Résultats publiés</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="example3" class="display" style="min-width:845px">
                                    <thead>
                                        <tr>
                                            <th>N°</th>
                                            <th>Étudiant</th>
                                            <th>Parcours</th>
                                            <th>Spécialité</th>
                                            <th>Examen</th>
                                            <th>Moyenne</th>
                                            <th>Décision</th>
                                            <th>Semestre</th>
                                            <th>Année univ.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $sql     = "SELECT * FROM recap WHERE etab='" . mysqli_real_escape_string($connexion, $_SESSION['etablissement']) . "'";
                                    $resultat = $connexion->query($sql);
                                    $num = 0;

                                    while ($ue = $resultat->fetch_object()) {
                                        $num++;
                                        $candidat_code = getCandidatCodeByInscription($ue->etudiant, $connexion);
                                        $spec_etud     = getSpecialiteDuCandidat($candidat_code, $ue->annee, $connexion);
                                        $parc_etud     = getParcours($spec_etud, $connexion);

                                        // Pastille décision
                                        $dec_badge = '';
                                        if (stripos($ue->decision, 'Validé') !== false || stripos($ue->decision, 'Valide') !== false) {
                                            $dec_badge = '<span class="badge-valide">' . htmlspecialchars($ue->decision) . '</span>';
                                        } elseif (stripos($ue->decision, 'Eliminatoire') !== false) {
                                            $dec_badge = '<span class="badge-eliminatoire">' . htmlspecialchars($ue->decision) . '</span>';
                                        } elseif (stripos($ue->decision, 'Ajourné') !== false || stripos($ue->decision, 'Ajourne') !== false) {
                                            $dec_badge = '<span class="badge-ajourné">' . htmlspecialchars($ue->decision) . '</span>';
                                        } else {
                                            $dec_badge = htmlspecialchars($ue->decision);
                                        }
                                    ?>
                                    <tr>
                                        <td><?php echo $ue->id; ?></td>
                                        <td><?php echo mettrePremieresLettresMajuscules(
                                            getNomEtudiant($candidat_code, $connexion, $_SESSION["lib_etab"])
                                            . "  " .
                                            getPrenomEtudiant($candidat_code, $connexion, $_SESSION["lib_etab"])
                                        ); ?></td>
                                        <td data-parcours="<?php echo htmlspecialchars($parc_etud); ?>"><?php echo htmlspecialchars($parc_etud); ?></td>
                                        <td><?php echo htmlspecialchars($spec_etud); ?></td>
                                        <td data-examen="<?php echo htmlspecialchars($ue->examen); ?>"><?php echo htmlspecialchars($ue->examen); ?></td>
                                        <td><?php echo htmlspecialchars($ue->moy); ?></td>
                                        <td><?php echo $dec_badge; ?></td>
                                        <td><?php echo htmlspecialchars($ue->semestre); ?></td>
                                        <td data-annee="<?php echo htmlspecialchars($ue->annee); ?>"><?php echo htmlspecialchars($ue->annee); ?></td>
                                    </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
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
<script src="../vendor/peity/jquery.peity.min.js"></script>
<script src="../js/dashboard/dashboard-2.js"></script>
<script src="../vendor/select2/js/select2.full.min.js"></script>
<script src="../js/plugins-init/select2-init.js"></script>
<script src="../vendor/svganimation/vivus.min.js"></script>
<script src="../vendor/svganimation/svg.animation.js"></script>
<script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
<script src="../js/plugins-init/datatables.init.js"></script>

<script>
$(document).ready(function() {

    var table = $('#example3').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json"
        }
    });

    /* ── Filtrage côté client sur le tableau affiché ── */
    function appliquerFiltres() {
        var examen   = $('#filtre_examen').val().toLowerCase();
        var annee    = $('#filtre_annee').val();
        var parcours = $('#filtre_parcours').val().toLowerCase();

        $.fn.dataTable.ext.search.length = 0; // vider filtres custom

        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            var row         = table.row(dataIndex).node();
            var rowExamen   = $(row).find('td[data-examen]').data('examen')   || '';
            var rowAnnee    = $(row).find('td[data-annee]').data('annee')     || '';
            var rowParcours = $(row).find('td[data-parcours]').data('parcours') || '';

            if (examen   && rowExamen.toLowerCase()   !== examen)   return false;
            if (annee    && rowAnnee                  !== annee)    return false;
            if (parcours && rowParcours.toLowerCase() !== parcours) return false;
            return true;
        });

        table.draw();
    }

    $('#filtre_examen, #filtre_annee, #filtre_parcours').on('change', function() {
        appliquerFiltres();
    });

  

    /* ── Bouton PDF : ouvrir dans un nouvel onglet ── */
    $('#btnImprimerPDF').on('click', function(e) {
        e.preventDefault();

        var $form = $('#formFiltres');
        // On s'assure que le champ caché imprimer_recap est bien là
        if ($form.find('input[name="imprimer_recap"]').length === 0) {
            $form.append('<input type="hidden" name="imprimer_recap" value="1">');
        }
        $form.attr('action', '').attr('target', '_blank');
        $form[0].submit();
        $form.attr('target', '');
        $form.find('input[name="imprimer_recap"]').remove();
    });

    /* ── Messages URL ── */
    var urlParams = new URLSearchParams(window.location.search);
    var erreur    = urlParams.get('erreur');
    var success   = urlParams.get('sucess');
    if (erreur || success) {
        var message = erreur ? "Erreur : " + erreur : "Message : " + success;
        alert(message);
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});
</script>

</body>
</html>
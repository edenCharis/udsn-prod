<?php
/**
 * generer_pv.php
 * Procès-Verbal de Délibération – Génération HTML + window.print()
 */

include '../php/connexion.php';
include '../php/lib.php';
session_start();

// Sécurité
if (!($_SESSION['id'] == session_id() && in_array($_SESSION['role'], ['scolarité', 'pvd']))) {
    header("location: ../login");
    exit;
}

// ── Fonction utilitaire ──
function obtenirMoyenne1($connexion, $etudiant, $semestre, $examen, $annee, $etablissement) {
    $requete = "SELECT moy, decision FROM recap WHERE etudiant = ? AND semestre = ? AND examen = ? AND annee = ? AND etab = ?";
    $stmt = $connexion->prepare($requete);
    $stmt->bind_param("issss", $etudiant, $semestre, $examen, $annee, $etablissement);
    $stmt->execute();
    $resultat = $stmt->get_result();
    $row = $resultat->fetch_assoc();
    $stmt->close();
    return $row ?? null; // retourne ['moy' => ..., 'decision' => ...] ou null
}

function removeAccents($str) {
    $from = ['À','Á','Â','Ã','Ä','Å','à','á','â','ã','ä','å',
             'È','É','Ê','Ë','è','é','ê','ë',
             'Ì','Í','Î','Ï','ì','í','î','ï',
             'Ò','Ó','Ô','Õ','Ö','ò','ó','ô','õ','ö',
             'Ù','Ú','Û','Ü','ù','ú','û','ü',
             'Ý','ý','ÿ','Ç','ç','Ñ','ñ'];
    $to   = ['A','A','A','A','A','A','a','a','a','a','a','a',
             'E','E','E','E','e','e','e','e',
             'I','I','I','I','i','i','i','i',
             'O','O','O','O','O','o','o','o','o','o',
             'U','U','U','U','u','u','u','u',
             'Y','y','y','C','c','N','n'];
    return str_replace($from, $to, $str);
}

// Upload handler – PV signés
$upload_success = '';
$upload_error   = '';
if (isset($_POST['upload_pv'])) {
    $pv_dir = __DIR__ . '/pv_signes/';
    if (!is_dir($pv_dir)) mkdir($pv_dir, 0755, true);

    $u_classe   = preg_replace('/[^\w\s\-]/', '', trim($_POST['u_classe']   ?? ''));
    $u_semestre = preg_replace('/[^\w\s\-]/', '', trim($_POST['u_semestre'] ?? ''));
    $u_examen   = in_array(trim($_POST['u_examen'] ?? ''), ['ordinaire','rattrapage'])
                    ? trim($_POST['u_examen']) : '';
    $u_annee    = preg_replace('/[^\w\-]/', '', trim($_POST['u_annee'] ?? ''));
    $u_etab     = preg_replace('/[^\w\s\-]/', '', $_SESSION['etablissement'] ?? '');

    if (!empty($_FILES['pv_file']['name']) && $_FILES['pv_file']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['pv_file']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['pdf','jpg','jpeg','png'])) {
            $sanitize = fn($s) => str_replace([' ','/','\\'], '_', $s);
            $safe_name = implode('__', [
                $sanitize($u_etab), $sanitize($u_classe),
                $sanitize($u_semestre), $u_examen, $sanitize($u_annee),
            ]) . '.' . $ext;
            if (move_uploaded_file($_FILES['pv_file']['tmp_name'], $pv_dir . $safe_name)) {
                $upload_success = "PV signé importé avec succès.";
            } else {
                $upload_error = "Erreur lors de l'importation. Vérifiez les permissions du dossier.";
            }
        } else {
            $upload_error = "Format non autorisé. Acceptés : PDF, JPG, PNG.";
        }
    } else {
        $upload_error = "Aucun fichier sélectionné ou erreur d'upload.";
    }
}

/* ═══════════════════════════════════════════════════════════
   MODE IMPRESSION : PHP génère uniquement la page imprimable
   ═══════════════════════════════════════════════════════════ */
if (isset($_POST['imprimer'])) {

    $specialite  = trim($_POST['specialite'] ?? '');
    $parcours    = getParcours($specialite, $connexion);

    $classe      = trim($_POST['classe'] ?? '');
    $semestre    = trim($_POST['semestre'] ?? '');
    $examen      = trim($_POST['examen'] ?? '');
    $annee       = trim($_POST['annee'] ?? '');
    $president   = trim($_POST['president'] ?? '');
    $rapporteur  = trim($_POST['rapporteur'] ?? '');
    $membres_raw = trim($_POST['membres'] ?? '');
    $ville       = trim($_POST['ville'] ?? 'Brazzaville');
    $date_pv     = trim($_POST['date_pv'] ?? date('d/m/Y'));
    $type_liste  = trim($_POST['type_liste'] ?? 'valides');

    $membres = array_values(array_filter(array_map('trim', explode("\n", $membres_raw))));

    $classe_sql      = mysqli_real_escape_string($connexion, $classe);
    $semestre_sql    = mysqli_real_escape_string($connexion, $semestre);
    $annee_sql       = mysqli_real_escape_string($connexion, $annee);
    $specialite_sql  = mysqli_real_escape_string($connexion, $specialite);
    $etablissement   = $_SESSION['etablissement'];

    $sql_niveau = "SELECT niveau, specialite FROM classe WHERE libelle='$classe_sql'";
    $niveau_object = $connexion->query($sql_niveau);
    $niveau = ''; $spec = '';
    if ($niveau_object) {
        while ($ns = $niveau_object->fetch_object()) {
            $niveau = $ns->niveau;
            $spec   = $ns->specialite;
        }
    }

    // ── Récupérer les étudiants de la classe ──
    $sql_etudiants = "
        SELECT candidat.nom AS nom, candidat.prenom AS prenom,
               candidat.date_nais AS dn, candidat.bac AS bac, candidat.lieu_nais AS ln,
               candidat.sexe AS sexe, inscription.id AS insc_id
        FROM inscription
        JOIN candidat   ON candidat.code      = inscription.candidat
        JOIN classe     ON inscription.classe  = classe.libelle
        JOIN specialite ON classe.specialite   = specialite.libelle
        JOIN parcours   ON specialite.parcours = parcours.libelle
        WHERE classe.libelle    = '$classe_sql'
          AND inscription.annee = '$annee_sql'
        GROUP BY candidat.nom, candidat.prenom
        ORDER BY LOWER(nom), LOWER(prenom)
    ";

    $r     = $connexion->query($sql_etudiants);
    $admis = [];

    if ($r) {
        while ($et = $r->fetch_object()) {

            // ── Récupérer moyenne + décision depuis recap ──
            $recap = obtenirMoyenne1($connexion, $et->insc_id, $semestre_sql, $examen, $annee_sql, $etablissement);

            // Étudiant absent de recap → on ignore
            if ($recap === null || $recap['moy'] === null || $recap['moy'] === '') continue;

            $tt       = $recap['moy'];
            $decision = $recap['decision'] ?? '-';

            // En session de rattrapage, si pas de ligne rattrapage
            // on vérifie aussi qu'il n'a pas déjà validé en ordinaire
            if ($examen === 'rattrapage') {
                $sql_check = "SELECT etudiant FROM recap
                              WHERE etudiant = ?
                                AND semestre = ?
                                AND annee    = ?
                                AND etab     = ?
                                AND examen   = 'ordinaire'
                                AND decision <> 'Validé'
                              LIMIT 1";
                $stmt = $connexion->prepare($sql_check);
                $stmt->bind_param("isss", $et->insc_id, $semestre_sql, $annee_sql, $etablissement);
                $stmt->execute();
                $stmt->store_result();
                $doit_rattraper = ($stmt->num_rows > 0);
                $stmt->close();
                if (!$doit_rattraper) continue;
            }

            // ── Filtrer selon type_liste ──
            $est_valide  = ($decision === 'Validé');
            $est_ajourné = !$est_valide && $decision !== '-';

            if ($type_liste === 'valides') {
                if (!$est_valide) continue;
            } else {
                if ($est_valide) continue;
                if ($decision === '-') continue;
            }

            $admis[] = [
                'nom'      => strtoupper($et->nom),
                'prenom'   => ucwords(strtolower($et->prenom)),
                'dn'       => htmlspecialchars($et->dn ?? ''),
                'ln'       => htmlspecialchars($et->ln ?? ''),
                'bac'      => htmlspecialchars($et->bac ?? ''),
                'sexe'     => htmlspecialchars($et->sexe ?? ''),
                'moyenne'  => number_format(floatval($tt), 2, ',', ''),
                'mention'  => mentionParmoyenne($tt, 2),
                'decision' => $decision,
            ];
        }
    }

    // ── Tri ──
    if ($type_liste === 'valides') {
        usort($admis, fn($a, $b) => floatval(str_replace(',', '.', $b['moyenne'])) <=> floatval(str_replace(',', '.', $a['moyenne'])));
    } else {
        usort($admis, fn($a, $b) => strcmp($a['nom'] . $a['prenom'], $b['nom'] . $b['prenom']));
    }
    foreach ($admis as $i => &$et) { $et['rang'] = $i + 1; }
    unset($et);

    $nb_admis     = count($admis);
    $liste_label  = ($type_liste === 'valides') ? 'Admis' : 'Ajournés';
    $examen_label = ($examen === 'ordinaire') ? 'Session Ordinaire' : 'Session de Rattrapage';
    $univ         = $_SESSION['univ'] ?? '';
    $etab         = $_SESSION['lib_etab'] ?? $etablissement;

    function nbEnLettres($n) {
        $ones = ['','un','deux','trois','quatre','cinq','six','sept','huit','neuf',
                 'dix','onze','douze','treize','quatorze','quinze','seize','dix-sept','dix-huit','dix-neuf'];
        $tens = ['','dix','vingt','trente','quarante','cinquante','soixante','soixante','quatre-vingt','quatre-vingt'];
        if ($n == 0) return 'zéro';
        if ($n < 20) return $ones[$n];
        if ($n < 100) {
            $t = intdiv($n, 10); $u = $n % 10;
            if ($t == 7) return 'soixante-' . $ones[10 + $u];
            if ($t == 9) return $u ? 'quatre-vingt-' . $ones[$u] : 'quatre-vingts';
            return $tens[$t] . ($u ? '-' . $ones[$u] : ($t == 8 ? '' : ''));
        }
        if ($n < 1000) {
            $c = intdiv($n, 100); $rem = $n % 100;
            $pre = ($c == 1 ? 'cent' : $ones[$c] . ' cent') . ($rem == 0 && $c > 1 ? 's' : '');
            return $pre . ($rem ? ' ' . nbEnLettres($rem) : '');
        }
        return (string)$n;
    }

    $nb_str = nbEnLettres($nb_admis);

    /* ── PAGE HTML DÉDIÉE À L'IMPRESSION ── */
    ?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>PV Délibération – <?php echo htmlspecialchars($classe); ?> – <?php echo htmlspecialchars($semestre); ?></title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: "Times New Roman", Times, serif; font-size:11pt; color:#000; background:#fff; }

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
    width: 21cm; min-height: 29.7cm;
    margin: 0 auto; padding: 2cm 2.2cm 2cm 2.2cm; background: #fff;
  }

  .header-row    { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px; }
  .header-left   { flex:1; text-align:center; }
  .header-center { flex:0 0 auto; text-align:center; padding:0 16px; }
  .header-right  { flex:1; text-align:right; }
  .h-univ  { font-size:12pt; font-weight:bold; text-transform:uppercase; margin-bottom:3px; white-space:nowrap; }
  .h-dir   { font-size:10pt; margin-bottom:2px; }
  .h-serv  { font-size:9pt; }
  .h-devise{ font-size:12pt; margin-bottom:3px; }
  .h-etab  { font-size:10pt; font-weight:bold; margin-bottom:2px; }
  .header-line { border:none; border-top:2px solid #000; margin:8px 0 14px 0; }

  .titre-pv  { text-align:center; font-size:14pt; font-weight:bold; text-transform:uppercase; letter-spacing:1px; margin-bottom:6px; }
  .sous-titre{ text-align:center; font-size:10pt; margin-bottom:14px; }

  .info-grid { width:100%; margin-bottom:14px; border-collapse:collapse; }
  .info-grid td { padding:2px 6px; font-size:10.5pt; vertical-align:top; }
  .info-grid td:first-child { font-weight:bold; white-space:nowrap; width:150px; }

  .intro-text { text-align:justify; font-size:11pt; margin-bottom:14px; line-height:1.6; }

  .table-admis { width:100%; border-collapse:collapse; margin-bottom:14px; font-size:10pt; }
  .table-admis thead tr { background:#2c3e50; color:#fff; }
  .table-admis th { padding:6px 8px; text-align:center; font-weight:bold; border:1px solid #999; }
  .table-admis td { padding:4px 8px; border:1px solid #ccc; vertical-align:middle; }
  .table-admis tbody tr:nth-child(even) { background:#f5f5f5; }
  .table-admis .col-rang   { text-align:center; width:40px; }
  .table-admis .col-nom    { text-align:left; }
  .table-admis .col-prenom { text-align:left; }
  .table-admis .col-dn     { text-align:center; width:110px; }
  .table-admis .col-sexe   { text-align:center; width:55px; }
  .table-admis .col-moy    { text-align:center; width:70px; }
  .table-admis .col-mention{ text-align:center; width:100px; }

  .cloture { font-size:11pt; margin-bottom:16px; }

  .bas-page-wrapper{ page-break-inside:avoid; break-inside:avoid; }
  .bas-page        { display:flex; justify-content:space-between; align-items:flex-start; margin-top:10px;
                     page-break-inside:avoid; break-inside:avoid; }
  .bloc-membres    { flex:0 0 55%; }
  .bloc-signatures { flex:0 0 40%; text-align:center; }

  .membres-title { font-weight:bold; font-size:11pt; margin-bottom:6px; }
  .membres-list  { margin:0; padding-left:18px; font-size:10.5pt; }
  .membres-list li{ margin-bottom:10px; }

  .sig-title { font-weight:bold; font-size:11pt; margin-bottom:4px; }
  .sig-name  { font-size:11pt; margin-top:28px; }

  @media print {
    @page { size: A4 portrait; margin: 14mm 16mm; }
    .no-print { display:none !important; }
    .page { width:100%; margin:0; padding:0; min-height:auto; }
    .table-admis thead tr { background:#2c3e50 !important; color:#fff !important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    .table-admis tbody tr:nth-child(even) { background:#f5f5f5 !important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    body { font-size:9.5pt; }
  }
</style>
</head>
<body>

<div class="no-print">
  <button class="btn-print" onclick="window.print()">🖨️ Imprimer / Enregistrer en PDF</button>
  <button class="btn-close" onclick="window.close()">✕ Fermer</button>
  <br><small style="color:#666;">Utilisez <strong>Ctrl+P</strong> → choisissez « Enregistrer en PDF » pour obtenir un fichier PDF</small>
</div>

<div class="page">

  <!-- En-tête 3 colonnes -->
  <div class="header-row">
    <div class="header-left">
      <div class="h-univ">UNIVERSITE DENIS SASSOU-N'GUESSO</div>
      <div class="h-dir">VICE-PRESIDENCE</div>
      <div class="h-dir"><?php echo htmlspecialchars(removeAccents(mb_strtoupper($etab, 'UTF-8'))); ?></div>
      <div class="h-serv">SERVICE DE LA SCOLARITE ET DES EXAMENS</div>
    </div>
    <div class="header-center">
      <img src="../images/univ.png" alt="Logo" style="max-height:80px;">
    </div>
    <div class="header-right">
      <div class="h-devise">Rigueur – Excellence – Lumières</div>
    </div>
  </div>

  <!-- Titre -->
  <div class="titre-pv">Procès-Verbal de Délibération</div>
  <div class="sous-titre">Année académique <?php echo htmlspecialchars($annee); ?></div>

  <!-- Informations académiques -->
  <table class="info-grid">
    <tr><td>Parcours :</td><td><?php echo htmlspecialchars($parcours); ?></td></tr>
    <?php if ($spec !== $parcours): ?>
    <tr><td>Spécialité :</td><td><?php echo htmlspecialchars($specialite); ?></td></tr>
    <?php endif; ?>
    <tr><td>Classe :</td><td><?php echo htmlspecialchars($classe); ?></td></tr>
    <tr><td>Semestre :</td><td><?php echo htmlspecialchars($semestre); ?></td></tr>
    <tr><td>Type de session :</td><td><?php echo htmlspecialchars($examen_label); ?></td></tr>
  </table>

  <!-- Texte d'introduction -->
  <?php if ($type_liste === 'valides'): ?>
  <p class="intro-text">
    Ont validé le <strong><?php echo htmlspecialchars(ucfirst(strtolower($semestre))); ?></strong>
    à la <strong><?php echo htmlspecialchars(ucfirst(strtolower($examen_label))); ?></strong>,
    et par ordre de mérite, les étudiants dont les noms et prénoms suivent :
  </p>
  <?php else: ?>
  <p class="intro-text">
    Sont déclarés <strong>ajournés</strong> au <strong><?php echo htmlspecialchars(ucfirst(strtolower($semestre))); ?></strong>
    à la <strong><?php echo htmlspecialchars(ucfirst(strtolower($examen_label))); ?></strong>,
    et par ordre alphabétique, les étudiants dont les noms et prénoms suivent :
  </p>
  <?php endif; ?>

  <!-- Tableau -->
  <table class="table-admis">
    <thead>
      <tr>
        <th>N°</th>
        <th>Nom(s)</th>
        <th>Prénom(s)</th>
        <th>Date et lieu de naissance</th>
        <th>Bac</th>
        <th>Genre</th>
        <th>Moyenne</th>
        <th>Mention</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($admis)): ?>
      <tr>
        <td colspan="8" style="text-align:center;padding:12px;">
          Aucun étudiant <?php echo ($type_liste === 'valides') ? 'admis' : 'ajourné'; ?> par le jury
        </td>
      </tr>
      <?php else: foreach ($admis as $et): ?>
      <tr>
        <td class="col-rang"><?php echo $et['rang']; ?></td>
        <td class="col-nom"><?php echo htmlspecialchars($et['nom']); ?></td>
        <td class="col-prenom"><?php echo htmlspecialchars($et['prenom']); ?></td>
        <td class="col-dn">
          <?php
            $date = !empty($et['dn']) ? date('d-m-Y', strtotime($et['dn'])) : '';
            echo htmlspecialchars($date) . ' à ' . htmlspecialchars($et['ln']);
          ?>
        </td>
        <td><?php echo htmlspecialchars($et['bac']); ?></td>
        <td class="col-sexe"><?php echo htmlspecialchars($et['sexe']); ?></td>
        <td class="col-moy"><?php echo htmlspecialchars($et['moyenne']); ?></td>
        <td class="col-mention"><?php echo htmlspecialchars($et['mention']); ?></td>
      </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>

  <!-- Clôture + signatures -->
  <div class="bas-page-wrapper">

    <p class="cloture">
      <?php if ($type_liste === 'valides'): ?>
      Arrêtée la présente liste à <strong><?php echo $nb_str . ' (' . $nb_admis . ') validés'; ?></strong>.
      <?php else: ?>
      Arrêtée la présente liste à <strong><?php echo $nb_str . ' (' . $nb_admis . ') ajourné(s)'; ?></strong>.
      <?php endif; ?>
    </p>

    <div class="bas-page">

      <!-- Gauche : Rapporteur + membres -->
      <div class="bloc-membres">
        <div class="sig-title">Le Rapporteur</div>
        <div class="sig-name" style="margin-bottom:20px;"><?php echo htmlspecialchars($rapporteur); ?></div>
        <div class="membres-title" style="margin-top:16px;">Les membres :</div>
        <ol class="membres-list">
          <?php foreach ($membres as $m): ?>
          <li><?php echo htmlspecialchars($m); ?></li>
          <?php endforeach; ?>
        </ol>
      </div>

      <!-- Droite : date + Président -->
      <div class="bloc-signatures">
        <p style="text-align:right; font-size:11pt; margin-bottom:40px;">
          Fait à <strong><?php echo htmlspecialchars($ville); ?></strong>, le <strong><?php echo htmlspecialchars($date_pv); ?></strong>
        </p>
        <div class="sig-title">Le Président du Jury</div>
        <div class="sig-name"><?php echo htmlspecialchars($president); ?></div>
      </div>

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
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>PV Délibération – <?php echo htmlspecialchars($_SESSION['univ'] ?? ''); ?></title>
    <link rel="icon" type="image/png" sizes="16x16" href="../administrateur/<?php echo $_SESSION['logo_univ'] ?? ''; ?>">
    <link rel="stylesheet" href="../vendor/bootstrap-select/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/skin.css">
    <link rel="stylesheet" href="../vendor/select2/css/select2.min.css">
    <style>
        .section-label {
            font-weight: 700; color: #333; font-size: 13px;
            text-transform: uppercase; letter-spacing: .6px;
            margin-bottom: 12px; border-left: 4px solid #c0392b; padding-left: 8px;
        }
        .jury-box {
            background: #f8f9fa; border: 1px solid #dee2e6;
            border-radius: 6px; padding: 18px 20px; margin-bottom: 18px;
        }
        label .req { color: #dc3545; }
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
                        <h3><i class="fa fa-print mr-2 text-danger"></i>Imprimer le PV de Délibération</h3>
                    </div>
                </div>
                <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="../scolarite/">Scolarité</a></li>
                        <li class="breadcrumb-item"><a href="pvd">PV Délibération</a></li>
                        <li class="breadcrumb-item active">Imprimer</li>
                    </ol>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-xl-9 col-lg-11">
                    <div class="card shadow-sm">
                        <div class="card-header" style="background:#c0392b; color:#fff;">
                            <h4 class="card-title mb-0" style="color:#fff;">
                                <i class="fa fa-file-text-o mr-2"></i>
                                Paramètres du Procès-Verbal de Délibération
                            </h4>
                        </div>
                        <div class="card-body">

                            <form method="post" id="formPV">

                                <div class="section-label"><i class="fa fa-university"></i>&nbsp; 1. Informations académiques</div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Spécialité <span class="req">*</span></label>
                                        <select class="form-control" name="specialite" required>
                                            <option value="">— Choisir —</option>
                                            <?php
                                            $etab_lib = $_SESSION['lib_etab'] ?? '';
                                            $sql = "SELECT * FROM specialite WHERE etab='" . mysqli_real_escape_string($connexion, $etab_lib) . "'";
                                            $res = $connexion->query($sql);
                                            if ($res) while ($row = $res->fetch_assoc()) {
                                                $lib = str_replace("+", "'", $row['libelle']);
                                                echo '<option value="' . htmlspecialchars($lib) . '">' . htmlspecialchars($lib) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Classe <span class="req">*</span></label>
                                        <select class="form-control" name="classe" required>
                                            <option value="">— Choisir —</option>
                                            <?php
                                            $res = $connexion->query("SELECT * FROM classe ORDER BY libelle");
                                            if ($res) while ($row = $res->fetch_assoc()) {
                                                $lib = str_replace("+", "'", $row['libelle']);
                                                echo '<option value="' . htmlspecialchars($lib) . '">' . htmlspecialchars($lib) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Semestre <span class="req">*</span></label>
                                        <select class="form-control" name="semestre" required>
                                            <option value="">— Choisir —</option>
                                            <?php
                                            $res = $connexion->query("SELECT * FROM semestre");
                                            if ($res) while ($row = $res->fetch_assoc()) {
                                                $lib = str_replace("+", "'", $row['libelle']);
                                                echo '<option value="' . htmlspecialchars($lib) . '">' . htmlspecialchars($lib) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Type d'examen <span class="req">*</span></label>
                                        <select class="form-control" name="examen" required>
                                            <option value="">— Choisir —</option>
                                            <option value="ordinaire">Session Ordinaire</option>
                                            <option value="rattrapage">Session de Rattrapage</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Liste à imprimer <span class="req">*</span></label>
                                        <select class="form-control" name="type_liste" required>
                                            <option value="valides">✅ Admis / Validés</option>
                                            <option value="ajournes">❌ Ajournés (incl. Note Éliminatoire)</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Année académique <span class="req">*</span></label>
                                        <select class="form-control" name="annee" required>
                                            <option value="">— Choisir —</option>
                                            <?php
                                            $res = $connexion->query("SELECT * FROM annee ORDER BY libelle DESC");
                                            if ($res) while ($row = $res->fetch_assoc()) {
                                                $lib = str_replace("+", "'", $row['libelle']);
                                                echo '<option value="' . htmlspecialchars($lib) . '">' . htmlspecialchars($lib) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <hr>

                                <div class="section-label"><i class="fa fa-users"></i>&nbsp; 2. Composition du Jury</div>
                                <div class="jury-box">
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label><strong>Président du Jury</strong> <span class="req">*</span></label>
                                            <input type="text" class="form-control" name="president" required
                                                   placeholder="Ex : Pr Florent BOUDZOUMOU"
                                                   value="<?php echo htmlspecialchars(getNomUtilisateurParId($connexion, $_SESSION['id_user'])); ?>">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label><strong>Rapporteur</strong> <span class="req">*</span></label>
                                            <input type="text" class="form-control" name="rapporteur" required
                                                   placeholder="Ex : Dr Brunel ANGOUNDA">
                                        </div>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label>
                                            <strong>Membres du Jury</strong>
                                            <small class="text-muted font-weight-normal">(un nom par ligne)</small>
                                        </label>
                                        <textarea class="form-control" name="membres" rows="4"
                                                  placeholder="Dr Claude N'DEMBE BIBALOU&#10;Dr Nerly GAMPIO GUEYE&#10;Dr Stanislas Kevin MBEKE"></textarea>
                                    </div>
                                </div>

                                <hr>

                                <div class="section-label"><i class="fa fa-calendar"></i>&nbsp; 3. Informations du document</div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Ville</label>
                                        <input type="text" class="form-control" name="ville"
                                               placeholder="Brazzaville" value="Brazzaville">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Date du PV</label>
                                        <input type="text" class="form-control" name="date_pv"
                                               placeholder="jj/mm/aaaa" value="<?php echo date('d/m/Y'); ?>">
                                    </div>
                                </div>

                                <hr>

                                <div class="text-center">
                                    <button type="button" class="btn btn-danger btn-lg px-5" id="btnImprimer">
                                        <i class="fa fa-print mr-2"></i>Aperçu &amp; Impression
                                    </button>
                                    <a href="pvd" class="btn btn-secondary btn-lg ml-2 px-4">
                                        <i class="fa fa-arrow-left mr-1"></i>Retour
                                    </a>
                                </div>

                                <p class="text-center text-muted mt-3" style="font-size:12px;">
                                    <i class="fa fa-info-circle"></i>
                                    Le document s'ouvrira dans un nouvel onglet. Utilisez <kbd>Ctrl+P</kbd> → <strong>"Enregistrer en PDF"</strong> pour obtenir un fichier PDF.
                                </p>

                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Section Import PV signés ── -->
            <div class="row justify-content-center mt-4">
                <div class="col-xl-9 col-lg-11">
                    <div class="card shadow-sm">
                        <div class="card-header" style="background:#2c3e50; color:#fff;">
                            <h4 class="card-title mb-0" style="color:#fff;">
                                <i class="fa fa-upload mr-2"></i>Importer un PV signé
                            </h4>
                        </div>
                        <div class="card-body">

                            <?php if ($upload_success): ?>
                            <div class="alert alert-success"><i class="fa fa-check mr-2"></i><?php echo htmlspecialchars($upload_success); ?></div>
                            <?php endif; ?>
                            <?php if ($upload_error): ?>
                            <div class="alert alert-danger"><i class="fa fa-times mr-2"></i><?php echo htmlspecialchars($upload_error); ?></div>
                            <?php endif; ?>

                            <form method="post" enctype="multipart/form-data" id="formUpload">
                                <div class="row">
                                    <div class="form-group col-md-3">
                                        <label>Classe <span style="color:#dc3545">*</span></label>
                                        <select class="form-control" name="u_classe" required>
                                            <option value="">— Choisir —</option>
                                            <?php
                                            $res2 = $connexion->query("SELECT libelle FROM classe ORDER BY libelle");
                                            if ($res2) while ($r2 = $res2->fetch_assoc()) {
                                                $lib2 = htmlspecialchars(str_replace("+", "'", $r2['libelle']));
                                                echo "<option value=\"$lib2\">$lib2</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Semestre <span style="color:#dc3545">*</span></label>
                                        <select class="form-control" name="u_semestre" required>
                                            <option value="">— Choisir —</option>
                                            <?php
                                            $res3 = $connexion->query("SELECT libelle FROM semestre");
                                            if ($res3) while ($r3 = $res3->fetch_assoc()) {
                                                $lib3 = htmlspecialchars(str_replace("+", "'", $r3['libelle']));
                                                echo "<option value=\"$lib3\">$lib3</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Type d'examen <span style="color:#dc3545">*</span></label>
                                        <select class="form-control" name="u_examen" required>
                                            <option value="">— Choisir —</option>
                                            <option value="ordinaire">Session Ordinaire</option>
                                            <option value="rattrapage">Session de Rattrapage</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Année académique <span style="color:#dc3545">*</span></label>
                                        <select class="form-control" name="u_annee" required>
                                            <option value="">— Choisir —</option>
                                            <?php
                                            $res4 = $connexion->query("SELECT libelle FROM annee ORDER BY libelle DESC");
                                            if ($res4) while ($r4 = $res4->fetch_assoc()) {
                                                $lib4 = htmlspecialchars(str_replace("+", "'", $r4['libelle']));
                                                echo "<option value=\"$lib4\">$lib4</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-12">
                                        <label>Fichier PV signé <span style="color:#dc3545">*</span> <small class="text-muted">(PDF, JPG ou PNG)</small></label>
                                        <input type="file" class="form-control-file" name="pv_file" accept=".pdf,.jpg,.jpeg,.png" required>
                                    </div>
                                </div>
                                <button type="submit" name="upload_pv" class="btn btn-dark">
                                    <i class="fa fa-upload mr-2"></i>Importer
                                </button>
                            </form>

                            <?php
                            $pv_dir = __DIR__ . '/pv_signes/';
                            $etab_san = str_replace([' ','/','\\'], '_', preg_replace('/[^\w\s\-]/', '', $_SESSION['etablissement'] ?? ''));
                            $files = is_dir($pv_dir)
                                ? glob($pv_dir . $etab_san . '__*.{pdf,jpg,jpeg,png}', GLOB_BRACE)
                                : [];
                            if (!empty($files)):
                            ?>
                            <hr>
                            <h6 class="mt-3 mb-3"><i class="fa fa-folder-open mr-2"></i>PV signés importés</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-hover">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Classe</th><th>Semestre</th><th>Session</th>
                                            <th>Année</th><th>Date import</th><th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach (array_reverse($files) as $f):
                                        $parts = explode('__', pathinfo($f, PATHINFO_FILENAME));
                                        $fc  = htmlspecialchars(str_replace('_', ' ', $parts[1] ?? ''));
                                        $fs  = htmlspecialchars(str_replace('_', ' ', $parts[2] ?? ''));
                                        $fe  = ($parts[3] ?? '') === 'ordinaire' ? 'Session Ordinaire' : 'Session de Rattrapage';
                                        $fa  = htmlspecialchars(str_replace('_', ' ', $parts[4] ?? ''));
                                        $fd  = date('d/m/Y H:i', filemtime($f));
                                        $url = htmlspecialchars('pv_signes/' . basename($f));
                                    ?>
                                    <tr>
                                        <td><?php echo $fc; ?></td>
                                        <td><?php echo $fs; ?></td>
                                        <td><?php echo $fe; ?></td>
                                        <td><?php echo $fa; ?></td>
                                        <td><?php echo $fd; ?></td>
                                        <td>
                                            <a href="<?php echo $url; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fa fa-eye mr-1"></i>Ouvrir
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="footer">
        <div class="copyright">
            <p>Copyright &copy; Designed &amp; Développé par <a href="https://www.cet-up.com" target="_blank">CETUP</a> 2023</p>
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
    $('#btnImprimer').on('click', function (e) {
        e.preventDefault();

        var ok = true;
        $('#formPV [required]').each(function () {
            if (!$(this).val()) { ok = false; $(this).addClass('is-invalid'); }
            else $(this).removeClass('is-invalid');
        });
        if (!ok) { alert('Veuillez remplir tous les champs obligatoires (*)'); return; }

        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i>Ouverture...');

        var $form = $('#formPV');
        var originalTarget = $form.attr('target');
        $form.attr('target', '_blank');
        $form.append('<input type="hidden" name="imprimer" value="1">');
        $form[0].submit();
        $form.attr('target', originalTarget || '');
        $form.find('input[name="imprimer"]').remove();

        setTimeout(function () {
            btn.prop('disabled', false).html('<i class="fa fa-print mr-2"></i>Aperçu &amp; Impression');
        }, 2000);
    });
});
</script>
</body>
</html>
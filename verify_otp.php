<?php
include('php/connexion.php');
include('php/lib.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pas de données en attente → retour login
if (!isset($_SESSION['pending_user']) || !isset($_SESSION['otp'])) {
    header("Location: login");
    exit;
}

$error       = '';
$success     = '';
$userIP      = $_SERVER['REMOTE_ADDR'];
$emailMasque = maskEmail((string)($_SESSION['pending_user']['email'] ?? ''));

// ── Traitement formulaire ─────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['resend'])) {
        // Renvoyer un code
        $otp = sprintf('%06d', random_int(0, 999999));
        $_SESSION['otp']          = password_hash($otp, PASSWORD_DEFAULT);
        $_SESSION['otp_expires']  = time() + 300;
        $_SESSION['otp_attempts'] = 0;
        $u = $_SESSION['pending_user'];
        envoyerOTP((string)$u['email'], (string)$u['nom'], $otp);
        $success = "Un nouveau code a été envoyé à $emailMasque.";

    } else {
        $saisie = trim(implode('', $_POST['otp'] ?? []));

        if ((int)$_SESSION['otp_attempts'] >= 5) {
            nettoyerOTP();
            header("Location: login?erreur=Trop de tentatives. Reconnectez-vous");
            exit;
        }
        if (time() > (int)$_SESSION['otp_expires']) {
            nettoyerOTP();
            header("Location: login?erreur=Code expiré. Reconnectez-vous");
            exit;
        }

        $_SESSION['otp_attempts']++;

        if (!password_verify($saisie, (string)$_SESSION['otp'])) {
            $restants = 5 - (int)$_SESSION['otp_attempts'];
            $error    = "Code incorrect. Il vous reste $restants tentative(s).";
        } else {
            // ✅ Code valide → récupérer les données et lancer TON code original
            $p = $_SESSION['pending_user'];
            nettoyerOTP();
            demarrerSession($p, $connexion, $userIP);
        }
    }
}

// ═════════════════════════════════════════════════════════════════════════════
// TON CODE ORIGINAL — copié tel quel, rien n'a changé
// ═════════════════════════════════════════════════════════════════════════════
function demarrerSession(array $p, $connexion, string $userIP): void
{
    // On remet les variables dans les noms attendus par ton code
    $id              = $p['id'];
    $nom             = $p['nom'];
    $userName        = $p['login'];
    $userRole        = $p['role'];
    $mdp             = $p['mdp'];
    $etablissement   = $p['etablissement'];
    $univ            = $p['univ'];
    $img             = $p['img'];
    $classe          = $p['classe'];
    $matricule       = $p['matricule'];
    $ecue            = $p['ecue'];
    $parcours        = $p['parcours'];
    $annee           = $p['annee'];
    $semestre        = $p['semestre'];
    $examen          = $p['examen'];
    $code_enseignant = $p['code_enseignant'];

    // ─────────────────────────────────────────────────────────────────────────
    // CI-DESSOUS : TON CODE ORIGINAL DE routeur.php, COPIE EXACTE
    // ─────────────────────────────────────────────────────────────────────────

    if ($userRole === 'administrateur') {
        $_SESSION['id']       = session_id();
        $_SESSION['nom_user'] = $nom;
        $_SESSION['id_user']  = $id;
        $_SESSION['login']    = $userName;
        $_SESSION['role']     = $userRole;
        $_SESSION['mdp']      = $mdp;
        logUserAction($connexion, $id, "connexion", date("Y-m-d H:i:s"), $userIP, null);
        header("Location: administrateur");

    } else if ($userRole === 'candidat') {
        $_SESSION['id']        = session_id();
        $_SESSION['id_user']   = $id;
        $_SESSION['login']     = $userName;
        $_SESSION['role']      = $userRole;
        $_SESSION['mdp']       = $mdp;
        $_SESSION['logo_univ'] = getlogo($id, $connexion);
        $_SESSION['email']     = $userName;
        $_SESSION['classe']    = $classe;
        $_SESSION['matricule'] = $matricule;
        if (idCandidatExiste1($id, $connexion)) {
            $_SESSION['statut']    = getStatutCandidat($id, $connexion);
            $_SESSION['code']      = getCodeCandidat($id, $connexion);
            $_SESSION['img']       = getImgCandidat($id, $connexion);
            $_SESSION['annee']     = getAnneeCandidat($id, $connexion);
        }
        logUserAction($connexion, $id, "connexion candidat", date("Y-m-d H:i:s"), $userIP, "valeur :  $id");
        header("Location: candidat");

    } else if ($userRole === "scolarité") {
        $_SESSION['id']        = session_id();
        $_SESSION['nom_user']  = $nom;
        $_SESSION['id_user']   = $id;
        $_SESSION['login']     = $userName;
        $_SESSION['role']      = $userRole;
        $_SESSION['mdp']       = $mdp;
        $_SESSION['img']       = $img;
        $_SESSION['etablissement'] = $etablissement;
        $_SESSION['lib_etab']  = getLibelleEtablissement($etablissement, $connexion);
        $_SESSION['univ']      = $univ;
        $_SESSION['statut']    = getstatut($id, $connexion);
        $_SESSION['logo_univ'] = getlogo($id, $connexion);
        $_SESSION['img']       = getimguser($id, $connexion);
        logUserAction($connexion, $id, "connexion", date("Y-m-d H:i:s"), $userIP, null);
        header("Location: scolarite");

    } else if ($userRole === "caisse") {
        $_SESSION['id']        = session_id();
        $_SESSION['nom_user']  = $nom;
        $_SESSION['id_user']   = $id;
        $_SESSION['login']     = $userName;
        $_SESSION['role']      = $userRole;
        $_SESSION['mdp']       = $mdp;
        $_SESSION['img']       = $img;
        $_SESSION['etablissement'] = $etablissement;
        $_SESSION['lib_etab']  = getLibelleEtablissement($etablissement, $connexion);
        $_SESSION['univ']      = $univ;
        $_SESSION['statut']    = getstatut($id, $connexion);
        $_SESSION['logo_univ'] = getlogo($id, $connexion);
        $_SESSION['img']       = getimguser($id, $connexion);
        logUserAction($connexion, $id, "connexion", date("Y-m-d H:i:s"), $userIP, null);
        header("Location: caisse");

    } else if ($userRole === "cours") {
        $_SESSION['id']        = session_id();
        $_SESSION['nom_user']  = $nom;
        $_SESSION['id_user']   = $id;
        $_SESSION['login']     = $userName;
        $_SESSION['role']      = $userRole;
        $_SESSION['mdp']       = $mdp;
        $_SESSION['img']       = $img;
        $_SESSION['etablissement'] = $etablissement;
        $_SESSION['lib_etab']  = getLibelleEtablissement($etablissement, $connexion);
        $_SESSION['univ']      = $univ;
        $_SESSION['statut']    = getstatut($id, $connexion);
        $_SESSION['logo_univ'] = getlogo($id, $connexion);
        $_SESSION['img']       = getimguser($id, $connexion);
        logUserAction($connexion, $id, "connexion", date("Y-m-d H:i:s"), $userIP, null);
        header("Location: cours");

    } else if ($userRole === "gesnote") {
        $_SESSION['id']        = session_id();
        $_SESSION['nom_user']  = $nom;
        $_SESSION['id_user']   = $id;
        $_SESSION['login']     = $userName;
        $_SESSION['role']      = $userRole;
        $_SESSION['mdp']       = $mdp;
        $_SESSION['img']       = $img;
        $_SESSION['etablissement'] = $etablissement;
        $_SESSION['lib_etab']  = getLibelleEtablissement($etablissement, $connexion);
        $_SESSION['univ']      = $univ;
        if ($classe !== "" && $ecue !== "") {
            $_SESSION['classe'] = $classe;
            $_SESSION['ecue']   = $ecue;
        }
        $_SESSION['statut']    = getstatut($id, $connexion);
        $_SESSION['logo_univ'] = getlogo($id, $connexion);
        $_SESSION['img']       = getimguser($id, $connexion);
        logUserAction($connexion, $id, "connexion", date("Y-m-d H:i:s"), $userIP, null);
        header("Location: gesnote");

    } else if ($userRole === 'Administrateur' && $univ != null) {
        $_SESSION['id']        = session_id();
        $_SESSION['id_user']   = $id;
        $_SESSION['login']     = $userName;
        $_SESSION['role']      = $userRole;
        $_SESSION['mdp']       = $mdp;
        $_SESSION['univ']      = $univ;
        $_SESSION['nom_user']  = $nom;
        $_SESSION['statut']    = getstatut($id, $connexion);
        $_SESSION['logo_univ'] = getlogo($id, $connexion);
        $_SESSION['img']       = getimguser($id, $connexion);
        logUserAction($connexion, $id, "connexion utilisateur", date("Y-m-d H:i:s"), $userIP, "valeur :  $id");
        header("Location: admin");

    } else if ($userRole === 'soutenance') {
        $_SESSION['id']        = session_id();
        $_SESSION['nom_user']  = $nom;
        $_SESSION['id_user']   = $id;
        $_SESSION['login']     = $userName;
        $_SESSION['role']      = $userRole;
        $_SESSION['mdp']       = $mdp;
        $_SESSION['img']       = $img;
        $_SESSION['etablissement'] = $etablissement;
        $_SESSION['lib_etab']  = getLibelleEtablissement($etablissement, $connexion);
        $_SESSION['univ']      = $univ;
        $_SESSION['statut']    = getstatut($id, $connexion);
        $_SESSION['logo_univ'] = getlogo($id, $connexion);
        $_SESSION['img']       = getimguser($id, $connexion);
        logUserAction($connexion, $id, "connexion", date("Y-m-d H:i:s"), $userIP, null);
        header("Location: soutenance");

    } else if ($userRole === 'inscription') {
        $_SESSION['id']        = session_id();
        $_SESSION['nom_user']  = $nom;
        $_SESSION['id_user']   = $id;
        $_SESSION['login']     = $userName;
        $_SESSION['role']      = $userRole;
        $_SESSION['mdp']       = $mdp;
        $_SESSION['img']       = $img;
        $_SESSION['etablissement'] = $etablissement;
        $_SESSION['lib_etab']  = getLibelleEtablissement($etablissement, $connexion);
        $_SESSION['univ']      = $univ;
        $_SESSION['statut']    = getstatut($id, $connexion);
        $_SESSION['logo_univ'] = getlogo($id, $connexion);
        $_SESSION['img']       = getimguser($id, $connexion);
        logUserAction($connexion, $id, "connexion", date("Y-m-d H:i:s"), $userIP, null);
        header("Location: inscription");

    } else if ($userRole === 'pvd') {
        $_SESSION['id']        = session_id();
        $_SESSION['nom_user']  = $nom;
        $_SESSION['id_user']   = $id;
        $_SESSION['login']     = $userName;
        $_SESSION['role']      = $userRole;
        $_SESSION['img']       = $img;
        $_SESSION['etablissement'] = $etablissement;
        $_SESSION['lib_etab']  = getLibelleEtablissement($etablissement, $connexion);
        $_SESSION['univ']      = $univ;
        $_SESSION['parcours']  = $parcours;
        $_SESSION['semestre']  = $semestre;
        $_SESSION['annee']     = $annee;
        $_SESSION['examen']    = $examen;
        $_SESSION['statut']    = getstatut($id, $connexion);
        $_SESSION['logo_univ'] = getlogo($id, $connexion);
        $_SESSION['img']       = getimguser($id, $connexion);
        logUserAction($connexion, $id, "connexion", date("Y-m-d H:i:s"), $userIP, null);
        header("Location: pvd");

    } else if ($userRole === 'daarhspe') {
        $_SESSION['id']        = session_id();
        $_SESSION['nom_user']  = $nom;
        $_SESSION['id_user']   = $id;
        $_SESSION['login']     = $userName;
        $_SESSION['role']      = $userRole;
        $_SESSION['mdp']       = $mdp;
        $_SESSION['img']       = $img;
        $_SESSION['etablissement'] = $etablissement;
        $_SESSION['statut']    = getstatut($id, $connexion);
        $_SESSION['logo_univ'] = getlogo($id, $connexion);
        $_SESSION['img']       = getimguser($id, $connexion);
        logUserAction($connexion, $id, "connexion", date("Y-m-d H:i:s"), $userIP, null);
        header("Location: DAARHSPE");

    } else if ($userRole === 'anonymat') {
        $_SESSION['id']        = session_id();
        $_SESSION['nom_user']  = $nom;
        $_SESSION['id_user']   = $id;
        $_SESSION['login']     = $userName;
        $_SESSION['role']      = $userRole;
        $_SESSION['mdp']       = $mdp;
        $_SESSION['img']       = $img;
        $_SESSION['etablissement'] = $etablissement;
        $_SESSION['lib_etab']  = getLibelleEtablissement($etablissement, $connexion);
        $_SESSION['univ']      = $univ;
        if ($classe !== "" && $ecue !== "") {
            $_SESSION['classe'] = $classe;
            $_SESSION['ecue']   = $ecue;
        }
        $_SESSION['statut']    = getstatut($id, $connexion);
        $_SESSION['logo_univ'] = getlogo($id, $connexion);
        $_SESSION['img']       = getimguser($id, $connexion);
        logUserAction($connexion, $id, "connexion", date("Y-m-d H:i:s"), $userIP, null);
        header("Location: anonymat");

    } else if ($userRole === 'doyen') {
        $_SESSION['id']        = session_id();
        $_SESSION['nom_user']  = $nom;
        $_SESSION['id_user']   = $id;
        $_SESSION['login']     = $userName;
        $_SESSION['role']      = $userRole;
        $_SESSION['img']       = $img;
        $_SESSION['etablissement'] = $etablissement;
        $_SESSION['lib_etab']  = getLibelleEtablissement($etablissement, $connexion);
        $_SESSION['univ']      = $univ;
        $_SESSION['annee']     = $annee;
        $_SESSION['statut']    = getstatut($id, $connexion);
        $_SESSION['logo_univ'] = getlogo($id, $connexion);
        $_SESSION['img']       = getimguser($id, $connexion);
        logUserAction($connexion, $id, "connexion", date("Y-m-d H:i:s"), $userIP, null);
        header("Location: doyen");

    } else if ($userRole === 'enseignant') {
        $_SESSION['id']              = session_id();
        $_SESSION['nom_user']        = $nom;
        $_SESSION['id_user']         = $id;
        $_SESSION['login']           = $userName;
        $_SESSION['role']            = $userRole;
        $_SESSION['img']             = $img;
        $_SESSION['etablissement']   = $etablissement;
        $_SESSION['lib_etab']        = getLibelleEtablissement($etablissement, $connexion);
        $_SESSION['univ']            = $univ;
        $_SESSION['code_enseignant'] = $code_enseignant;
        $_SESSION['annee']           = $annee;
        $_SESSION['statut']          = getstatut($id, $connexion);
        $_SESSION['logo_univ']       = getlogo($id, $connexion);
        $_SESSION['img']             = getimguser($id, $connexion);
        logUserAction($connexion, $id, "connexion", date("Y-m-d H:i:s"), $userIP, null);
        header("Location: professeur");

    } else {
        header("Location: login?erreur=Utilisateur inconnu. Essayez à nouveau");
    }

    exit;
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function nettoyerOTP(): void {
    unset($_SESSION['pending_user'], $_SESSION['otp'],
          $_SESSION['otp_expires'], $_SESSION['otp_attempts'],
          $_SESSION['otp_need_email']);
}

function maskEmail(string $email): string {
    if (!str_contains($email, '@')) return '***';
    [$user, $domain] = explode('@', $email, 2);
    return substr($user, 0, 2) . str_repeat('*', max(2, strlen($user) - 2)) . '@' . $domain;
}


function envoyerOTP(string $to, string $nom, string $otp): bool
{
    $body = "
    <html><body style='font-family:Arial,sans-serif;background:#f9f9f9;padding:20px;'>
      <div style='max-width:480px;margin:auto;background:#fff;border-radius:10px;
                  padding:30px;box-shadow:0 2px 8px rgba(0,0,0,.1);'>
        <h2 style='color:#28a745;margin-top:0;'>Université Denis Sassou-N'Guesso</h2>
        <p>Bonjour <strong>" . htmlspecialchars($nom) . "</strong>,</p>
        <p>Votre code de connexion :</p>
        <div style='font-size:40px;font-weight:bold;letter-spacing:10px;
                    background:#f0fff4;color:#155724;padding:20px;
                    text-align:center;border-radius:8px;margin:20px 0;
                    border:2px solid #28a745;'>$otp</div>
        <p>Valable <strong>5 minutes</strong>. Ne le partagez jamais.</p>
      </div>
    </body></html>";
 
    // Connexion SSL Hostinger (port 465)
    $socket = @fsockopen("ssl://" . MAIL_HOST, MAIL_PORT, $errno, $errstr, 10);
    if (!$socket) {
        error_log("SMTP erreur connexion : $errstr ($errno)");
        return false;
    }
 
    // Lire réponse SMTP
    $lire = function() use ($socket): string {
        $data = '';
        while ($line = fgets($socket, 515)) {
            $data .= $line;
            if ($line[3] === ' ') break;
        }
        return $data;
    };
 
    // Envoyer commande et lire réponse
    $cmd = function(string $c) use ($socket, $lire): string {
        fputs($socket, $c . "\r\n");
        return $lire();
    };
 
    $lire(); // Bienvenue serveur
    $cmd("EHLO " . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    $cmd("AUTH LOGIN");
    $cmd(base64_encode(MAIL_USERNAME));
    $rep = $cmd(base64_encode(MAIL_PASSWORD));
 
    if (strpos($rep, '235') === false) {
        error_log("SMTP auth échouée : $rep");
        fclose($socket);
        return false;
    }
 
    $cmd("MAIL FROM: <" . MAIL_FROM . ">");
    $cmd("RCPT TO: <$to>");
    $cmd("DATA");
 
    $sujet   = "=?UTF-8?B?" . base64_encode("Votre code de vérification — UDSN") . "?=";
    $expnom  = "=?UTF-8?B?" . base64_encode(MAIL_FROM_NAME) . "?=";
 
    $data  = "From: $expnom <" . MAIL_FROM . ">\r\n";
    $data .= "To: <$to>\r\n";
    $data .= "Subject: $sujet\r\n";
    $data .= "MIME-Version: 1.0\r\n";
    $data .= "Content-Type: text/html; charset=UTF-8\r\n";
    $data .= "Content-Transfer-Encoding: base64\r\n";
    $data .= "\r\n";
    $data .= chunk_split(base64_encode($body));
    $data .= "\r\n.\r\n";
 
    $rep = $cmd($data);
    $cmd("QUIT");
    fclose($socket);
 
    return strpos($rep, '250') !== false;
}

$attempts = (int)($_SESSION['otp_attempts'] ?? 0);
$pct      = max(0, 100 - ($attempts * 20));
$barColor = $pct > 60 ? '#28a745' : ($pct > 20 ? '#ffc107' : '#dc3545');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>UDSN — Vérification en deux étapes</title>
<link rel="shortcut icon" href="images/univ.png">
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/plugins/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">
<link rel="stylesheet" href="assets/css/style.css">
<style>
  .otp-inputs { display:flex; gap:10px; justify-content:center; margin:20px 0; }
  .otp-inputs input {
    width:52px; height:60px; font-size:28px; font-weight:700;
    text-align:center; border:2px solid #ced4da; border-radius:10px;
    transition:border-color .2s; background:#fff;
  }
  .otp-inputs input:focus { border-color:#28a745; outline:none; box-shadow:0 0 0 3px rgba(40,167,69,.2); }
  .otp-inputs input.filled { border-color:#28a745; background:#f0fff4; }
  .timer { font-size:13px; color:#888; text-align:center; margin-top:6px; }
  .shield-icon { font-size:52px; color:#28a745; text-align:center; display:block; margin-bottom:8px; }
  .attempts-bar { height:6px; border-radius:3px; background:#e9ecef; margin:8px 0; }
  .attempts-fill { height:100%; border-radius:3px; transition:width .4s,background .4s; }
</style>
</head>
<body>
<div class="main-wrapper login-body">
<div class="login-wrapper">
<div class="container">
<div class="loginbox">
  <div class="login-left">
    <img class="img-fluid" src="images/univ.png" alt="Logo">
  </div>
  <div class="login-right">
    <div class="login-right-wrap">
      <h1 class="alert alert-success text-center"
          style="font-family:'Times New Roman',serif;font-size:14px;padding:8px;">
        UNIVERSITE DENIS SASSOU-N'GUESSO
      </h1>
      <i class="fas fa-shield-alt shield-icon"></i>
      <h2 class="text-center mb-1">Vérification en deux étapes</h2>
      <p class="text-center text-muted mb-2" style="font-size:14px;">
        Code envoyé à <strong><?= htmlspecialchars($emailMasque) ?></strong>
      </p>

      <div class="attempts-bar">
        <div class="attempts-fill" style="width:<?= $pct ?>%;background:<?= $barColor ?>;"></div>
      </div>
      <p class="text-center mb-2" style="font-size:12px;color:#aaa;">
        <?= 5 - $attempts ?> tentative(s) restante(s)
      </p>

      <?php if ($error): ?>
        <div class="alert alert-danger text-center py-2">
          <i class="fas fa-times-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success text-center py-2">
          <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>

      <form method="post" id="otpForm">
        <div class="otp-inputs">
          <?php for ($i = 0; $i < 6; $i++): ?>
            <input type="text" name="otp[<?= $i ?>]" maxlength="1"
                   inputmode="numeric" pattern="[0-9]" class="otp-digit" autocomplete="off">
          <?php endfor; ?>
        </div>
        <div class="timer mb-3">
          Expire dans : <span id="countdown" style="font-weight:700;color:#dc3545;">05:00</span>
        </div>
        <div class="form-group">
          <button class="btn btn-success btn-block" type="submit" id="btnVerif" disabled>
            <i class="fas fa-check-circle"></i> Vérifier le code
          </button>
        </div>
      </form>

      <form method="post" class="text-center">
        <button type="submit" name="resend" class="btn btn-link text-muted"
                id="btnResend" disabled style="font-size:13px;">
          <i class="fas fa-redo"></i> Renvoyer (<span id="resendTimer">30</span>s)
        </button>
      </form>

      <div class="text-center mt-2">
        <a href="login" class="text-muted" style="font-size:13px;">
          <i class="fas fa-arrow-left"></i> Retour à la connexion
        </a>
      </div>
    </div>
  </div>
</div>
</div>
</div>
</div>
<script src="assets/js/jquery-3.6.0.min.js"></script>
<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
const digits   = document.querySelectorAll('.otp-digit');
const btnVerif = document.getElementById('btnVerif');

digits.forEach((input, idx) => {
  input.addEventListener('input', () => {
    input.value = input.value.replace(/\D/g,'').slice(-1);
    input.classList.toggle('filled', !!input.value);
    if (input.value && idx < digits.length - 1) digits[idx+1].focus();
    checkFilled();
  });
  input.addEventListener('keydown', e => {
    if (e.key==='Backspace' && !input.value && idx>0) {
      digits[idx-1].focus(); digits[idx-1].value='';
      digits[idx-1].classList.remove('filled'); checkFilled();
    }
  });
  input.addEventListener('paste', e => {
    e.preventDefault();
    const p = (e.clipboardData||window.clipboardData).getData('text').replace(/\D/g,'');
    [...p.slice(0,6)].forEach((c,i) => {
      if (digits[idx+i]) { digits[idx+i].value=c; digits[idx+i].classList.add('filled'); }
    });
    digits[Math.min(idx+p.length, digits.length-1)].focus();
    checkFilled();
  });
});

function checkFilled() {
  const all = [...digits].every(d => d.value !== '');
  btnVerif.disabled = !all;
  if (all) document.getElementById('otpForm').submit();
}
digits[0].focus();

let total = 300;
const cdEl = document.getElementById('countdown');
const cdI  = setInterval(() => {
  total--;
  cdEl.textContent = String(Math.floor(total/60)).padStart(2,'0') + ':' + String(total%60).padStart(2,'0');
  if (total <= 0) {
    clearInterval(cdI); btnVerif.disabled = true;
    cdEl.closest('.timer').innerHTML =
      '<span style="color:#dc3545;"><i class="fas fa-exclamation-triangle"></i> Expiré — <a href="login">Reconnectez-vous</a></span>';
  }
}, 1000);

let rs = 30;
const rtEl = document.getElementById('resendTimer'), btnR = document.getElementById('btnResend');
const rsI  = setInterval(() => {
  rs--; rtEl.textContent = rs;
  if (rs <= 0) { clearInterval(rsI); btnR.disabled=false; btnR.innerHTML='<i class="fas fa-redo"></i> Renvoyer le code'; }
}, 1000);
</script>
</body>
</html>
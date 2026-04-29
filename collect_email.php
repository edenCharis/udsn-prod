<?php
include('php/connexion.php');
include('php/lib.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Seuls les utilisateurs venant de routeur.php peuvent accéder ici
if (empty($_SESSION['pending_user']) || empty($_SESSION['otp_need_email'])) {
    header("Location: login");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newEmail     = trim($_POST['email']         ?? '');
    $confirmEmail = trim($_POST['email_confirm'] ?? '');

    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse email invalide.";

    } elseif ($newEmail !== $confirmEmail) {
        $error = "Les deux adresses ne correspondent pas.";

    } else {
        $id = $_SESSION['pending_user']['id'];

        // Vérifier que l'email n'est pas déjà utilisé par quelqu'un d'autre
        $stmt = $connexion->prepare("SELECT id FROM utilisateur WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $newEmail, $id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Cet email est déjà utilisé par un autre compte.";
            $stmt->close();
        } else {
            $stmt->close();

            // Enregistrer l'email en BDD
            $upd = $connexion->prepare("UPDATE utilisateur SET email = ? WHERE id = ?");
            $upd->bind_param("si", $newEmail, $id);
            $upd->execute();
            $upd->close();

            // Mettre à jour pending_user avec le nouvel email
            $_SESSION['pending_user']['email'] = $newEmail;
            unset($_SESSION['otp_need_email']);

            // Générer et envoyer l'OTP
            $otp = sprintf('%06d', random_int(0, 999999));
            $_SESSION['otp']          = password_hash($otp, PASSWORD_DEFAULT);
            $_SESSION['otp_expires']  = time() + 300;
            $_SESSION['otp_attempts'] = 0;

            session_write_close();

            envoyerOTP($newEmail, (string)$_SESSION['pending_user']['nom'], $otp);

            header("Location: verify_otp.php");
            exit;
        }
    }
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

    $socket = @fsockopen("ssl://" . MAIL_HOST, MAIL_PORT, $errno, $errstr, 10);
    if (!$socket) return false;

    $lire = function() use ($socket): string {
        $data = '';
        while ($line = fgets($socket, 515)) {
            $data .= $line;
            if ($line[3] === ' ') break;
        }
        return $data;
    };
    $cmd = function(string $c) use ($socket, $lire): string {
        fputs($socket, $c . "\r\n");
        return $lire();
    };

    $lire();
    $cmd("EHLO " . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    $cmd("AUTH LOGIN");
    $cmd(base64_encode(MAIL_USERNAME));
    $rep = $cmd(base64_encode(MAIL_PASSWORD));

    if (strpos($rep, '235') === false) { fclose($socket); return false; }

    $cmd("MAIL FROM: <" . MAIL_FROM . ">");
    $cmd("RCPT TO: <$to>");
    $cmd("DATA");

    $sujet  = "=?UTF-8?B?" . base64_encode("Votre code de vérification — UDSN") . "?=";
    $expnom = "=?UTF-8?B?" . base64_encode(MAIL_FROM_NAME) . "?=";
    $data   = "From: $expnom <" . MAIL_FROM . ">\r\nTo: <$to>\r\nSubject: $sujet\r\n"
            . "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($body)) . "\r\n.\r\n";

    $rep = $cmd($data);
    $cmd("QUIT");
    fclose($socket);
    return strpos($rep, '250') !== false;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>UDSN — Enregistrer votre email</title>
<link rel="shortcut icon" href="images/univ.png">
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/plugins/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">
<link rel="stylesheet" href="assets/css/style.css">
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

      <i class="fas fa-envelope fa-3x text-success text-center d-block mb-3"></i>
      <h2 class="text-center mb-1">Email requis</h2>
      <p class="text-center text-muted mb-4" style="font-size:14px;">
        Aucun email n'est associé à votre compte.<br>
        Enregistrez-en un pour recevoir votre code de vérification.
      </p>

      <?php if ($error): ?>
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="post">
        <div class="form-group">
          <label>Adresse email <span class="text-danger">*</span></label>
          <input class="form-control" type="email" name="email"
                 required placeholder="exemple@domaine.com">
        </div>
        <div class="form-group">
          <label>Confirmer l'email <span class="text-danger">*</span></label>
          <input class="form-control" type="email" name="email_confirm"
                 required placeholder="exemple@domaine.com">
        </div>
        <div class="form-group">
          <button class="btn btn-success btn-block" type="submit">
            <i class="fas fa-paper-plane"></i> Envoyer le code
          </button>
        </div>
      </form>

      <div class="text-center mt-3">
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
</body>
</html>
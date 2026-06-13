<?php
session_start();
include_once 'config/logo_config.php';
$logoConfig = getLogoConfig();
$logo = $logoConfig->getDefaultLogo();
$favicon = $logoConfig->getDefaultFavicon();
$universite_nom = $logoConfig->getDefaultUniversityName();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
<title>UDSN - Connexion</title>
<link rel="shortcut icon" href="<?php echo htmlspecialchars($favicon); ?>">
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;0,500;0,700;0,900;1,400;1,500;1,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/plugins/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/plugins/feather/feather.css">
<link rel="stylesheet" href="assets/plugins/icons/flags/flags.css">
<link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css">
<link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">
<link rel="stylesheet" href="assets/css/style.css">
<style>
      .custom-font {
            font-family: "Times New Roman", Times, serif;
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .register-link a {
            color: #28a745;
            text-decoration: none;
            font-weight: 500;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
</style>
</head>
<body>
<div class="main-wrapper login-body">
<div class="login-wrapper">
<div class="container">
<div class="loginbox">
<div class="login-left">
<img class="img-fluid" src="<?php echo htmlspecialchars($logo); ?>" alt="Logo">
</div>
<div class="login-right">
<div class="login-right-wrap">
<h1 class="alert alert-success text-center custom-font"><?php echo htmlspecialchars($universite_nom); ?></h1>
<p class="account-subtitle"></a></p>
<h2>Connexion </h2>
<form method="post" class="custom-font" action="php/routeur.php">
<div class="form-group">
<label>Login <span class="login-danger">*</span></label>
<input class="form-control" type="text" name="username" required> 
<span class="profile-views"><i class="fas fa-user-circle"></i></span>
</div>
<div class="form-group">
<label>Mot de Passe<span class="login-danger">*</span></label>
<input class="form-control pass-input" type="password" name="password" required>
<span class="profile-views feather-eye toggle-password"></span>
</div>
<?php
    // Afficher le message d'erreur si auth_failed est présent dans l'URL
    if (isset($_GET['erreur'])) {
        echo "<p style='color: red;'>".$_GET['erreur']."</p>";
    }
    // Afficher le message de succès
    if (isset($_GET['success'])) {
        echo "<p style='color: green;'>".$_GET['success']."</p>";
    }
?>
<div class="forgotpass">
<div class="remember-me">
<label class="custom_check mr-2 mb-0 d-inline-flex remember-me"> se souvenir de moi
<input type="checkbox" name="radio">
<span class="checkmark"></span>
</label>
</div>

 <a href="forgot_password.php" class="text-success custom-font">
        Mot de passe oublié ?
    </a>
</div>
<div class="form-group">
<button class="btn btn-success btn-block" type="submit">CONNEXION</button>
</div>
</form>

<!-- Registration Link -->
<div class="register-link">
    <p class="custom-font">Vous n'avez pas de compte ?</p>
    <a href="register_account.php" class="custom-font">
        <i class="fas fa-user-plus"></i> Créer un compte
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
<script src="assets/js/feather.min.js"></script>
<script src="assets/js/script.js"></script>
<script>
    $(document).ready(function() {
        // Vérifier si les attributs "erreur" ou "success" sont présents dans l'URL
        var urlParams = new URLSearchParams(window.location.search);
        var erreur = urlParams.get('erreur');
        var success = urlParams.get('success');
        // Afficher le modal si l'un des attributs est présent
        if (erreur || success) {
            var message = erreur ? "Erreur : " + erreur : "Message : " + success;
            $('#messageBody').text(message);
            $('#messageModal').modal('show');
        }
    });

    // Toggle password visibility
    $('.toggle-password').click(function() {
        var input = $(this).siblings('.pass-input');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            $(this).removeClass('feather-eye').addClass('feather-eye-off');
        } else {
            input.attr('type', 'password');
            $(this).removeClass('feather-eye-off').addClass('feather-eye');
        }
    });
</script> 
</body>
</html>
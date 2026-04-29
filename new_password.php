<?php
session_start();
require_once 'php/connexion.php';

// Vérifier si le token existe
$token = $_GET['token'] ?? '';
$type = $_GET['type'] ?? '';

if (empty($token) || empty($type)) {
    header("Location: index.php?erreur=" . urlencode("Lien invalide"));
    exit();
}

// Vérifier la validité du token
$table = ($type === 'enseignant') ? 'utilisateur' : 'utilisateur';
$query = "SELECT * FROM $table WHERE reset_token = ? AND reset_token_expiry > NOW()";
$stmt = $connexion->prepare($query);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php?erreur=" . urlencode("Le lien de réinitialisation a expiré ou est invalide"));
    exit();
}

$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
<title>UDSN - Nouveau mot de passe</title>
<link rel="shortcut icon" href="images/univ.png">
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
    .password-strength {
        font-size: 12px;
        margin-top: 5px;
    }
    .strength-weak { color: #dc3545; }
    .strength-medium { color: #ffc107; }
    .strength-strong { color: #28a745; }
    .password-requirements {
        font-size: 13px;
        margin-top: 10px;
    }
    .requirement {
        color: #6c757d;
    }
    .requirement.met {
        color: #28a745;
    }
    .requirement i {
        margin-right: 5px;
    }
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
<h1 class="alert alert-success text-center custom-font">UNIVERSITE DENIS SASSOU-N'GUESSO</h1>
<h2>Définir un nouveau mot de passe</h2>
<p class="account-subtitle">Bonjour <strong><?php echo htmlspecialchars($user['login']); ?></strong></p>

<form method="post" class="custom-font" action="php/update_password.php" id="newPasswordForm">
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
    <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>">

    <div class="form-group">
        <label>Nouveau mot de passe <span class="login-danger">*</span></label>
        <input class="form-control pass-input" type="password" name="password" id="password" required>
        <span class="profile-views feather-eye toggle-password"></span>
        <div id="password-strength" class="password-strength"></div>
    </div>

    

    <div class="form-group">
        <label>Confirmer le mot de passe <span class="login-danger">*</span></label>
        <input class="form-control pass-input" type="password" name="confirm_password" id="confirm_password" required>
        <span class="profile-views feather-eye toggle-password"></span>
        <div id="password-match" class="password-strength"></div>
    </div>

    <?php
        if (isset($_GET['erreur'])) {
            echo "<div class='alert alert-danger custom-font'><i class='fas fa-exclamation-triangle'></i> ".$_GET['erreur']."</div>";
        }
    ?>

    <div class="form-group">
        <button class="btn btn-success btn-block" type="submit" id="submitBtn" disabled>
            <i class="fas fa-check"></i> ENREGISTRER LE NOUVEAU MOT DE PASSE
        </button>
    </div>
</form>

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
    let requirements = {
        length: false,
        lowercase: false,
        uppercase: false,
        number: false,
        special: false
    };

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

    // Password validation
    $('#password').on('input', function() {
        var password = $(this).val();
        
        // Check each requirement
        requirements.length = password.length >= 8;
        requirements.lowercase = /[a-z]/.test(password);
        requirements.uppercase = /[A-Z]/.test(password);
        requirements.number = /[0-9]/.test(password);
        requirements.special = /[!@#$%^&*]/.test(password);
        
        // Update UI for each requirement
        updateRequirement('req-length', requirements.length);
        updateRequirement('req-lowercase', requirements.lowercase);
        updateRequirement('req-uppercase', requirements.uppercase);
        updateRequirement('req-number', requirements.number);
        updateRequirement('req-special', requirements.special);
        
        // Calculate strength
        var strength = Object.values(requirements).filter(Boolean).length;
        var strengthDiv = $('#password-strength');
        
        strengthDiv.removeClass('strength-weak strength-medium strength-strong');
        
        if (password.length === 0) {
            strengthDiv.text('');
        } else if (strength < 3) {
            strengthDiv.addClass('strength-weak').text('Faible');
        } else if (strength < 5) {
            strengthDiv.addClass('strength-medium').text('Moyen');
        } else {
            strengthDiv.addClass('strength-strong').text('Fort');
        }
        
        checkFormValidity();
    });

    // Password confirmation checker
    $('#confirm_password').on('input', function() {
        checkPasswordMatch();
        checkFormValidity();
    });

    function checkPasswordMatch() {
        var password = $('#password').val();
        var confirmPassword = $('#confirm_password').val();
        var matchDiv = $('#password-match');
        
        if (confirmPassword.length === 0) {
            matchDiv.text('');
        } else if (password === confirmPassword) {
            matchDiv.removeClass('strength-weak').addClass('strength-strong').text('✓ Les mots de passe correspondent');
        } else {
            matchDiv.removeClass('strength-strong').addClass('strength-weak').text('✗ Les mots de passe ne correspondent pas');
        }
    }

    function updateRequirement(id, met) {
        var element = $('#' + id);
        if (met) {
            element.addClass('met');
            element.find('i').removeClass('fa-circle').addClass('fa-check-circle');
        } else {
            element.removeClass('met');
            element.find('i').removeClass('fa-check-circle').addClass('fa-circle');
        }
    }

    function checkFormValidity() {
        var allRequirementsMet = Object.values(requirements).every(Boolean);
        var password = $('#password').val();
        var confirmPassword = $('#confirm_password').val();
        var passwordsMatch = password === confirmPassword && password.length > 0;
        
        if (allRequirementsMet && passwordsMatch) {
            $('#submitBtn').prop('disabled', false);
        } else {
            $('#submitBtn').prop('disabled', true);
        }
    }

    // Form submission
    $('#newPasswordForm').submit(function(e) {
        var password = $('#password').val();
        var confirmPassword = $('#confirm_password').val();
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Les mots de passe ne correspondent pas.');
            return false;
        }
        
        var allRequirementsMet = Object.values(requirements).every(Boolean);
        if (!allRequirementsMet) {
            e.preventDefault();
            alert('Le mot de passe ne respecte pas toutes les exigences.');
            return false;
        }
    });
});
</script>
</body>
</html>
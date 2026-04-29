<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
<title>UDSN - Créer un compte</title>
<link rel="shortcut icon" href="images/univ.png">
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;0,500;0,700;0,900;1,400;1,500;1,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/plugins/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/feather-icons@4.29.0/dist/feather.min.css">
<link rel="stylesheet" href="assets/plugins/icons/flags/flags.css">
<link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css">
<link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">
<link rel="stylesheet" href="assets/css/style.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<style>
    .custom-font {
        font-family: "Times New Roman", Times, serif;
    }
    .login-link {
        text-align: center;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }
    .login-link a {
        color: #28a745;
        text-decoration: none;
        font-weight: 500;
    }
    .login-link a:hover {
        text-decoration: underline;
    }
    .register-wrapper .loginbox {
        max-width: 600px;
    }
    .password-strength {
        font-size: 12px;
        margin-top: 5px;
    }
    .strength-weak { color: #dc3545; }
    .strength-medium { color: #ffc107; }
    .strength-strong { color: #28a745; }
</style>
</head>
<body>
<div class="main-wrapper login-body">
<div class="login-wrapper register-wrapper">
<div class="container">
<div class="loginbox">
<div class="login-left">
<img class="img-fluid" src="images/univ.png" alt="Logo">
</div>
<div class="login-right">
<div class="login-right-wrap">
<h1 class="alert alert-success text-center custom-font">UNIVERSITE DENIS SASSOU-N'GUESSO</h1>
<h2>Créer un compte</h2>
<form method="post" class="custom-font" action="php/routeur1.php" id="registerForm">

    <!-- Personal Information -->
    <div class="row">
     

    <!-- Account Type -->
    
    
     <div class="form-group custom-font" style="position: relative;">
                                            <label><strong>Rôle</strong></label>
                                           <select name="role" class="form-control"  id="role" onchange="afficherEtablissement()">

                                          
                                           <option value="candidat">Etudiant</option>
                                           <option value="enseignant">Enseignant</option>
                                           </select>
                                        </div>
                                        
                                        
                                        <div class="form-group custom-font" id="etablissementField" style="position: relative;" style="display: none;">
         <label><strong>Etablissement</strong></label>
        <select id="etablissement" class="form-control" name="etablissement">
            <option value="">-- Sélectionnez un établissement --</option>
        
            <option value="FSA">Faculté des sciences appliquées</option>
                                 
        </select>
    </div>

    <!-- Login Information -->
    <div class="form-group">
        <label>Nom d'utilisateur <span class="login-danger">*</span>
        <input class="form-control" type="text" name="username" required>
        <span class="profile-views"><i class="fas fa-user-circle"></i></span></label>
        <small class="form-text text-muted">Minimum 4 caractères, lettres et chiffres uniquement</small>
    </div>
        <div class="form-group">
        <label>email <span class="login-danger">*</span></label>
        <input class="form-control" type="email" name="email" required>
        
       </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="form-group">
                <label>Mot de passe <span class="login-danger">*</span>
                <span class="toggle-password" onclick="togglePassword()">
                                                <i class="fa fa-eye"></i>
                                                </span>	</label>
                <input class="form-control pass-input" type="password" name="password" id="password" required>
               
                <div id="password-strength" class="password-strength"></div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label>Confirmer le mot de passe <strong class="login-danger">*</strong>
              	<span class="toggle-password" onclick="togglePassword()"> <i class="fa fa-eye"></i></span>
               </label>
                <input class="form-control pass-input" type="password" name="confirm_password" id="confirm_password" required>
                                              
                <div id="password-match" class="password-strength"></div>
            </div>
        </div>
    </div>

  

    <?php
        // Afficher les messages d'erreur ou de succès
        if (isset($_GET['erreur'])) {
            echo "<p style='color: red;'>".$_GET['erreur']."</p>";
        }
        if (isset($_GET['success'])) {
            echo "<p style='color: green;'>".$_GET['success']."</p>";
        }
    ?>



    <div class="form-group">
        <button class="btn btn-success btn-block" type="submit">CRÉER LE COMPTE</button>
    </div>
</form>

<!-- Login Link -->
<div class="login-link">
    <p class="custom-font">Vous avez déjà un compte ?</p>
    <a href="index.php" class="custom-font">
        <i class="fas fa-sign-in-alt"></i> Se connecter
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
<script src="https://cdn.jsdelivr.net/npm/feather-icons@4.29.0/dist/feather.min.js"></script>
<script src="assets/js/script.js"></script>
<script>
$(document).ready(function() {
    // Show/hide additional fields based on account type
    $('select[name="type_compte"]').change(function() {
        var accountType = $(this).val();
        $('#student-fields, #teacher-fields').hide();
        
        if (accountType === 'etudiant') {
            $('#student-fields').show();
        } else if (accountType === 'enseignant') {
            $('#teacher-fields').show();
        }
    });

    // Password strength checker
    $('#password').on('input', function() {
        var password = $(this).val();
        var strength = checkPasswordStrength(password);
        var strengthDiv = $('#password-strength');
        
        strengthDiv.removeClass('strength-weak strength-medium strength-strong');
        
        if (password.length === 0) {
            strengthDiv.text('');
        } else if (strength < 2) {
            strengthDiv.addClass('strength-weak').text('Faible');
        } else if (strength < 4) {
            strengthDiv.addClass('strength-medium').text('Moyen');
        } else {
            strengthDiv.addClass('strength-strong').text('Fort');
        }
    });

    // Password confirmation checker
    $('#confirm_password').on('input', function() {
        var password = $('#password').val();
        var confirmPassword = $(this).val();
        var matchDiv = $('#password-match');
        
        if (confirmPassword.length === 0) {
            matchDiv.text('');
        } else if (password === confirmPassword) {
            matchDiv.removeClass('strength-weak').addClass('strength-strong').text('✓ Correspond');
        } else {
            matchDiv.removeClass('strength-strong').addClass('strength-weak').text('✗ Ne correspond pas');
        }
    });

   

    // Form validation
    $('#registerForm').submit(function(e) {
        var password = $('#password').val();
        var confirmPassword = $('#confirm_password').val();
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Les mots de passe ne correspondent pas.');
            return false;
        }
        
        if (password.length < 6) {
            e.preventDefault();
            alert('Le mot de passe doit contenir au moins 6 caractères.');
            return false;
        }
    });
});

function checkPasswordStrength(password) {
    var strength = 0;
    
    // Length check
    if (password.length >= 6) strength++;
    if (password.length >= 10) strength++;
    
    // Character variety checks
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    return strength;
}
</script>
    <script>
        function afficherEtablissement() {
            // Récupère la valeur sélectionnée pour le rôle
            const role = document.getElementById("role").value;
            const etablissementField = document.getElementById("etablissementField");

            // Affiche ou cache le champ de sélection d'établissement en fonction du rôle choisi
            if (role === "enseignant") {
                etablissementField.style.display = "block";
            } else {
                etablissementField.style.display = "none";
            }
        }
    </script>
  <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const togglePasswordIcon = document.querySelector('.toggle-password');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                togglePasswordIcon.classList.remove('fa-eye');
                togglePasswordIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                togglePasswordIcon.classList.remove('fa-eye-slash');
                togglePasswordIcon.classList.add('fa-eye');
            }
        }

           
</body>
</html>
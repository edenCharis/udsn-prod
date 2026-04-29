<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
<title>UDSN - Mot de passe oublié</title>
<link rel="shortcut icon" href="images/univ.png">
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;0,500;0,700;0,900;1,400;1,500;1,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/plugins/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/plugins/feather/feather.css">
<link rel="stylesheet" href="assets/plugins/icons/flags/flags.css">
<link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css">
<link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">
<link rel="stylesheet" href="assets/css/style.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<style>
    .custom-font {
        font-family: "Times New Roman", Times, serif;
    }
    .back-link {
        text-align: center;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }
    .back-link a {
        color: #28a745;
        text-decoration: none;
        font-weight: 500;
    }
    .back-link a:hover {
        text-decoration: underline;
    }
    .forgot-wrapper .loginbox {
        max-width: 650px;
    }
    .select2-container {
        width: 100% !important;
    }
    .select2-container .select2-selection--multiple {
        min-height: 38px;
        border: 1px solid #ced4da;
        border-radius: 4px;
    }
    .role-section {
        display: none;
    }
    .role-section.active {
        display: block;
    }
</style>
</head>
<body>
<div class="main-wrapper login-body">
<div class="login-wrapper forgot-wrapper">
<div class="container">
<div class="loginbox">
<div class="login-left">
<img class="img-fluid" src="images/univ.png" alt="Logo">
</div>
<div class="login-right">
<div class="login-right-wrap">
<h1 class="alert alert-success text-center custom-font">UNIVERSITE DENIS SASSOU-N'GUESSO</h1>
<h2>Récupération de mot de passe</h2>
<p class="account-subtitle">Veuillez fournir les informations requises pour réinitialiser votre mot de passe</p>

<form method="post" class="custom-font" action="php/reset_password.php" id="forgotPasswordForm">

    <!-- Sélection du type d'utilisateur -->
    <div class="form-group">
        <label>Type de compte <span class="login-danger">*</span></label>
        <select name="user_type" class="form-control" id="userType" required>
            <option value="">-- Sélectionnez --</option>
            <option value="enseignant">Enseignant</option>
            <option value="etudiant">Étudiant</option>
        </select>
        <span class="profile-views"><i class="fas fa-user-tag"></i></span>
    </div>

    <!-- Section ENSEIGNANT -->
    <div id="enseignantSection" class="role-section">
     
     

        <div class="form-group">
            <label>Login <span class="login-danger">*</span></label>
            <input class="form-control" type="text" name="enseignant_login" id="enseignant_login">
            <span class="profile-views"><i class="fas fa-user"></i></span>
        </div>

        <div class="form-group">
            <label>Établissement <span class="login-danger">*</span></label>
            <select class="form-control" name="enseignant_etablissement" id="enseignant_etablissement">
                <option value="">-- Sélectionnez --</option>
                <option value="FSA">Faculté des sciences appliquées</option>
             
            </select>
            <span class="profile-views"><i class="fas fa-building"></i></span>
        </div>

        <div class="form-group">
            <label>Code unique de contrat <span class="login-danger">*</span></label>
            <input class="form-control" type="text" name="code_contrat" id="code_contrat" placeholder="Ex: AAXXXXX">
            <span class="profile-views"><i class="fas fa-file-contract"></i></span>
        </div>
\

     
    </div>

    <!-- Section ETUDIANT -->
    <div id="etudiantSection" class="role-section">
   

        <div class="form-group">
            <label>Login <span class="login-danger">*</span></label>
            <input class="form-control" type="text" name="etudiant_login" id="etudiant_login">
            <span class="profile-views"><i class="fas fa-user"></i></span>
        </div>

        <div class="form-group">
            <label>Établissement <span class="login-danger">*</span></label>
            <select class="form-control" name="etudiant_etablissement" id="etudiant_etablissement">
                <option value="">-- Sélectionnez --</option>
                <option value="FSA">Faculté des sciences appliquées</option>
            
            </select>
            <span class="profile-views"><i class="fas fa-building"></i></span>
        </div>

        <div class="form-group">
            <label>Matricule <span class="login-danger">*</span></label>
            <input class="form-control" type="text" name="matricule" id="matricule" placeholder="Ex: 2024FSA001">
            <span class="profile-views"><i class="fas fa-id-card"></i></span>
            <small class="form-text text-muted">Votre numéro matricule universitaire</small>
        </div>
    </div>

    <?php
        // Afficher les messages d'erreur ou de succès
        if (isset($_GET['erreur'])) {
            echo "<div class='alert alert-danger custom-font'><i class='fas fa-exclamation-triangle'></i> ".$_GET['erreur']."</div>";
        }
        if (isset($_GET['success'])) {
            echo "<div class='alert alert-success custom-font'><i class='fas fa-check-circle'></i> ".$_GET['success']."</div>";
        }
    ?>

    <div class="form-group">
        <button class="btn btn-success btn-block" type="submit">
            <i class="fas fa-key"></i> RÉINITIALISER 
    </div>
</form>

<!-- Back to Login Link -->
<div class="back-link">
    <a href="index.php" class="custom-font">
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
<script src="https://cdnjs.cloudflare.com/ajax/li<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
<title>UDSN - Mot de passe oublié</title>
<link rel="shortcut icon" href="images/univ.png">
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;0,500;0,700;0,900;1,400;1,500;1,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/plugins/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/plugins/feather/feather.css">
<link rel="stylesheet" href="assets/plugins/icons/flags/flags.css">
<link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css">
<link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">
<link rel="stylesheet" href="assets/css/style.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<style>
    .custom-font {
        font-family: "Times New Roman", Times, serif;
    }
    .back-link {
        text-align: center;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }
    .back-link a {
        color: #28a745;
        text-decoration: none;
        font-weight: 500;
    }
    .back-link a:hover {
        text-decoration: underline;
    }
    .forgot-wrapper .loginbox {
        max-width: 650px;
    }
    .select2-container {
        width: 100% !important;
    }
    .select2-container .select2-selection--multiple {
        min-height: 38px;
        border: 1px solid #ced4da;
        border-radius: 4px;
    }
    .role-section {
        display: none;
    }
    .role-section.active {
        display: block;
    }
</style>
</head>
<body>
<div class="main-wrapper login-body">
<div class="login-wrapper forgot-wrapper">
<div class="container">
<div class="loginbox">
<div class="login-left">
<img class="img-fluid" src="images/univ.png" alt="Logo">
</div>
<div class="login-right">
<div class="login-right-wrap">
<h1 class="alert alert-success text-center custom-font">UNIVERSITE DENIS SASSOU-N'GUESSO</h1>
<h2>Récupération de mot de passe</h2>
<p class="account-subtitle">Veuillez fournir les informations requises pour réinitialiser votre mot de passe</p>

<form method="post" class="custom-font" action="php/reset_password.php" id="forgotPasswordForm">

    <!-- Sélection du type d'utilisateur -->
    <div class="form-group">
        <label>Type de compte <span class="login-danger">*</span></label>
        <select name="user_type" class="form-control" id="userType" required>
            <option value="">-- Sélectionnez --</option>
            <option value="enseignant">Enseignant</option>
            <option value="etudiant">Étudiant</option>
        </select>
        <span class="profile-views"><i class="fas fa-user-tag"></i></span>
    </div>

    <!-- Section ENSEIGNANT -->
    <div id="enseignantSection" class="role-section">
    
    

        <div class="form-group">
            <label>Login <span class="login-danger">*</span></label>
            <input class="form-control" type="text" name="enseignant_login" id="enseignant_login">
            <span class="profile-views"><i class="fas fa-user"></i></span>
        </div>

        <div class="form-group">
            <label>Établissement <span class="login-danger">*</span></label>
            <select class="form-control" name="enseignant_etablissement" id="enseignant_etablissement">
                <option value="">-- Sélectionnez --</option>
                <option value="FSA">Faculté des sciences appliquées</option>
             
            </select>
            <span class="profile-views"><i class="fas fa-building"></i></span>
        </div>

        <div class="form-group">
            <label>Code unique de contrat <span class="login-danger">*</span></label>
            <input class="form-control" type="text" name="code_contrat" id="code_contrat" placeholder="Ex: CONT-2024-001">
            <span class="profile-views"><i class="fas fa-file-contract"></i></span>
        </div>

        <div class="form-group">
            <label>Classe(s) enseignée(s) <span class="login-danger">*</span></label>
            <select class="form-control" name="classes[]" id="classes" multiple="multiple">
             
             
            </select>
            <small class="form-text text-muted">Sélectionnez au moins une classe</small>
        </div>

        <div class="form-group">
            <label>Année académique <span class="login-danger">*</span></label>
            <select class="form-control" name="annee_academique" id="annee_academique">
                <option value="">-- Sélectionnez --</option>
               
                <option value="2025-2026">2025-2026</option>
            </select>
            <span class="profile-views"><i class="fas fa-calendar-alt"></i></span>
        </div>
    </div>

    <!-- Section ETUDIANT -->
    <div id="etudiantSection" class="role-section">
   
   

        <div class="form-group">
            <label>Login <span class="login-danger">*</span></label>
            <input class="form-control" type="text" name="etudiant_login" id="etudiant_login">
            <span class="profile-views"><i class="fas fa-user"></i></span>
        </div>

        <div class="form-group">
            <label>Établissement <span class="login-danger">*</span></label>
            <select class="form-control" name="etudiant_etablissement" id="etudiant_etablissement">
                <option value="">-- Sélectionnez --</option>
                <option value="FSA">Faculté des sciences appliquées</option>
            
            </select>
            <span class="profile-views"><i class="fas fa-building"></i></span>
        </div>

        <div class="form-group">
            <label>Matricule <span class="login-danger">*</span></label>
            <input class="form-control" type="text" name="matricule" id="matricule" placeholder="Ex: 2024FSA001">
            <span class="profile-views"><i class="fas fa-id-card"></i></span>
            <small class="form-text text-muted">Votre numéro matricule universitaire</small>
        </div>
    </div>

    <?php
        // Afficher les messages d'erreur ou de succès
        if (isset($_GET['erreur'])) {
            echo "<div class='alert alert-danger custom-font'><i class='fas fa-exclamation-triangle'></i> ".$_GET['erreur']."</div>";
        }
        if (isset($_GET['success'])) {
            echo "<div class='alert alert-success custom-font'><i class='fas fa-check-circle'></i> ".$_GET['success']."</div>";
        }
    ?>

    <div class="form-group">
        <button class="btn btn-success btn-block" type="submit">
            <i class="fas fa-key"></i> RÉINITIALISER
        </button>
    </div>
</form>

<!-- Back to Login Link -->
<div class="back-link">
    <a href="index.php" class="custom-font">
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="assets/js/feather.min.js"></script>
<script src="assets/js/script.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 for multiple select
    $('#classes').select2({
        placeholder: "Sélectionnez une ou plusieurs classes",
        allowClear: true
    });

    // Show/hide sections based on user type
    $('#userType').change(function() {
        var userType = $(this).val();
        $('.role-section').removeClass('active');
        
        if (userType === 'enseignant') {
            $('#enseignantSection').addClass('active');
            // Make enseignant fields required
            $('#enseignant_login, #enseignant_etablissement, #code_contrat, #annee_academique').prop('required', true);
            // Make etudiant fields not required
            $('#etudiant_login, #etudiant_etablissement, #matricule').prop('required', false);
        } else if (userType === 'etudiant') {
            $('#etudiantSection').addClass('active');
            // Make etudiant fields required
            $('#etudiant_login, #etudiant_etablissement, #matricule').prop('required', true);
            // Make enseignant fields not required
            $('#enseignant_login, #enseignant_etablissement, #code_contrat, #annee_academique').prop('required', false);
        }
    });


});
</script>
</body>
</html>bs/select2/4.0.13/js/select2.min.js"></script>
<script src="assets/js/feather.min.js"></script>
<script src="assets/js/script.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 for multiple select
    $('#classes').select2({
        placeholder: "Sélectionnez une ou plusieurs classes",
        allowClear: true
    });

    // Show/hide sections based on user type
    $('#userType').change(function() {
        var userType = $(this).val();
        $('.role-section').removeClass('active');
        
        if (userType === 'enseignant') {
            $('#enseignantSection').addClass('active');
            // Make enseignant fields required
            $('#enseignant_login, #enseignant_etablissement, #code_contrat, #annee_academique').prop('required', true);
            // Make etudiant fields not required
            $('#etudiant_login, #etudiant_etablissement, #matricule').prop('required', false);
        } else if (userType === 'etudiant') {
            $('#etudiantSection').addClass('active');
            // Make etudiant fields required
            $('#etudiant_login, #etudiant_etablissement, #matricule').prop('required', true);
            // Make enseignant fields not required
            $('#enseignant_login, #enseignant_etablissement, #code_contrat, #annee_academique').prop('required', false);
        }
    });

   
});
</script>
</body>
</html>
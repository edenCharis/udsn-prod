<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
<title>UDSN - Création compte</title>

<link rel="shortcut icon" href="images/univ.png">

<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;0,500;0,700;0,900;1,400;1,500;1,700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="assets/plugins/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/plugins/feather/feather.css">
<link rel="stylesheet" href="assets/plugins/icons/flags/flags.css">
<link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css">
<link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">

<!-- Inclusion de Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<link rel="stylesheet" href="assets/css/style.css">

<style>
    .custom-font {
        font-family: "Roboto", sans-serif;
    }
    @media screen and (max-width: 500px) {
        .authincation-content {
            padding: 20px;
        }
        .logo {
            width: 100px;
            height: 100px;
        }
        .auth-form {
            padding: 20px;
        }
        .btn-block {
            width: 100%;
        }
    }
    .toggle-password {
        cursor: pointer;
        position: absolute;
        right: 10px;
        top: 35px;
        color: #007BFF;
    }
</style>
</head>
<body class="h-100">
    <div class="authincation h-100">
        <div class="container h-100">
            <div class="row justify-content-center h-100 align-items-center">
                <div class="col-md-6">
                    <div class="authincation-content">
                        <div class="row no-gutters">
                            <div class="col-xl-12">
                                <div class="auth-form custom-font">
                                    <h4 class="text-center mb-4"><img class="logo" width="100px" heigth="10px" src="images/univ.png" alt=""><br></h4>
                                    <h3 class="alert alert-success text-center mb-4 custom-font">UNIVERSITE DENIS SASSOU-N'GUESSO</h3>
                                    <h5 class="text-center mb-4 custom-font">Création de compte</h5>
                                    <form action="php/routeur1.php" method="post">
                                        <div class="form-group custom-font">
                                            <label><strong>Login</strong></label>
                                            <input type="text" class="form-control" name="r_username" placeholder="andrew">
                                            <p class="text-danger"><?php echo (isset($_GET["login"]) ? $_GET["login"] : ""); ?></p>
                                        </div>
                                        <div class="form-group custom-font">
                                            <label><strong>Email</strong></label>
                                            <input type="email" class="form-control" name="email" placeholder="hello@example.com">
                                            <p class="text-danger"><?php echo (isset($_GET["email"]) ? $_GET["email"] : ""); ?></p>
                                        </div>
                                        <div class="form-group custom-font" style="position: relative;">
                                            <label><strong>Password</strong></label>
                                            <input type="password" class="form-control" name="r_password" id="password">
                                            <span class="toggle-password" onclick="togglePassword()">
                                                <i class="fas fa-eye"></i>
                                            </span>
                                            <p class="text-danger"><?php echo (isset($_GET["erreur"]) ? $_GET["erreur"] : ""); ?></p>
                                        </div>
                                          <div class="form-group custom-font" style="position: relative;">
                                            <label><strong>Rôle</strong></label>
                                           <select name="role" class="form-control"  id="role" onchange="afficherEtablissement()">

                                           <option value="none">Entrez votre rôle</option>
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
                                        <div class="text-center mt-4">
                                            <button type="submit" class="btn btn-primary btn-block">CRÉER</button>
                                        </div>
                                    </form>
                                    <div class="new-account mt-4">
                                        <p>Avez-vous un compte? <a class="text-primary" href="connexion">Se connecter</a></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="vendor/global/global.min.js"></script>
    <script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="js/dlabnav-init.js"></script>
    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const togglePasswordIcon = document.querySelector('.toggle-password i');
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
</body>
</html>
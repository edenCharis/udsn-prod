<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
<title>UDSN - Connexion </title>

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

        .auth-form {
            padding: 20px;
        }
        .logo{
            width: 100px;
            height: 50px;
        }

        .btn-block {
            width: 100%;
        }
        .toggle-password {
            top: 10px;
        }
    }
    .toggle-password {
        cursor: pointer;
        position: absolute;
        right: 10px;
        top: 35px;
        color: #007BFF;
        font-size: 1.2em;
    }
    .g-signin2 {
        width: 100%;
        display: flex;
        justify-content: center;
        margin-top: 20px;
    }
</style>
<script src="https://apis.google.com/js/platform.js" async defer></script>
<meta name="google-signin-client_id" content="750618913312-09qo79ctgrbntupbq2ekrhqk02sblhf5.apps.googleusercontent.com">
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
                                    <h4 class="text-center mb-4"><img class="logo" width="100px" heigth="50px" src="images/univ.png" alt=""><br></h4>
                                    <h4 class="alert alert-success custom-font text-center mb-4">UNIVERSITE DENIS SASSOU-N'GUESSO</h4>
                                    <h5 class="text-center mb-4 custom-font">Connectez-vous pour accéder à la plateforme </h5>
                                    <?php
                                    if (isset($_GET['erreur'])) {
                                        echo "<p style='color: red;'>".$_GET['erreur']."</p>";
                                    }
                                    ?>
                                    <form action="php/routeur2.php" method="POST">
                                        <div class="form-group custom-font">
                                            <label><strong>Email</strong></label>
                                            <input type="email" class="form-control" name="email" placeholder="hello@example.com">
                                        </div>
                                        <div class="form-group custom-font" style="position: relative;">
                                            <label><strong>Password</strong></label>
                                            <input type="password" class="form-control" name="password" id="password">
                                            <span class="toggle-password" onclick="togglePassword()">
                                                <i class="fas fa-eye"></i>
                                            </span>
                                        </div>
                                        <div class="text-center mt-4 custom-font col-sm-12">
                                            <button type="submit" class="btn btn-primary btn-block">SE CONNECTER</button>
                                        </div>
                                    </form>
                                    <div class="g-signin2" data-onsuccess="onSignIn"></div>
                                    <div class="new-account mt-3 custom-font">
                                        <p>N'avez-vous pas de compte ? <a class="text-primary" href="register">Créer</a></p>
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

           function onSignIn(googleUser) {
            var profile = googleUser.getBasicProfile();
            var id_token = googleUser.getAuthResponse().id_token;
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'php/routeur_google.php');
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    window.location.href = 'candidat/'; // Redirige vers la page d'accueil ou le tableau de bord
                } else {
                    console.log('Sign in failed: ' + xhr.responseText);
                    // Affichez un message d'erreur ou effectuez d'autres actions
                }
            };
            xhr.send(JSON.stringify({token: id_token}));
        }
    </script>
</body>
</html>

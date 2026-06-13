<?php
session_start();
// Vérifier que l'utilisateur est administrateur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'administrateur') {
    header("Location: ../login.php");
    exit();
}

include '../php/connexion.php';
include '../php/lib.php';
include '../config/logo_config.php';

$logoConfig = getLogoConfig();
$message = '';
$error = '';

// Traiter l'upload du logo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['logo'])) {
    $file = $_FILES['logo'];
    $univ_code = isset($_POST['univ_code']) ? intval($_POST['univ_code']) : 0;
    
    if ($univ_code <= 0) {
        $error = "Code d'université invalide.";
    } elseif ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "Erreur lors du téléchargement du fichier.";
    } else {
        // Valider le type de fichier
        $allowed_types = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime, $allowed_types)) {
            $error = "Type de fichier non autorisé. Utilisez PNG, JPEG, GIF ou WebP.";
        } else {
            // Créer le dossier s'il n'existe pas
            $upload_dir = __DIR__ . '/logo/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Générer un nom de fichier unique
            $filename = 'logo_' . $univ_code . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            $filepath = $upload_dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Mettre à jour la base de données
                if ($logoConfig->updateUniversityLogo($univ_code, 'logo/' . $filename)) {
                    $message = "Logo téléchargé et enregistré avec succès.";
                } else {
                    $error = "Erreur lors de l'enregistrement en base de données.";
                    unlink($filepath); // Supprimer le fichier
                }
            } else {
                $error = "Erreur lors du téléchargement du fichier.";
            }
        }
    }
}

// Traiter la modification du nom d'université
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_name') {
    $univ_code = isset($_POST['univ_code']) ? intval($_POST['univ_code']) : 0;
    $nom_univ = isset($_POST['nom_univ']) ? trim($_POST['nom_univ']) : '';
    
    if ($univ_code <= 0) {
        $error = "Code d'université invalide.";
    } elseif (empty($nom_univ)) {
        $error = "Le nom de l'université ne peut pas être vide.";
    } elseif (strlen($nom_univ) > 255) {
        $error = "Le nom est trop long (max 255 caractères).";
    } else {
        if ($logoConfig->updateUniversityName($univ_code, $nom_univ)) {
            $message = "Nom de l'université mis à jour avec succès.";
        } else {
            $error = "Erreur lors de la mise à jour du nom.";
        }
    }
}

// Récupérer toutes les universités
$sql = "SELECT code, nom, logo FROM univ ORDER BY nom";
$result = $connexion->query($sql);
$universites = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $universites[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Logos - Administration</title>
    <link rel="icon" type="image/png" sizes="16x16" href="../administrateur/<?php echo isset($_SESSION['logo_univ']) ? htmlspecialchars($_SESSION['logo_univ']) : 'images/univ.png'; ?>">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;0,500;0,700;0,900;1,400;1,500;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/plugins/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/plugins/feather/feather.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .logo-preview {
            max-width: 150px;
            max-height: 150px;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
        }
        .logo-container {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
        }
        .upload-section {
            margin-top: 30px;
            padding: 20px;
            background-color: #f5f5f5;
            border-radius: 5px;
        }
        .alert {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div id="main-wrapper">
    <?php include 'header.php'; ?>
    <?php include 'nav.php'; ?>
    
    <div class="content-body">
        <div class="container-fluid">
            <div class="page-titles">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Tableau de bord</a></li>
                    <li class="breadcrumb-item active"><a href="#">Gestion des Logos</a></li>
                </ol>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Gestion des Logos des Universités</h4>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($message)): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <strong>Succès!</strong> <?php echo htmlspecialchars($message); ?>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <strong>Erreur!</strong> <?php echo htmlspecialchars($error); ?>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            <?php endif; ?>
                            
                            <h5 class="mb-4">Gestion des Universités</h5>
                            <div class="row">
                                <?php foreach ($universites as $univ): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="logo-container">
                                            <h6><?php echo htmlspecialchars($univ['nom']); ?></h6>
                                            <p class="text-muted small">Code: <?php echo $univ['code']; ?></p>
                                            
                                            <!-- Affichage du logo -->
                                            <?php if ($univ['logo']): ?>
                                                <div class="mb-3">
                                                    <img src="<?php echo htmlspecialchars($univ['logo']); ?>" alt="Logo" class="logo-preview">
                                                    <p class="small mt-2"><strong>Fichier:</strong> <?php echo htmlspecialchars($univ['logo']); ?></p>
                                                </div>
                                            <?php else: ?>
                                                <div class="mb-3">
                                                    <p class="text-danger">Aucun logo configuré</p>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="btn-group" role="group" style="margin-bottom: 10px;">
                                                <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#logoModal<?php echo $univ['code']; ?>">
                                                    <i class="fas fa-upload"></i> Logo
                                                </button>
                                                <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#nameModal<?php echo $univ['code']; ?>">
                                                    <i class="fas fa-edit"></i> Nom
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Modal pour modifier le nom d'université -->
                                    <div class="modal fade" id="nameModal<?php echo $univ['code']; ?>" tabindex="-1" role="dialog" aria-labelledby="nameModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="nameModalLabel">Modifier le nom - <?php echo htmlspecialchars($univ['nom']); ?></h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="action" value="update_name">
                                                        <input type="hidden" name="univ_code" value="<?php echo $univ['code']; ?>">
                                                        <div class="form-group">
                                                            <label for="nom<?php echo $univ['code']; ?>">Nom de l'université</label>
                                                            <input type="text" class="form-control" id="nom<?php echo $univ['code']; ?>" name="nom_univ" value="<?php echo htmlspecialchars($univ['nom']); ?>" required maxlength="255">
                                                            <small class="form-text text-muted">Le nom qui s'affichera sur les pages de connexion</small>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="fas fa-save"></i> Enregistrer
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Modal pour télécharger le logo -->
                                    <div class="modal fade" id="logoModal<?php echo $univ['code']; ?>" tabindex="-1" role="dialog" aria-labelledby="logoModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="logoModalLabel">Télécharger le logo - <?php echo htmlspecialchars($univ['nom']); ?></h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form method="POST" enctype="multipart/form-data">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="univ_code" value="<?php echo $univ['code']; ?>">
                                                        <div class="form-group">
                                                            <label for="logo<?php echo $univ['code']; ?>">Sélectionner une image</label>
                                                            <input type="file" class="form-control-file" id="logo<?php echo $univ['code']; ?>" name="logo" accept="image/*" required>
                                                            <small class="form-text text-muted">Formats acceptés: PNG, JPEG, GIF, WebP. Taille max: 5MB</small>
                                                        </div>
                                                        <div class="alert alert-info" role="alert">
                                                            <strong>Conseil:</strong> Utilisez une image carrée ou avec un bon ratio pour un meilleur rendu.
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="fas fa-upload"></i> Télécharger
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/jquery-3.6.0.min.js"></script>
<script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/feather.min.js"></script>
<script src="../assets/js/script.js"></script>

<script>
// Validation du fichier côté client
document.querySelectorAll('input[type="file"]').forEach(function(input) {
    input.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const maxSize = 5 * 1024 * 1024; // 5MB
            if (file.size > maxSize) {
                alert('Le fichier est trop volumineux. Taille max: 5MB');
                this.value = '';
                return;
            }
            
            const validTypes = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                alert('Type de fichier non autorisé. Utilisez PNG, JPEG, GIF ou WebP.');
                this.value = '';
            }
        }
    });
});
</script>
</body>
</html>

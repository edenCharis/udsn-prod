<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables from GET parameters
$classe = isset($_GET["classe"]) ? trim($_GET["classe"]) : "";
$specialite = isset($_GET["specialite"]) ? trim($_GET["specialite"]) : "";
$annee = isset($_GET["annee"]) ? trim($_GET["annee"]) : "";

// Fetch valid values for validation
$annees = array();
$sql = "SELECT libelle FROM annee";
$resultat = $connexion->query($sql);
while($row = $resultat->fetch_assoc()) {
    $annees[] = $row["libelle"];
}

$specialites = array();
$sql = "SELECT libelle FROM specialite WHERE etab=?";
$stmt = $connexion->prepare($sql);
$stmt->bind_param("s", $_SESSION['lib_etab']);
$stmt->execute();
$resultat = $stmt->get_result();
while($row = $resultat->fetch_assoc()) {
    $specialites[] = $row["libelle"];
}
$stmt->close();

$classes = array();
$sql = "SELECT libelle FROM classe WHERE etab=?";
$stmt = $connexion->prepare($sql);
$stmt->bind_param("s", $_SESSION['etablissement']);
$stmt->execute();
$resultat = $stmt->get_result();
while($row = $resultat->fetch_assoc()) {
    $classes[] = $row["libelle"];
}
$stmt->close();

?>
<!DOCTYPE html>
<html lang="fr">

<head>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Liste des Étudiants</title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../administrateur/<?php echo htmlspecialchars($_SESSION['logo_univ']); ?>">
	<link rel="stylesheet" href="../vendor/bootstrap-select/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="../vendor/select2/css/select2.min.css">
    <link rel="stylesheet" href="../css/style.css">
	<link rel="stylesheet" href="../css/skin.css">
    <link href="../vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
    
    <style>
        .underline-text {
            text-decoration: underline;
        }
        
        .table-bold-rows-cols {
            border-collapse: collapse;
        }

        .table-bold-rows-cols tbody tr {
            font-weight: bold;
        }
        
        .custom-font {
            font-family: "Times New Roman", Times, serif;
        }
        
        .table-bold-rows-cols tbody tr td,
        .table-bold-rows-cols tbody tr th {
            border: 1px solid black;
            padding: 8px;
        }

        .table-bold-rows-cols tbody tr td:not(:first-child),
        .table-bold-rows-cols tbody tr th:not(:first-child) {
            border-left: 1px solid black;
        }

        .table-bold-rows-cols tbody tr td:not(:last-child),
        .table-bold-rows-cols tbody tr th:not(:last-child) {
            border-right: 1px solid black;
        }

        body {
            position: relative; 
            font-family: "Times New Roman", Times, serif;
        }

        .logo-filigrane {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.2;
            z-index: -1;
        }
    </style>
</head>

<body>
    <!--*******************
        Preloader start
    ********************-->
    <div id="preloader">
        <div class="sk-three-bounce">
            <img src="../images/univ.png" class="logo-filigrane" alt="Logo en filigrane">
        </div>
    </div>
    <!--*******************
        Preloader end
    ********************-->

    <!--**********************************
        Main wrapper start
    ***********************************-->
    <div id="main-wrapper">
        <div class="content h-50" id="contenu-a-imprimer">
            <!-- row -->
            <div class="container-fluid">
                <div class="row" style="height: 250px;">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <!-- Université Denis Sassou Nguesso -->
                            <div class="p-2">
                                <h4 class="justify-content-center custom-font"><b>UNIVERSITE DENIS SASSOU N'GUESSO</b></h4>
                                <h5 class="justify-content-center custom-font">DIRECTION DE LA SCOLARITE ET DES EXAMENS</h5>
                                <p>SERVICE DE LA SCOLARITE ET DES EXAMENS</p>
                            </div>
                            <div class="p-2">
                                <img src="../images/univ.png" alt="Logo de l'université" style="max-width: 100px;">
                            </div>
                            <!-- Devise -->
                            <div class="p-2">
                                <h4 class="custom-font">Rigueur-Excellence-Lumieres</h4>
                                <p><?php echo mb_strtoupper(htmlspecialchars($_SESSION["lib_etab"])); ?></p>
                                <?php if(typeEtablissement($_SESSION["lib_etab"], $connexion) == "faculté"): ?>
                                    <p class="justify-content-center custom-font">VICE-DECANAT</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
 
                <div class="row justify-content-center align-items-center">
                    <h1 class="custom-font">LISTE DES ETUDIANTS</h1>
                </div>
                
                <?php if(!empty($classe) && in_array($classe, $classes)): ?>
                <div class="row justify-content-center align-items-center">
                    <h2 class="custom-font">Classe : <?php echo htmlspecialchars($classe); ?></h2>
                </div>
                <?php endif; ?>
                
                <?php if(!empty($specialite) && in_array($specialite, $specialites)): ?>
                <div class="row justify-content-center align-items-center">
                    <h2 class="custom-font">Parcours : <?php echo htmlspecialchars(getParcours($specialite, $connexion)); ?></h2>
                </div>
                
                <div class="row justify-content-center align-items-center">
                    <h2 class="custom-font">Specialité : <?php echo htmlspecialchars($specialite); ?></h2>
                </div>
                <?php endif; ?>
                
                <?php if(!empty($annee) && in_array($annee, $annees)): ?>
                <div class="row justify-content-center align-items-center">
                    <h2 class="custom-font">Année académique : <?php echo htmlspecialchars($annee); ?></h2>
                </div>
                <?php endif; ?>

                <div class="row row h-100 justify-content-center align-items-center" style="height: 500px;">
                    <div class="container mt-5">
                        <div class="table-responsive">
                            <table class="table table-striped" style="min-width: 845px">
                                <thead>
                                    <tr>
                                        <th>N°</th>
                                        <th>Matricule</th>
                                        <th>Nom(s)</th>
                                        <th>Prénom(s)</th>
                                        <th>Classe</th>
                                        <th>Niveau</th>
                                        <th>Specialité</th>
                                        <th>Année scolaire</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // Build query dynamically with prepared statements
                                    $sql = "SELECT * FROM inscription WHERE etab=?";
                                    $params = array($_SESSION['etablissement']);
                                    $types = "s";
                                    
                                    // Add classe filter if valid
                                    if(!empty($classe) && in_array($classe, $classes)) {
                                        $sql .= " AND classe=?";
                                        $params[] = $classe;
                                        $types .= "s";
                                    }
                                    
                                    // Add specialite filter if valid (only if classe is not specified)
                                    if(!empty($specialite) && in_array($specialite, $specialites) && empty($classe)) {
                                        $sql .= " AND classe IN (SELECT libelle FROM classe WHERE specialite=?)";
                                        $params[] = $specialite;
                                        $types .= "s";
                                    }
                                    
                                    // Add annee filter if valid
                                    if(!empty($annee) && in_array($annee, $annees)) {
                                        $sql .= " AND annee=?";
                                        $params[] = $annee;
                                        $types .= "s";
                                    }

                                    $stmt = $connexion->prepare($sql);
                                    if($stmt) {
                                        $stmt->bind_param($types, ...$params);
                                        $stmt->execute();
                                        $resultat = $stmt->get_result();
                                        $count = 1;

                                        while($etudiant = $resultat->fetch_assoc()) {
                                            $candidat_code = getCandidatCodeByInscription($etudiant['id'], $connexion);
                                            $nom = getNomEtudiant($candidat_code, $connexion, $_SESSION["lib_etab"]) ;
                                            $prenom = mettrePremieresLettresMajuscules(getPrenomEtudiant($candidat_code, $connexion, $_SESSION["lib_etab"]));
                                            $niveau = obtenirNiveauClasse($etudiant['classe'], $connexion);
                                            $spec = obtenirSpecialiteClasse($etudiant['classe'], $connexion);
                                    ?>
                                    <tr>
                                        <td><?php echo $count; ?></td>
                                        <td><?php echo htmlspecialchars($candidat_code); ?></td>
                                        <td><?php echo htmlspecialchars($nom); ?></td>
                                        <td><?php echo htmlspecialchars($prenom); ?></td>
                                        <td><?php echo htmlspecialchars($etudiant['classe']); ?></td>
                                        <td><?php echo htmlspecialchars($niveau); ?></td>	
                                        <td><?php echo htmlspecialchars($spec); ?></td>	
                                        <td><?php echo htmlspecialchars($etudiant['annee']); ?></td>
                                    </tr>
                                    <?php 
                                            $count++; 
                                        }
                                        $stmt->close();
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div> 
        </div>
    </div>

    <!--**********************************
        Scripts
    ***********************************-->
    <!-- Required vendors -->
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

            // Effacer les attributs de l'URL
            var cleanUrl = window.location.pathname;
            var otherParams = ['classe', 'specialite', 'annee'].map(function(param) {
                var val = urlParams.get(param);
                return val ? param + '=' + encodeURIComponent(val) : null;
            }).filter(Boolean).join('&');
            
            if (otherParams) {
                cleanUrl += '?' + otherParams;
            }
            window.history.replaceState({}, document.title, cleanUrl);
        }
    });

    $(document).ready(function() {
        // Déclencher l'impression une fois que le document est prêt
        window.print();
    });
    </script>
</body>
</html>
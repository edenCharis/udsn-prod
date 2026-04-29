<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="scolarité"){

    // ── Mise à jour statut individuel ──────────────────────────────────────────
    if(isset($_GET['statut']) and isset($_GET['id'])){
        $id = intval($_GET['id']);
      $statut = ($_GET['statut'] === 'O' || $_GET['statut'] === '1') ? 'O' : 'N';

        $sql = "UPDATE utilisateur SET statut='$statut' WHERE id=$id";
        if($connexion->query($sql)){
            $userIP = $_SERVER['REMOTE_ADDR'];
            logUserAction($connexion,$_SESSION['id_user'],"Mise à jour statut d'un utilisateur",date("Y-m-d H:i:s"),$userIP," utilisateur : $id; valeur mise à jour : '$statut'");
            header("location: anonymat?sucess=opération effectuée avec succès");
        } else {
            header("location: anonymat?erreur=".$connexion->error);
        }
        exit;
    }

    // ── Activation / Désactivation GLOBALE par rôle ────────────────────────────
    if(isset($_GET['action_globale']) and isset($_GET['role_global'])){
        $role_global = $connexion->real_escape_string($_GET['role_global']);
        $statut_global = $_GET['action_globale'] === 'activer' ? 'O' : 'N';

        $sql = "UPDATE utilisateur SET statut='$statut_global' WHERE etab='".$_SESSION['etablissement']."' AND role='$role_global'";
        if($connexion->query($sql)){
            $userIP = $_SERVER['REMOTE_ADDR'];
            logUserAction($connexion,$_SESSION['id_user'],"Action globale sur le rôle $role_global",date("Y-m-d H:i:s"),$userIP,"Statut mis à jour : $statut_global");
            header("location: anonymat?sucess=Action globale effectuée avec succès&filtre_role=$role_global");
        } else {
            header("location: anonymat?erreur=".$connexion->error);
        }
        exit;
    }

    // ── Suppression compte ─────────────────────────────────────────────────────
    // (géré via traitement5, inchangé)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $_SESSION['univ'];?> - Scolarité de <?php echo $_SESSION['etablissement'];?></title>
    <link rel="icon" type="image/png" sizes="16x16" href="../administrateur/<?php echo $_SESSION['logo_univ']?>">
    <link rel="stylesheet" href="../vendor/bootstrap-select/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/skin.css">
    <link href="../vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
    <style>
        .filtre-roles { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:16px; align-items:center; }
        .filtre-roles .btn-filtre { border-radius:20px; font-size:13px; }
        .filtre-roles .btn-filtre.actif { font-weight:700; box-shadow:0 0 0 3px rgba(0,123,255,.3); }
        .panel-global { background:#f8f9fa; border:1px solid #dee2e6; border-radius:8px; padding:14px 18px; margin-bottom:18px; display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
        .panel-global label { margin:0; font-weight:600; }
        .badge-statut-O { background:#28a745; color:#fff; border-radius:10px; padding:2px 8px; font-size:11px; }
        .badge-statut-N { background:#dc3545; color:#fff; border-radius:10px; padding:2px 8px; font-size:11px; }
    </style>
</head>
<body>
    <div id="preloader">
        <div class="sk-three-bounce">
            <div class="sk-child sk-bounce1"></div>
            <div class="sk-child sk-bounce2"></div>
            <div class="sk-child sk-bounce3"></div>
        </div>
    </div>

    <div id="main-wrapper">
        <?php include "header.php"; ?>
        <?php include 'nav.html'; ?>

        <div class="content-body">
            <div class="container-fluid">

                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4>Gestion des profils d'utilisateur <i class="la la-user"></i></h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index">Tableau de Bord</a></li>
                        </ol>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="row tab-content">
                        <div id="list-view" class="tab-pane fade active show col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Liste des utilisateurs</h4>
                                    <a href="add-student.html" class="btn btn-primary" data-toggle="modal" data-target="#typeAgentModal">+ Ajouter</a>
                                </div>
                                <div class="card-body">

                                    <?php
                                    /* ── Filtre actif ── */
                                    $roles_disponibles = [
                                        'tous'        => 'Tous',
                                        'anonymat'    => 'Anonymat',
                                        'inscription' => 'Inscription',
                                        'gesnote'     => 'Gestion notes',
                                        'pvd'         => 'Président jury',
                                        'enseignant'  => 'Enseignants',
                                    ];
                                    $filtre_role = isset($_GET['filtre_role']) ? $_GET['filtre_role'] : 'tous';
                                    if(!array_key_exists($filtre_role, $roles_disponibles)) $filtre_role = 'tous';
                                    ?>

                                    <!-- ══ Boutons de filtre ══ -->
                                    <div class="filtre-roles">
                                        <span style="font-weight:600;">Filtrer par rôle :</span>
                                        <?php foreach($roles_disponibles as $val => $lib): ?>
                                            <a href="anonymat?filtre_role=<?php echo $val; ?>"
                                               class="btn btn-sm btn-filtre <?php echo $filtre_role===$val ? 'btn-primary actif' : 'btn-outline-secondary'; ?>">
                                                <?php echo $lib; ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>

                                    <!-- ══ Panel action globale ══ -->
                                    <?php if($filtre_role !== 'tous'): ?>
                                    <div class="panel-global">
                                        <label>Action globale pour le rôle <strong><?php echo htmlspecialchars($roles_disponibles[$filtre_role]); ?></strong> :</label>
                                        <a href="anonymat?action_globale=activer&role_global=<?php echo urlencode($filtre_role); ?>&filtre_role=<?php echo urlencode($filtre_role); ?>"
                                           class="btn btn-success btn-sm"
                                           onclick="return confirm('Activer TOUS les comptes du rôle <?php echo htmlspecialchars($roles_disponibles[$filtre_role]); ?> ?')">
                                            <i class="la la-check-circle"></i> Tout activer
                                        </a>
                                        <a href="anonymat?action_globale=desactiver&role_global=<?php echo urlencode($filtre_role); ?>&filtre_role=<?php echo urlencode($filtre_role); ?>"
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Désactiver TOUS les comptes du rôle <?php echo htmlspecialchars($roles_disponibles[$filtre_role]); ?> ?')">
                                            <i class="la la-times-circle"></i> Tout désactiver
                                        </a>
                                    </div>
                                    <?php endif; ?>

                                    <!-- ══ Tableau ══ -->
                                    <div class="table-responsive">
                                        <table id="example3" class="display" style="min-width: 845px">
                                            <thead>
                                                <tr>
                                                    <th>Photo</th>
                                                    <th>Nom(s) et Prénom(s)</th>
                                                    <th>ECUE</th>
                                                    <th>Classe</th>
                                                    <th>Parcours</th>
                                                    <th>Semestre</th>
                                                    <th>Examen</th>
                                                    <th>Login</th>
                                                    <th>Mot de passe</th>
                                                    <th>Rôle</th>
                                                    <th>Statut</th>
                                                    <th>Année universitaire</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                            /* ── Construction de la requête selon le filtre ── */
                                            $etab = $connexion->real_escape_string($_SESSION['etablissement']);

                                            if($filtre_role === 'tous'){
                                                $where_role = "(role='anonymat' OR role='inscription' OR role='gesnote' OR role='pvd' OR role='enseignant')";
                                            } elseif($filtre_role === 'enseignant'){
                                                $where_role = "role='enseignant'";
                                            } else {
                                                $where_role = "role='".$connexion->real_escape_string($filtre_role)."'";
                                            }

                                            $sql = "SELECT * FROM utilisateur WHERE etab='$etab' AND $where_role ORDER BY nom ASC";
                                            $resultat = $connexion->query($sql);

                                            while($type = $resultat->fetch_assoc()):
                                                $role_label = [
                                                    'anonymat'    => 'Anonymat',
                                                    'inscription' => 'Inscription',
                                                    'gesnote'     => 'Gestion notes',
                                                    'pvd'         => 'Président jury',
                                                    'enseignant'  => 'Enseignant',
                                                    'suivi'       => 'Suivi cours',
                                                ][$type['role']] ?? $type['role'];
                                            ?>
                                            <tr>
                                                <td><img class="rounded-circle" width="50" src="<?php echo htmlspecialchars($type['img']); ?>" alt=""></td>
                                                <td><?php echo htmlspecialchars(str_replace("+","'", $type['nom'])); ?></td>
                                                <td><?php echo ($type['ecue'] == null) ? '<span class="text-muted">—</span>' : htmlspecialchars(str_replace("+","'", $type['ecue'])); ?></td>
                                                <td><?php echo ($type['classe'] == null) ? '<span class="text-muted">—</span>' : htmlspecialchars(str_replace("+","'", $type['classe'])); ?></td>
                                                <td><?php echo ($type['parcours'] == null) ? '<span class="text-muted">—</span>' : htmlspecialchars(str_replace("+","'", $type['parcours'])); ?></td>
                                                <td><?php echo ($type['semestre'] == null) ? '<span class="text-muted">—</span>' : htmlspecialchars(str_replace("+","'", $type['semestre'])); ?></td>
                                                <td><?php echo ($type['examen'] == null) ? '<span class="text-muted">—</span>' : htmlspecialchars(str_replace("+","'", $type['examen'])); ?></td>
                                                <td><?php echo htmlspecialchars(str_replace("+","'", $type['login'])); ?></td>
                                                <td>************</td>
                                                <td><span class="badge badge-info"><?php echo $role_label; ?></span></td>
                                                <td>
                                                    <?php if($type['statut'] == 'O'): ?>
                                                        <span class="badge-statut-O">Actif</span>
                                                    <?php else: ?>
                                                        <span class="badge-statut-N">Inactif</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars(str_replace("+","'", $type['annee'])); ?></td>
                                                <td>
                                                    <?php
                                                    $back = urlencode("filtre_role=$filtre_role");
                                                    if($type['statut'] == 'O'): ?>
                                                        <a href="anonymat?statut=N&id=<?php echo $type['id']; ?>&filtre_role=<?php echo urlencode($filtre_role); ?>"
                                                           class="btn btn-warning btn-sm mb-1">
                                                            <i class="la la-toggle-off"></i> Désactiver
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="anonymat?statut=O&id=<?php echo $type['id']; ?>&filtre_role=<?php echo urlencode($filtre_role); ?>"
                                                           class="btn btn-success btn-sm mb-1">
                                                            <i class="la la-toggle-on"></i> Activer
                                                        </a>
                                                    <?php endif; ?>
                                                    <a class="btn btn-danger btn-sm eden"
                                                       data-id="<?php echo $type['id']; ?>"
                                                       data-toggle="modal" data-target="#delete_employee">
                                                        <i class="la la-trash-o"></i> Supprimer
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div><!-- /table-responsive -->

                                </div><!-- /card-body -->
                            </div><!-- /card -->
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- Content body end -->

        <!-- ══ Modal message ══ -->
        <div class="modal" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><?php echo $_SESSION['univ'];?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body" id="messageBody"></div>
                </div>
            </div>
        </div>

        <!-- ══ Modal ajout utilisateur ══ -->
        <div class="modal fade" id="typeAgentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Enregistrement d'un nouvel utilisateur</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <form id="typeAgentForm" method="post" action="traitement5" enctype="multipart/form-data">
                            <div class="input-group mb-3">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" name="img">
                                    <label class="custom-file-label">Photo</label>
                                </div>
                                <div class="input-group-append"><span class="input-group-text">Importer</span></div>
                            </div>

                            <label for="nom">Agent</label>
                            <select id="classeInput" class="disabling-options form-control mb-3" name="nom">
                                <option selected=""></option>
                                <?php
                                $sql = "SELECT * FROM enseignant WHERE etab='".$_SESSION['etablissement']."'";
                                $res = $connexion->query($sql);
                                while($e = $res->fetch_assoc()):
                                ?>
                                <option><?php echo htmlspecialchars(str_replace("+","'", $e['nom']." ".$e['prenom'])); ?></option>
                                <?php endwhile; ?>
                            </select>

                            <div class="form-group">
                                <label>Login</label>
                                <input type="text" class="form-control" name="login" placeholder="Nom de connexion" required>
                            </div>
                            <div class="form-group">
                                <label>Mot de passe</label>
                                <input type="text" class="form-control" name="mdp" placeholder="Mot de passe" required>
                            </div>

                            <!-- Classe optionnelle -->
                            <div class="mb-3">
                                <label><input type="checkbox" id="classeCheck" onchange="toggleClasseInput()"> Préciser la classe ?</label>
                                <div id="classeInputContainer" style="display:none; margin-top:6px;">
                                    <select class="form-control" name="classe">
                                        <option selected=""></option>
                                        <?php
                                        $sql = "SELECT * FROM classe WHERE etab='".$_SESSION['etablissement']."'";
                                        $res = $connexion->query($sql);
                                        while($e = $res->fetch_assoc()):
                                        ?>
                                        <option value="<?php echo $e['libelle'];?>"><?php echo htmlspecialchars(str_replace("+","'", $e['libelle'])); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- ECUE optionnelle -->
                            <div class="mb-3">
                                <label><input type="checkbox" id="classeCheck1" onchange="toggleClasseInput()"> Préciser l'ECUE ?</label>
                                <div id="classeInputContainer1" style="display:none; margin-top:6px;">
                                    <select class="form-control" name="ecue">
                                        <option selected=""></option>
                                        <?php
                                        $sql = "SELECT * FROM ecue WHERE etab='".$_SESSION['etablissement']."'";
                                        $res = $connexion->query($sql);
                                        while($e = $res->fetch_assoc()):
                                        ?>
                                        <option value="<?php echo $e['libelle'];?>"><?php echo htmlspecialchars(str_replace("+","'", $e['libelle'])); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Rôle -->
                            <div class="input-group mb-3">
                                <div class="input-group-prepend"><label class="input-group-text">Rôle</label></div>
                                <select id="type" class="form-control" name="role" required>
                                    <option selected=""></option>
                                    <option value="anonymat">Agent d'anonymat</option>
                                    <option value="inscription">Agent d'inscription</option>
                                    <option value="gesnote">Gestionnaire de notes</option>
                                    <option value="pvd">Président de jury de délibération</option>
                                    <option value="enseignant">Enseignant</option>
                                    <option value="suivi">Suivi d'avancement des cours</option>
                                </select>
                            </div>

                            <!-- Champs PVD -->
                            <div id="champsSaisie" style="display:none;" class="mb-3 p-3 border rounded">
                                <label>Parcours :</label>
                                <select class="form-control mb-2" name="parcours">
                                    <option selected=""></option>
                                    <?php
                                    $sql = "SELECT * FROM parcours WHERE etab='".$_SESSION['lib_etab']."'";
                                    $res = $connexion->query($sql);
                                    while($e = $res->fetch_assoc()):
                                    ?>
                                    <option value="<?php echo $e['libelle'];?>"><?php echo $e['libelle'];?></option>
                                    <?php endwhile; ?>
                                </select>
                                <label>Semestre :</label>
                                <select class="form-control mb-2" name="semestre">
                                    <option selected=""></option>
                                    <?php
                                    $sql = "SELECT * FROM semestre";
                                    $res = $connexion->query($sql);
                                    while($e = $res->fetch_assoc()):
                                    ?>
                                    <option value="<?php echo $e['libelle'];?>"><?php echo $e['libelle'];?></option>
                                    <?php endwhile; ?>
                                </select>
                                <label>Type d'examen :</label>
                                <select class="form-control mb-2" name="examen">
                                    <option value=""></option>
                                    <option value="ordinaire">Ordinaire</option>
                                    <option value="rattrapage">Rattrapage</option>
                                </select>
                                <div class="form-group">
                                    <label>Début délibération</label>
                                    <input type="date" class="form-control" name="debut">
                                </div>
                                <div class="form-group">
                                    <label>Fin délibération</label>
                                    <input type="date" class="form-control" name="fin">
                                </div>
                            </div>

                            <!-- Année -->
                            <div class="input-group mb-3">
                                <div class="input-group-prepend"><label class="input-group-text">Année académique</label></div>
                                <select class="form-control" name="annee" required>
                                    <option selected=""></option>
                                    <?php
                                    $sql = "SELECT * FROM annee";
                                    $res = $connexion->query($sql);
                                    while($e = $res->fetch_assoc()):
                                    ?>
                                    <option value="<?php echo $e['libelle'];?>"><?php echo $e['libelle'];?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ Modal suppression ══ -->
        <div id="delete_employee" class="modal" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Suppression d'un utilisateur</h4>
                    </div>
                    <div class="modal-body">
                        <p>La suppression de ce compte entraînera aussi la suppression de toutes les informations liées.</p>
                        <p>Voulez-vous vraiment supprimer cet utilisateur ?</p>
                        <button type="button" id="lien" class="btn btn-danger">Supprimer</button>
                        <a href="" class="btn btn-primary" data-dismiss="modal">Annuler</a>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /main-wrapper -->

    <div class="footer">
        <div class="copyright">
            <p>Copyright © Designed &amp; Développé par <a href="https://www.cet-up.com" target="_blank">CETUP</a> 2023</p>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../vendor/global/global.min.js"></script>
    <script src="../vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="../js/custom.min.js"></script>
    <script src="../vendor/raphael/raphael.min.js"></script>
    <script src="../vendor/morris/morris.min.js"></script>
    <script src="../vendor/peity/jquery.peity.min.js"></script>
    <script src="../js/dashboard/dashboard-2.js"></script>
    <script src="../vendor/svganimation/vivus.min.js"></script>
    <script src="../vendor/svganimation/svg.animation.js"></script>
    <script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../js/plugins-init/datatables.init.js"></script>

    <script>
    /* ── Toggles classe/ECUE ── */
    function toggleClasseInput() {
        var cc = document.getElementById("classeCheck");
        document.getElementById("classeInputContainer").style.display = cc.checked ? "block" : "none";
        var cc1 = document.getElementById("classeCheck1");
        document.getElementById("classeInputContainer1").style.display = cc1.checked ? "block" : "none";
    }

    /* ── Affichage champs PVD ── */
    document.getElementById("type").addEventListener("change", function() {
        document.getElementById("champsSaisie").style.display = (this.value === "pvd") ? "block" : "none";
    });

    /* ── Modal message erreur/succès ── */
    $(document).ready(function() {
        var urlParams = new URLSearchParams(window.location.search);
        var erreur = urlParams.get('erreur');
        var success = urlParams.get('sucess');
        if (erreur || success) {
            var message = erreur ? "Erreur : " + erreur : "✔ " + success;
            $('#messageBody').text(message);
            $('#messageModal').modal('show');
            window.history.replaceState({}, document.title, window.location.pathname + (urlParams.get('filtre_role') ? '?filtre_role='+urlParams.get('filtre_role') : ''));
        }
    });

    /* ── Suppression avec id ── */
    $(document).ready(function() {
        var idToDelete = null;
        $('.btn.btn-danger.eden').on('click', function() {
            idToDelete = $(this).data('id');
        });
        $('#lien').on('click', function() {
            if(idToDelete) {
                window.location.href = 'traitement5?supcompte=' + idToDelete;
            }
        });
    });
    </script>

</body>
</html>
<?php
} else {
    header("location: ../login");
}
?>
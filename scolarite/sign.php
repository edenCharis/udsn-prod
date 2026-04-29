

<?php 
include '../php/connexion.php';
include '../php/lib.php';

session_start();

if( $_SESSION['id'] == session_id() and  $_SESSION['role']=="scolarité"){


    
if ( isset($_POST['signature_data'])) {

    $signatureData = $_POST['signature_data'];
    $signataire=$_POST["signataire"];
    $etab=$_SESSION["etablissement"];
    $type=$_POST["type"];


    $sql1="select id from sign where type='$type' and etab='$etab'";
    $r =$connexion->query($sql1);
    if($r->num_rows > 0){

        while($t=$r->fetch_assoc()){
            $id =$t["id"];
        }

       if( $connexion->query("update sign set signature='$signatureData' where id=$id")){

        header("location: sign?id=$type");
    } else {
        header("location: sign?erreur=$connexion->error");
    }
        
    }else{
        
    if (!empty($signatureData)) {
        $signatureData = mysqli_real_escape_string($connexion, $signatureData);

       
        $query = "INSERT INTO sign (signature,signataire,etab,type) VALUES ('$signatureData',$signataire,'$etab','$type')";

        if (mysqli_query($connexion, $query)) {
           
            $id = mysqli_insert_id($connexion);

             header("location: sign?id=$type");
        } else {
            header("location: sign?erreur=$connexion->error");
        }
    } else {
        header("location: sign?erreur=Aucune signature soumise");
    }

    }


}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $_SESSION['univ'];?> - Scolarité de  <?php echo $_SESSION['etablissement'];?> </title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <link rel="icon" type="image/png" sizes="16x16" href="../administrateur/<?php echo  $_SESSION['logo_univ']?>">
	<link rel="stylesheet" href="../vendor/bootstrap-select/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="../css/style.css">
	<link rel="stylesheet" href="../css/skin.css">

    
    <link rel="stylesheet" href="../vendor/select2/css/select2.min.css">

    <link href="../vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">

    <style>
   
        #signature-pad canvas {
            border: 1px solid #000;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>



<div  class="container mt-5">
<div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h3>Enregistrement d'une signature éléctronique</h3>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../scolarite/">Scolarité</a></li>
                        </ol>
                    </div>
                </div>
    <div class="row">
        <div class="col-md-6">
            <h2>Signez ici :</h2>
            <div class="card">
                <div class="card-body">
                    <canvas id="signature-pad" class="border" width="500" height="200"></canvas>
                    <form id="signature_form" action="sign" method="post">
        <input type="hidden" id="signature_data" name="signature_data">
        <select   class="form-control form-control-lg" name="signataire"  recquired>
                                              <option value=""></option>
                                              <?php 
                                                     $sql="select * from enseignant where etab='".$_SESSION["etablissement"]."'";
                                                     $resultat=$connexion->query($sql);
                                                     while($etablissement =$resultat->fetch_assoc()){
                                             ?>
                                              <option value="<?php echo $etablissement["id"]; ?>"><?php echo  (($etablissement["grade"] !== "Aucun") ? $etablissement["grade"] : "")." ".str_replace("+","'",$etablissement['nom']." ".$etablissement["prenom"]);?></option>
                                              <?php }?>
                                         </select>
                                         <select    class="form-control form-control-lg" name="type"  recquired>
                                            <option value=""></option>
                                              
                                              <option>Note de service</option>
                                              <option>Diplome</option>
                                              
                                         </select>
        <button id="sav" type="submit" name="save" class="btn btn-primary"> Enregistrer</button>
    </form>
   
                </div>
                <?php 
if(isset($_GET["id"])){
    $id=$_GET["id"];
    $signature_blob = getSignatureData($connexion,$id,$_SESSION["etablissement"]);


    echo '<img src='.$signature_blob.' alt="Signature">';
    echo 'Signature Enregistrée. '.$id;
    
}



?>
            </div>

        
        </div>
    </div>
</div>



<div class="modal" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageModalLabel"><?php echo $_SESSION['univ'];?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="messageBody">
                <!-- Contenu du message -->
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/1.5.3/signature_pad.min.js"></script>
<script>
    var canvas = document.getElementById('signature-pad');
    var signaturePad = new SignaturePad(canvas);

    document.getElementById('sav').addEventListener('click', function () {
        var dataURL = signaturePad.toDataURL();
        document.getElementById('signature_data').value = dataURL;
        document.getElementById('signature_form').submit();
    });
</script>

<script>
    $(document).ready(function() {
        // Vérifier si les attributs "erreur" ou "success" sont présents dans l'URL
        var urlParams = new URLSearchParams(window.location.search);
        var erreur = urlParams.get('erreur');
        var success = urlParams.get('sucess');

        // Afficher le modal si l'un des attributs est présent
        if (erreur || success) {
            var message = erreur ? "Erreur : " + erreur : "Message : " + success;
            $('#messageBody').text(message);
            $('#messageModal').modal('show');

            // Effacer les attributs de l'URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });
    </script>

</body>
</html>
<?php

}else{
    header("location: ../login");
}?>
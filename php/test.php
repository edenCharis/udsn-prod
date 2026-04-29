<?php 

include '../php/connexion.php';
include '../php/lib.php';

$ecue = getEcuesForUE(4,6,$connexion);

foreach ($ecue as $cle => $valeur) {
    echo $cle;
}

?>
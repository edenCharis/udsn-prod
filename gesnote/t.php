<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entête de Tableau</title>
    <style>
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .small-column {
            width: 40px; /* Ajustez la largeur selon vos besoins */
        }
    </style>
</head>
<body>

<table>
    <thead>
        <tr>
            <th rowspan="3">Nom</th>
            <th rowspan="3">Prénom</th>

            <th colspan="10">UE</th>
        

            <th colspan="4" rowspan="2">UE 2</th>
            <th rowspan="3">Points</th>
            <th rowspan="3">UE validées sur 10</th>
            <th rowspan="3">Moyenne Generale</th>
            <th rowspan="3">Decision du jury</th>
            
        </tr>
        <tr>
            <th colspan="3">ECUE 1</th>
            <th colspan="3">ECUE 2</th>
            <th colspan="3">ECUE 3</th>
            <th rowspan="2">Moy UE</th>
           
          
            <!-- Ajoutez d'autres colonnes ECUE selon vos besoins -->
        </tr>
        <tr>
            <th class="small-column">CC</th>
            <th class="small-column">EX</th>
            <th class="small-column">MOY</th>
            <th class="small-column">CC</th>
            <th class="small-column">EX</th>
            <th class="small-column">MOY</th>
            <th class="small-column">CC</th>
            <th class="small-column">EX</th>
            <th class="small-column">MOY</th>
            <th class="small-column">CC</th>
            <th class="small-column">EX</th>
            <th class="small-column">MOY</th>
            <th class="small-column"> MOY UE</th>
            <!-- Ajoutez d'autres colonnes CC, EX, MOY selon vos besoins -->
        </tr>
    </thead>
    <tbody>
        <tr>
            <th>h</th>
            <th>i</th>
            <th class="small-column">CC</th>
            <th class="small-column">EX</th>
            <th class="small-column">MOY</th>
            <th class="small-column">CC</th>
            <th class="small-column">EX</th>
            <th class="small-column">MOY</th>
            <th class="small-column">CC</th>
            <th class="small-column">EX</th>
            <th class="small-column">MOY</th>
            <th  class="small-column">d</th>
            <th class="small-column">CC</th>
            <th class="small-column">EX</th>
            <th class="small-column">MOY</th>
            <th class="small-column"> MOY UE</th>
        </tr>
      
    </tbody>
</table>



</body>
</html>

</body>
</html>

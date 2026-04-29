<?php 
include '../php/connexion.php';
session_start();

if($_SESSION['id'] == session_id() && $_SESSION['role'] == "daarhspe") {
    
    // ÉTAPE 1: DÉTECTER LES DOUBLONS (même enseignant + même année = plusieurs contrats)
    echo '<div class="container mt-4">';
    echo '<h2>Analyse des Contrats Dupliqués</h2>';
    echo '<p class="text-muted">Un enseignant ne doit avoir qu\'un seul contrat par année académique.</p>';
    
    // Requête pour trouver les enseignants avec plusieurs contrats la même année
    $sql_detect = "SELECT 
        e.id as enseignant_id,
        e.nom,
        e.prenom,
        c.annee,
        COUNT(DISTINCT c.enseignant) as nombre_contrats,
        GROUP_CONCAT(DISTINCT c.numero_contrat ORDER BY c.numero_contrat SEPARATOR '|||') as contrats_list
    FROM enseignant e
    JOIN contrat c ON c.enseignant = e.id
    GROUP BY e.id, c.annee
    HAVING COUNT(DISTINCT c.enseignant) > 1
    ORDER BY e.nom, e.prenom, c.annee";
    
    $result_detect = $connexion->query($sql_detect);
    
    if($result_detect && $result_detect->num_rows > 0) {
        echo '<div class="alert alert-warning">';
        echo '<strong>⚠️ Problèmes détectés:</strong> ' . $result_detect->num_rows . ' cas d\'enseignants ayant plusieurs contrats pour la même année.';
        echo '</div>';
        
        echo '<table class="table table-bordered table-striped">';
        echo '<thead class="table-dark">';
        echo '<tr>';
        echo '<th>Enseignant</th>';
        echo '<th>Année</th>';
        echo '<th>Nombre de Contrats</th>';
        echo '<th>Détails des Contrats et ECUE</th>';
        echo '<th>Action</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        while($row = $result_detect->fetch_assoc()) {
            echo '<tr>';
            echo '<td><strong>' . htmlspecialchars(str_replace("+", "'", $row['nom'])) . ' ' . htmlspecialchars(str_replace("+", "'", $row['prenom'])) . '</strong></td>';
            echo '<td>' . htmlspecialchars($row['annee']) . '</td>';
            echo '<td><span class="badge bg-danger fs-6">' . $row['nombre_contrats'] . ' contrats</span></td>';
            
            // Récupérer les détails des ECUE pour chaque contrat
            echo '<td>';
            $contrats_array = explode('|||', $row['contrats_list']);
            $total_ecues = 0;
            
            foreach($contrats_array as $num_contrat) {
                $sql_ecue = "SELECT 
                    c.numero_contrat,
                    c.date_signature,
                    GROUP_CONCAT(DISTINCT ec.code_ecue ORDER BY ec.code_ecue SEPARATOR ', ') as codes_ecues,
                    GROUP_CONCAT(DISTINCT ec.libelle ORDER BY ec.code_ecue SEPARATOR '<br>• ') as ecues_details,
                    COUNT(DISTINCT ec.code_ecue) as nb_ecues
                FROM contrat c
                LEFT JOIN contrat_couverture cv ON c.numero_contrat = cv.contrat
                LEFT JOIN ecue ec ON cv.ecue = ec.code_ecue
                WHERE c.numero_contrat = ?
                GROUP BY c.numero_contrat";
                
                $stmt_ecue = $connexion->prepare($sql_ecue);
                $stmt_ecue->bind_param("s", $num_contrat);
                $stmt_ecue->execute();
                $result_ecue = $stmt_ecue->get_result();
                
                if($ecue_row = $result_ecue->fetch_assoc()) {
                    $nb_ecues = $ecue_row['nb_ecues'] ?? 0;
                    $total_ecues += $nb_ecues;
                    
                    echo '<div class="mb-3 p-2 border rounded bg-light">';
                    echo '<strong>📄 Contrat: ' . htmlspecialchars($num_contrat) . '</strong><br>';
                    echo '<small class="text-muted">Date: ' . htmlspecialchars($ecue_row['date_signature'] ?? 'Non définie') . '</small><br>';
                    echo '<span class="badge bg-info">' . $nb_ecues . ' ECUE(s)</span><br>';
                    
                    if($nb_ecues > 0) {
                        echo '<small><strong>Codes:</strong> ' . htmlspecialchars($ecue_row['codes_ecues']) . '</small><br>';
                        echo '<small class="text-secondary">• ' . $ecue_row['ecues_details'] . '</small>';
                    } else {
                        echo '<small class="text-danger">Aucun ECUE assigné</small>';
                    }
                    echo '</div>';
                }
                $stmt_ecue->close();
            }
            
            echo '<div class="alert alert-info mt-2 mb-0">';
            echo '<strong>Total: ' . $total_ecues . ' ECUE(s) au total sur ' . count($contrats_array) . ' contrats</strong>';
            echo '</div>';
            echo '</td>';
            
            echo '<td>';
            echo '<button class="btn btn-primary btn-sm consolidate-btn" 
                    data-enseignant="' . $row['enseignant_id'] . '" 
                    data-annee="' . htmlspecialchars($row['annee']) . '" 
                    data-nom="' . htmlspecialchars(str_replace("+", "'", $row['nom']) . ' ' . str_replace("+", "'", $row['prenom'])) . '">
                    🔧 Fusionner
                  </button>';
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        
        // Bouton pour tout consolider automatiquement
        echo '<div class="text-center mt-4 mb-4">';
        echo '<button id="consolidate-all" class="btn btn-success btn-lg">
                🔧 Consolider TOUS les Contrats Automatiquement
              </button>';
        echo '<p class="text-muted mt-2">Cette action fusionnera tous les contrats dupliqués en un seul contrat par enseignant/année</p>';
        echo '</div>';
        
    } else {
        echo '<div class="alert alert-success">';
        echo '✅ Aucun doublon détecté ! Tous les enseignants ont un seul contrat par année académique.';
        echo '</div>';
    }
    
    echo '<div id="result-message" class="mt-4"></div>';
    echo '</div>';
    
    echo '<script>
    document.addEventListener("DOMContentLoaded", function() {
        
        // Fonction pour consolider un enseignant spécifique
        document.querySelectorAll(".consolidate-btn").forEach(btn => {
            btn.addEventListener("click", function() {
                const enseignant = this.getAttribute("data-enseignant");
                const annee = this.getAttribute("data-annee");
                const nom = this.getAttribute("data-nom");
                
                if(confirm("Êtes-vous sûr de vouloir fusionner les contrats de " + nom + " pour l\'année " + annee + " ?\\n\\nTous les ECUE seront regroupés dans un seul contrat.")) {
                    this.disabled = true;
                    this.innerHTML = "⏳ En cours...";
                    consolidateContracts(enseignant, annee, this);
                }
            });
        });
        
        // Bouton pour tout consolider
        const consolidateAllBtn = document.getElementById("consolidate-all");
        if(consolidateAllBtn) {
            consolidateAllBtn.addEventListener("click", function() {
                if(confirm("⚠️ ATTENTION: Cette action va consolider TOUS les contrats dupliqués détectés.\\n\\nCela signifie:\\n- Fusion de tous les contrats multiples en un seul par enseignant/année\\n- Tous les ECUE seront regroupés\\n- Les contrats en double seront supprimés\\n\\nContinuer ?")) {
                    this.disabled = true;
                    this.innerHTML = "⏳ Consolidation en cours...";
                    consolidateAllContracts(this);
                }
            });
        }
        
        function consolidateContracts(enseignant, annee, button) {
            fetch("consolidate_contracts.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "action=consolidate_one&enseignant=" + enseignant + "&annee=" + encodeURIComponent(annee)
            })
            .then(response => response.json())
            .then(data => {
                const resultDiv = document.getElementById("result-message");
                if(data.success) {
                    resultDiv.innerHTML = "<div class=\'alert alert-success\'><strong>✅ Succès!</strong> " + data.message + "</div>";
                    setTimeout(() => location.reload(), 2000);
                } else {
                    resultDiv.innerHTML = "<div class=\'alert alert-danger\'><strong>❌ Erreur:</strong> " + data.message + "</div>";
                    if(button) {
                        button.disabled = false;
                        button.innerHTML = "🔧 Fusionner";
                    }
                }
            })
            .catch(error => {
                console.error("Erreur:", error);
                document.getElementById("result-message").innerHTML = 
                    "<div class=\'alert alert-danger\'><strong>❌ Erreur:</strong> Erreur lors de la consolidation</div>";
                if(button) {
                    button.disabled = false;
                    button.innerHTML = "🔧 Fusionner";
                }
            });
        }
        
        function consolidateAllContracts(button) {
            fetch("consolidate_contracts.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "action=consolidate_all"
            })
            .then(response => response.json())
            .then(data => {
                const resultDiv = document.getElementById("result-message");
                if(data.success) {
                    resultDiv.innerHTML = "<div class=\'alert alert-success\'><strong>✅ Succès!</strong> " + data.message + "</div>";
                    setTimeout(() => location.reload(), 2000);
                } else {
                    resultDiv.innerHTML = "<div class=\'alert alert-danger\'><strong>❌ Erreur:</strong> " + data.message + "</div>";
                    if(button) {
                        button.disabled = false;
                        button.innerHTML = "🔧 Consolider TOUS les Contrats Automatiquement";
                    }
                }
            })
            .catch(error => {
                console.error("Erreur:", error);
                document.getElementById("result-message").innerHTML = 
                    "<div class=\'alert alert-danger\'><strong>❌ Erreur:</strong> Erreur lors de la consolidation</div>";
                if(button) {
                    button.disabled = false;
                    button.innerHTML = "🔧 Consolider TOUS les Contrats Automatiquement";
                }
            });
        }
    });
    </script>';
    
} else {
    echo '<div class="alert alert-danger">Accès non autorisé.</div>';
}

$connexion->close();
?>
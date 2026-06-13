<?php
/**
 * Migration Helper Script
 * 
 * Ce script aide à identifier les pages qui utilisent des logos en dur
 * et à les migrer vers le système de logos personnalisables
 * 
 * À exécuter une seule fois pour analyser et lister les fichiers à modifier
 */

// Fichiers contenant des références à des logos codés en dur
$patterns = [
    'images/univ.png' => [],
    'images/logo' => [],
    '../images/univ.png' => [],
];

$root_dir = __DIR__;

function scanDirectory($dir, $patterns) {
    $results = [];
    
    try {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $file) {
            if ($file->isFile() && (strpos($file->getPathname(), '.php') !== false)) {
                $content = file_get_contents($file->getPathname());
                
                foreach ($patterns as $pattern => $dummy) {
                    if (strpos($content, $pattern) !== false) {
                        $relative_path = str_replace($dir . '/', '', $file->getPathname());
                        if (!isset($results[$pattern])) {
                            $results[$pattern] = [];
                        }
                        $results[$pattern][] = $relative_path;
                    }
                }
            }
        }
    } catch (Exception $e) {
        echo "Erreur lors du scan: " . $e->getMessage() . "\n";
    }
    
    return $results;
}

// Exécuter le scan
$found_files = scanDirectory($root_dir, $patterns);

// Afficher les résultats
echo "=== Migration des Logos - Rapport d'analyse ===\n\n";

$total_files = 0;
foreach ($found_files as $pattern => $files) {
    if (!empty($files)) {
        echo "Pattern trouvé: " . $pattern . "\n";
        echo "Nombre de fichiers: " . count($files) . "\n";
        echo "Fichiers concernés:\n";
        
        foreach ($files as $file) {
            echo "  - " . $file . "\n";
            $total_files++;
        }
        echo "\n";
    }
}

echo "Total de fichiers à migrer: " . $total_files . "\n";
echo "\n=== Instructions de migration ===\n";
echo "Pour chaque fichier, remplacez les références codées en dur par:\n";
echo "\nOption 1 (Recommandée - Classe LogoConfig):\n";
echo "<?php\n";
echo "include_once 'config/logo_config.php';\n";
echo "\$logoConfig = getLogoConfig();\n";
echo "\$logo = \$logoConfig->getDefaultLogo();\n";
echo "?>\n";
echo "<img src=\"<?php echo htmlspecialchars(\$logo); ?>\" alt=\"Logo\">\n";

echo "\nOption 2 (Fonctions helper):\n";
echo "<?php include_once 'php/lib.php'; ?>\n";
echo "<img src=\"<?php echo getDefaultLogo(); ?>\" alt=\"Logo\">\n";

echo "\nOption 3 (Pages authentifiées - Session):\n";
echo "<img src=\"<?php echo 'administrateur/' . htmlspecialchars(\$_SESSION['logo_univ'] ?? 'images/univ.png'); ?>\" alt=\"Logo\">\n";

?>

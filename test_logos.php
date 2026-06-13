<?php
/**
 * Script de test du système de logos
 * Vérifier que tout est correctement installé
 */

$tests_passed = 0;
$tests_failed = 0;
$issues = [];

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║         TEST DU SYSTÈME DE LOGOS - UDSN                       ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Test 1: Fichiers créés
echo "1️⃣  Vérification des fichiers créés...\n";
$required_files = [
    'config/logo_config.php',
    'administrateur/parametre_logos.php',
    'php/api_logos.php',
    'config/migration_helper.php',
    'LOGO_PERSONALIZATION_GUIDE.md',
    'QUICK_START_LOGOS.md',
    'CHANGELOG_LOGOS.md',
    'examples_api_logos.html',
    'README_LOGOS_FINAL.md'
];

$base_path = __DIR__;
foreach ($required_files as $file) {
    $full_path = $base_path . '/' . $file;
    if (file_exists($full_path)) {
        echo "   ✅ $file\n";
        $tests_passed++;
    } else {
        echo "   ❌ $file (MANQUANT)\n";
        $tests_failed++;
        $issues[] = "Fichier manquant: $file";
    }
}

echo "\n";

// Test 2: Fichiers modifiés
echo "2️⃣  Vérification des modifications...\n";
$modified_files = [
    'login.php' => 'config/logo_config.php',
    'index.php' => 'config/logo_config.php',
    'connexion.php' => 'config/logo_config.php',
    'php/lib.php' => 'getDefaultLogo'
];

foreach ($modified_files as $file => $content) {
    $full_path = $base_path . '/' . $file;
    if (file_exists($full_path)) {
        $file_content = file_get_contents($full_path);
        if (strpos($file_content, $content) !== false) {
            echo "   ✅ $file modifié correctement\n";
            $tests_passed++;
        } else {
            echo "   ⚠️  $file existe mais ne contient pas '$content'\n";
            $tests_failed++;
            $issues[] = "$file n'a pas été modifié correctement";
        }
    } else {
        echo "   ❌ $file (MANQUANT)\n";
        $tests_failed++;
        $issues[] = "Fichier manquant: $file";
    }
}

echo "\n";

// Test 3: Classe LogoConfig
echo "3️⃣  Vérification de la classe LogoConfig...\n";
$config_file = $base_path . '/config/logo_config.php';
if (file_exists($config_file)) {
    $content = file_get_contents($config_file);
    
    $methods = [
        'getDefaultLogo',
        'getLogoForUser',
        'getLogoFromSession',
        'updateUniversityLogo',
        'getAllLogos',
        'getDefaultFavicon'
    ];
    
    foreach ($methods as $method) {
        if (strpos($content, "function $method") !== false || strpos($content, "public function $method") !== false) {
            echo "   ✅ Méthode: $method()\n";
            $tests_passed++;
        } else {
            echo "   ❌ Méthode manquante: $method()\n";
            $tests_failed++;
            $issues[] = "Méthode manquante: $method()";
        }
    }
} else {
    echo "   ❌ logo_config.php introuvable\n";
    $tests_failed++;
    $issues[] = "config/logo_config.php introuvable";
}

echo "\n";

// Test 4: Dossier logo
echo "4️⃣  Vérification du dossier de stockage...\n";
$logo_dir = $base_path . '/administrateur/logo';
if (is_dir($logo_dir)) {
    echo "   ✅ Dossier administrateur/logo existe\n";
    $tests_passed++;
} else {
    echo "   ℹ️  Dossier administrateur/logo sera créé à la première utilisation\n";
}

echo "\n";

// Test 5: Sécurité des fichiers
echo "5️⃣  Vérification de la sécurité...\n";
$security_checks = [
    'login.php' => 'htmlspecialchars',
    'index.php' => 'htmlspecialchars',
    'connexion.php' => 'htmlspecialchars',
    'administrateur/parametre_logos.php' => 'htmlspecialchars',
];

foreach ($security_checks as $file => $check) {
    $full_path = $base_path . '/' . $file;
    if (file_exists($full_path)) {
        $file_content = file_get_contents($full_path);
        if (strpos($file_content, $check) !== false) {
            echo "   ✅ $file - Sécurité OK (htmlspecialchars)\n";
            $tests_passed++;
        } else {
            echo "   ⚠️  $file - Sécurité à vérifier\n";
            $tests_failed++;
            $issues[] = "$file manque htmlspecialchars";
        }
    }
}

echo "\n";

// Test 6: API
echo "6️⃣  Vérification de l'API...\n";
$api_file = $base_path . '/php/api_logos.php';
if (file_exists($api_file)) {
    $api_content = file_get_contents($api_file);
    
    $endpoints = [
        'get_default',
        'get_university_logo',
        'get_user_logo',
        'list_all',
        'get_favicon'
    ];
    
    foreach ($endpoints as $endpoint) {
        if (strpos($api_content, "'$endpoint'") !== false || strpos($api_content, "\"$endpoint\"") !== false) {
            echo "   ✅ Endpoint: $endpoint\n";
            $tests_passed++;
        } else {
            echo "   ❌ Endpoint manquant: $endpoint\n";
            $tests_failed++;
            $issues[] = "Endpoint manquant: $endpoint";
        }
    }
} else {
    echo "   ❌ api_logos.php introuvable\n";
    $tests_failed++;
    $issues[] = "php/api_logos.php introuvable";
}

echo "\n";

// Test 7: Documentation
echo "7️⃣  Vérification de la documentation...\n";
$docs = [
    'LOGO_PERSONALIZATION_GUIDE.md' => 'Configuration centralisée',
    'QUICK_START_LOGOS.md' => 'Utilisation',
    'CHANGELOG_LOGOS.md' => 'Changements',
    'examples_api_logos.html' => 'Exemples'
];

foreach ($docs as $doc => $keyword) {
    $full_path = $base_path . '/' . $doc;
    if (file_exists($full_path)) {
        $content = file_get_contents($full_path);
        if (strlen($content) > 100) {
            echo "   ✅ $doc\n";
            $tests_passed++;
        }
    }
}

echo "\n";

// Résumé
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                        RÉSUMÉ DES TESTS                        ║\n";
echo "╠════════════════════════════════════════════════════════════════╣\n";
printf("║ Tests réussis:    %45d ✅ ║\n", $tests_passed);
printf("║ Tests échoués:    %45d ❌ ║\n", $tests_failed);
printf("║ Total:            %45d    ║\n", $tests_passed + $tests_failed);
echo "╚════════════════════════════════════════════════════════════════╝\n";

if ($tests_failed === 0) {
    echo "\n✅ TOUS LES TESTS SONT PASSÉS ! LE SYSTÈME EST PRÊT À L'EMPLOI.\n";
} else {
    echo "\n⚠️  ATTENTION: $tests_failed test(s) ont échoué.\n";
    echo "\nProblèmes détectés:\n";
    foreach ($issues as $issue) {
        echo "  - $issue\n";
    }
}

echo "\n";
echo "ℹ️  PROCHAINES ÉTAPES:\n";
echo "  1. Accédez à /administrateur/parametre_logos.php\n";
echo "  2. Uploadez un logo pour chaque université\n";
echo "  3. Testez la connexion - Le logo s'affichera\n";
echo "  4. Consultez QUICK_START_LOGOS.md pour plus d'infos\n";
echo "\n";

?>

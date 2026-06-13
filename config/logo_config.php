<?php
/**
 * Configuration centralisée des logos de l'application
 * Ce fichier gère tous les chemins et les configurations liées aux logos
 */

// Inclure la connexion à la base de données si elle n'est pas déjà incluse
if (!isset($connexion)) {
    include_once __DIR__ . '/../php/connexion.php';
}

class LogoConfig {
    
    private $connexion;
    private $logo_cache = [];

    public function __construct($connexion = null) {
        global $connexion;
        $this->connexion = $connexion ?? $GLOBALS['connexion'] ?? null;
    }

    /**
     * Obtenir le logo par défaut de l'application
     * @return string Chemin du logo par défaut
     */
    public function getDefaultLogo() {
        return 'images/univ.png';
    }

    /**
     * Obtenir le logo de l'université pour un utilisateur authentifié
     * @param int $user_id ID de l'utilisateur
     * @return string Chemin du logo
     */
    public function getLogoForUser($user_id) {
        if (isset($this->logo_cache[$user_id])) {
            return $this->logo_cache[$user_id];
        }

        if (!$this->connexion) {
            return $this->getDefaultLogo();
        }

        $sql = "SELECT logo FROM univ WHERE code IN (SELECT univ FROM utilisateur WHERE id = ?)";
        $stmt = $this->connexion->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $row = $result->fetch_assoc()) {
                $logo = !empty($row['logo']) ? 'administrateur/' . $row['logo'] : $this->getDefaultLogo();
                $this->logo_cache[$user_id] = $logo;
                $stmt->close();
                return $logo;
            }
            $stmt->close();
        }

        return $this->getDefaultLogo();
    }

    /**
     * Obtenir le logo de l'université pour une session authentifiée
     * @return string Chemin du logo depuis la session ou le logo par défaut
     */
    public function getLogoFromSession() {
        if (isset($_SESSION['logo_univ']) && !empty($_SESSION['logo_univ'])) {
            return 'administrateur/' . $_SESSION['logo_univ'];
        }
        return $this->getDefaultLogo();
    }

    /**
     * Obtenir le favicon pour une session authentifiée
     * @return string Chemin du favicon
     */
    public function getFaviconFromSession() {
        return $this->getLogoFromSession();
    }

    /**
     * Obtenir le logo par défaut de l'université (avant authentification)
     * @return string Chemin du logo par défaut
     */
    public function getDefaultUniversityLogo() {
        return $this->getDefaultLogo();
    }

    /**
     * Obtenir le favicon par défaut
     * @return string Chemin du favicon
     */
    public function getDefaultFavicon() {
        return 'images/univ.png';
    }

    /**
     * Mettre à jour le logo d'une université
     * @param int $univ_code Code de l'université
     * @param string $logo_path Chemin du nouveau logo
     * @return bool Succès de l'opération
     */
    public function updateUniversityLogo($univ_code, $logo_path) {
        if (!$this->connexion) {
            return false;
        }

        $sql = "UPDATE univ SET logo = ? WHERE code = ?";
        $stmt = $this->connexion->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("si", $logo_path, $univ_code);
            $success = $stmt->execute();
            $stmt->close();
            
            // Vider le cache
            $this->logo_cache = [];
            
            return $success;
        }

        return false;
    }

    /**
     * Obtenir tous les logos configurés
     * @return array Liste des logos avec leurs codes d'université
     */
    public function getAllLogos() {
        if (!$this->connexion) {
            return [];
        }

        $sql = "SELECT code, logo FROM univ WHERE logo IS NOT NULL AND logo != ''";
        $result = $this->connexion->query($sql);
        
        $logos = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $logos[$row['code']] = $row['logo'];
            }
        }

        return $logos;
    }

    /**
     * Obtenir le nom de l'université par défaut
     * @return string Nom de l'université par défaut
     */
    public function getDefaultUniversityName() {
        return "UNIVERSITE DENIS SASSOU-N'GUESSO";
    }

    /**
     * Obtenir le nom de l'université pour un utilisateur
     * @param int $user_id ID de l'utilisateur
     * @return string Nom de l'université
     */
    public function getUniversityNameForUser($user_id) {
        if (!$this->connexion) {
            return $this->getDefaultUniversityName();
        }

        $sql = "SELECT nom FROM univ WHERE code IN (SELECT univ FROM utilisateur WHERE id = ?)";
        $stmt = $this->connexion->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $row = $result->fetch_assoc()) {
                $nom = !empty($row['nom']) ? $row['nom'] : $this->getDefaultUniversityName();
                $stmt->close();
                return $nom;
            }
            $stmt->close();
        }

        return $this->getDefaultUniversityName();
    }

    /**
     * Obtenir le nom de l'université depuis la session
     * @return string Nom de l'université depuis la session
     */
    public function getUniversityNameFromSession() {
        if (isset($_SESSION['nom_univ']) && !empty($_SESSION['nom_univ'])) {
            return $_SESSION['nom_univ'];
        }
        return $this->getDefaultUniversityName();
    }

    /**
     * Obtenir le nom de l'université par code
     * @param int $univ_code Code de l'université
     * @return string Nom de l'université
     */
    public function getUniversityName($univ_code) {
        if (!$this->connexion) {
            return $this->getDefaultUniversityName();
        }

        $sql = "SELECT nom FROM univ WHERE code = ?";
        $stmt = $this->connexion->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("i", $univ_code);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $row = $result->fetch_assoc()) {
                $nom = !empty($row['nom']) ? $row['nom'] : $this->getDefaultUniversityName();
                $stmt->close();
                return $nom;
            }
            $stmt->close();
        }

        return $this->getDefaultUniversityName();
    }

    /**
     * Mettre à jour le nom d'une université
     * @param int $univ_code Code de l'université
     * @param string $nom_univ Nouveau nom de l'université
     * @return bool Succès de l'opération
     */
    public function updateUniversityName($univ_code, $nom_univ) {
        if (!$this->connexion || empty($nom_univ)) {
            return false;
        }

        $sql = "UPDATE univ SET nom = ? WHERE code = ?";
        $stmt = $this->connexion->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("si", $nom_univ, $univ_code);
            $success = $stmt->execute();
            $stmt->close();
            
            // Vider le cache
            $this->logo_cache = [];
            
            return $success;
        }

        return false;
    }

    /**
     * Obtenir toutes les universités avec leurs noms et logos
     * @return array Liste des universités
     */
    public function getAllUniversities() {
        if (!$this->connexion) {
            return [];
        }

        $sql = "SELECT code, nom, logo FROM univ ORDER BY nom";
        $result = $this->connexion->query($sql);
        
        $universities = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $universities[] = $row;
            }
        }

        return $universities;
    }
}

// Instance globale pour utilisation facile
if (!isset($GLOBALS['logo_config'])) {
    $GLOBALS['logo_config'] = new LogoConfig($connexion ?? null);
}

function getLogoConfig() {
    return $GLOBALS['logo_config'];
}

?>

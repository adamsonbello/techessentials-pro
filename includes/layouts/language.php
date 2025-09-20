<?php
/**
 * TechEssentials Pro - Système de Langue
 * Gestion complète du multilinguisme
 */

class Language {
    private static $instance = null;
    private $translations = [];
    private $currentLang = DEFAULT_LANG;
    private $fallbackLang = 'en';
    
    /**
     * Singleton pattern
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructeur privé (Singleton)
     */
    private function __construct() {
        $this->loadTranslations();
        $this->detectLanguage();
    }
    
    /**
     * Charger les traductions depuis le fichier JSON
     */
    private function loadTranslations() {
        $file = ROOT_PATH . 'data/translations.json';
        
        if (file_exists($file)) {
            $json = file_get_contents($file);
            $this->translations = json_decode($json, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                logError("Erreur JSON traductions: " . json_last_error_msg());
                $this->translations = [];
            }
        } else {
            logError("Fichier de traductions introuvable: {$file}");
        }
    }
    
    /**
     * Détecter la langue (URL, session, navigateur)
     */
    private function detectLanguage() {
        // 1. Priorité à l'URL
        if (isset($_GET['lang']) && in_array($_GET['lang'], AVAILABLE_LANGS)) {
            $this->setLanguage($_GET['lang']);
            return;
        }
        
        // 2. Session
        if (isset($_SESSION['lang']) && in_array($_SESSION['lang'], AVAILABLE_LANGS)) {
            $this->currentLang = $_SESSION['lang'];
            return;
        }
        
        // 3. Cookie
        if (isset($_COOKIE['user_lang']) && in_array($_COOKIE['user_lang'], AVAILABLE_LANGS)) {
            $this->setLanguage($_COOKIE['user_lang']);
            return;
        }
        
        // 4. Détection navigateur
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            if (in_array($browserLang, AVAILABLE_LANGS)) {
                $this->setLanguage($browserLang);
                return;
            }
        }
        
        // 5. Langue par défaut
        $this->setLanguage(DEFAULT_LANG);
    }
    
    /**
     * Définir la langue active
     */
    public function setLanguage($lang) {
        if (in_array($lang, AVAILABLE_LANGS)) {
            $this->currentLang = $lang;
            $_SESSION['lang'] = $lang;
            setcookie('user_lang', $lang, time() + (365 * 24 * 60 * 60), '/');
        }
    }
    
    /**
     * Obtenir la langue actuelle
     */
    public function getCurrentLanguage() {
        return $this->currentLang;
    }
    
    /**
     * Obtenir une traduction
     * @param string $key Clé de traduction (ex: "nav.home")
     * @param array $params Paramètres à remplacer
     * @return string
     */
    public function get($key, $params = []) {
        $keys = explode('.', $key);
        $value = $this->translations;
        
        // Naviguer dans l'arbre des traductions
        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                // Clé non trouvée
                logError("Traduction manquante: {$key} [{$this->currentLang}]");
                return $this->getFallback($key, $params);
            }
        }
        
        // Obtenir la traduction dans la langue actuelle
        if (isset($value[$this->currentLang])) {
            $translation = $value[$this->currentLang];
        } elseif (isset($value[$this->fallbackLang])) {
            $translation = $value[$this->fallbackLang];
        } else {
            return $key; // Retourner la clé si aucune traduction
        }
        
        // Remplacer les paramètres
        if (!empty($params)) {
            foreach ($params as $param => $val) {
                $translation = str_replace('{{' . $param . '}}', $val, $translation);
            }
        }
        
        return $translation;
    }
    
    /**
     * Traduction de fallback
     */
    private function getFallback($key, $params = []) {
        // Essayer avec la langue de fallback
        $keys = explode('.', $key);
        $value = $this->translations;
        
        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $key; // Retourner la clé
            }
        }
        
        if (isset($value[$this->fallbackLang])) {
            $translation = $value[$this->fallbackLang];
            
            if (!empty($params)) {
                foreach ($params as $param => $val) {
                    $translation = str_replace('{{' . $param . '}}', $val, $translation);
                }
            }
            
            return $translation;
        }
        
        return $key;
    }
    
    /**
     * Obtenir toutes les traductions d'une section
     */
    public function getSection($section) {
        if (isset($this->translations[$section])) {
            $sectionData = $this->translations[$section];
            $result = [];
            
            foreach ($sectionData as $key => $value) {
                if (isset($value[$this->currentLang])) {
                    $result[$key] = $value[$this->currentLang];
                } elseif (isset($value[$this->fallbackLang])) {
                    $result[$key] = $value[$this->fallbackLang];
                } else {
                    $result[$key] = $key;
                }
            }
            
            return $result;
        }
        
        return [];
    }
    
    /**
     * Vérifier si une traduction existe
     */
    public function has($key) {
        $keys = explode('.', $key);
        $value = $this->translations;
        
        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return false;
            }
        }
        
        return isset($value[$this->currentLang]) || isset($value[$this->fallbackLang]);
    }
    
    /**
     * Obtenir l'URL avec changement de langue
     */
    public function getLanguageUrl($lang) {
        $currentUrl = $_SERVER['REQUEST_URI'];
        
        // Retirer le paramètre lang existant
        $url = preg_replace('/([?&])lang=[^&]*(&|$)/', '$1', $currentUrl);
        $url = rtrim($url, '?&');
        
        // Ajouter le nouveau paramètre lang
        $separator = (strpos($url, '?') === false) ? '?' : '&';
        return $url . $separator . 'lang=' . $lang;
    }
    
    /**
     * Générer le sélecteur de langue
     */
    public function renderLanguageSelector($class = 'language-switcher') {
        $html = '<div class="' . $class . '">';
        
        foreach (AVAILABLE_LANGS as $lang) {
            $active = ($lang === $this->currentLang) ? 'active' : '';
            $flag = ($lang === 'fr') ? '🇫🇷' : '🇺🇸';
            $url = $this->getLanguageUrl($lang);
            
            $html .= sprintf(
                '<a href="%s" class="lang-btn %s" data-lang="%s">%s %s</a>',
                $url,
                $active,
                $lang,
                $flag,
                strtoupper($lang)
            );
        }
        
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Obtenir les métadonnées de langue pour le HTML
     */
    public function getHtmlAttributes() {
        $lang = $this->currentLang;
        $dir = in_array($lang, ['ar', 'he', 'fa']) ? 'rtl' : 'ltr';
        
        return sprintf('lang="%s" dir="%s"', $lang, $dir);
    }
    
    /**
     * Format de date localisé
     */
    public function formatDate($date, $format = null) {
        if ($format === null) {
            $format = ($this->currentLang === 'fr') ? 'd/m/Y' : 'm/d/Y';
        }
        
        if ($date instanceof DateTime) {
            return $date->format($format);
        }
        
        return date($format, strtotime($date));
    }
    
    /**
     * Format de prix localisé
     */
    public function formatPrice($price, $currency = '€') {
        if ($this->currentLang === 'fr') {
            return number_format($price, 2, ',', ' ') . ' ' . $currency;
        } else {
            return $currency . number_format($price, 2, '.', ',');
        }
    }
    
    /**
     * Pluralisation
     */
    public function plural($count, $singular, $plural = null) {
        if ($plural === null) {
            $plural = $singular . 's';
        }
        
        return $count == 1 ? $singular : $plural;
    }
}

// ===============================
// FONCTIONS HELPER GLOBALES
// ===============================

/**
 * Obtenir une traduction (helper rapide)
 */
function __($key, $params = []) {
    return Language::getInstance()->get($key, $params);
}

/**
 * Echo une traduction (helper rapide)
 */
function _e($key, $params = []) {
    echo Language::getInstance()->get($key, $params);
}

/**
 * Obtenir la langue actuelle
 */
function getLang() {
    return Language::getInstance()->getCurrentLanguage();
}

/**
 * Définir la langue
 */
function setLang($lang) {
    Language::getInstance()->setLanguage($lang);
}

/**
 * Obtenir l'URL pour changer de langue
 */
function langUrl($lang) {
    return Language::getInstance()->getLanguageUrl($lang);
}

/**
 * Afficher le sélecteur de langue
 */
function languageSelector($class = 'language-switcher') {
    echo Language::getInstance()->renderLanguageSelector($class);
}

/**
 * Attributs HTML de langue
 */
function htmlLang() {
    echo Language::getInstance()->getHtmlAttributes();
}

// ===============================
// INITIALISATION
// ===============================
$lang = Language::getInstance();
<?php
/**
 * TechEssentials Pro - Service API Produits
 * Centralise les appels aux différentes API vendors
 * SOCKETS PRÉPARÉS - À compléter quand API disponibles
 */

if (!defined('TECHESSENTIALS_PRO')) {
    die('Accès direct non autorisé');
}

class ProductAPIService {
    
    private $config;
    private $cache_dir;
    private $errors = [];
    
    public function __construct() {
        $this->config = require __DIR__ . '/../api-config.php';
        $this->cache_dir = $this->config['cache']['directory'];
        
        // Créer dossier cache si inexistant
        if (!file_exists($this->cache_dir)) {
            mkdir($this->cache_dir, 0755, true);
        }
    }
    
    /**
     * Récupérer les prix d'un produit depuis tous les vendors actifs
     * 
     * @param string $product_id ID interne du produit
     * @param array $identifiers Identifiants externes (ASIN, EAN, SKU)
     * @return array Prix de tous les vendors disponibles
     */
    public function getProductPrices($product_id, $identifiers = []) {
        $cache_key = "prices_{$product_id}";
        
        // Vérifier cache
        if ($this->config['cache']['enabled']) {
            if ($cached = $this->getCache($cache_key)) {
                return $cached;
            }
        }
        
        $prices = [];
        
        // Amazon
        if ($this->config['amazon']['enabled'] && !empty($identifiers['asin'])) {
            $prices['amazon'] = $this->fetchAmazonPrice($identifiers['asin']);
        } elseif ($this->config['fallback']['use_mock_data']) {
            $prices['amazon'] = $this->getMockPrice('amazon', $product_id);
        }
        
        // Fnac
        if ($this->config['fnac']['enabled'] && !empty($identifiers['ean'])) {
            $prices['fnac'] = $this->fetchFnacPrice($identifiers['ean']);
        } elseif ($this->config['fallback']['use_mock_data']) {
            $prices['fnac'] = $this->getMockPrice('fnac', $product_id);
        }
        
        // Cdiscount
        if ($this->config['cdiscount']['enabled'] && !empty($identifiers['ean'])) {
            $prices['cdiscount'] = $this->fetchCdiscountPrice($identifiers['ean']);
        } elseif ($this->config['fallback']['use_mock_data']) {
            $prices['cdiscount'] = $this->getMockPrice('cdiscount', $product_id);
        }
        
        // AliExpress
        if ($this->config['aliexpress']['enabled'] && !empty($identifiers['keywords'])) {
            $prices['aliexpress'] = $this->fetchAliexpressPrice($identifiers['keywords']);
        } elseif ($this->config['fallback']['use_mock_data']) {
            $prices['aliexpress'] = $this->getMockPrice('aliexpress', $product_id);
        }
        
        // Rakuten
        if ($this->config['rakuten']['enabled'] && !empty($identifiers['ean'])) {
            $prices['rakuten'] = $this->fetchRakutenPrice($identifiers['ean']);
        } elseif ($this->config['fallback']['use_mock_data']) {
            $prices['rakuten'] = $this->getMockPrice('rakuten', $product_id);
        }
        
        // Filtrer les null et sauvegarder en cache
        $prices = array_filter($prices);
        
        if ($this->config['cache']['enabled'] && !empty($prices)) {
            $this->setCache($cache_key, $prices);
        }
        
        return $prices;
    }
    
    // ============================================
    // SOCKET AMAZON - À REMPLIR QUAND API PRÊTE
    // ============================================
    
    private function fetchAmazonPrice($asin) {
        if (empty($asin)) return null;
        
        try {
            // TODO: Implémenter Amazon Product Advertising API 5.0
            // Documentation: https://webservices.amazon.com/paapi5/documentation/
            
            /*
            EXEMPLE CODE À IMPLÉMENTER :
            
            require_once 'aws-sdk/autoload.php';
            
            $client = new \Amazon\ProductAdvertisingAPI\v1\ApiClient();
            $client->setAccessKey($this->config['amazon']['api_key']);
            $client->setSecretKey($this->config['amazon']['api_secret']);
            $client->setRegion($this->config['amazon']['region']);
            
            $request = new \Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsRequest();
            $request->setItemIds([$asin]);
            $request->setPartnerTag($this->config['amazon']['associate_tag']);
            $request->setResources(['Offers.Listings.Price', 'ItemInfo.Title', 'Images.Primary.Large']);
            
            $response = $client->getItems($request);
            
            if ($response->getItemsResult()->getItems()) {
                $item = $response->getItemsResult()->getItems()[0];
                $offer = $item->getOffers()->getListings()[0];
                
                return [
                    'vendor' => 'amazon',
                    'vendor_name' => 'Amazon',
                    'price' => (float)$offer->getPrice()->getAmount(),
                    'currency' => $offer->getPrice()->getCurrency(),
                    'available' => $offer->getAvailability()->getType() === 'Now',
                    'shipping' => 0,
                    'delivery_time' => '24-48h',
                    'url' => $item->getDetailPageURL(),
                    'stock_status' => 'En stock',
                    'last_updated' => time(),
                    'is_mock' => false
                ];
            }
            */
            
            $this->logError('amazon', 'API non configurée - utilisation mock data');
            return null;
            
        } catch (Exception $e) {
            $this->logError('amazon', $e->getMessage());
            return null;
        }
    }
    
    // ==========================================
    // SOCKET FNAC - À REMPLIR QUAND API PRÊTE
    // ==========================================
    
    private function fetchFnacPrice($ean) {
        if (empty($ean)) return null;
        
        try {
            // TODO: Implémenter Fnac API
            // Contacter Fnac pour accès API partenaire : https://www.fnac.com/corporate
            
            /*
            EXEMPLE CODE À IMPLÉMENTER :
            
            $url = $this->config['fnac']['endpoint'] . '?ean=' . $ean;
            $headers = [
                'Authorization: Bearer ' . $this->config['fnac']['api_key'],
                'Content-Type: application/json'
            ];
            
            $response = $this->makeAPIRequest($url, 'GET', null, $headers);
            
            if ($response && !empty($response['products'])) {
                $product = $response['products'][0];
                
                return [
                    'vendor' => 'fnac',
                    'vendor_name' => 'Fnac',
                    'price' => (float)$product['price'],
                    'currency' => 'EUR',
                    'available' => $product['available'],
                    'shipping' => 0,
                    'delivery_time' => '24-48h',
                    'url' => $product['url'],
                    'stock_status' => $product['stock_label'],
                    'last_updated' => time(),
                    'is_mock' => false
                ];
            }
            */
            
            $this->logError('fnac', 'API non configurée - utilisation mock data');
            return null;
            
        } catch (Exception $e) {
            $this->logError('fnac', $e->getMessage());
            return null;
        }
    }
    
    // ==============================================
    // SOCKET CDISCOUNT - À REMPLIR QUAND API PRÊTE
    // ==============================================
    
    private function fetchCdiscountPrice($ean) {
        if (empty($ean)) return null;
        
        try {
            // TODO: Implémenter Cdiscount API
            // S'inscrire sur : https://dev.cdiscount.com/
            
            /*
            EXEMPLE CODE À IMPLÉMENTER :
            
            $url = $this->config['cdiscount']['endpoint'];
            $params = [
                'apiKey' => $this->config['cdiscount']['api_key'],
                'siteId' => $this->config['cdiscount']['site_id'],
                'ean' => $ean
            ];
            
            $response = $this->makeAPIRequest($url, 'POST', json_encode($params));
            
            if ($response && $response['Products']) {
                $product = $response['Products'][0];
                
                return [
                    'vendor' => 'cdiscount',
                    'vendor_name' => 'Cdiscount',
                    'price' => (float)$product['BestOffer']['SalePrice'],
                    'currency' => 'EUR',
                    'available' => $product['IsAvailable'],
                    'shipping' => (float)$product['BestOffer']['ShippingCost'],
                    'delivery_time' => '2-3 jours',
                    'url' => $product['ProductUrl'],
                    'stock_status' => 'En stock',
                    'last_updated' => time(),
                    'is_mock' => false
                ];
            }
            */
            
            $this->logError('cdiscount', 'API non configurée - utilisation mock data');
            return null;
            
        } catch (Exception $e) {
            $this->logError('cdiscount', $e->getMessage());
            return null;
        }
    }
    
    // ===============================================
    // SOCKET ALIEXPRESS - À REMPLIR QUAND API PRÊTE
    // ===============================================
    
    private function fetchAliexpressPrice($keywords) {
        if (empty($keywords)) return null;
        
        try {
            // TODO: Implémenter AliExpress Affiliate API
            // S'inscrire sur : https://portals.aliexpress.com/
            
            /*
            EXEMPLE CODE À IMPLÉMENTER :
            
            $url = $this->config['aliexpress']['endpoint'];
            $params = [
                'app_key' => $this->config['aliexpress']['app_key'],
                'method' => 'aliexpress.affiliate.productdetail.get',
                'keywords' => $keywords,
                'tracking_id' => $this->config['aliexpress']['tracking_id'],
                'target_currency' => 'EUR',
                'target_language' => 'FR'
            ];
            
            // Générer signature
            $params['sign'] = $this->generateAliexpressSign($params);
            
            $response = $this->makeAPIRequest($url, 'POST', http_build_query($params));
            
            if ($response && $response['result']['products']) {
                $product = $response['result']['products'][0];
                
                return [
                    'vendor' => 'aliexpress',
                    'vendor_name' => 'AliExpress',
                    'price' => (float)$product['target_sale_price'],
                    'currency' => 'EUR',
                    'available' => true,
                    'shipping' => (float)($product['shipping_price'] ?? 0),
                    'delivery_time' => '15-30 jours',
                    'url' => $product['promotion_link'],
                    'stock_status' => 'Sur commande',
                    'last_updated' => time(),
                    'is_mock' => false
                ];
            }
            */
            
            $this->logError('aliexpress', 'API non configurée - utilisation mock data');
            return null;
            
        } catch (Exception $e) {
            $this->logError('aliexpress', $e->getMessage());
            return null;
        }
    }
    
    // ===========================================
    // SOCKET RAKUTEN - À REMPLIR QUAND API PRÊTE
    // ===========================================
    
    private function fetchRakutenPrice($ean) {
        if (empty($ean)) return null;
        
        try {
            // TODO: Implémenter Rakuten Affiliate
            // Programme partenaire : https://fr.shopping.rakuten.com/newaffiliate/
            
            $this->logError('rakuten', 'API non configurée - utilisation mock data');
            return null;
            
        } catch (Exception $e) {
            $this->logError('rakuten', $e->getMessage());
            return null;
        }
    }
    
    // ============================================
    // DONNÉES MOCKÉES (temporaire)
    // ============================================
    
    private function getMockPrice($vendor, $product_id) {
        // Seed pour avoir des prix cohérents pour chaque produit
        $seed = crc32($product_id . $vendor);
        mt_srand($seed);
        
        $vendor_config = [
            'amazon' => [
                'name' => 'Amazon',
                'base_min' => 150,
                'base_max' => 350,
                'shipping' => 0,
                'delivery' => '24-48h',
                'logo' => '🛒'
            ],
            'fnac' => [
                'name' => 'Fnac',
                'base_min' => 160,
                'base_max' => 360,
                'shipping' => 0,
                'delivery' => '24-48h',
                'logo' => '📚'
            ],
            'cdiscount' => [
                'name' => 'Cdiscount',
                'base_min' => 145,
                'base_max' => 340,
                'shipping' => 0,
                'delivery' => '2-3 jours',
                'logo' => '💰'
            ],
            'aliexpress' => [
                'name' => 'AliExpress',
                'base_min' => 80,
                'base_max' => 250,
                'shipping' => mt_rand(5, 15),
                'delivery' => '15-30 jours',
                'logo' => '🌏'
            ],
            'rakuten' => [
                'name' => 'Rakuten',
                'base_min' => 155,
                'base_max' => 345,
                'shipping' => 0,
                'delivery' => '2-4 jours',
                'logo' => '🛍️'
            ]
        ];
        
        if (!isset($vendor_config[$vendor])) {
            return null;
        }
        
        $config = $vendor_config[$vendor];
        $price = mt_rand($config['base_min'], $config['base_max']);
        $available = mt_rand(0, 10) > 2; // 80% disponible
        
        $stock_options = ['En stock', 'Stock limité', 'Sur commande'];
        
        return [
            'vendor' => $vendor,
            'vendor_name' => $config['name'],
            'vendor_logo' => $config['logo'],
            'price' => (float)$price,
            'currency' => 'EUR',
            'available' => $available,
            'shipping' => (float)$config['shipping'],
            'delivery_time' => $config['delivery'],
            'url' => '#', // URL affiliée à configurer
            'stock_status' => $available ? $stock_options[array_rand($stock_options)] : 'Rupture',
            'last_updated' => time(),
            'is_mock' => true, // Flag pour indiquer données mockées
            'savings' => mt_rand(0, 50) // Économies potentielles
        ];
    }
    
    // ============================================
    // SYSTÈME DE CACHE
    // ============================================
    
    private function getCache($key) {
        $file = $this->cache_dir . md5($key) . '.json';
        
        if (!file_exists($file)) {
            return null;
        }
        
        $data = json_decode(file_get_contents($file), true);
        
        // Vérifier expiration
        if ($data['expires'] < time()) {
            @unlink($file);
            return null;
        }
        
        return $data['content'];
    }
    
    private function setCache($key, $content) {
        $file = $this->cache_dir . md5($key) . '.json';
        
        $data = [
            'expires' => time() + $this->config['cache']['duration'],
            'created' => time(),
            'content' => $content
        ];
        
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }
    
    public function clearCache($product_id = null) {
        if ($product_id) {
            $file = $this->cache_dir . md5("prices_{$product_id}") . '.json';
            if (file_exists($file)) {
                unlink($file);
            }
        } else {
            // Vider tout le cache
            $files = glob($this->cache_dir . '*.json');
            foreach ($files as $file) {
                unlink($file);
            }
        }
    }
    
    // ============================================
    // HELPER: Requête API générique
    // ============================================
    
    private function makeAPIRequest($url, $method = 'GET', $data = null, $headers = []) {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->config['fallback']['timeout']);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
        }
        
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new Exception("CURL Error: {$error}");
        }
        
        if ($httpCode !== 200) {
            throw new Exception("HTTP Error: {$httpCode}");
        }
        
        return json_decode($response, true);
    }
    
    // ============================================
    // ANALYSE & HELPERS
    // ============================================
    
    /**
     * Obtenir le meilleur prix parmi tous les vendors
     */
    public function getBestPrice($product_id, $identifiers = []) {
        $prices = $this->getProductPrices($product_id, $identifiers);
        
        if (empty($prices)) {
            return null;
        }
        
        $available_prices = array_filter($prices, function($p) {
            return $p && $p['available'];
        });
        
        if (empty($available_prices)) {
            return null;
        }
        
        usort($available_prices, function($a, $b) {
            $total_a = $a['price'] + ($a['shipping'] ?? 0);
            $total_b = $b['price'] + ($b['shipping'] ?? 0);
            return $total_a <=> $total_b;
        });
        
        return $available_prices[0];
    }
    
    /**
     * Calculer le prix moyen
     */
    public function getAveragePrice($product_id, $identifiers = []) {
        $prices = $this->getProductPrices($product_id, $identifiers);
        
        if (empty($prices)) {
            return 0;
        }
        
        $available_prices = array_filter($prices, function($p) {
            return $p && $p['available'];
        });
        
        if (empty($available_prices)) {
            return 0;
        }
        
        $total = array_reduce($available_prices, function($sum, $p) {
            return $sum + $p['price'] + ($p['shipping'] ?? 0);
        }, 0);
        
        return round($total / count($available_prices), 2);
    }
    
    /**
     * Calculer les économies potentielles
     */
    public function getSavings($product_id, $identifiers = []) {
        $prices = $this->getProductPrices($product_id, $identifiers);
        
        if (count($prices) < 2) {
            return 0;
        }
        
        $available_prices = array_filter($prices, function($p) {
            return $p && $p['available'];
        });
        
        if (count($available_prices) < 2) {
            return 0;
        }
        
        $price_values = array_map(function($p) {
            return $p['price'] + ($p['shipping'] ?? 0);
        }, $available_prices);
        
        $min = min($price_values);
        $max = max($price_values);
        
        return round($max - $min, 2);
    }
    
    /**
     * Vérifier si les données sont mockées
     */
    public function isMockData($prices) {
        if (empty($prices)) {
            return true;
        }
        
        foreach ($prices as $price) {
            if (!empty($price['is_mock'])) {
                return true;
            }
        }
        
        return false;
    }
    
    // ============================================
    // LOGGING
    // ============================================
    
    private function logError($vendor, $message) {
        if (!$this->config['fallback']['log_errors']) {
            return;
        }
        
        $this->errors[] = [
            'vendor' => $vendor,
            'message' => $message,
            'time' => date('Y-m-d H:i:s')
        ];
        
        $log_file = $this->cache_dir . '../logs/api_errors.log';
        $log_dir = dirname($log_file);
        
        if (!file_exists($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        $log_entry = sprintf(
            "[%s] %s: %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($vendor),
            $message
        );
        
        file_put_contents($log_file, $log_entry, FILE_APPEND);
    }
    
    public function getErrors() {
        return $this->errors;
    }
}
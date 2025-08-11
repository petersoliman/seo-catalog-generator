<?php
// Bosch Professional Scraper
// This script scrapes JSON-LD data from Bosch Professional website

// Configuration
$mainUrl = 'https://www.bosch-professional.com/eg/en/professional-power-tools-131398-ocs-c/';
$outputFile = 'ldjson.php';

echo "🔧 Bosch Professional Scraper Starting...\n\n";
echo "🎯 Target: Extract all available JSON-LD data and find product listings\n";
echo "📁 Output: {$outputFile}\n\n";

// Function to scrape a page and extract JSON-LD
function scrapePage($url, $pageName) {
    echo "🔄 Scraping: {$pageName}\n";
    echo "   URL: {$url}\n";
    
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            echo "   ❌ HTTP Error: {$httpCode}\n";
            return null;
        }
        
        if (!$html) {
            echo "   ❌ No content received\n";
            return null;
        }
        
        // Extract JSON-LD data
        $jsonData = extractJsonLd($html, $pageName);
        
        if ($jsonData) {
            echo "   ✅ Found " . count($jsonData) . " JSON-LD entries\n";
            return $jsonData;
        } else {
            echo "   ⚠️  No JSON-LD data found\n";
            return null;
        }
        
    } catch (Exception $e) {
        echo "   ❌ Error: " . $e->getMessage() . "\n";
        return null;
    }
}

// Function to extract JSON-LD from HTML
function extractJsonLd($html, $pageName) {
    $jsonArray = [];
    
    // Look for JSON-LD script tags
    if (preg_match_all('/<script\s+type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches)) {
        foreach ($matches[1] as $jsonString) {
            $json = json_decode($jsonString, true);
            if ($json && json_last_error() === JSON_ERROR_NONE) {
                // Add page information
                $json['_page'] = $pageName;
                $json['_scraped_at'] = date('Y-m-d H:i:s');
                $jsonArray[] = $json;
            }
        }
    }
    
    // Also look for other data patterns that might contain product information
    $otherPatterns = [
        // Look for product data in JavaScript variables
        '/var\s+products\s*=\s*(\[.*?\]);/s',
        '/window\.products\s*=\s*(\[.*?\]);/s',
        '/products\s*:\s*(\[.*?\])/s',
        // Look for JSON data in data attributes
        '/data-products="([^"]*)"/',
        '/data-json="([^"]*)"/'
    ];
    
    foreach ($otherPatterns as $pattern) {
        if (preg_match_all($pattern, $html, $matches)) {
            foreach ($matches[1] as $dataString) {
                // Try to decode as JSON
                $data = json_decode($dataString, true);
                if ($data && json_last_error() === JSON_ERROR_NONE) {
                    $data['_page'] = $pageName;
                    $data['_scraped_at'] = date('Y-m-d H:i:s');
                    $data['_source'] = 'alternative_pattern';
                    $jsonArray[] = $data;
                }
            }
        }
    }
    
    return $jsonArray;
}

// Function to analyze main page and find actual category structure
function analyzeMainPage($url) {
    echo "🔍 Analyzing main page to find category structure...\n";
    
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200 || !$html) {
            echo "   ❌ Failed to load main page\n";
            return [];
        }
        
        echo "   ✅ Main page loaded for analysis\n";
        
        $categories = [];
        
        // Look for category links in various patterns
        $patterns = [
            // Pattern 1: Look for navigation menu items
            '/<a[^>]*href="([^"]*)"[^>]*class="[^"]*nav[^"]*"[^>]*>([^<]*)<\/a>/i',
            // Pattern 2: Look for category cards or tiles
            '/<a[^>]*href="([^"]*)"[^>]*class="[^"]*category[^"]*"[^>]*>([^<]*)<\/a>/i',
            // Pattern 3: Look for any links that might be categories
            '/<a[^>]*href="([^"]*)"[^>]*>([^<]*tool[^<]*)<\/a>/i',
            '/<a[^>]*href="([^"]*)"[^>]*>([^<]*drill[^<]*)<\/a>/i',
            '/<a[^>]*href="([^"]*)"[^>]*>([^<]*saw[^<]*)<\/a>/i',
            '/<a[^>]*href="([^"]*)"[^>]*>([^<]*grinder[^<]*)<\/a>/i',
            '/<a[^>]*href="([^"]*)"[^>]*>([^<]*hammer[^<]*)<\/a>/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $html, $matches)) {
                for ($i = 0; $i < count($matches[1]); $i++) {
                    $url = $matches[1][$i];
                    $name = trim(strip_tags($matches[2][$i]));
                    
                    // Convert relative URLs to absolute
                    if (strpos($url, 'http') !== 0) {
                        if (strpos($url, '/') === 0) {
                            $url = 'https://www.bosch-professional.com' . $url;
                        } else {
                            $url = rtrim($url, '/') . '/' . $url;
                        }
                    }
                    
                    if (!empty($name) && !empty($url) && !in_array($url, array_column($categories, 'url'))) {
                        $categories[] = [
                            'url' => $url,
                            'name' => $name
                        ];
                    }
                }
            }
        }
        
        // Also look for any product-related content in the HTML
        if (preg_match_all('/<div[^>]*class="[^"]*product[^"]*"[^>]*>(.*?)<\/div>/s', $html, $matches)) {
            echo "   📋 Found " . count($matches[1]) . " product divs in HTML\n";
        }
        
        if (preg_match_all('/<script[^>]*>.*?products.*?<\/script>/s', $html, $matches)) {
            echo "   📋 Found " . count($matches[0]) . " scripts containing 'products'\n";
            
            // Try to extract product data from these scripts
            foreach ($matches[0] as $scriptContent) {
                echo "   🔍 Analyzing script content for product data...\n";
                
                // Look for various product data patterns in the script
                $productPatterns = [
                    '/products\s*:\s*(\[.*?\])/s',
                    '/var\s+products\s*=\s*(\[.*?\]);/s',
                    '/window\.products\s*=\s*(\[.*?\]);/s',
                    '/"products"\s*:\s*(\[.*?\])/s',
                    '/products\s*=\s*(\[.*?\])/s'
                ];
                
                foreach ($productPatterns as $pattern) {
                    if (preg_match($pattern, $scriptContent, $productMatch)) {
                        echo "   ✅ Found product data pattern!\n";
                        
                        // Try to decode the JSON
                        $productData = json_decode($productMatch[1], true);
                        if ($productData && json_last_error() === JSON_ERROR_NONE) {
                            echo "   ✅ Successfully decoded product data: " . count($productData) . " products\n";
                            
                            // Save this product data to a separate file for inspection
                            $productFile = 'extracted_products.json';
                            file_put_contents($productFile, json_encode($productData, JSON_PRETTY_PRINT));
                            echo "   💾 Product data saved to {$productFile}\n";
                        } else {
                            echo "   ❌ Failed to decode product data: " . json_last_error_msg() . "\n";
                        }
                        break;
                    }
                }
            }
        }
        
        echo "   📋 Found " . count($categories) . " potential category links\n";
        
        // Show what we found
        if (!empty($categories)) {
            echo "   📋 Categories found:\n";
            foreach ($categories as $cat) {
                echo "      - {$cat['name']}: {$cat['url']}\n";
            }
        }
        
        return $categories;
        
    } catch (Exception $e) {
        echo "   ❌ Error analyzing main page: " . $e->getMessage() . "\n";
        return [];
    }
}

// Function to save data to file
function saveToFile($allData, $filename) {
    $content = "<?php\n";
    $content .= "// Bosch Professional Products - JSON-LD Data\n";
    $content .= "// Scraped on: " . date('Y-m-d H:i:s') . "\n";
    $content .= "// Total unique pages: " . count($allData) . "\n\n";
    
    $content .= "// JSON-LD data for all pages\n";
    $content .= '$allPagesData = ' . var_export($allData, true) . ";\n\n";
    
    $content .= "// Combined JSON-LD for parsing\n";
    $content .= '$combinedJsonLd = json_encode($allPagesData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);' . "\n";
    $content .= "?>\n";
    
    if (file_put_contents($filename, $content)) {
        echo "✅ Data saved to {$filename}\n";
        return true;
    } else {
        echo "❌ Error saving to {$filename}\n";
        return false;
    }
}

// Main scraping process
echo "🚀 Starting scraping process...\n\n";

// Step 1: Analyze main page to find actual category structure
$foundCategories = analyzeMainPage($mainUrl);

// Step 2: Define base pages to scrape (avoiding duplicates)
$basePages = [
    ['url' => $mainUrl, 'name' => 'Main Power Tools Page'],
    ['url' => 'https://www.bosch-professional.com/eg/en/measuring-technology-131410-ocs-c/', 'name' => 'Measuring Technology'],
    ['url' => 'https://www.bosch-professional.com/eg/en/accessories-2790339-ocs-ac/', 'name' => 'Accessories']
];

// Step 3: Create unique list of pages to scrape
$pagesToScrape = [];
$seenUrls = [];

// Add base pages first
foreach ($basePages as $page) {
    if (!in_array($page['url'], $seenUrls)) {
        $pagesToScrape[] = $page;
        $seenUrls[] = $page['url'];
    }
}

// Add found categories (avoiding duplicates)
foreach ($foundCategories as $category) {
    if (!in_array($category['url'], $seenUrls)) {
        $pagesToScrape[] = [
            'url' => $category['url'],
            'name' => $category['name']
        ];
        $seenUrls[] = $category['url'];
    }
}

echo "📋 Final list of unique pages to scrape: " . count($pagesToScrape) . "\n";
foreach ($pagesToScrape as $page) {
    echo "   - {$page['name']}: {$page['url']}\n";
}
echo "\n";

$allPageData = [];
$successCount = 0;
$errorCount = 0;

echo "🔄 Scraping pages for JSON-LD data...\n\n";

foreach ($pagesToScrape as $page) {
    $data = scrapePage($page['url'], $page['name']);
    
    if ($data) {
        $allPageData[$page['name']] = [
            'page_name' => $page['name'],
            'page_url' => $page['url'],
            'json_data' => $data,
            'entry_count' => count($data),
            'scraped_at' => date('Y-m-d H:i:s')
        ];
        $successCount++;
    } else {
        $errorCount++;
    }
    
    // Add delay to be respectful to the server
    sleep(1);
    
    echo "\n";
}

// Summary
echo "📊 Scraping Summary:\n";
echo "===================\n";
echo "Total unique pages: " . count($pagesToScrape) . "\n";
echo "Successfully scraped: {$successCount}\n";
echo "Failed: {$errorCount}\n";
echo "Success rate: " . (count($pagesToScrape) > 0 ? round(($successCount / count($pagesToScrape)) * 100, 1) : 0) . "%\n\n";

// Save all data
if ($successCount > 0) {
    echo "💾 Saving data to file...\n";
    if (saveToFile($allPageData, $outputFile)) {
        echo "\n🎉 Scraping completed successfully!\n";
        echo "📁 Data saved to: {$outputFile}\n";
        echo "📊 Total unique pages scraped: {$successCount}\n";
        
        // Show page details
        echo "\n📋 Scraped Pages:\n";
        foreach ($allPageData as $name => $data) {
            echo "  ✅ {$data['page_name']}: {$data['entry_count']} JSON-LD entries\n";
        }
        
    } else {
        echo "\n❌ Failed to save data\n";
    }
} else {
    echo "\n❌ No data was scraped successfully\n";
}

echo "\n🔧 Scraping process finished.\n";
?>

<?php

namespace Dgvirtual\Components\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

/**
 * Controller that handles the async fetch request from the famous-quotes
 * component's JavaScript.
 *
 * It checks the cache first, and only calls the external ZenQuotes API when
 * no cached quote is available — preserving the same caching logic that was
 * previously handled entirely in FamousQuotesComponent.
 *
 * Required route (add to app/Config/Routes.php):
 *   $routes->get('famous-quotes/fetch', '\Dgvirtual\Components\Controllers\FamousQuotes::fetch');
 */
class FamousQuotes extends Controller
{
    protected string $famousQuotesAPINode = 'https://zenquotes.io/api/random';
    protected array $fallback             = [
        'text'   => 'The only way to do great work is to love what you do',
        'author' => 'Steve Jobs',
    ];

    /**
     * Returns a quote as JSON. Checks the cache first; if a cached quote
     * exists it is returned immediately. Otherwise the external API is
     * called, the result is cached, and then returned.
     */
    public function fetch(): ResponseInterface
    {
        helper('cache');

        $seconds  = $this->request->getGet('seconds');
        $seconds  = (is_numeric($seconds) ? (int) round((float) $seconds, 0) : 5);
        $cacheKey = 'famous_quote';

        // Try cache first
        if ($cachedQuote = cache($cacheKey)) {
            return $this->response->setJSON([
                'quote'  => $cachedQuote,
                'cached' => true,
            ]);
        }

        // Fetch from the external API
        try {
            $response  = file_get_contents($this->famousQuotesAPINode);
            $quoteData = json_decode($response, true);

            if (isset($quoteData[0])) {
                $quote = [
                    'text'   => $quoteData[0]['q'],
                    'author' => $quoteData[0]['a'],
                ];

                // Cache for the requested duration
                cache()->save($cacheKey, $quote, $seconds);

                return $this->response->setJSON([
                    'quote'  => $quote,
                    'cached' => false,
                ]);
            }
        } catch (Exception $e) {
            // Fall through to fallback
        }

        // Return the fallback quote on any error
        return $this->response->setJSON([
            'quote'  => $this->fallback,
            'cached' => false,
        ]);
    }
}

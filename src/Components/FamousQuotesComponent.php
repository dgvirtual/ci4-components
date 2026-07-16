<?php

namespace Dgvirtual\Components\Components;

/**
 * This example controlled component allows to retrieve a random famous quote
 * from the ZenQuotes API and cache it for a specified number of seconds.
 *
 * How it works:
 * 1. On render, the component checks the cache first.
 * 2. If a cached quote exists, it is rendered directly in the HTML (server-side).
 * 3. If no cached quote exists, the component renders a loading placeholder
 *    with JavaScript that fetches the quote asynchronously from the
 *    `famous-quotes/fetch` endpoint. The endpoint caches the result so
 *    subsequent page loads serve the cached version directly.
 *
 * Number of seconds can be passed as a parameter to the component.
 * If you want to add a title to the component, you can pass it as a $slot variable.
 *
 * Usage in views:
 *   <x-famous-quotes />
 *   <x-famous-quotes seconds="30" />
 *   <x-famous-quotes seconds="30">Famous Quotes</x-famous-quotes>
 *
 * Required route (add to app/Config/Routes.php):
 *   $routes->get('famous-quotes/fetch', '\Dgvirtual\Components\Controllers\FamousQuotes::fetch');
 *
 * See README.md for more information.
 */

use Dgvirtual\Components\Libraries\Component;

class FamousQuotesComponent extends Component
{
    protected $defaultNoOfSeconds = 3600;
    protected array $fallback     = [
        'text'   => 'The only way to do great work is to love what you do',
        'author' => 'Steve Jobs',
    ];

    public function render(): string
    {
        $seconds = $this->getSeconds();

        // Try cache first
        helper('cache');
        $cacheKey = 'famous_quote';

        if ($cachedQuote = cache($cacheKey)) {
            // Cached — render the quote directly (server-side)
            return $this->renderView($this->view, [
                'quote'   => $cachedQuote,
                'seconds' => $seconds,
                'slot'    => $this->data['slot'] ?? '',
                'cached'  => true,
            ]);
        }

        // Not cached — render a loading placeholder; JavaScript will
        // fetch the quote from the API endpoint and populate the DOM.
        // The fallback quote is embedded in the JS so it displays even
        // when the route or external API is unavailable.
        return $this->renderView($this->view, [
            'quote'    => null,
            'seconds'  => $seconds,
            'slot'     => $this->data['slot'] ?? '',
            'cached'   => false,
            'fallback' => $this->fallback,
        ]);
    }

    /**
     * Returns the number of seconds to cache the quote.
     */
    protected function getSeconds(): int
    {
        if (isset($this->data['seconds']) && is_numeric($this->data['seconds'])) {
            return (int) round($this->data['seconds'], 0);
        }

        return $this->defaultNoOfSeconds;
    }
}

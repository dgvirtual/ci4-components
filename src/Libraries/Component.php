<?php

/**
 * This file is adapted from Bonfire2 project,
 * (c) Lonnie Ezell <lonnieje@gmail.com>
 *
 * Adapted as standalone module for CodeIgniter 4 by
 * Donatas Glodenis <dg@lapas.info>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Dgvirtual\Components\Libraries;

use Throwable;

/**
 * Class Component
 *
 * Provides the basic functionality used when rendering a
 * view component. This includes everything needed to render
 * a component that does not have a class associated with it.
 */
class Component
{
    /**
     * All collected attributes for the tag
     *
     * @var string
     */
    protected $attributes;

    /**
     * The values that can be used
     *
     * @var array
     */
    protected $data = [];

    /**
     * @var string
     */
    protected $view;

    /**
     * How many seconds the renderer should cache this component's rendered HTML.
     * Set to an integer to enable renderer-level output caching for this component.
     * Null (default) means no renderer caching.
     *
     * Note: this is separate from any data-caching the component may do internally.
     * See README for details and caveats.
     */
    public ?int $cacheTtl = null;

    /**
     * Stores the view name.
     *
     * @param string $view The name of the view to be rendered.
     *
     * @codeCoverageIgnore
     */
    public function withView(string $view): Component
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Set the data that should be passed along to the view.
     *
     * @param array $data The data to be passed to the view.
     *
     * @codeCoverageIgnore
     */
    public function withData(array $data): Component
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Returns extra data to mix into the renderer's cache key for this component.
     *
     * Override this method when the rendered output depends on something beyond
     * the component's attributes (e.g. the currently logged-in user, locale, etc.)
     * so that different contexts produce different cache entries.
     *
     * Example:
     *   public function cacheKey(): string
     *   {
     *       return (string) session('user_id');
     *   }
     */
    public function cacheKey(): string
    {
        return '';
    }

    /**
     * Returns the processed component view.
     *
     * @return string The rendered view content.
     *
     * @codeCoverageIgnore
     */
    public function render(): string
    {
        return $this->renderView($this->view, []);
    }

    /**
     * Renders the view when no corresponding class has been found.
     *
     * @param string $view The Component view file to be rendered.
     * @param array  $data The data to be passed to the view.
     *
     * @return string The rendered view content.
     */
    protected function renderView(string $view, array $data): string
    {
        // make sure the buffer is closed clean in case of error/exception
        return (static function (string $view, $data) {
            extract($data);
            ob_start();

            try {
                include $view;

                return ob_get_clean() ?: '';
            } catch (Throwable $e) {
                ob_end_clean();

                throw $e;
            } finally {
                if (ob_get_length()) {
                    ob_end_clean();
                }
            }
        })($view, $data);
    }
}

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
     * Stores the view name.
     *
     * @param string $view The name of the view to be rendered.
     * @return Component
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
     * @return Component
     * @codeCoverageIgnore
     */
    public function withData(array $data): Component
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Returns the processed component view.
     *
     * @return string The rendered view content.
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
     * @param array $data The data to be passed to the view.
     * @return string The rendered view content.
     */
    protected function renderView(string $view, array $data): string
    {
        return (static function (string $view, $data) {
            extract($data);
            ob_start();
            try {
                eval('?>' . file_get_contents($view));
                return ob_get_clean() ?: '';
            } catch (\Throwable $e) {
                ob_end_clean();
                throw $e;
            }
        })($view, $data);
    }
}

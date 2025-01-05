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

namespace dgvirtual\Components\Libraries;

use CodeIgniter\View\ViewDecoratorInterface;

/**
 * Class ComponentDecorator
 *
 * Enables rendering of View Components into the views.
 */
class ComponentDecorator implements ViewDecoratorInterface
{
    private static ?ComponentRenderer $components = null;

    /**
     * Decorates the given HTML with rendered components.
     *
     * @param string $html The HTML content to be decorated.
     * @return string The decorated HTML content.
     */
    public static function decorate(string $html): string
    {
        $components = self::factory();

        return $components->render($html);
    }

    /**
     * Factory method to create a new instance of ComponentRenderer.
     *
     * @return ComponentRenderer The instance of ComponentRenderer.
     */
    private static function factory(): ComponentRenderer
    {
        if (self::$components === null) {
            self::$components = new ComponentRenderer();
        }

        return self::$components;
    }
}

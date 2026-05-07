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

use Dgvirtual\Components\Config\Components;
use RuntimeException;
use Throwable;

/**
 * Class ComponentRenderer
 *
 * Responsible for rendering view components within the views.
 */
class ComponentRenderer
{
    /**
     * Caches resolved view file paths for the lifetime of the request so that
     * the filesystem is only hit once per unique component name.
     *
     * @var array<string, string>
     */
    private static array $viewPathCache = [];

    public function __construct()
    {
        helper('inflector');
        ini_set('pcre.backtrack_limit', '-1');
    }

    /**
     * Examines the given string and parses any view components,
     * returning the modified string.
     *
     * Called by the View class' render method.
     *
     * @param string|null $output The HTML content to be processed.
     *
     * @return string The processed HTML content with rendered components.
     */
    public function render(?string $output): string
    {
        if ($output === null || $output === '') {
            return '';
        }

        /**
         * Try to locate any custom tags, with names like: x-sidebar, x-btn, etc.
         * Set timers to measure performance of each step.
         */
        service('timer')->start('self-closing');
        $output = $this->renderSelfClosingTags($output);
        service('timer')->stop('self-closing');

        service('timer')->start('paired-tags');
        $output = $this->renderPairedTags($output);
        service('timer')->stop('paired-tags');

        return $output;
    }

    /**
     * Finds and renders any self-closing tags, i.e. <x-foo />
     *
     * @param string $output The HTML content to be processed.
     *
     * @return string The HTML content with self-closing tags replaced by the view
     *                component content.
     */
    private function renderSelfClosingTags(string $output): string
    {
        // Pattern borrowed from Laravel's ComponentTagCompiler
        $pattern = "/
            <
                \\s*
                x[-\\:](?<name>[\\w\\-\\:\\.]*)
                \\s*
                (?<attributes>
                    (?:
                        \\s+
                        (?:
                            (?:
                                \\{\\{\\s*\\\$attributes(?:[^}]+?)?\\s*\\}\\}
                            )
                            |
                            (?:
                                [\\w\\-:.@]+
                                (
                                    =
                                    (?:
                                        \\\"[^\\\"]*\\\"
                                        |
                                        \\'[^\\']*\\'
                                        |
                                        [^\\'\\\"=<>]+
                                    )
                                )?
                            )
                        )
                    )*
                    \\s*
                )
            \\/>
        /x";

        /*
            $matches[0] = full tags matched
            $matches[name] = tag name (minus the 'x-')
            $matches[attributes] = array of attribute string (class="foo")
         */
        return preg_replace_callback($pattern, function ($match) {
            $view       = $this->locateView($match['name']);
            $attributes = $this->parseAttributes($match['attributes']);
            $component  = $this->factory($match['name'], $view);

            return $this->renderCached($match['name'], $view, $attributes, $component);
        }, $output);
    }

    /**
     * Finds and renders any paired tags, i.e. <x-foo>content</x-foo>
     *
     * @param string $output The HTML content to be processed.
     *
     * @return string The HTML content with rendered paired tags.
     */
    private function renderPairedTags(string $output): string
    {
        $pattern = '/(?(DEFINE)(?<marker>x-))
                        <\g<marker>(?<name>\w[\w\-\:\.]+[^\>\/\s\/])
                                   (?<attributes>[\s\S\=\'\"]+?)??>
                            (?(?<!\/>) # Not paired so ignore the rest
                                (?<slot>.*?)??
                        <\/\g<marker>\k<name>\s*>
                            )
                    /uismx';

        /*
            $match['name']       = tag name (minus the `x-`)
            $match['attributes'] = string of tag attributes (class="foo")
            $match['slot']       = the content inside the tags
        */

        do {
            try {
                $output = preg_replace_callback($pattern, function ($match) {
                    $view               = $this->locateView($match['name']);
                    $attributes         = $this->parseAttributes($match['attributes']);
                    $attributes['slot'] = $match['slot'];
                    $component          = $this->factory($match['name'], $view);

                    return $this->renderCached($match['name'], $view, $attributes, $component);
                }, $output, -1, $replaceCount);
            } catch (Throwable $e) {
                break;
            }
        } while ($replaceCount !== 0);

        return $output ?? preg_last_error();
    }

    /**
     * Parses a string to grab any key/value pairs, HTML attributes.
     *
     * @param string $attributeString The string containing HTML attributes.
     *
     * @return array The parsed attributes as an associative array.
     */
    private function parseAttributes(string $attributeString): array
    {
        // Pattern borrowed from Laravel's ComponentTagCompiler
        $pattern = '/
            (?<attribute>[\w\-:.@]+)
            (
                =
                (?<value>
                    (
                        \"[^\"]+\"
                        |
                        \\\'[^\\\']+\\\'
                        |
                        [^\s>]+
                    )
                )
            )?
        /x';

        if (! preg_match_all($pattern, $attributeString, $matches, PREG_SET_ORDER)) {
            return [];
        }

        $attributes = [];

        foreach ($matches as $match) {
            $attributes[$match['attribute']] = $this->stripQuotes($match['value']);
        }

        return $attributes;
    }

    /**
     * Renders the view when no corresponding class has been found.
     *
     * @param string $view The view file to be rendered.
     * @param array  $data The data to be passed to the view.
     *
     * @return string The rendered view content.
     */
    private function renderView(string $view, array $data): string
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

    /**
     * Attempts to locate the view and/or class that
     * will be used to render this component. By default,
     * the only thing that is needed is a view, but a
     * Component class can also be found if more power is needed.
     *
     * If a class is used, the name is expected to be
     * <viewName>Component.php
     *
     * @param string $name The name of the component.
     * @param string $view The view file associated with the component.
     *
     * @return Component|null The instance of the component or null if not found.
     */
    private function factory(string $name, string $view): ?Component
    {
        // Locate the class in the same folder as the view
        $class    = pascalize(str_replace('-', '_', $name)) . 'Component';
        $filePath = str_replace($name . '.php', $class . '.php', $view);

        if (empty($filePath) || ! file_exists($filePath)) {
            return null;
        }

        // Use the locator service to get the fully qualified class name
        $className = service('locator')->getClassname($filePath);

        if (class_exists($className)) {
            return (new $className())->withView($view);
        }

        log_message('debug', 'Component class not found: ' . $className);

        return null;
    }

    /**
     * Locate the view file used to render the component.
     * The file's name must match the name of the component,
     * minus the 'x-'.
     *
     * @param string $name The name of the component.
     *
     * @return string The path to the view file.
     *
     * @throws RuntimeException If the view file is not found.
     */
    private function locateView(string $name): string
    {
        if (isset(self::$viewPathCache[$name])) {
            return self::$viewPathCache[$name];
        }

        // DONATO: removed Bonfire2 theme-related code here, changed lookup paths config file

        $componentsLookupPaths = $this->getComponentsLookupPaths();

        foreach ($componentsLookupPaths as $componentPath) {
            $filePath = $componentPath . $name . '.php';

            if (is_file($filePath)) {
                self::$viewPathCache[$name] = $filePath;

                return $filePath;
            }
        }

        throw new RuntimeException('View not found for component: ' . $name);
        // @todo look in all normal namespaces
    }

    /**
     * Renders a component, serving from or storing to CI4's cache when a TTL
     * is configured.
     *
     * For class-based components the TTL is read from Component::$cacheTtl.
     * For view-only components it is read from Components::$viewCacheTtl in the
     * app (or module) config. A null TTL skips caching entirely.
     *
     * @param string         $name       Component name (e.g. "button-green").
     * @param string         $view       Absolute path to the view file.
     * @param array          $attributes Parsed tag attributes (includes 'slot' for paired tags).
     * @param Component|null $component  Class-based component instance, or null for view-only.
     *
     * @return string The rendered HTML.
     */
    private function renderCached(string $name, string $view, array $attributes, ?Component $component): string
    {
        $cacheTtl = $component instanceof Component
            ? $component->cacheTtl
            : $this->getViewCacheTtl();

        $cacheKey = null;

        if ($cacheTtl !== null) {
            $cacheKey = $this->buildCacheKey($name, $view, $attributes, $component);
            $cached   = cache($cacheKey);

            if ($cached !== null && $cached !== false) {
                return $cached;
            }
        }

        $result = $component instanceof Component
            ? $component->withView($view)->withData($attributes)->render()
            : $this->renderView($view, $attributes);

        if ($cacheKey !== null) {
            cache()->save($cacheKey, $result, $cacheTtl);
        }

        return $result;
    }

    /**
     * Builds a deterministic cache key for a component invocation.
     *
     * The key is derived from the component name, the view file path and its
     * last-modified time (so it invalidates automatically after a deploy that
     * changes the file), the serialised attributes, and any extra contributor
     * returned by Component::cacheKey().
     *
     * @param string         $name       Component name.
     * @param string         $view       Absolute path to the view file.
     * @param array          $attributes Parsed tag attributes.
     * @param Component|null $component  Class-based component instance, or null.
     *
     * @return string Cache key string prefixed with 'xcomp_'.
     */
    private function buildCacheKey(string $name, string $view, array $attributes, ?Component $component): string
    {
        $fileMtime = @filemtime($view) ?: 0;
        $extraKey  = ($component instanceof Component) ? $component->cacheKey() : '';

        return 'xcomp_' . md5($name . $view . $fileMtime . serialize($attributes) . $extraKey);
    }

    /**
     * Returns the configured default cache TTL for view-only components.
     *
     * @return int|null Seconds, or null if caching is disabled.
     */
    private function getViewCacheTtl(): ?int
    {
        try {
            /** @disregard */
            return config(\Config\Components::class)->viewCacheTtl;
        } catch (Throwable $e) {
            return config(Components::class)->viewCacheTtl;
        }
    }

    /**
     * Retrieves the lookup paths for component views.
     *
     * @return array The array of lookup paths.
     */
    private function getComponentsLookupPaths(): array
    {
        try {
            /** @disregard */
            $componentsLookupPaths = config(\Config\Components::class)->componentsLookupPaths;
        } catch (Throwable $e) {
            // If no Config\Components file is created, fall back to the module configuration
            $componentsLookupPaths = config(Components::class)->componentsLookupPaths;
        }

        return $componentsLookupPaths;
    }

    /**
     * Removes surrounding quotes from a string.
     *
     * @param string $string The string to be processed.
     *
     * @return string The string without surrounding quotes.
     */
    private function stripQuotes(string $string): string
    {
        return trim($string, "\\'\"");
    }
}

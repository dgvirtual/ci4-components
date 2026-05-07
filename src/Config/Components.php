<?php

/**
 * Configuration class for the Components module.
 *
 * This class defines the lookup paths for component views.
 * If you want to change the configuration, it is preferably done in a version of this file
 * put into app/Config/ and namespaced as Config; It will be autodiscovered by the module
 * and used instead of this file.
 */

namespace Dgvirtual\Components\Config;

use CodeIgniter\Config\BaseConfig;

class Components extends BaseConfig
{
    /**
     * @var array Paths to look for component views.
     */
    public $componentsLookupPaths = [
        // your local components
        APPPATH . 'Views/Components/',
        // example components
        __DIR__ . '/../Components/',
    ];

    /**
     * Default renderer cache TTL (seconds) for view-only components (those without
     * a companion Component class). Set to null to disable caching (default).
     *
     * When set to a positive integer, the rendered HTML of every view-only component
     * is stored in CI4's cache service and re-used for that many seconds. The cache
     * key is derived from the component name, its source file mtime, and the
     * serialized attributes, so it automatically invalidates when the file changes
     * or when different attribute values are used.
     *
     * Class-based components control their own TTL via the $cacheTtl property on
     * the Component class, so this setting has no effect on them.
     */
    public ?int $viewCacheTtl = null;
}

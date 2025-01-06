<?php

/**
 * Configuration class for the Components module.
 *
 * This class defines the lookup paths for component views.
 * If you want to change the configuration, it is preferably done in a version of this file
 * put into app/Config/ and namespaced as Config; It will be autodiscovered by the module
 * and used instead of this file.
 */

namespace dgvirtual\Components\Config;

use CodeIgniter\Config\BaseConfig;

class Components extends BaseConfig
{
    /**
     * @var array $componentsLookupPaths Paths to look for component views.
     */
    public $componentsLookupPaths = [
        // your local components
        APPPATH . 'Views/Components/',
        // example components
        __DIR__ . '/../Views/Components/',
    ];
}

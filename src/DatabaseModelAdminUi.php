<?php

namespace Morningtrain\WP\DatabaseModelAdminUi;

use Morningtrain\PHPLoader\Loader;
use Morningtrain\WP\View\View;

class DatabaseModelAdminUi
{

    public static function setup(string|array $modelsDir): void
    {
        Loader::create($modelsDir)->call('initModelAdminUi');

        View::addNamespace('wpdbmodeladminui', dirname(__DIR__) . '/resources/views');
    }

}
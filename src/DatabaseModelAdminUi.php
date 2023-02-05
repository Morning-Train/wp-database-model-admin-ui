<?php

namespace Morningtrain\WP\DatabaseModelAdminUi;

use Morningtrain\PHPLoader\Loader;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\AcfEloquentModelLocation;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\Helper;
use Morningtrain\WP\Hooks\Hook;
use Morningtrain\WP\View\View;

class DatabaseModelAdminUi
{

    public static function setup(string|array $eloquentModelsDir): void
    {
        Helper::setEloquentModelsDirs($eloquentModelsDir);

        Loader::create($eloquentModelsDir)
            ->call('setupAdminUi');

        View::addNamespace('wpdbmodeladminui', dirname(__DIR__) . '/resources/views');

        static::setupAcf();
    }

    private static function setupAcf(): void
    {
        if (! class_exists('ACF')) {
            return;
        }

        Hook::action('acf/init', function () {
            acf_register_location_type(AcfEloquentModelLocation::class);
        });
    }

}
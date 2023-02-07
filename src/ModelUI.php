<?php

namespace Morningtrain\WP\DatabaseModelAdminUi;

use Morningtrain\PHPLoader\Loader;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\AcfEloquentModelLocation;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\Helper;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPageColumn;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPageRowAction;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPages;
use Morningtrain\WP\Hooks\Hook;
use Morningtrain\WP\View\View;

class ModelUI
{

    public static function setup(string|array $eloquentModelsDir): void
    {
        Helper::setEloquentModelsDirs($eloquentModelsDir);

        Loader::create($eloquentModelsDir);

        View::addNamespace('wpdbmodeladminui', dirname(__DIR__) . '/resources/views');

        static::setupAcf();

        Hook::action('wp_loaded', function () {
            ModelPages::setupModelPages();
        })->priority(11);
    }

    public static function modelPage(string $tableSlug, string $model): ModelPage
    {
        return new ModelPage($tableSlug, $model);
    }

    public static function modelPageColumn(string $slug): ModelPageColumn
    {
        return new ModelPageColumn($slug);
    }

    public static function modelPageRowAction(string $slug, callable|string $renderCallback): ModelPageRowAction
    {
        return new ModelPageRowAction($slug, $renderCallback);
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
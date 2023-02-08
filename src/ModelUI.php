<?php

namespace Morningtrain\WP\DatabaseModelAdminUi;

use Morningtrain\PHPLoader\Loader;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\AcfEloquentModelLocation;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\Helper;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage\ModelPage;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage\AcfEditableMetaBox;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage\AcfSettings;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage\Column;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage\RowAction;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPages;
use Morningtrain\WP\Hooks\Hook;
use Morningtrain\WP\View\View;

class ModelUI
{

    public static function setup(string|array $eloquentModelsDir): void
    {
        Helper::setEloquentModelsDirs($eloquentModelsDir);

        Loader::create($eloquentModelsDir)
            ->callStatic('setupAdminUi');

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

    public static function column(string $slug): Column
    {
        return new Column($slug);
    }

    public static function rowAction(string $slug, callable|string $renderCallback): RowAction
    {
        return new RowAction($slug, $renderCallback);
    }

    public static function acfSettings(): AcfSettings
    {
        return new AcfSettings();
    }

    public static function acfEditableMetaBox(string $slug, callable|string $renderCallback): AcfEditableMetaBox
    {
        return new AcfEditableMetaBox($slug, $renderCallback);
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
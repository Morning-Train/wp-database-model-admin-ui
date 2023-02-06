<?php

namespace Morningtrain\WP\DatabaseModelAdminUi;

use Illuminate\Database\Eloquent\Model;
use Morningtrain\PHPLoader\Loader;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\Acf\AcfEloquentModelLocation;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\Helper;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPages;
use Morningtrain\WP\Hooks\Hook;
use Morningtrain\WP\View\View;

class DatabaseModelAdminUi
{

    public static function setup(string|array $eloquentModelsDir): void
    {
        Helper::setEloquentModelsDirs($eloquentModelsDir);

        Loader::create($eloquentModelsDir);

        View::addNamespace('wpdbmodeladminui', dirname(__DIR__) . '/resources/views');

        static::setupAcf();

        ModelPages::setupModelPages();
    }

    public static function addModelPage(string $model, string $tableSlug): ModelPage
    {
        return new ModelPage($model, $tableSlug);
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
<?php

namespace Morningtrain\WP\DatabaseModelAdminUi;

use Morningtrain\PHPLoader\Loader;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\AcfEloquentModelLocation;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\Helper;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage\AcfCreatePage;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage\AcfLoadField;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage\AdminTableView;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage\ModelPage;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage\MetaBox;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage\AcfEditPage;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage\Column;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage\RowAction;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage\ViewPage;
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

        View::addNamespace('wpdbmodeladminui', dirname(__DIR__) . '/views');

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

    public static function adminTableView(string $urlKey, ?string $urlValue = null): AdminTableView
    {
        return new AdminTableView($urlKey, $urlValue);
    }

    public static function viewPage(): ViewPage
    {
        return new ViewPage();
    }

    public static function acfCreatePage(): AcfCreatePage
    {
        return new AcfCreatePage();
    }

    public static function acfEditPage(): AcfEditPage
    {
        return new AcfEditPage();
    }

    public static function acfLoadField(string $slug, callable|string $renderCallback): AcfLoadField
    {
        return new AcfLoadField($slug, $renderCallback);
    }

    public static function metaBox(string $slug, callable|string $renderCallback): MetaBox
    {
        return new MetaBox($slug, $renderCallback);
    }

    public static function getCurrentModelPage(): ?ModelPage
    {
        return ModelPages::getCurrentModelPage();
    }

    public static function getCurrentModelInstance(): \Illuminate\Database\Eloquent\Model|null
    {
        $modelPage = ModelPages::getCurrentModelPage();
        $modelId = $_GET['model_id'] ?? null;

        if ($modelPage === null || empty($modelId)) {
            return null;
        }

        return $modelPage->model::query()
            ->find($modelId);
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

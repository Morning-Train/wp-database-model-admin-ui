<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Classes;

use Morningtrain\WP\DatabaseModelAdminUi\Classes\AdminTable\AcfEditable;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\AdminTable\AdminUi;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\AdminTable\Readable;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\AdminTable\Removable;
use Morningtrain\WP\DatabaseModelAdminUi\Handlers\AdminUiHandler;
use Morningtrain\WP\Hooks\Hook;

class ModelPages
{

    /** @var ModelPage[] $modelPages */
    private static array $modelPages = [];

    public static function setupModelPages(): void
    {
        Hook::action('admin_menu', [AdminUiHandler::class, 'addModelMenuPages']);
        Hook::filter('set-screen-option', [AdminUiHandler::class, 'setPerPageScreenOption']);
        Hook::filter('current_screen', [AdminUiHandler::class, 'addScreenOption']);
        Hook::filter('parent_file', [AdminUiHandler::class, 'handleActiveAdminMenu']);

        Hook::action('wp_loaded', function () {
            static::setupAdminTables();
        })->priority(11);
    }

    public static function getModelPages(): array
    {
        return static::$modelPages;
    }

    public static function setModelPageForList(ModelPage $modelPage): void
    {
        static::$modelPages[$modelPage->pageSlug] = $modelPage;
    }

    private static function setupAdminTables(): void
    {
        foreach (static::$modelPages as $modelPage) {
            new AdminUi($modelPage);

            if ($modelPage->removable) {
                new Removable($modelPage);
            }

            if ($modelPage->acfEditable) {
                new AcfEditable($modelPage);
            }
        }
    }

}
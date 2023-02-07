<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Classes;

use Morningtrain\WP\DatabaseModelAdminUi\Handlers\AcfEditableHandler;
use Morningtrain\WP\DatabaseModelAdminUi\Handlers\AdminUiHandler;
use Morningtrain\WP\Hooks\Hook;

class ModelPages
{

    /** @var ModelPage[] $modelPages */
    private static array $modelPages = [];

    public static function setupModelPages(): void
    {
        Hook::action('wp_loaded', function () {
            Hook::action('admin_menu', [AdminUiHandler::class, 'addModelMenuPages']);
            Hook::filter('set-screen-option', [AdminUiHandler::class, 'setPerPageScreenOption']);
            Hook::action('current_screen', [AdminUiHandler::class, 'addScreenOption']);

            $currentModelPage = Helper::getCurrentModePageFromUrlPage();

            if (empty($currentModelPage)) {
                return;
            }

            if ($currentModelPage->acfEditable) {
                Hook::action('admin_menu', [AcfEditableHandler::class, 'addAcfEditMenuPage']);
                Hook::action('admin_init', [AcfEditableHandler::class, 'checkForNonExistingAcfEditableModel']);
                Hook::filter('acf/load_value', [AcfEditableHandler::class, 'handleLoadValueForAcfModel']);
                Hook::filter('acf/save_post', [AcfEditableHandler::class, 'handleSaveValueForAcfModel']);
                Hook::filter('parent_file', [AcfEditableHandler::class, 'fixSelectedAdminMenuForAcfEditable']);
            }

            if ($currentModelPage->removable) {
                Hook::action('admin_init', [AdminUiHandler::class, 'checkForModelDeleting']);
            }
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

}
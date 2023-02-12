<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Classes;

use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage\ModelPage;
use Morningtrain\WP\DatabaseModelAdminUi\Handlers\AcfEditableHandler;
use Morningtrain\WP\DatabaseModelAdminUi\Handlers\AdminUiHandler;
use Morningtrain\WP\Hooks\Hook;

class ModelPages
{
    /** @var ModelPage[] */
    private static array $modelPages = [];
    private static ?ModelPage $currentModelPage = null;

    public static function setupModelPages(): void
    {
        static::setupCurrentModelPage();

        Hook::action('admin_menu', [AdminUiHandler::class, 'addModelMenuPages']);
        Hook::filter('set-screen-option', [AdminUiHandler::class, 'setPerPageScreenOption']);
        Hook::action('current_screen', [AdminUiHandler::class, 'addScreenOption']);
        Hook::action('current_screen', [AdminUiHandler::class, 'addMetaBoxes']);

        $currentModelPage = static::getCurrentModelPage();

        if ($currentModelPage === null) {
            return;
        }

        if ($currentModelPage->acfEditable) {
            Hook::action('admin_menu', [AcfEditableHandler::class, 'addAcfEditMenuPage']);
            Hook::action('admin_init', [AcfEditableHandler::class, 'checkForNonExistingAcfEditableModel']);
            Hook::filter('acf/pre_load_post_id', [AcfEditableHandler::class, 'handlePreLoadPostIdForAcfModel']);
            Hook::filter('acf/decode_post_id', [AcfEditableHandler::class, 'handleDecodePostIdForAcfModel']);
            Hook::action('acf/load_value', [AcfEditableHandler::class, 'handleLoadValueForAcfModel']);
            Hook::filter('acf/pre_load_metadata', [AcfEditableHandler::class, 'handleLoadMetadataForAcfModel']);
            Hook::filter('acf/pre_update_metadata', [AcfEditableHandler::class, 'handleSaveMetadataForAcfModel']);
            Hook::action('acf/save_post', [AcfEditableHandler::class, 'handleSaveValueForAcfModel']);
            Hook::filter('parent_file', [AcfEditableHandler::class, 'fixSelectedAdminMenuForAcfEditable']);
        }

        if ($currentModelPage->removable) {
            Hook::action('admin_init', [AdminUiHandler::class, 'checkForModelDeleting']);
        }
    }

    public static function getModelPages(): array
    {
        return static::$modelPages;
    }

    public static function addModelPageToList(ModelPage $modelPage): void
    {
        static::$modelPages[$modelPage->pageSlug] = $modelPage;
    }

    public static function getCurrentModelPage(): ?ModelPage
    {
        return static::$currentModelPage;
    }

    private static function setupCurrentModelPage(): void
    {
        $page = $_GET['page'] ?? null;

        if (empty($page)) {
            return;
        }

        foreach (static::getModelPages() as $modelPage) {
            if ($page === $modelPage->pageSlug) {
                static::$currentModelPage = $modelPage;
                break;
            }

            if ($modelPage->acfEditable && $page === $modelPage->acfEditablePageSlug) {
                static::$currentModelPage = $modelPage;
                break;
            }
        }
    }
}

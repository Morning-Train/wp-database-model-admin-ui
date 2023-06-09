<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Classes;

use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage\ModelPage;
use Morningtrain\WP\DatabaseModelAdminUi\Handlers\AcfCreatePageHandler;
use Morningtrain\WP\DatabaseModelAdminUi\Handlers\AcfEditPageHandler;
use Morningtrain\WP\DatabaseModelAdminUi\Handlers\AdminUiHandler;
use Morningtrain\WP\DatabaseModelAdminUi\Handlers\ViewPageHandler;
use Morningtrain\WP\Hooks\Hook;

class ModelPages
{
    /** @var ModelPage[] */
    private static array $modelPages = [];

    private static ?ModelPage $currentModelPage = null;

    public static function setupModelPages(): void
    {
        static::setupCurrentModelPage();

        Hook::action('admin_menu', [AdminUiHandler::class, 'addModelMenuPages'])->priority(101);
        Hook::filter('set-screen-option', [AdminUiHandler::class, 'setPerPageScreenOption']);
        Hook::action('current_screen', [AdminUiHandler::class, 'addScreenOption']);
        Hook::action('current_screen', [AdminUiHandler::class, 'addMetaBoxes']);

        Hook::filter('acf/pre_load_metadata', [AdminUiHandler::class, 'handleGetFieldReturnValue']);

        $currentModelPage = static::getCurrentModelPage();

        if ($currentModelPage === null) {
            return;
        }

        if ($currentModelPage->viewPage !== null) {
            Hook::action('admin_menu', [ViewPageHandler::class, 'addReadableMenuPage']);
            Hook::filter('parent_file', [ViewPageHandler::class, 'fixSelectedAdminMenuForViewPage']);
        }

        if ($currentModelPage->acfCreatePage !== null) {
            Hook::action('admin_menu', [AcfCreatePageHandler::class, 'addAcfEditMenuPage']);
            Hook::filter('acf/pre_load_post_id', [AcfCreatePageHandler::class, 'handlePreLoadPostIdForAcfModel']);
            Hook::filter('acf/decode_post_id', [AcfCreatePageHandler::class, 'handleDecodePostIdForAcfModel']);
            Hook::action('acf/save_post', [AcfCreatePageHandler::class, 'handleSaveValueForAcfModel']);
            Hook::filter('parent_file', [AcfCreatePageHandler::class, 'fixSelectedAdminMenuForAcfCreatePage']);
        }

        if ($currentModelPage->acfEditPage !== null) {
            Hook::action('admin_menu', [AcfEditPageHandler::class, 'addAcfEditMenuPage']);
            Hook::filter('acf/pre_load_post_id', [AcfEditPageHandler::class, 'handlePreLoadPostIdForAcfModel']);
            Hook::filter('acf/decode_post_id', [AcfEditPageHandler::class, 'handleDecodePostIdForAcfModel']);
            Hook::action('acf/load_value', [AcfEditPageHandler::class, 'handleLoadValueForAcfModel']);
            Hook::filter('acf/pre_load_metadata', [AcfEditPageHandler::class, 'handleLoadMetadataForAcfModel']);
            Hook::filter('acf/pre_update_metadata', [AcfEditPageHandler::class, 'handleSaveMetadataForAcfModel']);
            Hook::action('acf/save_post', [AcfEditPageHandler::class, 'handleSaveValueForAcfModel']);
            Hook::filter('parent_file', [AcfEditPageHandler::class, 'fixSelectedAdminMenuForAcfEditable']);
        }

        if ($currentModelPage->removable) {
            Hook::action('admin_init', [AdminUiHandler::class, 'checkForModelDeleting']);
            Hook::action('acf/options_page/submitbox_major_actions', [AdminUiHandler::class, 'showDeleteLinkOnViewPage']);
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

            if ($modelPage->viewPage !== null && $page === $modelPage->viewPage->pageSlug) {
                static::$currentModelPage = $modelPage;
                break;
            }

            if ($modelPage->acfCreatePage !== null && $page === $modelPage->acfCreatePage->pageSlug) {
                static::$currentModelPage = $modelPage;
                break;
            }

            if ($modelPage->acfEditPage !== null && $page === $modelPage->acfEditPage->pageSlug) {
                static::$currentModelPage = $modelPage;
                break;
            }
        }
    }
}

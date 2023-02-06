<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Handlers;

use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPages;
use Morningtrain\WP\DatabaseModelAdminUi\Services\AdminUiMenuService;
use WP_Screen;

class AdminUiHandler
{

    public static function addModelMenuPages(): void
    {
        foreach (ModelPages::getModelPages() as $modelPage) {
            add_menu_page(
                $modelPage->pageTitle,
                $modelPage->menuTitle,
                $modelPage->capability,
                $modelPage->pageSlug,
                [AdminUiMenuService::class, 'displayMenuPage'],
                $modelPage->iconUrl,
                $modelPage->position
            );
        }
    }

    public static function setPerPageScreenOption($screen_option, string $option, int $value)
    {
        if ($option !== 'per_page') {
            return $screen_option;
        }

        return $value;
    }

    public static function addScreenOption(WP_Screen $screen): void
    {
        $page = $_GET['page'] ?? null;
        $modelPages = ModelPages::getModelPages();

        if ($page === null || ! array_key_exists($page, $modelPages)) {
            return;
        }

        add_screen_option('per_page', ['default' => 20, 'option' => 'per_page']);
    }

    public static function checkForModelDeleting(): void
    {
        $page = $_GET['page'] ?? null;
        $action = $_GET['action'] ?? null;
        $modelId = $_GET['model_id'] ?? null;
        $modelPage = ModelPages::getModelPages()[$page] ?? null;

        if (empty($page) || empty($action) || empty($modelId) || empty($modelPage)) {
            return;
        }

        if ($page !== $modelPage->pageSlug || $action !== 'delete' || ! is_numeric($modelId)) {
            return;
        }

        $modelPage->model::query()->where('id', $modelId)->delete();

        $url = $_SERVER['HTTP_REFERER'] ?? admin_url('admin.php?page=' . $modelPage->pageSlug);

        header('Location: ' . $url);
        exit();
    }

}
<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Handlers;

use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPages;
use Morningtrain\WP\DatabaseModelAdminUi\Enums\MetaBoxPage;
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
                $modelPage->listCapability,
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

    public static function addMetaBoxes(): void
    {
        $currentModelPage = ModelPages::getCurrentModelPage();

        if ($currentModelPage === null || empty($currentModelPage->metaBoxes)) {
            return;
        }

        $currentScreen = get_current_screen();

        if ($currentScreen === null) {
            return;
        }

        foreach ($currentModelPage->metaBoxes as $metaBox) {
            if ($metaBox->onPage === MetaBoxPage::ACF_EDIT && $currentScreen->id !== $currentModelPage->acfEditPage->pageSlug) {
                continue;
            }

            $screen = match ($metaBox->onPage) {
                MetaBoxPage::ADMIN_TABLE => $currentModelPage->pageScreen,
                MetaBoxPage::ACF_EDIT => 'acf_options_page',
                default => null
            };

            if (empty($screen)) {
                continue;
            }

            \add_meta_box(
                $metaBox->slug,
                $metaBox->title,
                function ($post, $metaBoxData) use ($metaBox) {
                    ($metaBox->renderCallback)(...$metaBoxData['args']);
                },
                $screen,
                $metaBox->context,
                $metaBox->priority,
                [
                    'modelId' => ! empty($_GET['model_id']) ? (int) $_GET['model_id'] : null,
                    'model' => $currentModelPage->model,
                ]
            );
        }
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

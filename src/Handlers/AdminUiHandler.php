<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Handlers;

use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPages;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage\ModelPage;
use Morningtrain\WP\DatabaseModelAdminUi\Enums\MetaBoxPage;
use Morningtrain\WP\DatabaseModelAdminUi\Services\AdminUiMenuService;
use WP_Screen;

class AdminUiHandler
{
    public static function addModelMenuPages(): void
    {
        // Ensure that pages with a parent are registered before those without and in correct order
        $modelPages = ModelPages::getModelPages();
        usort($modelPages, fn (ModelPage $a, ModelPage $b) => $a->position <=> $b->position);
        usort($modelPages, fn (ModelPage $a, ModelPage $b) => $a->parentSlug <=> $b->parentSlug);

        foreach ($modelPages as $modelPage) {
            if ($modelPage->parentSlug === null) {
                $modelPage->setPageScreen(
                    add_menu_page(
                        $modelPage->pageTitle,
                        $modelPage->menuTitle,
                        $modelPage->capability,
                        $modelPage->pageSlug,
                        [AdminUiMenuService::class, 'displayMenuPage'],
                        $modelPage->iconUrl,
                        $modelPage->position
                    )
                );

                continue;
            }

            $modelPage->setPageScreen(
                add_submenu_page(
                    $modelPage->parentSlug,
                    $modelPage->pageTitle,
                    $modelPage->menuTitle,
                    $modelPage->capability,
                    $modelPage->pageSlug,
                    [AdminUiMenuService::class, 'displayMenuPage'],
                    $modelPage->position
                )
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
            if ($metaBox->onPage === MetaBoxPage::ACF_EDIT && ! str_ends_with($currentScreen->id, $currentModelPage->acfEditPage->pageSlug)) {
                continue;
            }

            $screen = match ($metaBox->onPage) {
                MetaBoxPage::ADMIN_TABLE => $currentModelPage->pageScreen,
                MetaBoxPage::VIEW => $currentModelPage->viewPage->pageScreen,
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

        header('Location: ' . $modelPage->getOverviewPageUrl());
        exit();
    }

    public static function showDeleteLinkOnViewPage(array $pageData): void
    {
        $currentModelPage = ModelPages::getCurrentModelPage();

        if ($currentModelPage === null || $currentModelPage->acfEditPage?->pageSlug !== $pageData['menu_slug']) {
            return;
        }

        $modelId = $_GET['model_id'] ?? null;
        $href = admin_url('admin.php') . '?page=' . $currentModelPage->pageSlug . '&model_id=' . $modelId . '&action=delete';

        echo '<a href="' . $href . '" onclick="return confirm(\'' . __('Are you sure?') . '\')" style="color: #b32d2e;">' . __('Delete') . '</a>';
    }

    public static function handleGetFieldReturnValue($value, $postId, string $name, bool $hidden)
    {
        if (! is_string($postId)) {
            return $value;
        }

        $parts = explode('__', $postId);

        if (count($parts) !== 3 || $parts[0] !== 'eloquent-model') {
            return $value;
        }

        $prefix = $hidden ? '_' : '';
        $currentModelPage = ModelPages::getModelPages()[$parts[1]] ?? null;

        if ($currentModelPage === null) {
            return $value;
        }

        $fieldCallback = $currentModelPage->acfEditPage->loadFieldCallbacks[$prefix . $name] ?? null;

        if ($fieldCallback !== null) {
            return ($fieldCallback->renderCallback)($value, $prefix . $name, $parts[2], $currentModelPage->model);
        }

        $instance = $currentModelPage->model::query()
            ->find($parts[2]);

        return $instance->{$prefix . $name} ?? '__return_null';
    }
}

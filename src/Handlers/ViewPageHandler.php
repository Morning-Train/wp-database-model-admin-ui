<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Handlers;

use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPages;
use Morningtrain\WP\DatabaseModelAdminUi\Services\ViewPageMenuService;

class ViewPageHandler
{
    public static function addReadableMenuPage(): void
    {
        $modelId = $_GET['model_id'] ?? null;
        $currentModelPage = ModelPages::getCurrentModelPage();

        if (empty($modelId) || $currentModelPage === null) {
            return;
        }

        $acfEditableCurrentModel = $currentModelPage->model::query()
            ->find($modelId);

        if (empty($acfEditableCurrentModel)) {
            return;
        }

        $title = __('View');

        if (
            ! empty($currentModelPage->columns[$currentModelPage->primaryColumn]) &&
            $currentModelPage->columns[$currentModelPage->primaryColumn]->renderCallback !== null
        ) {
            $title = ($currentModelPage->columns[$currentModelPage->primaryColumn]->renderCallback)($acfEditableCurrentModel, $currentModelPage) . ' - ' . $title;
        }

        add_submenu_page(
            'options-writing.php',
            $title,
            $title,
            $currentModelPage->viewPage->capability,
            $currentModelPage->viewPage->pageSlug,
            [ViewPageMenuService::class, 'displayMenuPage'],
        );
    }

    public static function fixSelectedAdminMenuForViewPage(string $file): string
    {
        global $plugin_page, $submenu_file;
        $currentModelPage = ModelPages::getCurrentModelPage();

        if ($currentModelPage === null || $currentModelPage->viewPage === null) {
            return $file;
        }

        if ($plugin_page === $currentModelPage->viewPage->pageSlug) {
            $plugin_page = $currentModelPage->pageSlug;
            $submenu_file = $currentModelPage->pageSlug;
        }

        return $file;
    }
}

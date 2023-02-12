<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Handlers;

use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPages;
use Morningtrain\WP\DatabaseModelAdminUi\Services\ViewPageMenuService;

class ReadableHandler
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

        if (! empty($acfEditableCurrentModel->{$currentModelPage->primaryColumn})) {
            $title = $acfEditableCurrentModel->{$currentModelPage->primaryColumn} . ' - ' . $title;
        }

        add_submenu_page(
            'options-writing.php',
            $title,
            $title,
            $currentModelPage->viewPage->capability,
            $currentModelPage->viewPage->pageSlug,
            $currentModelPage->viewPage->renderCallback !== null ?
                ($currentModelPage->viewPage->renderCallback)() :
                [ViewPageMenuService::class, 'displayMenuPage']
        );
    }

    public static function fixSelectedAdminMenuForViewPage(string $file): string
    {
        global $plugin_page;
        $currentModelPage = ModelPages::getCurrentModelPage();

        if ($currentModelPage === null || $currentModelPage->viewPage === null) {
            return $file;
        }

        if ($plugin_page === $currentModelPage->viewPage->pageSlug) {
            $plugin_page = $currentModelPage->pageSlug;
        }

        return $file;
    }
}

<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Services;

use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPages;
use Morningtrain\WP\View\View;

class ViewPageMenuService
{
    public static function displayMenuPage(): void
    {
        $modelId = $_GET['model_id'] ?? null;
        $currentModelPage = ModelPages::getCurrentModelPage();

        if (empty($modelId) || $currentModelPage === null) {
            return;
        }

        $instance = $currentModelPage->model::query()
            ->find($modelId);

        if (empty($instance)) {
            return;
        }

        $data = $instance->toArray();

        if ($currentModelPage->viewPage->renderCallback !== null) {
            ($currentModelPage->viewPage->renderCallback)($data, $currentModelPage);
            return;
        }

        global $wp_meta_boxes;

        echo View::first(
            [
                'wpdbmodeladminui/admin-ui-view-page',
                'admin-ui-view-page',
            ],
            [
                'title' => $data[$currentModelPage->primaryColumn],
                'showDefaultView' => $currentModelPage->viewPage->showDefaultView,
                'data' => $data,
                'columns' => $currentModelPage->tableColumns ?? [],
                'hasSideMetaBoxes' => ! empty($wp_meta_boxes[$currentModelPage->viewPage->pageScreen]['side']),
                'pageScreen' => $currentModelPage->viewPage->pageScreen,
            ]
        );
    }
}

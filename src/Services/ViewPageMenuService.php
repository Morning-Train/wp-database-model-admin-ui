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

        echo View::first(
            [
                'wpdbmodeladminui/admin-ui-view-page',
                'wpdbmodeladminui::admin-ui-view-page',
            ],
            [
                'title' => $data[$currentModelPage->primaryColumn],
                'data' => $data,
                'columns' => $currentModelPage->tableColumns ?? [],
            ]
        );
    }
}

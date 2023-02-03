<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Traits;

use Morningtrain\WP\DatabaseModelAdminUi\Model\AdminTable;
use Morningtrain\WP\Hooks\Hook;
use Morningtrain\WP\View\View;

trait Readable
{

    public function initReadable(): void
    {
        $this->loadReadableHooks();
    }

    public function loadReadable(): void
    {
        if (empty($_GET['action']) || $_GET['action'] !== 'view' || empty($_GET['model_id'])) {
            return;
        }

        $queryFirstItem = static::query()
            ->where('id', $_GET['model_id'])
            ->first();

        if (empty($queryFirstItem)) {
            return;
        }

        $data = $queryFirstItem->toArray();

        echo View::first(
            [
                'wpdbmodeladminui/admin-ui-single',
                'wpdbmodeladminui::admin-ui-single',
            ],
            [
                'title' => $data[$this->adminTable->get_primary_column()],
                'data' => $data,
            ]
        );
    }

    public function loadReadableHooks(): void
    {
        Hook::filter(
            'wp-database-model-admin-ui/admin-table/' . $this->table . '/column_default',
            function ($value, object|array $item, string $column_name, AdminTable $adminTable) {
                if ($adminTable->get_primary_column() !== $column_name) {
                    return $value;
                }

                return '<a href="' . $this->getCurrentPageUrlWithParams($item['id'], $_GET['page']) . '">' . $item[$column_name] . '</a>';
            }
        );

        Hook::filter(
            'wp-database-model-admin-ui/admin-table/' . $this->table . '/column_default/row_actions',
            function (array $rowActions, object|array $item, string $column_name, AdminTable $adminTable): array
            {
                if ($adminTable->get_primary_column() === $column_name) {
                    $rowActions['view'] = '<a href="' . $this->getCurrentPageUrlWithParams($item['id'], $_GET['page']) . '">' . __('View') . '</a>';
                }

                return $rowActions;
            }
        );
    }

    private function getCurrentPageUrlWithParams(int $modelId, string $page): string
    {
        $current_page = admin_url('admin.php?page=' . $_GET["page"]);
        return add_query_arg(
            [
                'model_id' => $modelId,
                'page' => $page,
                'action' => 'view',
            ],
            $current_page
        );
    }

}
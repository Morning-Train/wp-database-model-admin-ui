<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Traits;

use Morningtrain\WP\DatabaseModelAdminUi\Model\AdminTable;
use Morningtrain\WP\Hooks\Hook;
use Morningtrain\WP\View\View;

trait Readable
{

    private string $readablePageSlug;
    private $readableCurrentModel;

    public function initReadable(): void
    {
        if (empty($this->adminUiTableData)) {
            return;
        }

        $this->readablePageSlug = $this->table . '-view';

        $this->checkForNonExistingModel();
        $this->loadReadableHooks();
    }

    public function displayReadableSubmenuPage(): void
    {
        $modelId = $_GET['model_id'] ?? null;
        $queryFirstItem = static::query()
            ->where('id', $modelId)
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
                'title' => $data[$this->primaryColumn],
                'data' => $data,
                'screen' => get_current_screen(),
            ]
        );
    }

    private function checkForNonExistingModel(): void
    {
        $page = $_GET['page'] ?? null;
        $modelId = $_GET['model_id'] ?? null;

        if (empty($page) || empty($modelId)) {
            return;
        }

        if ($page !== $this->readablePageSlug || ! is_numeric($modelId)) {
            return;
        }

        $this->readableCurrentModel = static::query()
            ->where('id', $_GET['model_id'])
            ->first();

        if (! empty($this->readableCurrentModel)) {
            return;
        }

        wp_safe_redirect(admin_url('admin.php?page=' . $_GET['page']));
        exit();
    }

    private function loadReadableHooks(): void
    {
        Hook::action('admin_menu', function () {
            $page = $_GET['page'] ?? null;

            if (empty($page) || $page !== $this->readablePageSlug) {
                return;
            }

            if (empty($this->readableCurrentModel)) {
                $this->readableCurrentModel = static::query()
                    ->where('id', $_GET['model_id'])
                    ->first();
            }

            \add_submenu_page(
                $this->table,
                $this->readableCurrentModel->{$this->primaryColumn},
                $this->readableCurrentModel->{$this->primaryColumn},
                'manage_options',
                $this->readablePageSlug,
                [$this, 'displayReadableSubmenuPage']
            );
        });

        Hook::filter(
            'wp-database-model-admin-ui/admin-table/' . $this->table . '/column_default',
            function ($value, object|array $item, string $column_name, AdminTable $adminTable) {
                if ($adminTable->get_primary_column() !== $column_name) {
                    return $value;
                }

                return '<a href="' . $this->getCurrentPageUrlWithParams($item['id'], $this->readablePageSlug) . '">' . $item[$column_name] . '</a>';
            }
        );

        Hook::filter(
            'wp-database-model-admin-ui/admin-table/' . $this->table . '/column_default/row_actions',
            function (array $rowActions, object|array $item, string $column_name, AdminTable $adminTable): array
            {
                if ($adminTable->get_primary_column() === $column_name) {
                    $rowActions['view'] = '<a href="' . $this->getCurrentPageUrlWithParams($item['id'], $this->readablePageSlug) . '">' . __('View') . '</a>';
                }

                return $rowActions;
            }
        );
    }

    private function getCurrentPageUrlWithParams(int $modelId, string $page): string
    {
        $current_page = admin_url('admin.php');
        return add_query_arg(
            [
                'page' => $page,
                'model_id' => $modelId,
            ],
            $current_page
        );
    }

}
<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Traits;

use Morningtrain\WP\DatabaseModelAdminUi\Model\AdminTable;
use Morningtrain\WP\Hooks\Hook;

trait Removable
{

    public function initRemovable(): void
    {
        Hook::action('admin_init', function () {
            if (empty($this->adminUiTableData)) {
                return;
            }

            $this->checkForDeleting();
            $this->loadRemovableHooks();
        });
    }

    public function checkForDeleting(): void
    {
        $page = $_GET['page'] ?? null;
        $action = $_GET['action'] ?? null;
        $modelId = $_GET['model_id'] ?? null;

        if (empty($page) || empty($action) || empty($modelId)) {
            return;
        }

        if ($page !== $this->table || $action !== 'delete' || ! is_numeric($modelId)) {
            return;
        }

        if (! empty($_GET['nonce']) && \wp_verify_nonce($_GET['nonce'], 'row-actions-delete') !== false) {
            // TODO: Add delete notice
            static::query()->where('id', $modelId)->delete();
        }

        wp_safe_redirect(admin_url('admin.php?page=' . $this->table));
        exit();
    }

    public function loadRemovableHooks(): void
    {
        Hook::filter(
            'wp-database-model-admin-ui/admin-table/' . $this->table . '/column_default/row_actions',
            function (array $rowActions, object|array $item, string $column_name, AdminTable $adminTable): array
            {
                if ($adminTable->get_primary_column() === $column_name) {
                    $current_page = admin_url('admin.php');
                    $href = add_query_arg(
                        [
                            'model_id' => $item['id'],
                            'page' => $this->table,
                            'action' => 'delete',
                            'nonce' => wp_create_nonce('row-actions-delete'),
                        ],
                        $current_page
                    );

                    $rowActions['delete'] = '<a href="' . $href . '" onclick="return confirm(\'' .  __('Are you sure?') . '\')">' . __('Delete') . '</a>';
                }

                return $rowActions;
            }
        );
    }

}
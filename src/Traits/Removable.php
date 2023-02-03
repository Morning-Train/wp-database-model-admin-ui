<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Traits;

use Morningtrain\WP\DatabaseModelAdminUi\Model\AdminTable;
use Morningtrain\WP\Hooks\Hook;
use Morningtrain\WP\View\View;

trait Removable
{

    public function initRemovable(): void
    {
        Hook::action('admin_init', function () {
            if (empty($this->tableData)) {
                return;
            }

            $this->checkForDeleting();
            $this->loadRemovableHooks();
        });
    }

    public function checkForDeleting(): void
    {
        $action = $_GET['action'] ?? null;
        $modelId = $_GET['model_id'] ?? null;

        if (empty($action) || $action !== 'delete' || empty($modelId) || ! is_numeric($modelId)) {
            return;
        }

        if (! empty($_GET['nonce']) && \wp_verify_nonce($_GET['nonce'], 'row-actions-delete') !== false) {
            // TODO: Add delete notice
            static::query()->where('id', $modelId)->delete();
        }

        wp_safe_redirect(admin_url('admin.php?page=' . $_GET['page']));
        exit();
    }

    public function loadRemovableHooks(): void
    {
        Hook::action('load-toplevel_page_' . $this->table, function () {
            wp_enqueue_script('common');
            wp_enqueue_script('wp-lists');
            wp_enqueue_script('postbox');

            Hook::action('add_meta_boxes', function () {
                add_meta_box( 'meta-box-id', __( 'My Meta Box', 'textdomain' ), function () { ray([123]); echo 123123; }, 'toplevel_page_' . $this->table);
            });
        });

        Hook::filter(
            'wp-database-model-admin-ui/admin-table/' . $this->table . '/column_default/row_actions',
            function (array $rowActions, object|array $item, string $column_name, AdminTable $adminTable): array
            {
                if ($adminTable->get_primary_column() === $column_name) {
                    $current_page = admin_url('admin.php?page=' . $_GET['page']);
                    $href = add_query_arg(
                        [
                            'model_id' => $item['id'],
                            'page' => $_GET['page'],
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
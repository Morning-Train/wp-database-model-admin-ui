<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Traits;

use Morningtrain\WP\DatabaseModelAdminUi\DatabaseModelAdminUi;
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

        $this->readablePageSlug = 'view_' . $this->table;

        $this->checkForNonExistingModel();
        $this->loadReadableHooks();
    }

    public function displayReadableSubmenuPage(): void
    {
        $modelId = $_GET['model_id'] ?? null;
        $queryFirstItem = static::query()
            ->find($modelId);

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
            ->find($_GET['model_id']);

        if (! empty($this->readableCurrentModel)) {
            return;
        }

        header('Location: ' . admin_url('admin.php?page=' . $this->table));
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

            add_submenu_page(
                'options-writing.php', // This will hide it from the admin menu
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

                $href = DatabaseModelAdminUi::getAdminPageUrlWithQueryArgs(
                    $this->readablePageSlug,
                    $item['id']
                );

                return '<a href="' . $href . '">' . $item[$column_name] . '</a>';
            }
        );

        Hook::filter('parent_file', function (string $file) {
            global $plugin_page;
            if ($plugin_page === $this->readablePageSlug) {
                $plugin_page = $this->table;
            }

            return $file;
        });
    }

}
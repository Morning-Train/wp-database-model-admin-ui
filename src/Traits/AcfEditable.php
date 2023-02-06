<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Traits;

use Morningtrain\WP\DatabaseModelAdminUi\Classes\Helper;
use Morningtrain\WP\DatabaseModelAdminUi\Model\AdminTable;
use Morningtrain\WP\Hooks\Hook;

trait AcfEditable
{

    private string $acfEditablePageSlug;
    private $acfEditableCurrentModel;

    public function initAcfEditable(): void
    {
        if (empty($this->adminUiTableData) || ! class_exists('ACF')) {
            return;
        }

        $this->acfEditablePageSlug = 'edit_' . $this->table;

        $this->checkForNonExistingAcfEditableModel();
        $this->loadAcfEditableHooks();
    }

    private function checkForNonExistingAcfEditableModel(): void
    {
        $page = $_GET['page'] ?? null;
        $modelId = $_GET['model_id'] ?? null;

        if (empty($page) || empty($modelId)) {
            return;
        }

        if ($page !== $this->acfEditablePageSlug || ! is_numeric($modelId)) {
            return;
        }

        $this->acfEditableCurrentModel = static::query()
            ->find($_GET['model_id']);

        if (! empty($this->acfEditableCurrentModel)) {
            global $currentAcfEditableModel, $currentAcfEditablePage;
            $currentAcfEditableModel = static::class;
            $currentAcfEditablePage = $page;

            return;
        }

        header('Location: ' . admin_url('admin.php?page=' . $this->table));
        exit();
    }

    private function loadAcfEditableHooks(): void
    {
        Hook::action('admin_menu', function () {
            $page = $_GET['page'] ?? null;
            $modelId = $_GET['model_id'] ?? null;

            if (empty($page) || empty($modelId) || $page !== $this->acfEditablePageSlug) {
                return;
            }

            if (empty($this->acfEditableCurrentModel)) {
                $this->acfEditableCurrentModel = static::query()
                    ->find($modelId);
            }

            acf_add_options_sub_page([
                'parent_slug' => 'options-writing.php', // This will hide it from the admin menu
                'page_title' => $this->acfEditableCurrentModel->{$this->primaryColumn} . ' - ' . __('Edit'),
                'menu_title' => $this->acfEditableCurrentModel->{$this->primaryColumn} . ' - ' . __('Edit'),
                'capability' => 'manage_options',
                'menu_slug' => $this->acfEditablePageSlug,
                'post_id' => 'eloquent_model__' . $this->table . '__' . $modelId
            ]);
        });

        Hook::filter(
            'wpdbmodeladminui/admin-table/' . $this->table . '/row_actions',
            function (array $rowActions, object|array $item): array
            {
                $href = Helper::getAdminPageUrlWithQueryArgs(
                    $this->acfEditablePageSlug,
                    $item['id']
                );

                $rowActions['edit'] = '<a href="' . $href . '">' . __('Edit') . '</a>';

                return $rowActions;
            }
        )->priority(9);

        Hook::filter('acf/load_value', function ($value, int|string $post_id, array $field) {
            $parts = explode('__', $post_id);

            if (count($parts) !== 3 || $parts[0] !== 'eloquent_model' || $parts[1] !== $this->table) {
                return $value;
            }

            $model = static::query()
                ->find($parts[2]);

            if (empty($model)) {
                return $value;
            }

            return $model->{$field['name']} ?? $value;
        });

        Hook::filter('acf/save_post', function (int|string $post_id) {
            $parts = explode('__', $post_id);

            if (count($parts) !== 3 || $parts[0] !== 'eloquent_model' || $parts[1] !== $this->table) {
                return;
            }

            // ACF 'get_fields()' method, does some validation from the $post_id.
            // TODO: Maybe another way to get the data for this??
            $keys = array_keys(get_fields($post_id));
            $values = array_combine($keys, $_POST['acf']);
            static::query()
                ->find($parts[2])
                ->update($values);
        });

        Hook::filter('parent_file', function (string $file) {
            global $plugin_page;
            if ($plugin_page === $this->acfEditablePageSlug) {
                $plugin_page = $this->table;
            }

            return $file;
        });
    }

}
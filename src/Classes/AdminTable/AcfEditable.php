<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Classes\AdminTable;

use Morningtrain\WP\DatabaseModelAdminUi\Classes\Helper;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage;
use Morningtrain\WP\Hooks\Hook;

class AcfEditable
{

    private ModelPage $modelPage;

    public function __construct(ModelPage $modelPage)
    {
        $this->modelPage = $modelPage;

        if (! class_exists('ACF')) {
            return;
        }

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

        if ($page !== $this->modelPage->acfEditablePageSlug || ! is_numeric($modelId)) {
            return;
        }

        $acfEditableCurrentModel = $this->modelPage->model::query()
            ->find($_GET['model_id']);

        if (! empty($acfEditableCurrentModel)) {
            global $currentAcfEditableModel, $currentAcfEditablePage;
            $currentAcfEditableModel = $this->modelPage->model;
            $currentAcfEditablePage = $page;

            return;
        }

        header('Location: ' . admin_url('admin.php?page=' . $this->modelPage->pageSlug));
        exit();
    }

    private function loadAcfEditableHooks(): void
    {
        Hook::action('admin_menu', function () {
            $page = $_GET['page'] ?? null;
            $modelId = $_GET['model_id'] ?? null;

            if (empty($page) || empty($modelId) || $page !== $this->modelPage->acfEditablePageSlug) {
                return;
            }

            $acfEditableCurrentModel = $this->modelPage->model::query()
                ->find($modelId);

            acf_add_options_sub_page([
                'parent_slug' => 'options-writing.php', // This will hide it from the admin menu
                'page_title' => $acfEditableCurrentModel->{$this->modelPage->primaryColumn} . ' - ' . __('Edit'),
                'menu_title' => $acfEditableCurrentModel->{$this->modelPage->primaryColumn} . ' - ' . __('Edit'),
                'capability' => 'manage_options',
                'menu_slug' => $this->modelPage->acfEditablePageSlug,
                'post_id' => 'eloquent_model__' . $this->modelPage->pageSlug . '__' . $modelId
            ]);
        });

        Hook::filter(
            'wpdbmodeladminui/admin-table/' . $this->modelPage->pageSlug . '/row_actions',
            function (array $rowActions, object|array $item): array
            {
                $href = Helper::getAdminPageUrlWithQueryArgs(
                    $this->modelPage->acfEditablePageSlug,
                    $item['id']
                );

                $rowActions['edit'] = '<a href="' . $href . '">' . __('Edit') . '</a>';

                return $rowActions;
            }
        )->priority(9);

        Hook::filter('acf/load_value', function ($value, int|string $post_id, array $field) {
            $parts = explode('__', $post_id);

            if (count($parts) !== 3 || $parts[0] !== 'eloquent_model' || $parts[1] !== $this->modelPage->pageSlug) {
                return $value;
            }

            $model = $this->modelPage->model::query()
                ->find($parts[2]);

            if (empty($model)) {
                return $value;
            }

            return $model->{$field['name']} ?? $value;
        });

        Hook::filter('acf/save_post', function (int|string $post_id) {
            $parts = explode('__', $post_id);

            if (count($parts) !== 3 || $parts[0] !== 'eloquent_model' || $parts[1] !== $this->modelPage->pageSlug) {
                return;
            }

            // ACF 'get_fields()' method, does some validation from the $post_id.
            // TODO: Maybe another way to get the data for this??
            $keys = array_keys(get_fields($post_id));
            $values = array_combine($keys, $_POST['acf']);
            $this->modelPage->model::query()
                ->find($parts[2])
                ->update($values);
        });

        Hook::filter('parent_file', function (string $file) {
            global $plugin_page;
            if ($plugin_page === $this->modelPage->acfEditablePageSlug) {
                $plugin_page = $this->modelPage->pageSlug;
            }

            return $file;
        });
    }

}
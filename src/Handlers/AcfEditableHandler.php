<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Handlers;

use Morningtrain\WP\DatabaseModelAdminUi\Classes\Helper;

class AcfEditableHandler
{

    public static function addAcfEditMenuPage(): void
    {
        $modelId = $_GET['model_id'] ?? null;
        $currentModelPage = Helper::getCurrentModePageFromUrlAcfEditablePage();

        if (empty($currentModelPage)) {
            return;
        }

        $acfEditableCurrentModel = $currentModelPage->model::query()
            ->find($modelId);

        acf_add_options_sub_page([
            'parent_slug' => 'options-writing.php', // This will hide it from the admin menu
            'page_title' => $acfEditableCurrentModel->{$currentModelPage->primaryColumn} . ' - ' . __('Edit'),
            'menu_title' => $acfEditableCurrentModel->{$currentModelPage->primaryColumn} . ' - ' . __('Edit'),
            'capability' => 'manage_options',
            'menu_slug' => $currentModelPage->acfEditablePageSlug,
            'post_id' => 'eloquent_model__' . $currentModelPage->pageSlug . '__' . $modelId
        ]);
    }

    public static function checkForNonExistingAcfEditableModel(): void
    {
        $modelId = $_GET['model_id'] ?? null;
        $currentModelPage = Helper::getCurrentModePageFromUrlAcfEditablePage();

        if (empty($modelId) || ! is_numeric($modelId) || empty($currentModelPage)) {
            return;
        }

        $acfEditableCurrentModel = $currentModelPage->model::query()
            ->find($_GET['model_id']);

        if (! empty($acfEditableCurrentModel)) {
            return;
        }

        header('Location: ' . admin_url('admin.php?page=' . $currentModelPage->pageSlug));
        exit();
    }

    public static function handleLoadValueForAcfModel($value, int|string $post_id, array $field)
    {
        $currentModelPage = Helper::getCurrentModePageFromUrlAcfEditablePage();

        if (empty($currentModelPage)) {
            return $value;
        }

        $parts = explode('__', $post_id);

        if (count($parts) !== 3 || $parts[0] !== 'eloquent_model' || $parts[1] !== $currentModelPage->pageSlug) {
            return $value;
        }

        $model = $currentModelPage->model::query()
            ->find($parts[2]);

        if (empty($model)) {
            return $value;
        }

        return $model->{$field['name']} ?? $value;
    }

    public static function handleSaveValueForAcfModel(int|string $post_id): void
    {
        $currentModelPage = Helper::getCurrentModePageFromUrlAcfEditablePage();

        if (empty($currentModelPage)) {
            return;
        }

        $parts = explode('__', $post_id);

        if (count($parts) !== 3 || $parts[0] !== 'eloquent_model' || $parts[1] !== $currentModelPage->pageSlug) {
            return;
        }

        // ACF 'get_fields()' method, does some validation from the $post_id.
        // TODO: Maybe another way to get the data for this??
        $keys = array_keys(get_fields($post_id));
        $values = array_combine($keys, $_POST['acf']);
        $currentModelPage->model::query()
            ->find($parts[2])
            ->update($values);
    }

    public static function fixSelectedAdminMenuForAcfEditable(string $file): string
    {
        global $plugin_page;
        $currentModelPage = Helper::getCurrentModePageFromUrlAcfEditablePage();

        if (empty($currentModelPage)) {
            return $file;
        }

        if ($plugin_page === $currentModelPage->acfEditablePageSlug) {
            $plugin_page = $currentModelPage->pageSlug;
        }

        return $file;
    }

}
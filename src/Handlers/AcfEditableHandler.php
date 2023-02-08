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

        $title = __('Edit');
        $acfEditableCurrentModel = $currentModelPage->model::query()
            ->find($modelId);

        if (! empty($acfEditableCurrentModel->{$currentModelPage->primaryColumn})) {
            $title = $acfEditableCurrentModel->{$currentModelPage->primaryColumn} . ' - ' . $title;
        }

        acf_add_options_sub_page([
            'parent_slug' => 'options-writing.php', // This will hide it from the admin menu
            'page_title' => $title,
            'menu_title' => $title,
            'capability' => $currentModelPage->editCapability,
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

        $instance = $currentModelPage->model::query()
            ->find($parts[2]);

        if (empty($instance)) {
            return $value;
        }

        if (! empty($currentModelPage->acfSettings->extraLoadCallbacks[$field['name']])) {
            return ($currentModelPage->acfSettings->extraLoadCallbacks[$field['name']])($value, $instance);
        }

        return $instance->{$field['name']} ?? $value;
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

        if ($currentModelPage->acfSettings->extraSaveCallback !== null) {
            ($currentModelPage->acfSettings->extraSaveCallback)($parts[2], $currentModelPage->model, $values);
        }
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
<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Handlers;

use Illuminate\Support\Facades\Schema;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\Helper;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPages;

class AcfEditableHandler
{
    public static function addAcfEditMenuPage(): void
    {
        $modelId = $_GET['model_id'] ?? null;
        $currentModelPage = ModelPages::getCurrentModelPage();

        if ($currentModelPage === null) {
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
            'post_id' => 'eloquent-model_' . $currentModelPage->pageSlug . '_' . $modelId
        ]);
    }

    public static function checkForNonExistingAcfEditableModel(): void
    {
        $currentModelPage = ModelPages::getCurrentModelPage();
        $modelId = $_GET['model_id'] ?? null;

        if ($currentModelPage === null) {
            return;
        }

        if (empty($modelId) || ! is_numeric($modelId)) {
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

    public static function handlePreLoadPostIdForAcfModel($return, $post_id) {
        if($post_id instanceof \WP_Term){
            return $return;
        }

        $currentModelPage = ModelPages::getCurrentModelPage();

        if ($currentModelPage === null) {
            return $return;
        }

        $parts = explode('_', $post_id);

        if (count($parts) !== 3 || $parts[0] !== 'eloquent-model' || $parts[1] !== $currentModelPage->pageSlug) {
            return $return;
        }

        return $post_id;
    }

    public static function handleDecodePostIdForAcfModel($return, $post_id)
    {
        $currentModelPage = ModelPages::getCurrentModelPage();

        if ($currentModelPage === null) {
            return $return;
        }

        $parts = explode('_', $post_id);

        if (count($parts) !== 3 || $parts[0] !== 'eloquent-model' || $parts[1] !== $currentModelPage->pageSlug) {
            return $return;
        }

        $return['type'] = 'eloquent-model';

        return $return;
    }

    public static function handleLoadValueForAcfModel($value, $post_id, array $field)
    {
        if (! in_array($field['type'], ['repeater', 'group'], true)) {
            return $value;
        }

        $currentModelPage = ModelPages::getCurrentModelPage();

        if ($currentModelPage === null) {
            return $value;
        }

        $parts = explode('_', $post_id);

        if (count($parts) !== 3 || $parts[0] !== 'eloquent-model' || $parts[1] !== $currentModelPage->pageSlug) {
            return $value;
        }

        if (! empty($currentModelPage->acfSettings->extraLoadCallbacks[$field['name']])) {
            $newValue = ($currentModelPage->acfSettings->extraLoadCallbacks[$field['name']])($value, $parts[2], $currentModelPage->model);

            return Helper::convertNamesToFieldKeys($newValue);
        }
        
        return $value;
    }

    public static function handleLoadMetadataForAcfModel($value, $post_id, string $name, bool $hidden)
    {
        $currentModelPage = ModelPages::getCurrentModelPage();

        if ($currentModelPage === null) {
            return $value;
        }

        $prefix = $hidden ? '_' : '';
        $parts = explode('_', $post_id);

        if (count($parts) !== 3 || $parts[0] !== 'eloquent-model' || $parts[1] !== $currentModelPage->pageSlug) {
            return $value;
        }

        if (! empty($currentModelPage->acfSettings->extraLoadCallbacks[$prefix . $name])) {
            return ($currentModelPage->acfSettings->extraLoadCallbacks[$prefix . $name])($value, $parts[2], $currentModelPage->model);
        }

        $instance = $currentModelPage->model::query()
            ->find($parts[2]);

        return $instance->{$prefix . $name} ?? '__return_null';
    }

    public static function handleSaveMetadataForAcfModel($return, $post_id, string $name, $value, bool $hidden)
    {
        $currentModelPage = ModelPages::getCurrentModelPage();

        if ($currentModelPage === null) {
            return $return;
        }

        $parts = explode('_', $post_id);

        if (count($parts) !== 3 || $parts[0] !== 'eloquent-model' || $parts[1] !== $currentModelPage->pageSlug) {
            return $return;
        }

        $prefix = $hidden ? '_' : '';
        $modelColumns = Schema::getColumnListing((new ($currentModelPage->model)())->getTable());

        if (! in_array($prefix . $name, $modelColumns, true)) {
            return false;
        }

        $currentModelPage->model::query()
            ->find($parts[2])
            ->update([$prefix . $name => $value]);

        return true;
    }

    public static function handleSaveValueForAcfModel(int|string $post_id): void
    {
        $currentModelPage = ModelPages::getCurrentModelPage();

        if ($currentModelPage === null) {
            return;
        }

        $parts = explode('_', $post_id);

        if (count($parts) !== 3 || $parts[0] !== 'eloquent-model' || $parts[1] !== $currentModelPage->pageSlug) {
            return;
        }

        if ($currentModelPage->acfSettings !== null && $currentModelPage->acfSettings->extraSaveCallback !== null) {
            ($currentModelPage->acfSettings->extraSaveCallback)(
                $parts[2],
                $currentModelPage->model,
                Helper::getAcfValuesWithNames($_POST['acf'])
            );
        }
    }

    public static function fixSelectedAdminMenuForAcfEditable(string $file): string
    {
        global $plugin_page;
        $currentModelPage = ModelPages::getCurrentModelPage();

        if ($currentModelPage === null) {
            return $file;
        }

        if ($plugin_page === $currentModelPage->acfEditablePageSlug) {
            $plugin_page = $currentModelPage->pageSlug;
        }

        return $file;
    }

    public static function addMetaBoxes(): void
    {
        $currentModelPage = ModelPages::getCurrentModelPage();

        if ($currentModelPage === null || empty($currentModelPage->metaBoxes)) {
            return;
        }

        $currentScreen = get_current_screen();

        if ($currentScreen === null || $currentScreen->id !== 'admin_page_' . $currentModelPage->acfEditablePageSlug) {
            return;
        }

        foreach ($currentModelPage->metaBoxes as $metaBox) {
            \add_meta_box(
                $metaBox->slug,
                $metaBox->title,
                function ($post, $metaBoxData) use ($metaBox) {
                    ($metaBox->renderCallback)(...$metaBoxData['args']);
                },
                'acf_options_page',
                $metaBox->context,
                $metaBox->priority,
                [
                    'model_id' => $_GET['model_id'] ?? null,
                    'model' => $currentModelPage->model,
                ]
            );
        }
    }

}
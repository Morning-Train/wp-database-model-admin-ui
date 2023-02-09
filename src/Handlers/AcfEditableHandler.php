<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Handlers;

use Illuminate\Support\Facades\Schema;
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

    public static function handleLoadValueForAcfModel($value, string $post_id, array $field)
    {
        if (! in_array($field['type'], ['repeater', 'group'], true)) {
            return $value;
        }

        $currentModelPage = Helper::getCurrentModePageFromUrlAcfEditablePage();

        if (empty($currentModelPage)) {
            return $value;
        }

        $parts = explode('__', $post_id);

        if (count($parts) !== 3 || $parts[0] !== 'eloquent_model' || $parts[1] !== $currentModelPage->pageSlug) {
            return $value;
        }

        // TODO: Handle load of repeaters and groups!
        $newValue = [];
        $subfields = array_combine(
            array_column($field['sub_fields'], 'name'),
            array_column($field['sub_fields'], 'key')
        );

        foreach (['name' => 'Test', 'step' => 'step2', 'image_id' => 3] as $key => $item) {
            $modelValue = [];
            foreach ($subfields as $_key => $subfield) {
                if ($key !== $_key) {
                    continue;
                }

                $modelValue[$subfield] = $item;
            }

            $newValue[] = $modelValue;
        }

        $value = [['field_63e4d9afa5e35' => 'WOW']];
        ray([$field['name'] => $field]);
        
        return $value;
    }
    
    public static function handleLoadMetadataForAcfModel($value, string $post_id, string $name, bool $hidden)
    {
        $currentModelPage = Helper::getCurrentModePageFromUrlAcfEditablePage();

        if (empty($currentModelPage)) {
            return $value;
        }

        $parts = explode('__', $post_id);

        if (count($parts) !== 3 || $parts[0] !== 'eloquent_model' || $parts[1] !== $currentModelPage->pageSlug) {
            return $value;
        }
        

        $prefix = $hidden ? '_' : '';
        if ($prefix . $name === 'steps') {
            return [
                'step' => 'step2'
            ];
        }

        if (! empty($currentModelPage->acfSettings->extraLoadCallbacks[$prefix . $name])) {
            return ($currentModelPage->acfSettings->extraLoadCallbacks[$prefix . $name])($value, $parts[2], $currentModelPage->model);
        }

        $instance = $currentModelPage->model::query()
            ->find($parts[2]);

        return $instance->{$prefix . $name} ?? '__return_null';
    }

    public static function handleSaveMetadataForAcfModel($return, string $post_id, string $name, $value, bool $hidden)
    {
        // TODO: Handle repeaters and groups

        $currentModelPage = Helper::getCurrentModePageFromUrlAcfEditablePage();

        if (empty($currentModelPage)) {
            return $return;
        }

        $parts = explode('__', $post_id);

        if (count($parts) !== 3 || $parts[0] !== 'eloquent_model' || $parts[1] !== $currentModelPage->pageSlug) {
            return $return;
        }

        $prefix = $hidden ? '_' : '';
        $modelColumns = Schema::getColumnListing((new ($currentModelPage->model)())->getTable());

        if (in_array($prefix . $name, $modelColumns, true)) {
            $currentModelPage->model::query()
                ->find($parts[2])
                ->update([$prefix . $name => $value]);
        }

        return true;
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

        if ($currentModelPage->acfSettings->extraSaveCallback !== null) {
            ($currentModelPage->acfSettings->extraSaveCallback)($parts[2], $currentModelPage->model, get_fields($post_id));
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

    public static function addMetaBoxes(): void
    {
        $currentModelPage = Helper::getCurrentModePageFromUrlAcfEditablePage();

        if (empty($currentModelPage) || empty($currentModelPage->metaBoxes)) {
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
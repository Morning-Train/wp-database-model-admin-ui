<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Handlers;

use Illuminate\Support\Facades\Schema;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\Helper;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPages;

class AcfEditPageHandler
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

        if (empty($acfEditableCurrentModel)) {
            return;
        }

        if (
            ! empty($currentModelPage->columns[$currentModelPage->primaryColumn]) &&
            $currentModelPage->columns[$currentModelPage->primaryColumn]->renderCallback !== null
        ) {
            $title = ($currentModelPage->columns[$currentModelPage->primaryColumn]->renderCallback)($acfEditableCurrentModel, $currentModelPage) . ' - ' . $title;
        }

        acf_add_options_sub_page([
            'parent_slug' => 'options-writing.php', // This will hide it from the admin menu
            'page_title' => $title,
            'menu_title' => $title,
            'capability' => $currentModelPage->acfEditPage->capability,
            'menu_slug' => $currentModelPage->acfEditPage->pageSlug,
            'post_id' => 'eloquent-model__' . $currentModelPage->pageSlug . '__' . $modelId,
        ]);
    }

    public static function handlePreLoadPostIdForAcfModel($return, $postId)
    {
        if ($postId instanceof \WP_Term) {
            return $return;
        }

        $currentModelPage = ModelPages::getCurrentModelPage();

        if ($currentModelPage === null) {
            return $return;
        }

        $parts = explode('__', $postId);

        if (count($parts) !== 3 || $parts[0] !== 'eloquent-model' || $parts[1] !== $currentModelPage->pageSlug) {
            return $return;
        }

        return $postId;
    }

    public static function handleDecodePostIdForAcfModel($return, $postId)
    {
        $currentModelPage = ModelPages::getCurrentModelPage();

        if ($currentModelPage === null) {
            return $return;
        }

        $parts = explode('__', $postId);

        if (count($parts) !== 3 || $parts[0] !== 'eloquent-model' || $parts[1] !== $currentModelPage->pageSlug) {
            return $return;
        }

        $return['type'] = 'eloquent-model';

        return $return;
    }

    public static function handleLoadValueForAcfModel($value, $postId, array $field)
    {
        if (! in_array($field['type'], ['repeater', 'group'], true)) {
            return $value;
        }

        $currentModelPage = ModelPages::getCurrentModelPage();

        if ($currentModelPage === null) {
            return $value;
        }

        $parts = explode('__', $postId);

        if (count($parts) !== 3 || $parts[0] !== 'eloquent-model' || $parts[1] !== $currentModelPage->pageSlug) {
            return $value;
        }

        $fieldCallback = $currentModelPage->acfEditPage->loadFieldCallbacks[$field['name']] ?? null;

        if ($fieldCallback !== null) {
            $newValue = ($fieldCallback->renderCallback)($value, $field['name'], $parts[2], $currentModelPage->model);

            if (is_array($newValue)) {
                return $newValue;
            }
        }

        return $value;
    }

    public static function handleLoadMetadataForAcfModel($value, $postId, string $name, bool $hidden)
    {
        $currentModelPage = ModelPages::getCurrentModelPage();

        if ($currentModelPage === null) {
            return $value;
        }

        $prefix = $hidden ? '_' : '';
        $parts = explode('__', $postId);

        if (count($parts) !== 3 || $parts[0] !== 'eloquent-model' || $parts[1] !== $currentModelPage->pageSlug) {
            return $value;
        }

        $fieldCallback = $currentModelPage->acfEditPage->loadFieldCallbacks[$prefix . $name] ?? null;

        if ($fieldCallback !== null) {
            return ($fieldCallback->renderCallback)($value, $prefix . $name, $parts[2], $currentModelPage->model);
        }

        $instance = $currentModelPage->model::query()
            ->find($parts[2]);

        return $instance->{$prefix . $name} ?? '__return_null';
    }

    public static function handleSaveMetadataForAcfModel($return, $postId, string $name, $value, bool $hidden)
    {
        $currentModelPage = ModelPages::getCurrentModelPage();

        if ($currentModelPage === null) {
            return $return;
        }

        $parts = explode('__', $postId);

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

    public static function handleSaveValueForAcfModel(int|string $postId): void
    {
        $currentModelPage = ModelPages::getCurrentModelPage();

        if ($currentModelPage === null) {
            return;
        }

        $parts = explode('__', $postId);

        if (count($parts) !== 3 || $parts[0] !== 'eloquent-model' || $parts[1] !== $currentModelPage->pageSlug) {
            return;
        }

        if ($currentModelPage->acfEditPage !== null && $currentModelPage->acfEditPage->saveCallback !== null) {
            ($currentModelPage->acfEditPage->saveCallback)(
                $parts[2],
                $currentModelPage->model,
                Helper::getAcfValuesWithNames($_POST['acf'])
            );
        }
    }

    public static function fixSelectedAdminMenuForAcfEditable(string $file): string
    {
        global $plugin_page, $submenu_file;
        $currentModelPage = ModelPages::getCurrentModelPage();

        if ($currentModelPage === null) {
            return $file;
        }

        if ($plugin_page === $currentModelPage->acfEditPage->pageSlug) {
            $plugin_page = $currentModelPage->pageSlug;
            $submenu_file = $currentModelPage->pageSlug;
        }

        return $file;
    }
}

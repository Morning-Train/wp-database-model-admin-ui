<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Handlers;

use Illuminate\Support\Facades\Schema;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\Helper;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPages;

class AcfCreatePageHandler
{
    public static function addAcfEditMenuPage(): void
    {
        $currentModelPage = ModelPages::getCurrentModelPage();

        if ($currentModelPage === null) {
            return;
        }

        acf_add_options_sub_page([
            'parent_slug' => 'options-writing.php', // This will hide it from the admin menu
            'page_title' => __('Create'),
            'menu_title' => __('Create'),
            'capability' => $currentModelPage->acfCreatePage->capability,
            'menu_slug' => $currentModelPage->acfCreatePage->pageSlug,
            'post_id' => 'eloquent-model__' . $currentModelPage->pageSlug,
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

        if (count($parts) !== 2 || $parts[0] !== 'eloquent-model' || $parts[1] !== $currentModelPage->pageSlug) {
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

        if (count($parts) !== 2 || $parts[0] !== 'eloquent-model' || $parts[1] !== $currentModelPage->pageSlug) {
            return $return;
        }

        $return['type'] = 'eloquent-model';

        return $return;
    }

    public static function handleSaveValueForAcfModel(int|string $postId): void
    {
        $currentModelPage = ModelPages::getCurrentModelPage();

        if ($currentModelPage === null) {
            return;
        }

        $parts = explode('__', $postId);

        if (count($parts) !== 2 || $parts[0] !== 'eloquent-model' || $parts[1] !== $currentModelPage->pageSlug) {
            return;
        }

        $values = Helper::getAcfValuesWithNames($_POST['acf']);
        $modelColumns = Schema::getColumnListing((new ($currentModelPage->model)())->getTable());
        $modelValues = array_filter(
            $values,
            function ($key) use ($modelColumns) {
                return in_array($key, $modelColumns);
            },
            ARRAY_FILTER_USE_KEY
        );

        $instance = $currentModelPage->model::query()
           ->create($modelValues);

        if ($currentModelPage->acfCreatePage !== null && $currentModelPage->acfCreatePage->saveCallback !== null) {
            ($currentModelPage->acfCreatePage->saveCallback)(
                $instance,
                $currentModelPage->model,
                Helper::getAcfValuesWithNames($_POST['acf'])
            );
        }

        header('Location: ' . $currentModelPage->getAcfEditPageUrl($instance->id));
        exit();
    }

    public static function fixSelectedAdminMenuForAcfCreatePage(string $file): string
    {
        global $plugin_page, $submenu_file;
        $currentModelPage = ModelPages::getCurrentModelPage();

        if ($currentModelPage === null) {
            return $file;
        }

        if ($plugin_page === $currentModelPage->acfCreatePage->pageSlug) {
            $plugin_page = $currentModelPage->pageSlug;
            $submenu_file = $currentModelPage->pageSlug;
        }

        return $file;
    }
}

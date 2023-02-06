<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Handlers;

use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPages;
use WP_Screen;

class AdminUiHandler
{

    public static function addModelMenuPages(): void
    {
        foreach (ModelPages::getModelPages() as $pageSlug => $modelPage) {
            add_menu_page(
                $modelPage->pageTitle,
                $modelPage->menuTitle,
                $modelPage->capability,
                $modelPage->pageSlug,
                [$this, 'displayMenuPage'],
                $modelPage->iconUrl,
                $modelPage->position
            );
        }
    }

    public static function setPerPageScreenOption($screen_option, string $option, int $value)
    {
        if ($option !== 'per_page') {
            return $screen_option;
        }

        return $value;
    }

    public static function addScreenOption(WP_Screen $screen): void
    {
        $page = $_GET['page'] ?? null;
        $modelPages = ModelPages::getModelPages();

        if ($page === null || ! array_key_exists($page, $modelPages)) {
            return;
        }

        add_screen_option('per_page', ['default' => 20, 'option' => 'per_page']);
    }

    public static function handleActiveAdminMenu(string $file): string
    {
        // TODO: Broken?!?
        global $plugin_page;
        $pageSlugs = array_keys(ModelPages::getModelPages());

        foreach ($pageSlugs as $pageSlug) {
            if (in_array($plugin_page, ['edit_' . $pageSlug, 'view_' . $pageSlug], true)) {
                return $pageSlug;
            }
        }

        return $file;
    }

}
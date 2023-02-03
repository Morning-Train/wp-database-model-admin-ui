<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Traits;

use Morningtrain\WP\Hooks\Hook;
use WP_Screen;

trait OptionPage
{

    private array $tableData;
    private array $traitsInUse;

    // Option page settings
    private string $pageTitle;
    private string $menuTitle;
    private string $capability;
    private string $menu_slug;
    private ?string $callback;
    private string $iconUrl;
    private ?int $position;

    private string $primaryColumn;

    public function initOptionPage(): void
    {
        $this->pageTitle = $this->tableData['pageTitle'] ?? __('Admin Table', '');
        $this->menuTitle = $this->tableData['menuTitle'] ?? __('Admin Table', '');
        $this->capability = $this->tableData['capability'] ?? 'manage_options';
        $this->menu_slug = $this->table;
        $this->callback = $this->tableData['callback'] ?? null;
        $this->iconUrl = $this->tableData['iconUrl'] ?? '';
        $this->position = $this->tableData['position'] ?? null;

        /*** @see \WP_List_Table::get_default_primary_column_name */
        $this->primaryColumn = array_keys($this->tableData['tableColumns'])[0];

        $this->loadOptionHooks();
    }

    public function loadOptionHooks(): void
    {
        Hook::action('admin_menu', function () {
            \add_menu_page(
                $this->pageTitle,
                $this->menuTitle,
                $this->capability,
                $this->table,
                [$this, $this->callback ?? 'displayMenuPage'],
                $this->iconUrl,
                $this->position
            );
        });

        Hook::filter('set-screen-option', function ($screen_option, string $option, int $value) {
            if (empty($_REQUEST['page']) || $this->table !== $_REQUEST['page']) {
                return $screen_option;
            }

            if ($option === 'per_page') {
                return $value;
            }

            return $screen_option;
        });

        Hook::action('current_screen', function (WP_Screen $screen) {
            if ($screen->id !== 'toplevel_page_' . $this->table) {
                return;
            }

            add_screen_option('per_page', ['default' => 20, 'option' => 'per_page']);
        });
    }

    public function displayMenuPage(): void
    {
        if (empty($_GET['page']) || $_GET['page'] !== $this->table) {
            return;
        }

        $screen = get_current_screen();

        if ($screen === null || $screen->id !== 'toplevel_page_' . $this->table) {
            return;
        }

        do_action('wp-database-model-admin-ui/traits/option-page/display-menu-page/' . $this->table);
    }

}
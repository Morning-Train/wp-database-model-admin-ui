<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Traits;

use Illuminate\Database\Eloquent\Builder;
use Morningtrain\WP\DatabaseModelAdminUi\Model\AdminTable;
use Morningtrain\WP\Hooks\Hook;
use Morningtrain\WP\View\View;
use WP_Screen;

trait AdminUi
{

    private AdminTable $adminTable;

    // Table columns
    private array $columnsData;
    private array $columns;
    private array $searchableColumns;
    private array $sortableColumns;

    private string $primaryColumn;
    private string $searchButtonText;

    // Option page settings
    private string $pageTitle;
    private string $menuTitle;
    private string $capability;
    private string $menu_slug;
    private ?string $callback;
    private string $iconUrl;
    private ?int $position;


    public function initAdminUi(): void
    {
        $this->handleAdminUiTableData();
        $this->loadAdminUiHooks();
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

        $this->prepareItemsToAdminUi();

        echo View::first(
            [
                'wpdbmodeladminui/admin-ui-form',
                'wpdbmodeladminui::admin-ui-form',
            ],
            [
                'pageTitle' => $this->pageTitle,
                'page' => $this->table,
                'searchBoxText' => $this->searchButtonText,
                'searchBoxInputId' => $this->table,
                'adminTable' => $this->adminTable,
            ]
        );
    }

    private function handleAdminUiTableData(): void
    {
        $this->adminUiTableData = $this->adminUiTableData ?? [];
        $this->columnsData = $this->adminUiTableData['tableColumns'] ?? [];
        $this->searchButtonText = $this->adminUiTableData['tableSearchButtonText'] ?? __('Search', '');

        $this->pageTitle = $this->adminUiTableData['pageTitle'] ?? __('Admin Table', '');
        $this->menuTitle = $this->adminUiTableData['menuTitle'] ?? __('Admin Table', '');
        $this->capability = $this->adminUiTableData['capability'] ?? 'manage_options';
        $this->menu_slug = $this->table;
        $this->iconUrl = $this->adminUiTableData['iconUrl'] ?? '';
        $this->position = $this->adminUiTableData['position'] ?? null;

        /*** @see \WP_List_Table::get_default_primary_column_name */
        $this->primaryColumn = array_keys($this->adminUiTableData['tableColumns'])[0];
    }

    private function loadAdminUiHooks() : void
    {
        Hook::action('admin_menu', function () {
            add_menu_page(
                $this->pageTitle,
                $this->menuTitle,
                $this->capability,
                $this->table,
                [$this, 'displayMenuPage'],
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

    private function prepareItemsToAdminUi(): void
    {
        $this->adminTable = new AdminTable($this->table);

        $this->handleAdminTableColumns();

        $query = static::query();
        $query = $this->handleQueryWheres($query);
        $query = $this->handleQueryOrderBy($query);

        $data = $query
            ->get()
            ->toArray();

        $data = $this->markSearchWordInSearchableColumns($data);

        $this->adminTable->prepare_items($data);
    }

    private function handleAdminTableColumns(): void
    {
        $this->columns = array_combine(
            array_keys($this->columnsData),
            array_map(function ($column) {
                return $column['title'];
            }, $this->columnsData)
        );
        $this->sortableColumns = array_keys(array_filter($this->columnsData, function ($column) {
            return ! empty($column['sortable']);
        }));
        $this->searchableColumns = array_keys(array_filter($this->columnsData, function ($column) {
            return ! empty($column['searchable']);
        }));

        $this->adminTable->addColumns($this->columns);
        $this->adminTable->addSortableColumns($this->sortableColumns);
    }

    private function handleQueryWheres(Builder $query): Builder
    {
        $searchWord = $_GET['s'] ?? null;

        if (empty($searchWord) || empty($this->searchableColumns)) {
            return $query;
        }

        return $query
            ->where(function (Builder $query) use ($searchWord) {
                foreach ($this->searchableColumns as $searchableColumn) {
                    $query->orWhere($searchableColumn, 'LIKE', '%' . $searchWord . '%');
                }

                return $query;
            });
    }

    private function handleQueryOrderBy(Builder $query): Builder
    {
        $orderby = $_GET['orderby'] ?? $this->adminTable->get_primary_column();
        $order = $_GET['order'] ?? 'asc';

        return $query
            ->orderBy($orderby, $order);
    }

    private function markSearchWordInSearchableColumns($data): array
    {
        $searchWord = $_GET['s'] ?? null;

        if (empty($searchWord)) {
            return $data;
        }

        foreach ($data as $key => $item) {
            foreach ($this->searchableColumns as $searchableColumn) {
                $pos = stripos($data[$key][$searchableColumn], $searchWord);

                while ($pos !== false) {
                    $wordLength = strlen($searchWord);

                    $data[$key][$searchableColumn] = substr_replace($data[$key][$searchableColumn], '<mark>', $pos, 0);
                    $data[$key][$searchableColumn] = substr_replace($data[$key][$searchableColumn], '</mark>', $pos + $wordLength + 7, 0);

                    $pos = stripos($data[$key][$searchableColumn], $searchWord, $pos + $wordLength + 13);
                }
            }
        }

        return $data;
    }

}
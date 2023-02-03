<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Traits;

use Illuminate\Database\Eloquent\Builder;
use Morningtrain\WP\DatabaseModelAdminUi\Model\AdminTable;
use Morningtrain\WP\Hooks\Hook;
use Morningtrain\WP\View\View;

trait AdminUi
{

    use OptionPage;

    private AdminTable $adminTable;
    private string $searchButtonText;
    private array $columnsData;
    private array $columns;
    private array $searchableColumns;
    private array $sortableColumns;


    public function initAdminUi(): void
    {
        $this->handleTableData();
        $this->initOptionPage();
        $this->loadAdminUiHooks();
    }

    private function handleTableData(): void
    {
        $this->tableData = $this->adminUiTableData ?? [];
        $this->columnsData = $this->tableData['tableColumns'] ?? [];
        $this->searchButtonText = $this->tableData['tableSearchButtonText'] ?? __('Search', '');
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

    public function loadAdminUiHooks(): void
    {
        Hook::action('wp-database-model-admin-ui/traits/option-page/display-menu-page/' . $this->table, function () {
            if (! empty($_GET['action']) && ! empty($_GET['model_id'])) {
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
        });
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
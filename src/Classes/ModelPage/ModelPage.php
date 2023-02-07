<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage;

use Illuminate\Support\Facades\Schema;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPages;
use Morningtrain\WP\DatabaseModelAdminUi\ModelUI;

class ModelPage
{

    public array $columns;
    public array $tableColumns;
    public array $searchableColumns;
    public array $sortableColumns;
    public array $excludeColumns;

    public array $rowActions;

    public bool $acfEditable;
    public bool $removable;

    public string $pageTitle;
    public string $menuTitle;
    public string $capability;
    public string $iconUrl;
    public ?int $position;
    public string $searchButtonText;

    public string $acfEditablePageSlug;
    public string $primaryColumn;

    public function __construct(
        public string $pageSlug,
        public string $model
    )
    {
        $this->acfEditable = false;
        $this->removable = false;

        $this->columns = [];
        $this->tableColumns = [];
        $this->searchableColumns = [];
        $this->sortableColumns = [];
        $this->excludeColumns = [];

        $this->rowActions = [];

        $this->pageTitle = __('Admin table');
        $this->menuTitle = __('Admin table');
        $this->capability = 'manage_options';
        $this->iconUrl = '';
        $this->position = null;
        $this->searchButtonText = __('Search');

        $this->acfEditablePageSlug = 'edit_' . $this->pageSlug;
    }

    public function register(): void
    {
        if (! $this->checkForTableColumns()) {
            return;
        }

        $this->checkForEditRowAction();
        $this->checkForDeleteRowAction();

        ModelPages::setModelPageForList($this);
    }

    public function withRowActions(array $rowActions): self
    {
        $this->rowActions = $rowActions;

        return $this;
    }

    public function withColumns(array $columns): self
    {
        $this->columns = array_combine(
            array_column($columns, 'slug'),
            $columns
        );

        $this->tableColumns = array_combine(
            array_column($this->columns, 'slug'),
            array_column($this->columns, 'title')
        );

        $this->searchableColumns = array_column(array_filter($columns, function (ModelPageColumn $column) {
            return $column->searchable;
        }), 'slug');

        $this->sortableColumns = array_column(array_filter($columns, function (ModelPageColumn $column) {
            return $column->sortable;
        }), 'slug');

        return $this;
    }

    public function withoutColumns(array $columnSlugs): self
    {
        $this->excludeColumns = $columnSlugs;

        return $this;
    }

    public function makeRemovable(): self
    {
        $this->removable = true;

        return $this;
    }

    public function makeAcfEditable(): self
    {
        $this->acfEditable = true;

        return $this;
    }

    public function withIconUrl(string $iconUrl): self
    {
        $this->iconUrl = $iconUrl;

        return $this;
    }

    public function withPageTitle(string $pageTitle): self
    {
        $this->pageTitle = $pageTitle;

        return $this;
    }

    public function withMenuTitle(string $menuTitle): self
    {
        $this->menuTitle = $menuTitle;

        return $this;
    }

    public function withCapability(string $capability): self
    {
        $this->capability = $capability;

        return $this;
    }

    public function withPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function withSearchButtonText(string $searchButtonText): self
    {
        $this->searchButtonText = $searchButtonText;

        return $this;
    }

    private function checkForTableColumns(): bool
    {
        if (empty($this->tableColumns)) {
            $columns = Schema::getColumnListing((new $this->model())->getTable());
            $this->tableColumns = array_combine(
                $columns,
                array_map(function ($column) {
                    return ucfirst(str_replace('_', ' ', $column));
                }, $columns)
            );
        }

        if (empty($this->tableColumns)) {
            return false;
        }

        $this->tableColumns = array_filter($this->tableColumns, function ($tableColumnKey) {
            return ! in_array($tableColumnKey, $this->excludeColumns, true);
        }, ARRAY_FILTER_USE_KEY);

        /*** @see \WP_List_Table::get_default_primary_column_name */
        $this->primaryColumn = array_keys($this->tableColumns)[0];

        return true;
    }

    private function checkForEditRowAction(): void
    {
        if (! $this->acfEditable || ! empty($this->rowActions['edit'])) {
            return;
        }

        $this->rowActions[] = ModelUI::modelPageRowAction(
            'edit',
            function (array $item, ModelPage $modelPage) {
                $href = admin_url('admin.php') . '?page=' . $modelPage->acfEditablePageSlug . '&model_id=' . $item['id'];

                return '<a href="' . $href . '">' . __('Edit') . '</a>';
            }
        );
    }

    private function checkForDeleteRowAction(): void
    {
        if (! $this->removable || ! empty($this->rowActions['delete'])) {
            return;
        }

        $this->rowActions[] = ModelUI::modelPageRowAction(
            'delete',
            function (array $item, ModelPage $modelPage) {
                $href = admin_url('admin.php') . '?page=' . $modelPage->pageSlug . '&model_id=' . $item['id'] . '&action=delete';

                return '<a href="' . $href . '" onclick="return confirm(\'' .  __('Are you sure?') . '\')">' . __('Delete') . '</a>';
            }
        );
    }

}
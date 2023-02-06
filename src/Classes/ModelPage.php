<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Classes;

use Illuminate\Support\Facades\Schema;
use Morningtrain\Test\Models\Caar;

class ModelPage
{

    public array $columns = [];
    public array $tableColumns = [];
    public array $searchableColumns = [];
    public array $sortableColumns = [];

    public array $rowActions = [];

    public bool $acfEditable = false;
    public bool $removable = false;

    public string $pageTitle;
    public string $menuTitle;
    public string $capability;
    public string $iconUrl = '';
    public ?int $position = null;
    public string $searchButtonText;

    public string $acfEditablePageSlug;
    public string $primaryColumn;

    public function __construct(
        public string $pageSlug,
        public string $model
    )
    {
        $this->pageTitle = __('Admin table');
        $this->menuTitle = __('Admin table');
        $this->capability = 'manage_options';
        $this->searchButtonText = __('Search');

        $this->acfEditablePageSlug = 'edit_' . $this->pageSlug;
    }

    public function register(): void
    {
        if (empty($this->tableColumns)) {
            $columns = Schema::getColumnListing((new Caar())->getTable());
            $this->tableColumns = array_combine($columns, $columns);
        }

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

        /*** @see \WP_List_Table::get_default_primary_column_name */
        $this->primaryColumn = array_keys($this->columns)[0];

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

}
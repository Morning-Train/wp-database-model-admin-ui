<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Classes;

class ModelPage
{

    public array $columns = [];
    public array $searchableColumns = [];
    public array $sortableColumns = [];

    public bool $acfEditable = false;
    public bool $readable = false;
    public bool $removable = false;

    public string $pageTitle;
    public string $menuTitle;
    public string $capability;
    public string $iconUrl = '';
    public ?int $position = null;
    public string $searchButtonText;

    public string $readablePageSlug;
    public string $acfEditablePageSlug;
    public string $primaryColumn;

    public function __construct(
        public string $model,
        public string $pageSlug
    )
    {
        $this->pageTitle = __('Admin table');
        $this->menuTitle = __('Admin table');
        $this->capability = 'manage_options';
        $this->searchButtonText = __('Search');

        $this->readablePageSlug = 'view_' . $this->pageSlug;
        $this->acfEditablePageSlug = 'edit_' . $this->pageSlug;
    }

    public function init(): void
    {
        ModelPages::setModelPageForList($this);
    }

    public function columns(array $columns): self
    {
        $this->columns = array_combine(
            array_keys($columns),
            array_map(function ($column) {
                return $column['title'];
            }, $columns)
        );

        $this->sortableColumns = array_keys(array_filter($columns, function ($column) {
            return ! empty($column['sortable']);
        }));

        $this->searchableColumns = array_keys(array_filter($columns, function ($column) {
            return ! empty($column['searchable']);
        }));

        /*** @see \WP_List_Table::get_default_primary_column_name */
        $this->primaryColumn = array_keys($this->columns)[0];

        return $this;
    }

    public function readable(): self
    {
        $this->readable = true;

        return $this;
    }

    public function removable(): self
    {
        $this->removable = true;

        return $this;
    }

    public function acfEditable(): self
    {
        $this->acfEditable = true;

        return $this;
    }

    public function setIconUrl(string $iconUrl): self
    {
        $this->iconUrl = $iconUrl;

        return $this;
    }

    public function setPageTitle(string $pageTitle): self
    {
        $this->pageTitle = $pageTitle;

        return $this;
    }

    public function setMenuTitle(string $menuTitle): self
    {
        $this->menuTitle = $menuTitle;

        return $this;
    }

    public function setCapability(string $capability): self
    {
        $this->capability = $capability;

        return $this;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function setSearchButtonText(string $searchButtonText): self
    {
        $this->searchButtonText = $searchButtonText;

        return $this;
    }

}
<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage;

use Illuminate\Support\Facades\Schema;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPages;
use Morningtrain\WP\DatabaseModelAdminUi\ModelUI;

class ModelPage
{
    public bool $removable = false;

    public ?string $pageScreen = null;

    public array $columns = [];

    public array $tableColumns = [];

    /** @var Column[] */
    public array $searchableColumns = [];

    /** @var Column[] */
    public array $sortableColumns = [];

    public array $excludeColumns = [];

    public $modifyQueryCallback = null;

    /** @var AdminTableView[] */
    public array $adminTableViews = [];

    public $adminTableViewsCallback = null;

    /** @var AdminTableExtraTablenav[] */
    public array $adminTableTopExtraTablenavs = [];

    /** @var AdminTableExtraTablenav[] */
    public array $adminTableBottomExtraTablenavs = [];

    public ?ViewPage $viewPage = null;

    public ?AcfCreatePage $acfCreatePage = null;

    public ?AcfEditPage $acfEditPage = null;

    /** @var MetaBox[] */
    public array $metaBoxes = [];

    public array $rowActions = [];

    public ?string $parentSlug = null;

    public string $pageTitle;

    public string $menuTitle;

    public string $capability = 'manage_options';

    public string $iconUrl = '';

    public ?int $position = null;

    public string $searchButtonText;

    public string $primaryColumn;

    public string $primaryOrder = 'DESC';

    public function __construct(
        public string $pageSlug,
        public string $model
    ) {
        $this->pageTitle = ucfirst($pageSlug);
        $this->menuTitle = ucfirst($pageSlug);
        $this->searchButtonText = __('Search');
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

    public function withIconUrl(string $iconUrl): self
    {
        $this->iconUrl = $iconUrl;

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

    public function addPrimaryColumn(string $column): self
    {
        $this->primaryColumn = $column;

        return $this;
    }

    public function addPrimaryOrder(string $order): self
    {
        $this->primaryOrder = $order;

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

        $this->searchableColumns = array_filter($columns, function (Column $column) {
            return $column->searchable;
        });

        $this->sortableColumns = array_filter($columns, function (Column $column) {
            return $column->sortable;
        });

        return $this;
    }

    public function withRowActions(array $rowActions): self
    {
        $this->rowActions = $rowActions;

        return $this;
    }

    public function withModifyQueryCallback(callable|string $callback): self
    {
        $this->modifyQueryCallback = $callback;

        return $this;
    }

    public function withAdminTableViews(array $views): self
    {
        $this->adminTableViews = $views;

        return $this;
    }

    public function withAdminTableViewsCallback(callable|string $callback): self
    {
        $this->adminTableViewsCallback = $callback;

        return $this;
    }

    public function withAdminTableTopExtraTablenavs(array $extraTablenavs): self
    {
        $this->adminTableTopExtraTablenavs = $extraTablenavs;

        return $this;
    }

    public function withAdminTableBottomExtraTablenavs(array $extraTablenavs): self
    {
        $this->adminTableBottomExtraTablenavs = $extraTablenavs;

        return $this;
    }

    public function withViewPage(ViewPage $viewPage): self
    {
        $this->viewPage = $viewPage;
        $this->viewPage->setPageSlugAndCapability('view_' . $this->pageSlug, $this->capability);

        return $this;
    }

    public function withAcfCreatePage(AcfCreatePage $acfCreatePage): self
    {
        $this->acfCreatePage = $acfCreatePage;
        $this->acfCreatePage->setPageSlugAndCapability('create_' . $this->pageSlug, $this->capability);

        return $this;
    }

    public function withAcfEditPage(AcfEditPage $acfEditPage): self
    {
        $this->acfEditPage = $acfEditPage;
        $this->acfEditPage->setPageSlugAndCapability('edit_' . $this->pageSlug, $this->capability);

        return $this;
    }

    public function withMetaBoxes(array $metaBoxes): self
    {
        $this->metaBoxes = $metaBoxes;

        return $this;
    }

    public function withoutColumns(array $columnSlugs): self
    {
        $this->excludeColumns = $columnSlugs;

        return $this;
    }

    public function makeSubMenu(string $parentSlug): self
    {
        $this->parentSlug = $parentSlug;

        return $this;
    }

    public function makeRemovable(): self
    {
        $this->removable = true;

        return $this;
    }

    public function register(): void
    {
        if (! $this->checkForTableColumns()) {
            return;
        }

        $this->checkForViewRowAction();
        $this->checkForEditRowAction();
        $this->checkForDeleteRowAction();

        ModelPages::addModelPageToList($this);
    }

    public function setPageScreen(string $screen): self
    {
        $this->pageScreen = $screen;

        return $this;
    }

    public function getOverviewPageUrl(): ?string
    {
        $pageParams = 'page=' . $this->pageSlug;

        if ($this->parentSlug === null) {
            return admin_url('admin.php') . '?' . $pageParams;
        }

        if (str_contains($this->parentSlug, '?')) {
            return admin_url($this->parentSlug) . '&' . $pageParams;
        }

        // Does have a parent slug, but it's a page
        return admin_url('admin.php') . '?' . $pageParams;
    }

    public function getAcfCreatePageUrl(): ?string
    {
        if ($this->acfCreatePage === null) {
            return null;
        }

        return admin_url('admin.php') . '?page=' . $this->acfCreatePage->pageSlug;
    }

    public function getAcfEditPageUrl(string|int $modelId): ?string
    {
        if ($this->acfEditPage === null) {
            return null;
        }

        return admin_url('admin.php') . '?page=' . $this->acfEditPage->pageSlug . '&model_id=' . $modelId;
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

        if (empty($this->primaryColumn)) {
            /*** @see \WP_List_Table::get_default_primary_column_name */
            $this->primaryColumn = array_keys($this->tableColumns)[0];
        }

        return true;
    }

    private function checkForViewRowAction(): void
    {
        if (empty($this->viewPage) || ! empty($this->rowActions['view'])) {
            return;
        }

        $this->rowActions[] = ModelUI::rowAction(
            'view',
            function (array $item, ModelPage $modelPage) {
                $href = admin_url('admin.php') . '?page=' . $modelPage->viewPage->pageSlug . '&model_id=' . $item['id'];

                return '<a href="' . $href . '">' . __('View') . '</a>';
            }
        );
    }

    private function checkForEditRowAction(): void
    {
        if ($this->acfEditPage === null || ! empty($this->rowActions['edit'])) {
            return;
        }

        $this->rowActions[] = ModelUI::rowAction(
            'edit',
            function (array $item, ModelPage $modelPage) {
                $href = admin_url('admin.php') . '?page=' . $modelPage->acfEditPage->pageSlug . '&model_id=' . $item['id'];

                return '<a href="' . $href . '">' . __('Edit') . '</a>';
            }
        );
    }

    private function checkForDeleteRowAction(): void
    {
        if (! $this->removable || ! empty($this->rowActions['delete'])) {
            return;
        }

        $this->rowActions[] = ModelUI::rowAction(
            'delete',
            function (array $item, ModelPage $modelPage) {
                $href = admin_url('admin.php') . '?page=' . $modelPage->pageSlug . '&model_id=' . $item['id'] . '&action=delete';

                return '<a href="' . $href . '" onclick="return confirm(\'' . __('Are you sure?') . '\')">' . __('Delete') . '</a>';
            }
        );
    }
}

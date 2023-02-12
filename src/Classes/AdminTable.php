<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Classes;

if (! class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage\ModelPage;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage\RowAction;
use WP_List_Table;

class AdminTable extends WP_List_Table
{
    private ModelPage $modelPage;
    private array $columns = [];
    private array $sortableColumns = [];

    public function __construct(string $slug)
    {
        parent::__construct(
            [
                'plural' => '',
                'singular' => '',
                'screen' => 'toplevel_page_' . $slug,
            ]
        );
    }

    public function get_columns(): array
    {
        return $this->columns;
    }

    public function get_sortable_columns(): array
    {
        return $this->sortableColumns;
    }

    public function prepare_items(array $data = []): void
    {
        $per_page = $this->getPerPage();

        $this->_column_headers = [
            $this->get_columns(),
            [],
            $this->get_sortable_columns(),
        ];

        $this->items = $data;

        $total_items = count($this->items);

        if (count($this->items) > $per_page) {
            $this->items = array_slice($this->items, ($this->get_pagenum() - 1) * $per_page, $per_page);
        }

        $this->set_pagination_args(['total_items' => $total_items, 'per_page' => $per_page]);
    }

    /**
     * @param  object|array  $item
     * @param  string  $column_name
     */
    protected function column_default($item, $column_name): void
    {
        if (! empty($this->modelPage->columns[$column_name]) && $this->modelPage->columns[$column_name]->renderCallback !== null) {
            echo $this->modelPage->columns[$column_name]->render($item, $this->modelPage);
        } elseif ($this->modelPage->viewPage !== null && $this->get_primary_column() === $column_name) {
            $href = admin_url('admin.php') . '?page=' . $this->modelPage->viewPage->pageSlug . '&model_id=' . $item['id'];

            echo '<a href="' . $href . '">' . $item[$column_name] . '</a>';
        } elseif ($this->modelPage->acfEditPage !== null && $this->get_primary_column() === $column_name) {
            $href = admin_url('admin.php') . '?page=' . $this->modelPage->acfEditPage->pageSlug . '&model_id=' . $item['id'];

            echo '<a href="' . $href . '">' . $item[$column_name] . '</a>';
        } else {
            echo $item[$column_name];
        }

        if ($this->get_primary_column() === $column_name) {
            $rowActions = array_combine(
                array_column($this->modelPage->rowActions, 'slug'),
                array_map(function (RowAction $rowAction) use ($item) {
                    return $rowAction->render($item, $this->modelPage);
                }, $this->modelPage->rowActions)
            );

            echo $this->row_actions($rowActions);
        }
    }

    public function addModelPage(ModelPage $modelPage): void
    {
        $this->modelPage = $modelPage;
    }

    public function addColumns(array $columns = []): void
    {
        foreach ($columns as $id => $title) {
            $this->columns[$id] = $title;
        }
    }

    public function addSortableColumns(array $sortableColumns = []): void
    {
        foreach ($sortableColumns as $key) {
            $this->sortableColumns[$key] = [$key, 'asc'];
        }
    }

    public function getPerPage(): int
    {
        $screen = get_current_screen();

        if ($screen === null) {
            return 20;
        }

        return $this->get_items_per_page($screen->get_option('per_page', 'option'), 20);
    }
}

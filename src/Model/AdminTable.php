<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Model;

if (! class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

use WP_List_Table;

class AdminTable extends WP_List_Table
{

    private array $columns = [];
    private array $sortableColumns = [];
    private string $slug;

    public function __construct(string $slug)
    {
        $this->slug = $slug;

        parent::__construct(
            [
                'plural' => '',
                'singular' => '',
                'screen' => 'toplevel_page_' . $slug,
            ]
        );

        \add_screen_option('per_page', ['default' => 20, 'option' => 'per_page']);
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
            $this->get_sortable_columns()
        ];

        $this->items = $data;

        $total_items = count($this->items);

        if(count($this->items) > $per_page) {
            $this->items = array_slice($this->items, ($this->get_pagenum() - 1) * $per_page, $per_page);
        }

        $this->set_pagination_args(['total_items' => $total_items, 'per_page' => $per_page]);
    }

    /**
     * @param object|array $item
     * @param string $column_name
     */
    protected function column_default($item, $column_name): void
    {
        echo apply_filters('wp-database-model-admin-ui/admin-table/' . $this->slug . '/column_default', $item[$column_name] ?? null, $item, $column_name, $this);

        if($this->get_primary_column() === $column_name) {
            echo $this->row_actions(
                apply_filters('wp-database-model-admin-ui/admin-table/' . $this->slug . '/column_default/row_actions', [], $item, $column_name, $this)
            );
        }
    }


    public function addColumns(array $columns = []): void
    {
        foreach($columns as $id => $title) {
            $this->columns[$id] = $title;
        }
    }

    public function addSortableColumns(array $sortableColumns = []): void
    {
        foreach($sortableColumns as $key) {
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
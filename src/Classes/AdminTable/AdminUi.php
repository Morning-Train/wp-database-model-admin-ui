<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Classes\AdminTable;

use Illuminate\Database\Eloquent\Builder;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage;
use Morningtrain\WP\DatabaseModelAdminUi\Model\AdminTable;
use Morningtrain\WP\Hooks\Hook;
use Morningtrain\WP\View\View;
use WP_Screen;

class AdminUi
{

    private ModelPage $modelPage;
    private AdminTable $adminTable;

    public function __construct(ModelPage $modelPage)
    {
        $this->modelPage = $modelPage;

        $this->loadAdminUiHooks();
    }

    public function displayMenuPage(): void
    {
        if (empty($_GET['page']) || $_GET['page'] !== $this->modelPage->pageSlug) {
            return;
        }

        $screen = get_current_screen();

        if ($screen === null || $screen->id !== 'toplevel_page_' . $this->modelPage->pageSlug) {
            return;
        }

        $this->prepareItemsToAdminUi();

        echo View::first(
            [
                'wpdbmodeladminui/admin-ui-form',
                'wpdbmodeladminui::admin-ui-form',
            ],
            [
                'pageTitle' => $this->modelPage->pageTitle,
                'page' => $this->modelPage->pageSlug,
                'useSearchBox' => ! empty($this->modelPage->searchableColumns),
                'searchBoxText' => $this->modelPage->searchButtonText,
                'searchBoxInputId' => $this->modelPage->pageSlug,
                'adminTable' => $this->adminTable,
            ]
        );
    }

    private function loadAdminUiHooks() : void
    {
        Hook::action('admin_menu', function () {
            add_menu_page(
                $this->modelPage->pageTitle,
                $this->modelPage->menuTitle,
                $this->modelPage->capability,
                $this->modelPage->pageSlug,
                [$this, 'displayMenuPage'],
                $this->modelPage->iconUrl,
                $this->modelPage->position
            );
        });
    }

    private function prepareItemsToAdminUi(): void
    {
        $this->adminTable = new AdminTable($this->modelPage->pageSlug);

        $this->handleAdminTableColumns();

        $query = $this->modelPage->model::query();
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
        $this->adminTable->addColumns($this->modelPage->columns);
        $this->adminTable->addSortableColumns($this->modelPage->sortableColumns);
    }

    private function handleQueryWheres(Builder $query): Builder
    {
        $searchWord = $_GET['s'] ?? null;

        if (empty($searchWord) || empty($this->searchableColumns)) {
            return $query;
        }

        return $query
            ->where(function (Builder $query) use ($searchWord) {
                foreach ($this->modelPage->searchableColumns as $searchableColumn) {
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
            foreach ($this->modelPage->searchableColumns as $searchableColumn) {
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
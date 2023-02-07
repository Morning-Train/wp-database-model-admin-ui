<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Services;

use Illuminate\Database\Eloquent\Builder;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage\ModelPage;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPages;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\AdminTable;
use Morningtrain\WP\View\View;

class AdminUiMenuService
{

    public static function displayMenuPage(): void
    {
        $page = $_GET['page'] ?? null;
        $modelPage = ModelPages::getModelPages()[$page];

        $adminTable = static::prepareAdminTable($modelPage);

        echo View::first(
            [
                'wpdbmodeladminui/admin-ui-form',
                'wpdbmodeladminui::admin-ui-form',
            ],
            [
                'pageTitle' => $modelPage->pageTitle,
                'page' => $modelPage->pageSlug,
                'useSearchBox' => ! empty($modelPage->searchableColumns),
                'searchBoxText' => $modelPage->searchButtonText,
                'searchBoxInputId' => $modelPage->pageSlug,
                'adminTable' => $adminTable,
            ]
        );
    }

    private static function prepareAdminTable(ModelPage $modelPage): AdminTable
    {
        $query = $modelPage->model::query();
        $query = static::handleQueryWheres($query, $modelPage);
        $query = static::handleQueryOrderBy($query, $modelPage);

        $data = $query
            ->get()
            ->toArray();

        $data = static::markSearchWordInSearchableColumns($data, $modelPage);

        $adminTable = new AdminTable($modelPage->pageSlug);
        $adminTable->addModelPage($modelPage);
        $adminTable->addColumns($modelPage->tableColumns);
        $adminTable->addSortableColumns($modelPage->sortableColumns);
        $adminTable->prepare_items($data);

        return $adminTable;
    }

    private static function handleQueryWheres(Builder $query, ModelPage $modelPage): Builder
    {
        $searchWord = $_GET['s'] ?? null;

        if (empty($searchWord) || empty($modelPage->searchableColumns)) {
            return $query;
        }

        return $query
            ->where(function (Builder $query) use ($searchWord, $modelPage) {
                foreach ($modelPage->searchableColumns as $searchableColumn) {
                    $query->orWhere($searchableColumn, 'LIKE', '%' . $searchWord . '%');
                }

                return $query;
            });
    }

    private static function handleQueryOrderBy(Builder $query, ModelPage $modelPage): Builder
    {
        if (empty($modelPage->primaryColumn)) {
            return $query;
        }

        $orderby = $_GET['orderby'] ?? $modelPage->primaryColumn;
        $order = $_GET['order'] ?? 'asc';

        return $query
            ->orderBy($orderby, $order);
    }

    private static function markSearchWordInSearchableColumns($data, ModelPage $modelPage): array
    {
        $searchWord = $_GET['s'] ?? null;

        if (empty($searchWord)) {
            return $data;
        }

        foreach ($data as $key => $item) {
            foreach ($modelPage->searchableColumns as $searchableColumn) {
                $pos = stripos($data[$key][$searchableColumn], $searchWord);

                while ($pos !== false) {
                    $wordLength = strlen($searchWord);

                    $data[$key][$searchableColumn] = substr_replace($data[$key][$searchableColumn], '<mark>', $pos, 0);
                    $data[$key][$searchableColumn] = substr_replace($data[$key][$searchableColumn], '</mark>', $pos + $wordLength + 6, 0);

                    $pos = stripos($data[$key][$searchableColumn], $searchWord, $pos + $wordLength + 13);
                }
            }
        }

        return $data;
    }

}
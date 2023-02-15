<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage\ModelPage;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPages;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\AdminTable;
use Morningtrain\WP\View\View;

class AdminUiMenuService
{
    public static function displayMenuPage(): void
    {
        global $wp_meta_boxes;
        $page = $_GET['page'] ?? null;
        $modelPage = ModelPages::getModelPages()[$page];

        $adminTable = static::prepareAdminTable($modelPage);

        echo View::first(
            [
                'wpdbmodeladminui/admin-ui-form',
                'wpdbmodeladminui::admin-ui-form',
            ],
            [
                'hasSideMetaBoxes' => ! empty($wp_meta_boxes['toplevel_page_' . $_GET['page']]['side']),
                'pageTitle' => $modelPage->pageTitle,
                'postType' => $_GET['post_type'] ?? null,
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
        $data = $modelPage->model::query()
            ->get()
            ->toArray();

        $data = static::handleExtraColumnsData($data, $modelPage);
        $data = static::handleWheres($data, $modelPage);
        $data = static::handleOrderBy($data, $modelPage);
        $data = static::markSearchWordInSearchableColumns($data, $modelPage);

        $adminTable = new AdminTable($modelPage->pageSlug);
        $adminTable->addModelPage($modelPage);
        $adminTable->addColumns($modelPage->tableColumns);
        $adminTable->addSortableColumns($modelPage->sortableColumns);
        $adminTable->prepare_items($data);

        return $adminTable;
    }

    private static function handleExtraColumnsData(array $data, ModelPage $modelPage): array
    {
        foreach ($data as $key => $item) {
            foreach ($modelPage->columns as $columnName => $value) {
                if ($modelPage->columns[$columnName]->renderCallback === null) {
                    continue;
                }

                $data[$key][$columnName] = $modelPage->columns[$columnName]->render($item, $modelPage);
            }
        }

        return $data;
    }

    private static function handleWheres(array $data, ModelPage $modelPage): array
    {
        $searchWord = $_GET['s'] ?? null;

        if (empty($searchWord) || empty($modelPage->searchableColumns)) {
            return $data;
        }

        $filteredData = [];

        foreach ($modelPage->searchableColumns as $searchableColumn) {
            foreach ($data as $values) {
                if (empty($values[$searchableColumn]) || ! str_contains($values[$searchableColumn], $searchWord)) {
                    continue;
                }

                $filteredData[] = $values;
            }
        }
        
        return $filteredData;
    }

    private static function handleOrderBy(array $data, ModelPage $modelPage): array
    {
        if (empty($modelPage->primaryColumn)) {
            return $data;
        }

        $orderby = $_GET['orderby'] ?? $modelPage->primaryColumn;
        $order = $_GET['order'] ?? 'asc';

        usort(
            $data,
            function (array $itemOne, array $itemTwo) use($orderby, $order) {
                if ($order === 'asc') {
                    return strcmp($itemOne[$orderby], $itemTwo[$orderby]);
                }

                return strcmp($itemTwo[$orderby], $itemOne[$orderby]);
            }
        );

        return $data;
    }

    private static function markSearchWordInSearchableColumns($data, ModelPage $modelPage): array
    {
        $searchWord = $_GET['s'] ?? null;

        if (empty($searchWord)) {
            return $data;
        }

        foreach ($data as $key => $item) {
            foreach ($modelPage->searchableColumns as $searchableColumn) {
                if (empty($data[$key][$searchableColumn])) {
                    continue;
                }

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

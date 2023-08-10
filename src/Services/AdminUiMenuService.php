<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Services;

use Illuminate\Support\Collection;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\AdminTable;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage\ModelPage;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPages;
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
                'wpdbmodeladminui::admin-ui-form',
                'admin-ui-form',
            ],
            [
                'hasSideMetaBoxes' => ! empty($wp_meta_boxes[$modelPage->pageScreen]['side']),
                'modelPage' => $modelPage,
                'postType' => $_GET['post_type'] ?? null,
                'adminTable' => $adminTable,
            ]
        );
    }

    private static function prepareAdminTable(ModelPage $modelPage): AdminTable
    {
        $adminTable = new AdminTable($modelPage->pageSlug);

        $searchString = $_GET['s'] ?? '';
        $orderby = $_GET['orderby'] ?? $modelPage->primaryColumn;
        $order = $_GET['order'] ?? $modelPage->primaryOrder;

        $instance = (new $modelPage->model());
        $tableName = $instance->getTable();
        $dataQuery = $modelPage->model::query()
            ->select($tableName . '.*');

        if (! empty($searchString)) {
            foreach ($modelPage->searchableColumns as $searchableColumn) {
                if ($searchableColumn->searchableCallback !== null) {
                    call_user_func($searchableColumn->searchableCallback, $dataQuery, $searchString);

                    continue;
                }

                $dataQuery->orWhere($searchableColumn->slug, 'LIKE', '%' . $searchString . '%');
            }
        }

        $sortableColumn = collect($modelPage->sortableColumns)->firstWhere('slug', $orderby);
        if ($sortableColumn !== null) {
            if ($sortableColumn->sortableCallback !== null) {
                call_user_func($sortableColumn->sortableCallback, $dataQuery, $order);
            } else {
                $dataQuery->orderBy($orderby, $order);
            }
        }

        if ($modelPage->modifyQueryCallback !== null) {
            $dataQuery = call_user_func($modelPage->modifyQueryCallback, $dataQuery);
        }
        $dataQuery
            ->orderBy($tableName . '.' . $instance->getKeyName(), $modelPage->primaryOrder)
            ->paginate(page: $adminTable->get_pagenum(), perPage: $adminTable->getPerPage());

        $data = $dataQuery->get();

        $data = static::handleExtraColumnsData($data, $modelPage);
        $data = static::markSearchWordInSearchableColumns($data, $modelPage);

        $adminTable->addModelPage($modelPage);
        $adminTable->addColumns($modelPage->tableColumns);
        $adminTable->addSortableColumns($modelPage->sortableColumns);
        $adminTable->prepare_items($data, $dataQuery->count());

        return $adminTable;
    }

    private static function handleExtraColumnsData(Collection $data, ModelPage $modelPage): array
    {
        $dataArray = $data->toArray();

        foreach ($data as $key => $item) {
            foreach ($modelPage->columns as $columnName => $value) {
                if ($modelPage->columns[$columnName]->renderCallback === null) {
                    continue;
                }

                $dataArray[$key][$columnName] = $modelPage->columns[$columnName]->render($item, $modelPage);
            }
        }

        return $dataArray;
    }

    private static function markSearchWordInSearchableColumns(array $data, ModelPage $modelPage): array
    {
        $searchWord = $_GET['s'] ?? null;

        if (empty($searchWord)) {
            return $data;
        }

        foreach ($data as $key => $item) {
            foreach ($modelPage->searchableColumns as $searchableColumn) {
                if (empty($data[$key][$searchableColumn->slug])) {
                    continue;
                }

                $pos = stripos($data[$key][$searchableColumn->slug], $searchWord);

                while ($pos !== false) {
                    $wordLength = strlen($searchWord);

                    $data[$key][$searchableColumn->slug] = substr_replace($data[$key][$searchableColumn->slug], '<mark>', $pos, 0);
                    $data[$key][$searchableColumn->slug] = substr_replace($data[$key][$searchableColumn->slug], '</mark>', $pos + $wordLength + 6, 0);

                    $pos = stripos($data[$key][$searchableColumn->slug], $searchWord, $pos + $wordLength + 13);
                }
            }
        }

        return $data;
    }
}

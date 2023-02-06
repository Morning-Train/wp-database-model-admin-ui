<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Classes\AdminTable;

use Morningtrain\WP\DatabaseModelAdminUi\Classes\Helper;
use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage;
use Morningtrain\WP\Hooks\Hook;

class Removable
{

    private ModelPage $modelPage;

    public function __construct(ModelPage $modelPage)
    {
        $this->modelPage = $modelPage;

        $this->checkForDeleting();
        $this->loadRemovableHooks();
    }

    public function checkForDeleting(): void
    {
        $page = $_GET['page'] ?? null;
        $action = $_GET['action'] ?? null;
        $modelId = $_GET['model_id'] ?? null;

        if (empty($page) || empty($action) || empty($modelId)) {
            return;
        }

        if ($page !== $this->modelPage->pageSlug || $action !== 'delete' || ! is_numeric($modelId)) {
            return;
        }

        if (! empty($_GET['nonce']) && \wp_verify_nonce($_GET['nonce'], 'row-actions-delete') !== false) {
            // TODO: Add delete notice
            $this->modelPage->model::query()->where('id', $modelId)->delete();
        }

        $url = $_SERVER['HTTP_REFERER'] ?? admin_url('admin.php?page=' . $this->modelPage->pageSlug);

        header('Location: ' . $url);
        exit();
    }

    public function loadRemovableHooks(): void
    {
        Hook::filter(
            'wpdbmodeladminui/admin-table/' . $this->modelPage->pageSlug . '/row_actions',
            function (array $rowActions, object|array $item): array
            {
                $href = Helper::getAdminPageUrlWithQueryArgs(
                    $this->modelPage->pageSlug,
                    $item['id'],
                    'action',
                    wp_create_nonce('row-actions-delete')
                );

                $rowActions['delete'] = '<a href="' . $href . '" onclick="return confirm(\'' .  __('Are you sure?') . '\')">' . __('Delete') . '</a>';

                return $rowActions;
            }
        );
    }

}
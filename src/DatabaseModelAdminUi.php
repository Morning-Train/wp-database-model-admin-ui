<?php

namespace Morningtrain\WP\DatabaseModelAdminUi;

use Morningtrain\PHPLoader\Loader;
use Morningtrain\WP\View\View;

class DatabaseModelAdminUi
{

    public static function setup(string|array $modelsDir): void
    {
        Loader::create($modelsDir)
            ->call('setupAdminUi');

        View::addNamespace('wpdbmodeladminui', dirname(__DIR__) . '/resources/views');
    }

    public static function getAdminPageUrlWithQueryArgs(string $page, int $modelId, ?string $action = null, ?string $nonce = null): string
    {
        return add_query_arg(
            [
                'page' => $page,
                'model_id' => $modelId,
                'action' => $action,
                'nonce' => $nonce,
            ],
            admin_url('admin.php')
        );
    }

}
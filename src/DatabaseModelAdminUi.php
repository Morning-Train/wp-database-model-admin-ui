<?php

namespace Morningtrain\WP\DatabaseModelAdminUi;

use Morningtrain\PHPLoader\Loader;
use Morningtrain\WP\Hooks\Hook;
use Morningtrain\WP\View\View;

class DatabaseModelAdminUi
{

    private static array $modelsDir;

    public static function setup(string|array $modelsDir): void
    {
        static::$modelsDir = (array) $modelsDir;

        Loader::create($modelsDir)
            ->call('setupAdminUi');

        View::addNamespace('wpdbmodeladminui', dirname(__DIR__) . '/resources/views');

        static::setupAcf();
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

    public static function getEloquentModelsDirs(): array
    {
        return static::$modelsDir;
    }

    private static function setupAcf(): void
    {
        if (! class_exists('ACF')) {
            return;
        }

        Hook::action('acf/init', function () {
            acf_register_location_type(AcfEloquentModelLocation::class);
        });
    }

}
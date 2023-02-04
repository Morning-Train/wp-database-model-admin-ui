<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Classes;

class Helper
{

    private static array $eloquentModelsDirs;

    public static function setEloquentModelsDirs(array|string $eloquentModelsDirs): void
    {
        static::$eloquentModelsDirs = $eloquentModelsDirs;
    }

    public static function getEloquentModelsDirs(): array
    {
        return static::$eloquentModelsDirs;
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
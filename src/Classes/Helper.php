<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Classes;

class Helper
{

    private static array $eloquentModelsDirs;

    public static function setEloquentModelsDirs(array|string $eloquentModelsDirs): void
    {
        static::$eloquentModelsDirs = (array) $eloquentModelsDirs;
    }

    public static function getEloquentModelsDirs(): array
    {
        return static::$eloquentModelsDirs;
    }

    public static function getCurrentModePageFromUrlPage(): ?ModelPage
    {
        $page = $_GET['page'] ?? null;

        foreach (ModelPages::getModelPages() as $modelPage) {
            if (! $modelPage->acfEditable || $page !== $modelPage->acfEditablePageSlug) {
                continue;
            }

            return $modelPage;
        }

        return null;
    }

}
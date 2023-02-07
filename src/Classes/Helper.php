<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Classes;

use Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage\ModelPage;

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
            if ($modelPage->acfEditable && $page === $modelPage->acfEditablePageSlug) {
                return $modelPage;
            }

            if ($page === $modelPage->pageSlug) {
                return $modelPage;
            }
        }

        return null;
    }

    public static function getCurrentModePageFromUrlAcfEditablePage(): ?ModelPage
    {
        $page = $_GET['page'] ?? null;

        foreach (ModelPages::getModelPages() as $modelPage) {
            if ($modelPage->acfEditable && $page === $modelPage->acfEditablePageSlug) {
                return $modelPage;
            }
        }

        return null;
    }

}
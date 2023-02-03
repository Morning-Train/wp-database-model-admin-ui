<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Traits;

use Morningtrain\WP\Hooks\Hook;
use WP_Screen;

trait OptionPage
{

    use AdminUi;
    use Readable;
    use Removable;

    private array $tableData;
    private bool $isSetup = false;
    private array $traitsInUse;

    // Option page settings
    private string $pageTitle;
    private string $menuTitle;
    private string $capability;
    private string $menu_slug;
    private ?string $callback;
    private string $iconUrl;
    private ?int $position;

    public function initModelAdminUi(): void
    {
        if ($this->isSetup) {
            return;
        }

        $this->isSetup = true;
        $this->tableData = $this->getAdminUiData();

        $this->traitsInUse = $this->modelAdminUi ?? [];

        $this->pageTitle = $this->tableData['pageTitle'] ?? __('Admin Table', '');
        $this->menuTitle = $this->tableData['menuTitle'] ?? __('Admin Table', '');
        $this->capability = $this->tableData['capability'] ?? 'manage_options';
        $this->menu_slug = $this->table;
        $this->callback = $this->tableData['callback'] ?? null;
        $this->iconUrl = $this->tableData['iconUrl'] ?? '';
        $this->position = $this->tableData['position'] ?? null;

        $this->loadOptionHooks();
    }

    public function loadOptionHooks(): void
    {
        Hook::action('admin_menu', function () {
            \add_menu_page(
                $this->pageTitle,
                $this->menuTitle,
                $this->capability,
                $this->table,
                [$this, $this->callback ?? 'displayMenuPage'],
                $this->iconUrl,
                $this->position
            );
        });

        Hook::action('current_screen', function (WP_Screen $screen) {
            if ($screen->id !== 'toplevel_page_' . $this->table) {
                return;
            }

            $this->initAdminUi($this->tableData);

            foreach ($this->traitsInUse as $traitInUse) {
                $methodName = 'init' . ucfirst(basename($traitInUse));

                if (! method_exists($this, $methodName)) {
                    continue;
                }

                $this->{$methodName}();
            }
        });
    }

    public function displayMenuPage(): void
    {
        if (empty($_GET['page']) || $_GET['page'] !== $this->table) {
            return;
        }

        if (method_exists($this, 'loadOverview')) {
            $this->loadOverview();
        }

        foreach ($this->traitsInUse as $traitInUse) {
            $methodName = 'load' . ucfirst(basename($traitInUse));

            if (! method_exists($this, $methodName)) {
                continue;
            }

            $this->{$methodName}();
        }
    }

}
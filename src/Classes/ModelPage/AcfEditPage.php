<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage;

class AcfEditPage
{
    /** @var AcfLoadField[] */
    public array $loadFieldCallbacks = [];

    public $saveCallback = null;

    public ?string $pageSlug = null;

    public ?string $screen = null;

    public ?string $capability = null;

    public function withLoadFieldCallbacks(array $callbacks): self
    {
        $this->loadFieldCallbacks = array_combine(
            array_column($callbacks, 'slug'),
            $callbacks,
        );

        return $this;
    }

    public function withSaveCallback(callable|string $callback): self
    {
        $this->saveCallback = $callback;

        return $this;
    }

    public function withCapability(string $capability): self
    {
        $this->capability = $capability;

        return $this;
    }

    public function setPageSlugAndCapability(string $pageSlug, string $capability): self
    {
        $this->pageSlug = $pageSlug;
        $this->screen = 'admin_page_' . $this->pageSlug;
        $this->capability = $this->capability ?? $capability;

        return $this;
    }
}

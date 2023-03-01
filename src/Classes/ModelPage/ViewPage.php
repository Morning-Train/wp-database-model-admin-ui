<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage;

class ViewPage
{
    public $renderCallback = null;
    public ?string $pageSlug = null;
    public ?string $pageScreen = null;
    public ?string $capability = null;

    public function withRender(callable|string $renderCallback): self
    {
        $this->renderCallback = $renderCallback;

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
        $this->capability = $this->capability ?? $capability;

        return $this;
    }

    public function setPageScreen(string $pageScreen): self
    {
        $this->pageScreen = $pageScreen;

        return $this;
    }
}

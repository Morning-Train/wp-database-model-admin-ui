<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage;

class ViewPage
{
    public $renderCallback = null;
    public ?string $pageSlug = null;
    public ?string $screen = null;
    public string $capability = 'manage_options';

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

    public function setPageSlug(string $pageSlug): self
    {
        $this->pageSlug = $pageSlug;
        $this->screen = 'admin_page_' . $this->pageSlug;

        return $this;
    }
}

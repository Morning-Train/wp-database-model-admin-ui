<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage;

class AdminTableView
{
    public string $title;

    public ?string $count = null;

    public $countCallback = null;

    public function __construct(
        public string $urlKey,
        public ?string $urlValue = null
    ) {
        $this->title = ucfirst(str_replace('_', ' ', $this->urlKey));
    }

    public function withTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function withCount(string $count): self
    {
        $this->count = $count;
        $this->countCallback = null;

        return $this;
    }

    public function withCountCallback(callable|string $countCallback): self
    {
        $this->countCallback = $countCallback;
        $this->count = null;

        return $this;
    }
}

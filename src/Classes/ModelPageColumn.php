<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Classes;

class ModelPageColumn
{

    public string $title;
    public $renderCallback = null;
    public bool $searchable = false;
    public bool $sortable = false;

    public function __construct(
        public string $slug
    ) {
        $this->title = $this->slug;
    }

    public function withTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function withRender(callable|string $renderCallback): self
    {
        $this->renderCallback = $renderCallback;

        return $this;
    }

    public function makeSearchable(): self
    {
        $this->searchable = true;

        return $this;
    }

    public function makeSortable(): self
    {
        $this->sortable = true;

        return $this;
    }

    public function render(array $item): void
    {
        $page = $_GET['page'];

        if ($this->renderCallback !== null) {
            echo ($this->renderCallback)($item, $page);
            return;
        }

        echo $item[$this->slug];
    }

}
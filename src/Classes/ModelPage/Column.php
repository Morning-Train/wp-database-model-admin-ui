<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage;

class Column
{
    public string $title;

    public $renderCallback = null;

    public bool $searchable = false;

    public bool $sortable = false;

    public function __construct(
        public string $slug
    ) {
        $this->title = ucfirst(str_replace('_', ' ', $this->slug));
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

    public function render($instance, ModelPage $modelPage): ?string
    {
        if ($this->renderCallback !== null) {
            return ($this->renderCallback)($instance, $modelPage);
        }

        if ($modelPage->acfEditPage !== null && $modelPage->primaryColumn === $this->slug) {
            $href = admin_url('admin.php') . '?page=' . $modelPage->acfEditPage->pageSlug . '&model_id=' . $instance->id;

            return '<a href="' . $href . '">' . $instance->{$this->slug} . '</a>';
        }

        return $instance->{$this->slug};
    }
}

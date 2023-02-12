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

    public function render(array $item, ModelPage $modelPage): ?string
    {
        if ($this->renderCallback !== null) {
            return ($this->renderCallback)($item, $modelPage);
        }

        if ($modelPage->acfEditable && $modelPage->primaryColumn === $this->slug) {
            $href = admin_url('admin.php') . '?page=' . $modelPage->acfEditablePageSlug . '&model_id=' . $item['id'];

            return '<a href="' . $href . '">' . $item[$this->slug] . '</a>';
        }

        return $item[$this->slug];
    }
}

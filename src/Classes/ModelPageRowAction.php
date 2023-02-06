<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Classes;

class ModelPageRowAction
{

    public function __construct(
        public string $slug,
        public $renderCallback
    ) {
    }

    public function render(array $item): string
    {
        $page = $_GET['page'];

        return ($this->renderCallback)($item, $page);
    }

}
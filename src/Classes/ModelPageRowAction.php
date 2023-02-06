<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Classes;

class ModelPageRowAction
{

    public function __construct(
        public string $slug,
        public $renderCallback
    ) {
    }

    public function render(array $item, ModelPage $modelPage): string
    {
        return ($this->renderCallback)($item, $modelPage);
    }

}
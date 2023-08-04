<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage;

class AdminTableExtraTablenav
{
    public function __construct(
        public $renderCallback,
    ) {
    }

    public function render(ModelPage $modelPage): string
    {
        return ($this->renderCallback)($modelPage);
    }
}

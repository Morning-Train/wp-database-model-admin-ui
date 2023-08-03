<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage;

use Morningtrain\WP\DatabaseModelAdminUi\Enums\AdminTableExtraTablenavWhich;

class AdminTableExtraTablenav
{
    public function __construct(
        public string $which,
        public $renderCallback,
    ) {
    }

    public function render(ModelPage $modelPage): string
    {
        if (! in_array($this->which, [AdminTableExtraTablenavWhich::TOP, AdminTableExtraTablenavWhich::BOTTOM], true)) {
            return '';
        }

        return ($this->renderCallback)($this->which, $modelPage);
    }
}

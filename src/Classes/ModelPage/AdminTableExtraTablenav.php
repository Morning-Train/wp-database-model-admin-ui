<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage;

class AdminTableExtraTablenav
{
    public string $which = 'top';

    public function __construct(public $renderCallback)
    {
    }

    public function setWhichToBottom(): self
    {
        $this->which = 'bottom';

        return $this;
    }

    public function render(string $which, ModelPage $modelPage): string
    {
        return ($this->renderCallback)($which, $modelPage);
    }
}

<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage;

class AcfLoadField
{
    public function __construct(
        public string $slug,
        public $renderCallback
    ) {
    }
}

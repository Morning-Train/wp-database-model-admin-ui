<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage;

class AcfEditPage
{
    public array $loadFieldCallbacks = [];
    public $saveCallback = null;

    public function withLoadFieldCallback(string $slug, callable|string $callback): self
    {
        $this->loadFieldCallbacks[$slug] = $callback;

        return $this;
    }

    public function withSaveCallback(callable|string $callback): self
    {
        $this->saveCallback = $callback;

        return $this;
    }
}

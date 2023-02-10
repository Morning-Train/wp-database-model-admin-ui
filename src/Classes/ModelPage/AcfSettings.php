<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage;

class AcfSettings
{
    public array $extraLoadCallbacks = [];

    public $extraSaveCallback = null;

    public function withExtraLoadCallback(string $slug, callable|string $callback): self
    {
        $this->extraLoadCallbacks[$slug] = $callback;

        return $this;
    }

    public function withExtraSaveCallback(callable|string $callback): self
    {
        $this->extraSaveCallback = $callback;

        return $this;
    }
}

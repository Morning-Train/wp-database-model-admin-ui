<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage;

class AcfEditableMetaBox
{
    public string $title;

    public string $context = 'normal';

    public string $priority = 'default';

    public function __construct(
        public string $slug,
        public $renderCallback,
    ) {
        $this->title = ucfirst(str_replace('_', ' ', $this->slug));
    }

    public function withTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function withSideContext(): self
    {
        $this->context = 'side';

        return $this;
    }

    public function withHighPriority(): self
    {
        $this->priority = 'high';

        return $this;
    }

    public function withCorePriority(): self
    {
        $this->priority = 'core';

        return $this;
    }

    public function withLowPriority(): self
    {
        $this->priority = 'low';

        return $this;
    }
}

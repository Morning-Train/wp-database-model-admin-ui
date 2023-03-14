<?php

namespace Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage;

use Morningtrain\WP\DatabaseModelAdminUi\Enums\MetaBoxPage;

class MetaBox
{
    public string $title;

    public string $context = 'normal';

    public string $priority = 'default';

    public string $onPage = MetaBoxPage::ADMIN_TABLE;

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

    public function onSideContext(): self
    {
        $this->context = 'side';

        return $this;
    }

    public function onViewPage(): self
    {
        $this->onPage = MetaBoxPage::VIEW;

        return $this;
    }

    public function onAcfEditPage(): self
    {
        $this->onPage = MetaBoxPage::ACF_EDIT;

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace Codava\CopyrightBadge\View;

use Codava\CopyrightBadge\CopyrightBadge;

final class Badge extends CreditComponent
{
    protected function html(array $options): string
    {
        return CopyrightBadge::render($options);
    }
}

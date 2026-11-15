<?php

declare(strict_types=1);

namespace Codava\CopyrightBadge\View;

use Codava\CopyrightBadge\CopyrightBadge;

final class DeveloperCredit extends CreditComponent
{
    protected function html(array $options): string
    {
        return CopyrightBadge::renderDeveloper($options);
    }
}

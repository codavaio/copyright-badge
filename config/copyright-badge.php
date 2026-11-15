<?php

declare(strict_types=1);

use Codava\CopyrightBadge\CopyrightBadge;

/*
 * Site-wide options for <x-copyright-badge />, <x-developer-credit /> and <x-footer-credits />.
 * Attributes on a component win over this file. The values come from the environment:
 *
 * COPYRIGHT_OWNER_NAME, COPYRIGHT_OWNER_NAME_AR, COPYRIGHT_OWNER_URL,
 * COPYRIGHT_OWNER_LOGO, COPYRIGHT_OWNER_LOGO_DARK, COPYRIGHT_OWNER_LOGO_WIDTH, COPYRIGHT_OWNER_LOGO_HEIGHT,
 * COPYRIGHT_DEVELOPER_* (the same keys), COPYRIGHT_SHOW_DEVELOPER (true, false or auto),
 * COPYRIGHT_START_YEAR, COPYRIGHT_THEME (light, dark or auto).
 *
 * Publish this file to add what the environment cannot express, such as 'template'.
 */
return CopyrightBadge::optionsFromEnv(static fn (string $key): mixed => env('COPYRIGHT_'.$key));

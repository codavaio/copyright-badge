# copyright-badge

A small copyright line for the footer of a website: "All rights reserved", a tiny linked logo that opens in a new tab,
and the year, in Arabic and English.

```
All rights reserved [logo] © 2026
جميع الحقوق محفوظة [logo] © 2026
```

It ships twice from one repository, with the same options and the same output:

- a dependency-free JavaScript module for Vue, React, plain HTML or server rendering;
- a PHP renderer with a Laravel Blade component.

Out of the box it prints the Codava credit. Every part can be overridden, so you can use it for your own
company: name, link, logo, text, year range, colours of the logo and the CSS class. It can also print a
"Developed by" line next to the owner's copyright, driven by environment variables.

## JavaScript

```sh
npm install https://github.com/codavaio/copyright-badge/archive/refs/tags/v1.1.2.tar.gz
```

```js
import { renderCopyrightBadge } from '@codavaio/copyright-badge'

footer.innerHTML = renderCopyrightBadge({ locale: 'ar', theme: 'dark' })
```

To write the markup yourself (for example in a Vue template), ask for the parts instead:

```vue
<script setup>
import { copyrightBadgeParts } from '@codavaio/copyright-badge'
const badge = copyrightBadgeParts({ locale: 'en' })
</script>

<template>
  <span :class="badge.className" :dir="badge.dir">
    {{ badge.before }}
    <a :href="badge.link.href" :target="badge.link.target" :rel="badge.link.rel" :aria-label="badge.link.label">
      <img :src="badge.logo.src" :alt="badge.logo.alt" :width="badge.logo.width" :height="badge.logo.height" />
    </a>
    {{ badge.after }}
  </span>
</template>
```

Without a framework, register the custom element once and use it in HTML. It reads the language from the
nearest `lang` attribute when `locale` is not set.

```html
<script type="module">
  import { defineCopyrightBadge } from '@codavaio/copyright-badge'
  defineCopyrightBadge()
</script>

<copyright-badge locale="ar" theme="auto" start-year="2024"></copyright-badge>
```

Element attributes: `locale`, `theme`, `year`, `start-year`, `template`, `owner-name`, `owner-url`, `logo`,
`logo-dark`, `logo-width`, `logo-height`, `same-tab`, and `credits` to print both lines.

## Laravel / PHP

Until the package is on Packagist, add the repository to `composer.json`:

```json
"repositories": [{ "type": "vcs", "url": "https://github.com/codavaio/copyright-badge" }]
```

```sh
composer require codavaio/copyright-badge
```

```blade
<x-copyright-badge />
<x-copyright-badge theme="dark" :start-year="2024" />
```

The components use the application locale and read site-wide options from `.env` (see below). Publish the
config file only for what the environment cannot express, such as a custom template:

```sh
php artisan vendor:publish --tag=copyright-badge-config
```

Outside Laravel:

```php
use Codava\CopyrightBadge\CopyrightBadge;

echo CopyrightBadge::render(['locale' => 'ar', 'year' => 2026]);
```

## Owner copyright and developer credit

For a product built for someone else, show the owner's copyright on one side and the developer on the other:

```
All rights reserved Acme Trading © 2026                    Developed by: [Codava logo]
```

```js
import { optionsFromEnv, renderCredits } from '@codavaio/copyright-badge'

footer.innerHTML = renderCredits({ ...optionsFromEnv(import.meta.env), locale: 'en' })
```

```blade
<x-footer-credits />
```

`renderDeveloperCredit` / `<x-developer-credit />` print the developer line alone, and
`renderCopyrightBadge` / `<x-copyright-badge />` the copyright alone, so you can place them anywhere.

The owner is printed as a logo when a logo is set, otherwise as a name (linked when a URL is set). The
developer line is shown only when the owner is someone other than the developer; force it with
`showDeveloper: true` or hide it with `false`.

### Environment variables

Laravel reads these from `.env` with the `COPYRIGHT_` prefix. In a Vite project use the `VITE_COPYRIGHT_`
prefix and `optionsFromEnv(import.meta.env)`; pass another prefix as the second argument if you need one.

| Variable (after the prefix) | Meaning |
| --- | --- |
| `OWNER_NAME`, `OWNER_NAME_AR` | Owner name in English and Arabic. Setting a name makes the owner a company of its own. |
| `OWNER_URL` | Where the owner's name or logo links to. |
| `OWNER_LOGO`, `OWNER_LOGO_DARK` | Logo images for light and dark backgrounds. Without them the name is printed. |
| `OWNER_LOGO_WIDTH`, `OWNER_LOGO_HEIGHT` | Logo size in pixels. |
| `DEVELOPER_NAME`, `DEVELOPER_NAME_AR`, `DEVELOPER_URL`, `DEVELOPER_LOGO`, … | The same keys for the developer; the default is Codava. |
| `SHOW_DEVELOPER` | `true`, `false` or `auto` (the default). |
| `START_YEAR` | First year of the range. |
| `THEME` | `light`, `dark` or `auto`. |

```dotenv
COPYRIGHT_OWNER_NAME="Acme Trading"
COPYRIGHT_OWNER_NAME_AR="أكمي للتجارة"
COPYRIGHT_OWNER_URL=https://acme.example
COPYRIGHT_OWNER_LOGO=https://acme.example/logo.png
COPYRIGHT_START_YEAR=2025
```

## Options

| Option | Default | Meaning |
| --- | --- | --- |
| `locale` | `en` | `ar` and `en` are built in; any other locale falls back to English unless you pass its text. |
| `theme` | `light` | `light` shows the logo for light backgrounds, `dark` the one for dark backgrounds, `auto` follows the visitor's colour-scheme preference. |
| `year` | current year | |
| `startYear` | none | Prints a range such as `2024–2026`. |
| `template` | see above | Copyright text with `{year}`, `{logo}`, `{name}` and your own `values`. One string, or one per locale. Leave `{logo}` out to drop the name or logo. |
| `developerTemplate` | `Developed by: {logo}` | Text of the developer line. |
| `values` | none | Extra placeholders for the templates, for example `{ product: 'Hasoobi' }`. |
| `owner` | Codava | `name`, `url`, `logo` (`light`, `dark`, `width`, `height`). A name of your own drops the default logo and link. |
| `developer` | Codava | The same shape as `owner`. |
| `showDeveloper` | `auto` | Whether `renderCredits` includes the developer line. |
| `newTab` | `true` | Opens links in a new tab with `rel="noopener noreferrer"` and says so to screen readers. |
| `newTabLabel` | built in | Accessible name of the link, with `{name}`. |
| `className`, `developerClassName`, `creditsClassName` | `copyright-badge`, `developer-credit`, `footer-credits` | Classes of the wrapping elements. |
| `styles` | `true` | Set to `false` to drop the inline layout styles and style it yourself. |

In PHP the same keys go into the array given to `CopyrightBadge::render()`, `renderDeveloper()` and
`renderCredits()`, or as attributes on the Blade components.

## Development

```sh
npm test
composer install && composer test
```

`resources/defaults.json` holds the defaults the PHP side reads; `js/defaults.js` mirrors it and a test keeps
the two equal. `tests/cases.json` lists options with the exact HTML both sides must produce.

## License

MIT

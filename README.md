# copyright-badge

A small copyright line for the footer of a website: the year, a tiny linked logo that opens in a new tab,
and "All rights reserved", in Arabic and English.

```
© 2026 [logo]. All rights reserved.
© 2026 [logo]. جميع الحقوق محفوظة.
```

It ships twice from one repository, with the same options and the same output:

- a dependency-free JavaScript module for Vue, React, plain HTML or server rendering;
- a PHP renderer with a Laravel Blade component.

Out of the box it prints the Codava credit. Every part can be overridden, so you can use it for your own
company: name, link, logo, text, year range, colours of the logo and the CSS class.

## JavaScript

```sh
npm install https://github.com/codavaio/copyright-badge/archive/refs/tags/v1.0.0.tar.gz
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
`logo-dark`, `logo-width`, `logo-height`, `same-tab`.

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

The component uses the application locale. Site-wide overrides live in a config file:

```sh
php artisan vendor:publish --tag=copyright-badge-config
```

Outside Laravel:

```php
use Codava\CopyrightBadge\CopyrightBadge;

echo CopyrightBadge::render(['locale' => 'ar', 'year' => 2026]);
```

## Options

| Option | Default | Meaning |
| --- | --- | --- |
| `locale` | `en` | `ar` and `en` are built in; any other locale falls back to English unless you pass its text. |
| `theme` | `light` | `light` shows the logo for light backgrounds, `dark` the one for dark backgrounds, `auto` follows the visitor's colour-scheme preference. |
| `year` | current year | |
| `startYear` | none | Prints a range such as `2024–2026`. |
| `template` | see above | Text with `{year}`, `{logo}`, `{name}` and your own `values`. One string, or one per locale. Leave `{logo}` out for a text-only line. |
| `values` | none | Extra placeholders for the template, for example `{ product: 'Hasoobi' }`. |
| `owner.name` | Codava / كودافا | Alternative text of the logo. One string, or one per locale. |
| `owner.url` | `https://codava.io` | Where the logo links to. |
| `owner.logo` | Codava wordmark | `light`, `dark`, `width`, `height`. |
| `newTab` | `true` | Opens the link in a new tab with `rel="noopener noreferrer"` and says so to screen readers. |
| `newTabLabel` | built in | Accessible name of the link, with `{name}`. |
| `className` | `copyright-badge` | Class of the wrapping `<span>`. |
| `styles` | `true` | Set to `false` to drop the two inline alignment styles and style it yourself. |

### Using it for your own company

```js
renderCopyrightBadge({
  locale: 'en',
  startYear: 2021,
  template: { en: '© {year} {logo} · Made in Riyadh', ar: '© {year} {logo} · صُنع في الرياض' },
  owner: {
    name: { en: 'Your Company', ar: 'شركتك' },
    url: 'https://example.com',
    logo: { light: 'https://example.com/logo.png', dark: 'https://example.com/logo-white.png', width: 40, height: 18 },
  },
})
```

## Development

```sh
npm test
composer install && composer test
```

`resources/defaults.json` holds the defaults the PHP side reads; `js/defaults.js` mirrors it and a test keeps
the two equal. `tests/cases.json` lists options with the exact HTML both sides must produce.

## License

MIT

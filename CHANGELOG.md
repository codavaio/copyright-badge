# Changelog

## 1.1.2

- The default logos load from `cdn.codava.io`, the new address of the Codava assets.

## 1.1.1

- A year range keeps its order (2025–2026) in Arabic and other right-to-left text.

## 1.1.0

- "Developed by" line (`renderDeveloperCredit`, `<x-developer-credit />`) and both lines side by side
  (`renderCredits`, `<x-footer-credits />`); the developer line shows by itself when the owner is someone else.
- An owner or developer can be a plain name, a linked name or a logo.
- Options can come from the environment: `COPYRIGHT_*` in Laravel, `VITE_COPYRIGHT_*` through `optionsFromEnv`.
- A logo without a known width keeps its proportions at the default height.

## 1.0.2

- The default text now reads "All rights reserved", then the logo, then © and the year.

## 1.0.1

- The default logo is 44×24 so the wordmark stays readable, and the default text uses a middle dot after the logo.

## 1.0.0

- Footer copyright line with a linked logo, in Arabic and English.
- JavaScript module (`renderCopyrightBadge`, `copyrightBadgeParts`, `<copyright-badge>` element).
- PHP renderer and Laravel Blade component `<x-copyright-badge />`.

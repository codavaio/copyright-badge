// Mirrors resources/defaults.json, which the PHP side reads; a test keeps the two equal.
export const defaults = {
  "locale": "en",
  "theme": "light",
  "newTab": true,
  "className": "copyright-badge",
  "styles": true,
  "rtlLocales": [
    "ar",
    "fa",
    "he",
    "ur"
  ],
  "owner": {
    "name": {
      "en": "Codava",
      "ar": "كودافا"
    },
    "url": "https://codava.io",
    "logo": {
      "light": "https://assets.codava.io/brands/v11/main/06-web/dashboard/logo-navy-160w.png",
      "dark": "https://assets.codava.io/brands/v11/main/06-web/dashboard/logo-white-160w.png",
      "width": 44,
      "height": 24
    }
  },
  "templates": {
    "en": "© {year} {logo} · All rights reserved",
    "ar": "© {year} {logo} · جميع الحقوق محفوظة"
  },
  "newTabLabels": {
    "en": "{name} (opens in a new tab)",
    "ar": "{name} (يفتح في تبويب جديد)"
  }
}

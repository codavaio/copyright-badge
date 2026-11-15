import { defaults } from './defaults.js'

export { defaults }

const ESCAPES = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }
const escape = value => String(value).replace(/[&<>"']/g, character => ESCAPES[character])
const localized = (value, locale) => (value && typeof value === 'object' ? value[locale] ?? value.en ?? Object.values(value)[0] ?? '' : value ?? '')
const fill = (template, values) => template.replace(/\{(\w+)\}/g, (match, key) => (key in values ? String(values[key]) : match))

/**
 * Everything a footer needs to print the copyright line with its own markup.
 * @param {import('./index.js').CopyrightBadgeOptions} [options]
 * @returns {import('./index.js').CopyrightBadgeParts}
 */
export function copyrightBadgeParts(options = {}) {
  const locale = options.locale ?? defaults.locale
  const theme = options.theme ?? defaults.theme
  const owner = { ...defaults.owner, ...options.owner, logo: { ...defaults.owner.logo, ...options.owner?.logo } }
  const name = localized(owner.name, locale)
  const year = options.year ?? new Date().getFullYear()
  const years = options.startYear && Number(options.startYear) < Number(year) ? `${options.startYear}–${year}` : String(year)
  const template = localized(options.template ?? defaults.templates, locale)
  const [before, ...rest] = fill(template, { ...options.values, year: years, name }).split('{logo}')
  const newTab = options.newTab ?? defaults.newTab
  const light = owner.logo.light, dark = owner.logo.dark ?? light

  return {
    locale,
    dir: defaults.rtlLocales.includes(locale) ? 'rtl' : 'ltr',
    className: options.className ?? defaults.className,
    styles: options.styles ?? defaults.styles,
    year: years,
    showLogo: rest.length > 0,
    before,
    after: rest.join(''),
    link: {
      href: owner.url,
      target: newTab ? '_blank' : null,
      rel: newTab ? 'noopener noreferrer' : null,
      label: newTab ? fill(localized(options.newTabLabel ?? defaults.newTabLabels, locale), { name }) : name,
    },
    logo: {
      src: theme === 'dark' ? dark : light,
      darkSrc: theme === 'auto' && dark !== light ? dark : null,
      alt: name,
      width: owner.logo.width,
      height: owner.logo.height,
    },
  }
}

/**
 * The copyright line as an HTML string, safe to inject as-is.
 * @param {import('./index.js').CopyrightBadgeOptions} [options]
 */
export function renderCopyrightBadge(options = {}) {
  const parts = copyrightBadgeParts(options)
  const { link, logo } = parts
  const size = (logo.width ? ` width="${escape(logo.width)}"` : '') + (logo.height ? ` height="${escape(logo.height)}"` : '')
  const imageStyle = parts.styles ? ' style="display:inline-block;vertical-align:middle;height:auto;max-width:none"' : ''
  const image = `<img src="${escape(logo.src)}" alt="${escape(logo.alt)}"${size} loading="lazy" decoding="async"${imageStyle}>`
  const picture = logo.darkSrc ? `<picture><source srcset="${escape(logo.darkSrc)}" media="(prefers-color-scheme: dark)">${image}</picture>` : image
  const target = link.target ? ` target="${link.target}" rel="${link.rel}"` : ''
  const linkStyle = parts.styles ? ' style="display:inline-block;vertical-align:middle;line-height:1"' : ''
  const anchor = `<a href="${escape(link.href)}"${target} aria-label="${escape(link.label)}"${linkStyle}>${picture}</a>`

  return `<span class="${escape(parts.className)}" dir="${parts.dir}">${escape(parts.before)}${parts.showLogo ? anchor : ''}${escape(parts.after)}</span>`
}

const ATTRIBUTES = ['locale', 'theme', 'year', 'start-year', 'template', 'owner-name', 'owner-url', 'logo', 'logo-dark', 'logo-width', 'logo-height', 'same-tab']

/**
 * Registers `<copyright-badge>` for pages without a framework.
 * @param {string} [tag]
 */
export function defineCopyrightBadge(tag = 'copyright-badge') {
  if (typeof customElements === 'undefined' || customElements.get(tag)) return

  customElements.define(tag, class extends HTMLElement {
    static observedAttributes = ATTRIBUTES

    connectedCallback() { this.update() }

    attributeChangedCallback() { if (this.isConnected) this.update() }

    update() {
      const read = name => this.getAttribute(name) ?? undefined
      const number = name => (this.hasAttribute(name) ? Number(this.getAttribute(name)) : undefined)
      const logo = Object.fromEntries(Object.entries({ light: read('logo'), dark: read('logo-dark'), width: number('logo-width'), height: number('logo-height') }).filter(([, value]) => value !== undefined))
      const owner = Object.fromEntries(Object.entries({ name: read('owner-name'), url: read('owner-url') }).filter(([, value]) => value !== undefined))
      this.innerHTML = renderCopyrightBadge({
        locale: read('locale') ?? (this.closest('[lang]')?.getAttribute('lang') || undefined)?.split('-')[0],
        theme: read('theme'),
        year: number('year'),
        startYear: number('start-year'),
        template: read('template'),
        newTab: this.hasAttribute('same-tab') ? false : undefined,
        owner: { ...owner, ...(Object.keys(logo).length ? { logo } : {}) },
      })
    }
  })
}

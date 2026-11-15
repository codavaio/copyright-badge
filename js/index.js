import { defaults } from './defaults.js'

export { defaults }

const ESCAPES = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }
const escape = value => String(value).replace(/[&<>"']/g, character => ESCAPES[character])
const present = value => value !== undefined && value !== null && value !== ''
const localized = (value, locale) => (value && typeof value === 'object' ? value[locale] ?? value.en ?? Object.values(value)[0] ?? '' : value ?? '')
const fill = (template, values) => template.replace(/\{(\w+)\}/g, (match, key) => (key in values ? String(values[key]) : match))

// A party with its own name is someone else: it never inherits the default logo or link.
function resolveParty(base, given = {}) {
  const custom = present(given.name)
  let logo = null
  if (given.logo === undefined) logo = custom ? null : base.logo
  else if (given.logo && (present(given.logo.light) || !custom)) logo = custom ? given.logo : { ...base.logo, ...given.logo }

  return { name: custom ? given.name : base.name, url: present(given.url) ? given.url : custom ? null : base.url, logo: logo && present(logo.light) ? logo : null }
}

function buildParts(party, template, options, className) {
  const locale = options.locale ?? defaults.locale
  const theme = options.theme ?? defaults.theme
  const name = localized(party.name, locale)
  const year = options.year ?? new Date().getFullYear()
  const rtl = defaults.rtlLocales.includes(locale)
  // Isolated as left-to-right so a range keeps its order inside right-to-left text.
  const years = options.startYear && Number(options.startYear) < Number(year) ? (rtl ? `\u2066${options.startYear}–${year}\u2069` : `${options.startYear}–${year}`) : String(year)
  const [before, ...rest] = fill(localized(template, locale), { ...options.values, year: years, name }).split('{logo}')
  const newTab = options.newTab ?? defaults.newTab
  const light = party.logo?.light, dark = party.logo?.dark ?? light

  return {
    locale,
    dir: rtl ? 'rtl' : 'ltr',
    className,
    styles: options.styles ?? defaults.styles,
    year: years,
    showLogo: rest.length > 0,
    before,
    after: rest.join(''),
    name,
    link: party.url
      ? {
          href: party.url,
          target: newTab ? '_blank' : null,
          rel: newTab ? 'noopener noreferrer' : null,
          label: newTab ? fill(localized(options.newTabLabel ?? defaults.newTabLabels, locale), { name }) : name,
        }
      : null,
    logo: party.logo
      ? {
          src: theme === 'dark' ? dark : light,
          darkSrc: theme === 'auto' && dark !== light ? dark : null,
          alt: name,
          width: party.logo.width ?? null,
          height: party.logo.height ?? defaults.owner.logo.height,
        }
      : null,
  }
}

function renderParts(parts) {
  let mark = ''
  if (parts.showLogo) {
    const { link, logo } = parts
    let inner = escape(parts.name)
    if (logo) {
      const size = (logo.width ? ` width="${escape(logo.width)}"` : '') + ` height="${escape(logo.height)}"`
      const fit = logo.width ? 'height:auto' : 'width:auto'
      const imageStyle = parts.styles ? ` style="display:inline-block;vertical-align:middle;${fit};max-width:none"` : ''
      const image = `<img src="${escape(logo.src)}" alt="${escape(logo.alt)}"${size} loading="lazy" decoding="async"${imageStyle}>`
      inner = logo.darkSrc ? `<picture><source srcset="${escape(logo.darkSrc)}" media="(prefers-color-scheme: dark)">${image}</picture>` : image
    }
    if (link) {
      const target = link.target ? ` target="${link.target}" rel="${link.rel}"` : ''
      const linkStyle = parts.styles && logo ? ' style="display:inline-block;vertical-align:middle;line-height:1"' : ''
      mark = `<a href="${escape(link.href)}"${target} aria-label="${escape(link.label)}"${linkStyle}>${inner}</a>`
    } else mark = inner
  }

  return `<span class="${escape(parts.className)}" dir="${parts.dir}">${escape(parts.before)}${mark}${escape(parts.after)}</span>`
}

/**
 * The owner's copyright line, ready for your own markup.
 * @param {import('./index.js').CopyrightBadgeOptions} [options]
 * @returns {import('./index.js').CreditParts}
 */
export function copyrightBadgeParts(options = {}) {
  return buildParts(resolveParty(defaults.owner, options.owner), options.template ?? defaults.templates, options, options.className ?? defaults.className)
}

/**
 * The "Developed by" line, ready for your own markup.
 * @param {import('./index.js').CopyrightBadgeOptions} [options]
 * @returns {import('./index.js').CreditParts}
 */
export function developerCreditParts(options = {}) {
  return buildParts(resolveParty(defaults.developer, options.developer), options.developerTemplate ?? defaults.developerTemplates, options, options.developerClassName ?? defaults.developerClassName)
}

/**
 * Whether the developer line belongs next to the copyright: by default only when the owner is someone else.
 * @param {import('./index.js').CopyrightBadgeOptions} [options]
 */
export function showsDeveloperCredit(options = {}) {
  const wanted = options.showDeveloper ?? defaults.showDeveloper
  if (wanted !== 'auto') return Boolean(wanted)
  const owner = resolveParty(defaults.owner, options.owner), developer = resolveParty(defaults.developer, options.developer)

  return owner.url !== developer.url || localized(owner.name, 'en') !== localized(developer.name, 'en')
}

/**
 * Both lines for your own markup; `developer` is null when it is not shown.
 * @param {import('./index.js').CopyrightBadgeOptions} [options]
 */
export function creditsParts(options = {}) {
  return { className: options.creditsClassName ?? defaults.creditsClassName, copyright: copyrightBadgeParts(options), developer: showsDeveloperCredit(options) ? developerCreditParts(options) : null }
}

/** @param {import('./index.js').CopyrightBadgeOptions} [options] */
export function renderCopyrightBadge(options = {}) {
  return renderParts(copyrightBadgeParts(options))
}

/** @param {import('./index.js').CopyrightBadgeOptions} [options] */
export function renderDeveloperCredit(options = {}) {
  return renderParts(developerCreditParts(options))
}

/**
 * The owner's copyright on one side and the developer line on the other.
 * @param {import('./index.js').CopyrightBadgeOptions} [options]
 */
export function renderCredits(options = {}) {
  const parts = creditsParts(options)
  const style = parts.copyright.styles ? ' style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.5rem 1.5rem"' : ''

  return `<span class="${escape(parts.className)}"${style}>${renderParts(parts.copyright)}${parts.developer ? renderParts(parts.developer) : ''}</span>`
}

/**
 * Reads options from environment variables such as `VITE_COPYRIGHT_OWNER_NAME`.
 * @param {Record<string, unknown>} [env]
 * @param {string} [prefix]
 * @returns {import('./index.js').CopyrightBadgeOptions}
 */
export function optionsFromEnv(env = {}, prefix = 'VITE_COPYRIGHT_') {
  const read = key => (present(env?.[prefix + key]) ? String(env[prefix + key]) : undefined)
  const number = key => (read(key) === undefined || Number.isNaN(Number(read(key))) ? undefined : Number(read(key)))
  const compact = object => Object.fromEntries(Object.entries(object).filter(([, value]) => value !== undefined))
  const party = side => {
    const name = read(side + '_NAME'), arabic = read(side + '_NAME_AR'), light = read(side + '_LOGO')
    const logo = light ? compact({ light, dark: read(side + '_LOGO_DARK'), width: number(side + '_LOGO_WIDTH'), height: number(side + '_LOGO_HEIGHT') }) : undefined
    const result = compact({ name: arabic ? { en: name ?? arabic, ar: arabic } : name, url: read(side + '_URL'), logo })

    return Object.keys(result).length ? result : undefined
  }
  const show = read('SHOW_DEVELOPER')?.toLowerCase()

  return compact({
    owner: party('OWNER'),
    developer: party('DEVELOPER'),
    showDeveloper: show === undefined || show === 'auto' ? undefined : ['1', 'true', 'yes', 'on'].includes(show),
    startYear: number('START_YEAR'),
    theme: read('THEME'),
  })
}

const ATTRIBUTES = ['locale', 'theme', 'year', 'start-year', 'template', 'owner-name', 'owner-url', 'logo', 'logo-dark', 'logo-width', 'logo-height', 'same-tab', 'credits']

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
      const compact = object => Object.fromEntries(Object.entries(object).filter(([, value]) => value !== undefined))
      const logo = compact({ light: read('logo'), dark: read('logo-dark'), width: number('logo-width'), height: number('logo-height') })
      const owner = compact({ name: read('owner-name'), url: read('owner-url') })
      const options = {
        locale: read('locale') ?? (this.closest('[lang]')?.getAttribute('lang') || undefined)?.split('-')[0],
        theme: read('theme'),
        year: number('year'),
        startYear: number('start-year'),
        template: read('template'),
        newTab: this.hasAttribute('same-tab') ? false : undefined,
        owner: { ...owner, ...(Object.keys(logo).length ? { logo } : {}) },
      }
      this.innerHTML = this.hasAttribute('credits') ? renderCredits(options) : renderCopyrightBadge(options)
    }
  })
}

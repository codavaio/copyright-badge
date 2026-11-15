export type Localized = string | Record<string, string>

export interface CopyrightBadgeOwner {
  /** Shown as the logo's alternative text; one string or one per locale. */
  name?: Localized
  url?: string
  logo?: { light?: string; dark?: string; width?: number; height?: number }
}

export interface CopyrightBadgeOptions {
  /** Defaults to `en`. Arabic and English texts are built in. */
  locale?: string
  /** Which logo to show: `auto` follows the visitor's colour-scheme preference. */
  theme?: 'light' | 'dark' | 'auto'
  year?: number
  /** Prints a range such as 2024–2026 when earlier than `year`. */
  startYear?: number
  /** Text with `{year}`, `{logo}`, `{name}` and any key of `values`; one string or one per locale. */
  template?: Localized
  values?: Record<string, string | number>
  owner?: CopyrightBadgeOwner
  newTab?: boolean
  newTabLabel?: Localized
  className?: string
  /** Set to false to drop the inline alignment styles. */
  styles?: boolean
}

export interface CopyrightBadgeParts {
  locale: string
  dir: 'rtl' | 'ltr'
  className: string
  styles: boolean
  year: string
  /** False when the template has no `{logo}` placeholder. */
  showLogo: boolean
  before: string
  after: string
  link: { href: string; target: '_blank' | null; rel: string | null; label: string }
  logo: { src: string; darkSrc: string | null; alt: string; width?: number; height?: number }
}

export declare const defaults: {
  locale: string
  theme: 'light' | 'dark' | 'auto'
  newTab: boolean
  className: string
  styles: boolean
  rtlLocales: string[]
  owner: Required<CopyrightBadgeOwner>
  templates: Record<string, string>
  newTabLabels: Record<string, string>
}

export declare function copyrightBadgeParts(options?: CopyrightBadgeOptions): CopyrightBadgeParts
export declare function renderCopyrightBadge(options?: CopyrightBadgeOptions): string
export declare function defineCopyrightBadge(tag?: string): void

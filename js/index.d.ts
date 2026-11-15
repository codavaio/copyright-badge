export type Localized = string | Record<string, string>

export interface CreditParty {
  /** One string or one per locale. Giving a name makes this party someone else: the default logo and link no longer apply. */
  name?: Localized
  url?: string
  /** Leave out, or pass null, to print the name as text instead of an image. */
  logo?: { light?: string; dark?: string | null; width?: number; height?: number } | null
}

export interface CopyrightBadgeOptions {
  /** Defaults to `en`. Arabic and English texts are built in. */
  locale?: string
  /** Which logo to show: `auto` follows the visitor's colour-scheme preference. */
  theme?: 'light' | 'dark' | 'auto'
  year?: number
  /** Prints a range such as 2024–2026 when earlier than `year`. */
  startYear?: number
  /** Copyright text with `{year}`, `{logo}`, `{name}` and any key of `values`; one string or one per locale. */
  template?: Localized
  values?: Record<string, string | number>
  /** Who holds the copyright. */
  owner?: CreditParty
  /** Who built the product; shown by `renderDeveloperCredit` and `renderCredits`. */
  developer?: CreditParty
  developerTemplate?: Localized
  /** `auto` shows the developer line only when the owner is someone else. */
  showDeveloper?: boolean | 'auto'
  newTab?: boolean
  newTabLabel?: Localized
  className?: string
  developerClassName?: string
  creditsClassName?: string
  /** Set to false to drop the inline layout styles. */
  styles?: boolean
}

export interface CreditParts {
  locale: string
  dir: 'rtl' | 'ltr'
  className: string
  styles: boolean
  year: string
  /** False when the template has no `{logo}` placeholder. */
  showLogo: boolean
  before: string
  after: string
  name: string
  /** Null when the party has no link. */
  link: { href: string; target: '_blank' | null; rel: string | null; label: string } | null
  /** Null when the party is printed as text. */
  logo: { src: string; darkSrc: string | null; alt: string; width: number | null; height: number } | null
}

/** @deprecated Use CreditParts. */
export type CopyrightBadgeParts = CreditParts
/** @deprecated Use CreditParty. */
export type CopyrightBadgeOwner = CreditParty

export declare const defaults: {
  locale: string
  theme: 'light' | 'dark' | 'auto'
  newTab: boolean
  className: string
  developerClassName: string
  creditsClassName: string
  showDeveloper: boolean | 'auto'
  styles: boolean
  rtlLocales: string[]
  owner: Required<CreditParty>
  developer: Required<CreditParty>
  templates: Record<string, string>
  developerTemplates: Record<string, string>
  newTabLabels: Record<string, string>
}

export declare function copyrightBadgeParts(options?: CopyrightBadgeOptions): CreditParts
export declare function developerCreditParts(options?: CopyrightBadgeOptions): CreditParts
export declare function creditsParts(options?: CopyrightBadgeOptions): { className: string; copyright: CreditParts; developer: CreditParts | null }
export declare function showsDeveloperCredit(options?: CopyrightBadgeOptions): boolean
export declare function renderCopyrightBadge(options?: CopyrightBadgeOptions): string
export declare function renderDeveloperCredit(options?: CopyrightBadgeOptions): string
export declare function renderCredits(options?: CopyrightBadgeOptions): string
export declare function optionsFromEnv(env?: Record<string, unknown>, prefix?: string): CopyrightBadgeOptions
export declare function defineCopyrightBadge(tag?: string): void

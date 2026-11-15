import assert from 'node:assert/strict'
import { readFileSync } from 'node:fs'
import { test } from 'node:test'

import { copyrightBadgeParts, creditsParts, defaults, optionsFromEnv, renderCopyrightBadge, renderCredits, renderDeveloperCredit } from '../../js/index.js'

const shared = JSON.parse(readFileSync(new URL('../../resources/defaults.json', import.meta.url), 'utf8'))
const cases = JSON.parse(readFileSync(new URL('../cases.json', import.meta.url), 'utf8'))

test('the JavaScript defaults mirror the shared file', () => {
  assert.deepEqual(defaults, shared)
})

const renderers = { copyright: renderCopyrightBadge, developer: renderDeveloperCredit, credits: renderCredits }

for (const item of cases.render) {
  test(`renders ${item.kind}: ${item.name}`, () => {
    assert.equal(renderers[item.kind](item.options), item.html)
  })
}

for (const item of cases.env) {
  test(`reads the environment: ${item.name}`, () => {
    assert.deepEqual(optionsFromEnv(item.values, ''), item.options)
  })
}

test('environment keys carry the Vite prefix by default', () => {
  assert.deepEqual(optionsFromEnv({ VITE_COPYRIGHT_OWNER_NAME: 'Acme', OWNER_URL: 'https://ignored.example' }), { owner: { name: 'Acme' } })
})

test('the developer line appears only when the owner is someone else', () => {
  assert.equal(creditsParts({}).developer, null)
  assert.equal(creditsParts({ owner: { name: 'Acme' } }).developer?.before, 'Developed by: ')
})

test('the year defaults to the current one', () => {
  assert.equal(copyrightBadgeParts().year, String(new Date().getFullYear()))
})

test('parts expose the text around the logo and the link', () => {
  const parts = copyrightBadgeParts({ locale: 'ar', year: 2026 })
  assert.equal(parts.before, 'جميع الحقوق محفوظة ')
  assert.equal(parts.after, ' © 2026')
  assert.equal(parts.dir, 'rtl')
  assert.equal(parts.name, 'كودافا')
  assert.deepEqual(parts.link, { href: 'https://codava.io', target: '_blank', rel: 'noopener noreferrer', label: 'كودافا (يفتح في تبويب جديد)' })
  assert.equal(parts.logo.alt, 'كودافا')
})

test('an unknown locale falls back to English', () => {
  assert.equal(copyrightBadgeParts({ locale: 'fr', year: 2026 }).before, 'All rights reserved ')
})

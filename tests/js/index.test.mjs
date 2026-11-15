import assert from 'node:assert/strict'
import { readFileSync } from 'node:fs'
import { test } from 'node:test'

import { copyrightBadgeParts, defaults, renderCopyrightBadge } from '../../js/index.js'

const shared = JSON.parse(readFileSync(new URL('../../resources/defaults.json', import.meta.url), 'utf8'))
const cases = JSON.parse(readFileSync(new URL('../cases.json', import.meta.url), 'utf8'))

test('the JavaScript defaults mirror the shared file', () => {
  assert.deepEqual(defaults, shared)
})

for (const item of cases) {
  test(`renders: ${item.name}`, () => {
    assert.equal(renderCopyrightBadge(item.options), item.html)
  })
}

test('the year defaults to the current one', () => {
  assert.equal(copyrightBadgeParts().year, String(new Date().getFullYear()))
})

test('parts expose the text around the logo and the link', () => {
  const parts = copyrightBadgeParts({ locale: 'ar', year: 2026 })
  assert.equal(parts.before, '© 2026 ')
  assert.equal(parts.after, ' · جميع الحقوق محفوظة')
  assert.equal(parts.dir, 'rtl')
  assert.deepEqual(parts.link, { href: 'https://codava.io', target: '_blank', rel: 'noopener noreferrer', label: 'كودافا (يفتح في تبويب جديد)' })
  assert.equal(parts.logo.alt, 'كودافا')
})

test('an unknown locale falls back to English', () => {
  assert.equal(copyrightBadgeParts({ locale: 'fr', year: 2026 }).after, ' · All rights reserved')
})

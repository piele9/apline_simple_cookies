# APLINE Simple Cookies for PrestaShop 9

A lightweight, GDPR / UODO / ePrivacy compliant cookie consent banner for
PrestaShop 9. Bilingual **Polish + English** (Polish by default), category-based
consent, third-party tracker blocking before consent, and a consent audit log
for accountability.

Built for typical Polish e-commerce stores that run Google Analytics 4,
Google Tag Manager, Facebook (Meta) Pixel or Hotjar and need a banner that
actually complies with UODO/CNIL guidance — not just a cosmetic "Accept" bar.

---

## Features

- **Compliant banner** — Reject / Preferences / Accept at **equal prominence**
  (CNIL requirement); no dark patterns, no pre-checked non-essential boxes.
- **Bilingual PL + EN** — all banner copy is editable in the back office in a
  side-by-side PL/EN editor. Polish is the default; English is shown to
  English-language visitors.
- **Category-based consent** — Necessary / Functional / Analytics / Marketing
  out of the box, fully editable. Visitors can accept all, reject all, or
  choose per category in the Preferences modal.
- **Tracker blocking before consent** — Google Analytics 4, Google Tag
  Manager, Facebook Pixel and Hotjar are loaded **only after** the visitor
  consents to the mapped category. Enter just the ID — the module generates
  the script. Each field has a "how to add this" guide.
- **Custom snippets** — paste any other tracker (LinkedIn, Pinterest, your
  own) as raw HTML/JS, gated per consent category.
- **Consent audit log** — every decision is logged (hashed IP, policy version,
  language, source) for proof of consent, with retention cleanup and CSV
  export.
- **GPC honoring** — auto-rejects non-essential cookies when the browser sends
  the Global Privacy Control signal.
- **Re-prompt control** — bump the policy version (or wait for expiry) to ask
  visitors again.
- **Cookie policy template** — a ready PL/EN policy template you can copy into
  a CMS page.

## Requirements

- PrestaShop **9.0+**
- PHP 8.1+

## Installation

1. Download `apline_simple_cookies.zip` from the
   [Releases](https://github.com/piele9/apline_simple_cookies/releases) page.
2. In the back office go to **Modules → Module Manager → Upload a module** and
   upload the zip.
3. The module installs four default cookie categories, a few example cookies
   and sensible privacy-first defaults.

## Usage

1. Open the module configuration. Work through the **Setup checklist**:
   - Review the cookie **categories** and the individual **cookies** in each.
   - Add your tracking IDs (GA4, GTM, Facebook Pixel, Hotjar) — click
     **"How to add this"** next to any field if you are not sure where to find
     an ID.
   - Edit the banner **copy** (PL + EN) and choose appearance (bottom bar or
     center modal, light or dark).
   - Click **"Show example template"** to copy a ready cookie-policy text into
     a new CMS page, then paste that page's URL into **Cookie policy URL**.
2. Test the banner in an incognito window: it appears in Polish by default,
   blocks trackers until you choose, and does not reappear after you decide
   (until the re-prompt period elapses or you bump the policy version).
3. Visitors can reopen the banner anytime via the **"Cookie settings"** link in
   the footer.

## Uninstallation

Uninstall from the Module Manager. This drops the three database tables,
removes all `ASCO_*` configuration keys and the two hidden admin tabs. The
uninstall is idempotent and safe to run twice.

## Compliance notes

Out of the box the module supports GDPR (Art. 4(11), 7(3), 5(2)), the
ePrivacy Directive (Art. 5(3) prior consent), EDPB dark-pattern guidance, and
UODO/CNIL recommendations (equal button prominence, GPC honoring, re-prompt).
You still need to: fill in your own cookie-policy text, list every cookie your
specific shop actually uses, and have a lawyer review your setup. The module is
a tool, not legal advice.

## Troubleshooting

- **Banner doesn't appear** — check it in incognito (you may have already
  consented). Clear the `asco_consent` cookie to see it again.
- **A tracker still loads after Reject** — make sure the tracking code is
  entered in this module's Custom Scripts section and not hard-coded elsewhere
  in your theme or another module.
- **Polish characters look wrong in the CSV export** — the export includes a
  UTF-8 BOM; open it with an Excel version that respects it, or import as UTF-8.

## License

Custom Attribution License v1.0 — see [LICENSE.md](LICENSE.md). You may use,
modify and distribute the module, including in commercial projects; you may not
remove or hide the APLINE attribution on the configuration page.

Module created by [APLINE](https://apline.pl).

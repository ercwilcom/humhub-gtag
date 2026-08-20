# Google Analytics for HumHub

Google Analytics 4 (`gtag.js`) on every page, with an optional cookie-consent
banner.

## What it handles that a pasted snippet does not

**Pjax.** HumHub navigates without full page loads, so a plain snippet records
the first page someone sees and nothing after it. This module fires a pageview
on `pjax:end` too.

**Your own team.** Administrators browsing their own community are not
visitors. One checkbox keeps them out of the numbers.

**Consent.** Under GDPR or Québec's Law 25, sending data to Google generally
needs consent first. With the banner on, `gtag.js` is not loaded at all until
someone accepts — not loaded-then-ignored. The banner text, both button labels
and an optional privacy-policy link are all editable.

IP anonymisation is on. HumHub's own statistics are unaffected.

## Setup

*Administration → Modules → Google Analytics*. Paste the measurement ID
(`G-XXXXXXXXXX`) and enable tracking.

## Licence

AGPL-3.0-or-later. See `LICENSE`.

# PRWS - Production Ready Web Standard

A Laravel-based audit engine that scans websites for common production-readiness gaps - the basic legal, SEO, security, reliability, and accessibility items that get skipped, especially in fast/AI-assisted ("vibe coding") builds.

Scores sites out of 1000 (normalized to 100 in the MVP), assigns findings by severity, and issues a certification tier (Bronze / Silver / Gold / Platinum) based on score and critical-failure count.

Full rule specification: see `Production_Ready_Web_Standard_Specification.pdf` (v1.0) in the repo.

---

## Status: MVP working

The current build validates the end-to-end loop - submit a URL, run checks, score, certify, display a report - using 13 of the spec's rules. Tested against nsikacart.com (91/100, Gold) and en.wikipedia.org (76/100, Silver), with known behavior on JS-heavy/bot-defended sites like Twitter/X documented below.

### What's built

**Backend**
- `RuleEngine` service (`app/Services/RuleEngine.php`) - fetches homepage + known paths via Guzzle, parses DOM via `symfony/dom-crawler`, runs one check method per rule
- `Audit` and `Finding` Eloquent models + migrations
- `AuditController` - orchestrates scan → score → certify → persist
- Rule definitions in `config/prws_rules.php` (flat config, not yet DB-backed)

**Frontend**
- `audits/create.blade.php` - URL submission form, links to scan history, animated loading state on submit (readonly input + spinner button - full-page POST, no true progress tracking yet)
- `audits/show.blade.php` - scored report with SVG score gauge (color-coded by tier), icon-based pass/fail indicators, category icons, grouped by category with points per rule
- `audits/index.blade.php` - paginated scan history, links back to each report

**Error handling**
- Unreachable/invalid URLs (DNS failure, connection refused, timeout, non-2xx response) are caught in `RuleEngine` and surfaced as a friendly form error instead of producing a misleading 0/100 report

**Scoring**
- Category score = earned / available × 100
- Certification: Bronze ≥60, Silver ≥75, Gold ≥90 (all require no Critical failures), Platinum ≥95 with no Critical *and* no High failures - all four tiers now implemented

### Rules implemented (13 of 34 in spec)

| Rule ID | Name | Category | Severity | Status |
|---|---|---|---|---|
| LEG-001 | Privacy Policy Present | Legal | Critical | ✅ |
| LEG-002 | Terms of Service Present | Legal | High | ✅ |
| LEG-004 | Copyright Information | Legal | Low | ✅ (regex widened for CC/license wording) |
| CON-001 | Contact Page Exists | Contact | Medium | ✅ |
| CON-002 | Support Email Exists | Contact | Medium | ✅ (homepage + /contact fallback, mailto + plain-text regex) |
| REL-001 | Custom 404 Page | Reliability | Medium | ✅ (heuristic: 404 status + body >200 chars) |
| REL-004 | Favicon Exists | Reliability | Low | ✅ |
| SEO-001 | robots.txt | SEO | Medium | ✅ |
| SEO-002 | sitemap.xml | SEO | Medium | ✅ |
| SEO-003 | Title Tag | SEO | Low | ✅ |
| SEO-004 | Meta Description | SEO | Low | ✅ |
| ACC-003 | Semantic HTML | Accessibility | Low | ✅ (checks header/main/footer/nav all present) |
| ACC-004 | Language Declaration | Accessibility | Low | ✅ |

### Rules not yet implemented (21 remaining, per spec)

| Category | Rules | Notes |
|---|---|---|
| Legal | LEG-003 (Cookie Policy) | Simple path/link check, same pattern as LEG-001/002 |
| Contact | CON-003 (Organization Info / About page) | Simple path check |
| Security | SEC-001 through SEC-007 | HTTPS, HSTS, CSP, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, exposed secrets scan - mostly just reading response headers, already available from the Guzzle response object |
| Accessibility | ACC-001 (Image Alt), ACC-002 (Form Labels) | DOM queries on `<img>`/`<form>` - same crawler pattern as ACC-003/004 |
| Authentication | AUTH-001 through AUTH-004 | Secure/HttpOnly cookies, password reset, email verification - cookie flags are checkable from response headers without login; reset/verification workflows are hard to verify externally and may need to stay heuristic or be dropped from external scanning entirely |
| AI Readiness | AI-001 through AI-004 | AI disclosure, rate limiting, prompt injection notice, model usage terms - all niche/new, lowest priority |

---

## Known limitations (by design, for now)

1. **No JavaScript execution.** The engine fetches raw server HTML only. SPA/client-rendered sites (React, Vue, heavily hydrated pages) will false-negative on checks like Semantic HTML, Title, and Meta Description if that content is injected client-side rather than present in the initial response. Confirmed on Twitter/X.
2. **Bot-defended sites skew results.** Sites that serve reduced/different HTML to non-browser User-Agents (Twitter/X, likely others) will produce unreliable scores across the board. Not a bug in the rule logic - a fundamental constraint of static HTTP fetching.
3. **REL-001 (Custom 404) is a heuristic**, not a verified check - it assumes a 404 status with a substantial response body means a "custom" page. SPAs with client-side catch-all routing (which often return 200 for unknown paths) may score incorrectly here.
4. **CON-002 (Support Email) uses a loose regex** for plain-text email detection (`[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}`) against raw HTML, which can occasionally match addresses inside analytics scripts, meta tags, or other non-visible content. Acceptable tradeoff for MVP; could be tightened to only scan visible text nodes.
5. **Rules are config-based, not DB-based.** Per spec section 16, rules should eventually live in a DB table so they can be edited without a deploy. Currently in `config/prws_rules.php`.
6. **No queueing.** Scans run synchronously on the request. Fine for single-page checks; will need to move to a queued job once link-crawling (REL-002) or multi-page checks are added, to avoid blocking/timing out on slow sites.

---

## Polishing roadmap (current focus)

Since rule coverage expansion is deferred in favor of polish, suggested next steps in rough priority order:

1. ~~Scan history~~ - ✅ done (`/audits` index route, paginated, links to `create` and back from each row)
2. ~~Error handling in the UI~~ - ✅ done (unreachable/invalid URLs now return a friendly form error instead of a fake 0/100 report)
3. ~~Re-scan button~~ - ✅ done (`POST /audits/{audit}/rescan`, creates a new audit record rather than overwriting, preserves history)
4. ~~Shareable report links~~ - ✅ done (Copy Link button on report page, clipboard write; `show` route was already ID-addressable)
5. ~~Loading/progress state~~ - ✅ done (spinner + disabled button + readonly input on submit; note - input must use `readOnly`, not `disabled`, or its value won't POST)
6. ~~Platinum certification logic~~ - ✅ done (score ≥95, no Critical, no High - added as top arm in the `match(true)` block in both `store()` and `rescan()`)
7. ~~Visual polish on the report~~ - ✅ done (SVG score gauge color-coded by tier, icon-based pass/fail instead of ✓/✕ text, category icons, icon+label nav links)

**Tracked tech debt:** scoring/certification logic (`scoreAndCertify`) is currently duplicated between `store()` and `rescan()` in `AuditController`. Should be extracted into a shared private helper - deferred for now since it's not blocking, but worth doing before a third copy appears. Now also duplicated: `$badgeColors` array (in `show.blade.php` and `index.blade.php`) and gauge/tier color logic - worth consolidating into a shared helper (e.g. a `Certification` enum or a view composer) once UI stabilizes.

---

## Next improvements (up next)

1. **Extract scoring/certification + badge-color duplication** - the tech debt above; do this before adding more logic that touches certification tiers
2. **DB-backed rules table** - move `config/prws_rules.php` into a `rules` migration/model per spec section 16, so rules can be toggled/edited without a redeploy
3. **Fill in easy remaining rules** - LEG-003 (Cookie Policy) and CON-003 (About page) follow the exact same path-check pattern as existing rules; SEC-001 (HTTPS), SEC-002 (HSTS), SEC-004/005/006 (security headers) are just reading response headers already available from the Guzzle response - low effort, meaningfully raises real-world scores toward Platinum range
4. **Printable/exportable report** - PDF export or print-friendly CSS for the report page, useful if this is ever shared with a client or team as a deliverable
5. **Category subtotals on the report** - show earned/available points per category (e.g. "Legal: 100/100") above each group, not just per-rule
6. **True async scanning** - move the scan to a queued job with polling or websockets for real progress, replacing the current full-page-POST spinner; only worth doing once REL-002 (broken link crawling) or other slow multi-page checks are added, since single-page checks are fast enough to stay synchronous for now

---

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite    if using SQLite / if not you know what to do then ...
php artisan migrate
php artisan serve
```

**Windows-specific note:** if all scans return 0/100 across every rule regardless of target site, this is almost always a missing CA bundle for cURL/Guzzle (`cURL error 60: SSL certificate problem`). Fix:

1. Download `https://curl.se/ca/cacert.pem`
2. Find active php.ini via `php --ini`
3. Add: `curl.cainfo = "path\to\cacert.pem"` and `openssl.cafile = "path\to\cacert.pem"`
4. Restart `php artisan serve`

---

## Reference

Full rule catalog, scoring formulas, and certification thresholds: `Production_Ready_Web_Standard_Specification.pdf` (v1.0, this repo).
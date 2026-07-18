# PRWS - Production Ready Web Standard

PRWS audits a website against a fixed set of rules covering legal, contact, SEO, security, reliability, and accessibility basics - the kind of thing that gets skipped when a site is shipped quickly, including AI-assisted ("vibe coded") builds where the visible UI works but supporting infrastructure is incomplete.

A scan returns a score out of 100 and a certification tier (Bronze, Silver, Gold, Platinum) based on that score and the severity of any failed rules.

The original rule specification is in `Production_Ready_Web_Standard_Specification.pdf` (v1.0). The implemented rule set has diverged from that draft since; this file is the current reference.

## Requirements

- PHP 8.2+
- Composer
- SQLite (default) or another Laravel-supported database

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan db:seed --class=RuleSeeder
php artisan serve
```

Visit `http://127.0.0.1:8000`.

### Windows: cURL SSL errors

If every scan returns 0/100 regardless of target, this is a missing CA bundle for cURL/Guzzle (`cURL error 60: SSL certificate problem`).

1. Download `https://curl.se/ca/cacert.pem`
2. Run `php --ini` to find the active php.ini
3. Set `curl.cainfo` and `openssl.cafile` to the downloaded file's path
4. Restart the server

## Architecture

```
app/
  Http/Controllers/AuditController.php   scan/rescan/history/report routes
  Models/Audit.php                       one row per scan run
  Models/Finding.php                     one row per rule result, belongs to Audit
  Models/Rule.php                        one row per rule definition
  Services/RuleEngine.php                fetches a URL, runs active rules, returns results
  Support/Certification.php              tier thresholds + tier-to-CSS mappings, shared by controller and views

database/
  migrations/                            audits, findings, rules tables
  seeders/RuleSeeder.php                 rule definitions

resources/views/audits/
  create.blade.php                       scan form + landing page showcase
  show.blade.php                         individual report
  index.blade.php                        scan history
```

### Request flow

1. `AuditController::store` validates the submitted URL and passes it to `RuleEngine`.
2. `RuleEngine::run` fetches the homepage via Guzzle, builds a `symfony/dom-crawler` instance from the response body, and iterates every active row in the `rules` table. For each rule it calls a matching `check{RULE_ID}()` method (e.g. `SEO-001` → `checkSEO001()`) and records pass/fail.
3. If the fetch fails (DNS, timeout, non-2xx, SSL), `RuleEngine::isReachable()` returns false and the controller returns the user to the form with an error instead of persisting a zeroed-out audit.
4. `AuditController::scoreAndCertify` sums points, determines the certification tier via `Certification::determine`, and persists an `Audit` row plus one `Finding` row per rule.
5. `Audit.rules_count` records how many rules were active at scan time. This is read by the landing-page showcase query so that audits scored under an older, smaller rule set don't outrank current scans on the basis of an easier bar. Older audits remain in `/audits` history regardless.

### Adding a rule

1. Insert a row into `rules` (via a seeder or `Rule::create`) with a unique `rule_id`, `name`, `category`, `severity`, `points`, and `active = true`.
2. Add a `check{RULE_ID}()` protected method to `RuleEngine` (strip hyphens from the rule_id for the method name). It must return a bool.
3. No deploy is required to toggle a rule off - set `active = false` on its row.

## Rules

20 rules across 6 categories are currently implemented.

| Rule ID | Name | Category | Severity | Notes |
|---|---|---|---|---|
| LEG-001 | Privacy Policy Present | Legal | Critical | |
| LEG-002 | Terms of Service Present | Legal | High | |
| LEG-003 | Cookie Policy Present | Legal | Medium | |
| LEG-004 | Copyright Information | Legal | Low | regex also matches CC/license wording |
| CON-001 | Contact Page Exists | Contact | Medium | |
| CON-002 | Support Email Exists | Contact | Medium | checks homepage, falls back to /contact; mailto link or plain-text pattern |
| CON-003 | Organization Information | Contact | Low | checks /about |
| REL-001 | Custom 404 Page | Reliability | Medium | heuristic - 404 status with response body over 200 chars |
| REL-004 | Favicon Exists | Reliability | Low | |
| SEO-001 | robots.txt | SEO | Medium | |
| SEO-002 | sitemap.xml | SEO | Medium | |
| SEO-003 | Title Tag | SEO | Low | |
| SEO-004 | Meta Description | SEO | Low | |
| ACC-003 | Semantic HTML | Accessibility | Low | requires header, main, footer, and nav all present |
| ACC-004 | Language Declaration | Accessibility | Low | |
| SEC-001 | HTTPS Enabled | Security | Critical | checks submitted URL scheme only, does not verify enforcement - see Limitations |
| SEC-002 | HSTS Enabled | Security | High | `Strict-Transport-Security` header |
| SEC-004 | X-Frame-Options | Security | Medium | header presence only |
| SEC-005 | X-Content-Type-Options | Security | Medium | header presence only |
| SEC-006 | Referrer Policy | Security | Medium | header presence only |

Certification thresholds (`App\Support\Certification::determine`):

| Tier | Score | Additional requirement |
|---|---|---|
| Bronze | ≥ 60 | no Critical failures |
| Silver | ≥ 75 | no Critical failures |
| Gold | ≥ 90 | no Critical failures |
| Platinum | ≥ 95 | no Critical or High failures |

### Not yet implemented

From the original spec, still open:

- SEC-003 (Content-Security-Policy header), SEC-007 (exposed secrets scan)
- ACC-001 (image alt text), ACC-002 (form label association)
- AUTH-001 through AUTH-004 (cookie flags, password reset, email verification)
- AI-001 through AI-004 (AI disclosure, rate limiting, prompt injection notice, usage terms)

## Limitations

- **No JavaScript execution.** Only the raw server response is parsed. Client-rendered content (React, Vue, etc.) that isn't present in the initial HTML will not be detected - confirmed to produce false negatives on Twitter/X.
- **Sites that serve reduced HTML to non-browser user agents** will score unreliably across the board. This is a property of static HTTP fetching, not a rule bug.
- **REL-001 is a heuristic**, not a verified check. SPAs with catch-all client-side routing that return 200 for unknown paths will be scored incorrectly.
- **CON-002's plain-text email regex** can match addresses inside scripts or meta tags that aren't user-visible.
- **SEC-001 checks the URL scheme submitted, not enforcement.** A site reachable over plain HTTP could pass if the `https://` variant is submitted manually and loads without redirect verification.
- **Scans are synchronous.** The landing page shows a simulated progress sequence client-side while the real request runs in the background; there is no queue and no real progress tracking yet.
- **Favicon images depend on Google's public `s2/favicons` endpoint**, an external dependency outside this project's control.

## Roadmap

- SEC-003 (CSP header check)
- Printable/exportable report output
- Per-category subtotals on the report page
- Queued/async scanning with real progress tracking
- SEC-001 enforcement check (verify HTTP→HTTPS redirect rather than trusting scheme)
- ACC-001 / ACC-002 (alt text, form labels)

## License

Not yet set.
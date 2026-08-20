<div align="center">

<img src="resources/img/logo.png" width="150" alt="Spyrja logo">

# SPYRJA

**Frees your time so you can focus on what matters most.**

A bilingual (🇬🇧 / 🇮🇸) landing page for a Reykjavík-based personal assistant &amp; concierge service.
Static front-end, zero build step, one hardened PHP endpoint for the contact form.

<br>

![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/Vanilla_JS-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![PHP](https://img.shields.io/badge/PHP_8.1+-777BB4?style=for-the-badge&logo=php&logoColor=white)

![Build](https://img.shields.io/badge/build_step-none-brightgreen?style=flat-square)
![Dependencies](https://img.shields.io/badge/runtime_dependencies-1-blue?style=flat-square)
![Page weight](https://img.shields.io/badge/assets-416_KB-brightgreen?style=flat-square)
![Languages](https://img.shields.io/badge/i18n-EN_%7C_IS-informational?style=flat-square)
![No JS required](https://img.shields.io/badge/works_without_JS-yes-success?style=flat-square)
![License](https://img.shields.io/badge/license-proprietary-lightgrey?style=flat-square)

</div>

---

> [!IMPORTANT]
> The contact form needs SMTP credentials before it can deliver anything.
> Copy `.env.example` to `.env`, fill it in, and read [Configuration](#-configuration).
> Without them the endpoint answers with a readable error instead of silently
> pretending to succeed.

---

## 📑 Table of contents

- [What it is](#-what-it-is)
- [Features](#-features)
- [Tech stack](#-tech-stack)
- [Project structure](#-project-structure)
- [Getting started](#-getting-started)
- [Configuration](#-configuration)
- [Deployment](#-deployment)
- [Internationalization](#-internationalization)
- [Design system](#-design-system)
- [How it works](#-how-it-works)
- [Accessibility &amp; performance](#-accessibility--performance)
- [Contributing](#-contributing)
- [License](#-license)

---

## 🧭 What it is

SPYRJA is a one-page business card site (Polish: *wizytówka*) for a personal assistant service.
It presents the brand, lists six service categories, and collects enquiries through a contact form.
Everything is hand-written HTML/CSS/JS — no framework, no bundler, no npm.

Every page exists twice: once in English, once in Icelandic, as parallel static files.

---

## ✨ Features

| | Feature | Notes |
|---|---|---|
| 🌍 | **Full EN / IS localization** | Six pages plus every server response and validation message |
| 🎬 | **Scroll-triggered reveals** | `IntersectionObserver`, staggered 120 ms apart |
| 🛡️ | **Hardened contact endpoint** | Honeypot, rate limit, server-side validation, header-injection guard |
| 📮 | **AJAX form with a no-JS fallback** | `fetch()` when available, plain `POST` when not |
| ♿ | **Keyboard &amp; screen-reader ready** | Visible focus, live-region feedback, descriptive alt text |
| 🪶 | **416 KB of assets, total** | WebP with PNG fallbacks, lazy loading, no layout shift |
| ⚖️ | **Legal pages included** | Privacy Policy and Terms of Service, both languages |
| 🚫 | **No cookies, no trackers** | Nothing to consent to, no banner needed |

---

## 🛠 Tech stack

| Layer | Choice |
|---|---|
| Markup | HTML5, semantic sections, one `<h1>` per page |
| Styling | Hand-written CSS3 — custom properties, flexbox, `@supports`, `prefers-reduced-motion` |
| Behaviour | Vanilla JavaScript (ES5-compatible), one shared `scripts/main.js` |
| Backend | PHP 8.1+ — a single POST endpoint |
| Mail | [PHPMailer](https://github.com/PHPMailer/PHPMailer) `^6.10` over authenticated SMTP |
| Fonts | Montserrat (Google Fonts) |

---

## 📂 Project structure

```
wizytowka/
├── index.html                   # 🇬🇧 landing page
├── index_is.html                # 🇮🇸 landing page
├── privacy-policy.html          # 🇬🇧 privacy policy
├── privacy-policy_is.html       # 🇮🇸 privacy policy
├── tos.html                     # 🇬🇧 terms of service
├── tos_is.html                  # 🇮🇸 terms of service
│
├── scripts/
│   ├── contact.php              # contact form endpoint (POST only)
│   └── main.js                  # shared behaviour for all six pages
├── style/
│   └── style.css                # the entire stylesheet
├── resources/
│   ├── favicon/                 # 32 / 180 / 192 px icons
│   └── img/
│       ├── logo.png · logo.webp
│       └── services/
│           ├── en/              # service tiles, English captions
│           └── is/              # service tiles, Icelandic captions
│
├── .env.example                 # copy to .env and fill in
├── .htaccess                    # index, security headers, caching, vendor lockout
├── robots.txt
├── sitemap.xml
├── composer.json
└── composer.lock
```

`vendor/` is git-ignored — run `composer install` after cloning.

---

## 🚀 Getting started

### Prerequisites

- **PHP 8.1+** with the `mbstring` extension
- **Composer** 2.x

### Run it locally

```bash
# 1. Clone
git clone https://github.com/<your-user>/wizytowka.git
cd wizytowka

# 2. Install dependencies
composer install

# 3. Point the mailer at something
cp .env.example .env
$EDITOR .env

# 4. Serve
php -S localhost:8000
```

Then open **<http://localhost:8000/>** — the root resolves straight to `index.html`.

> [!TIP]
> `php -S` does not read `.env` files. For local testing, export the variables in the
> same shell instead:
> ```bash
> set -a && source .env && set +a && php -S localhost:8000
> ```

### Static preview

Opening `index.html` from the file system works for reviewing the design. The form will
report a network error on submit, since there is no PHP runtime — everything else behaves
normally.

---

## ⚙️ Configuration

Every setting comes from the environment. Nothing is hard-coded, and `.env` is git-ignored.

| Variable | Purpose | Example |
|---|---|---|
| `SMTP_HOST` | Outgoing mail server | `smtp.eu.mailgun.org` |
| `SMTP_PORT` | Submission port (STARTTLS) | `587` |
| `SMTP_USER` | SMTP username | `postmaster@spyrja.com` |
| `SMTP_PASS` | SMTP password | *(secret)* |
| `MAIL_FROM` | Envelope sender — **must** be a domain you control | `no-reply@spyrja.com` |
| `MAIL_TO` | Where enquiries are delivered | `samband@spyrja.com` |

If any of them is missing the endpoint answers `500` with a readable message and logs the
specific variable name via `error_log()`.

> [!WARNING]
> Set `MAIL_FROM` to an address on your own domain and publish matching SPF and DKIM records.
> Sending as the visitor's address is what puts contact-form mail in the spam folder — the
> visitor's address goes in `Reply-To` instead, which is what this endpoint does.

---

## 🌐 Deployment

Point the document root at the repository root. `.htaccess` handles the rest on Apache:
`DirectoryIndex`, security headers, long-lived asset caching, gzip, and 404s for
`vendor/`, `composer.*` and dotfiles.

**On nginx**, delete `.htaccess` and port the equivalent:

```nginx
root /var/www/spyrja;
index index.html;

location ~ ^/(vendor|composer\.(json|lock)|\.env) { return 404; }
location ~ /\.                                    { deny all; }

location ~ \.php$ {
    fastcgi_pass unix:/run/php/php8.1-fpm.sock;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
}
```

**Before going live**, replace `https://spyrja.com` with the real domain in `sitemap.xml`,
`robots.txt`, and the `canonical` / `hreflang` / `og:url` tags of all six pages:

```bash
grep -rl 'spyrja\.com' --include='*.html' --include='*.xml' --include='*.txt' .
```

---

## 🌍 Internationalization

Localization is file-based: each page has an `_is` twin, and the header switcher links to it.

| Page | 🇬🇧 English | 🇮🇸 Íslenska |
|---|---|---|
| Landing | `index.html` | `index_is.html` |
| Privacy Policy | `privacy-policy.html` | `privacy-policy_is.html` |
| Terms of Service | `tos.html` | `tos_is.html` |

Translated strings live in exactly **three** places, and nowhere else:

1. The page copy itself.
2. `STRINGS` in `scripts/main.js` — client-side validation and network errors, keyed by `<html lang>`.
3. `MESSAGES` in `scripts/contact.php` — server responses, keyed by the form's hidden `lang` field.

Service tile graphics are localized too, since captions are baked into the images — hence
the split `resources/img/services/en/` and `resources/img/services/is/` directories.

**Adding a language:** copy the three pages, translate the copy, add a
`resources/img/services/<code>/` image set, add one entry to `STRINGS` and one to `MESSAGES`,
and extend the switcher. No JavaScript or CSS is duplicated.

---

## 🎨 Design system

All colours are CSS custom properties declared in `:root`.

| Swatch | Token | Hex | Used for |
|---|---|---|---|
| ![](https://img.shields.io/badge/-002b51-002b51?style=flat-square) | `--navy` | `#002b51` | Headers, cards, buttons |
| ![](https://img.shields.io/badge/-033663-033663?style=flat-square) | `--navy-accent` | `#033663` | Hover states, success ring |
| ![](https://img.shields.io/badge/-b8dafa-b8dafa?style=flat-square) | `--sky` | `#b8dafa` | Gradient end stop |
| ![](https://img.shields.io/badge/-afd7fc-afd7fc?style=flat-square) | `--sky-light` | `#afd7fc` | Page background top |
| ![](https://img.shields.io/badge/-f2f1eb-f2f1eb?style=flat-square) | `--cream` | `#f2f1eb` | Text on navy |
| ![](https://img.shields.io/badge/-b3261e-b3261e?style=flat-square) | `--error` | `#b3261e` | Form error state |

**Type:** Montserrat 400 / 500 / 600, Arial fallback.
**Signature detail:** section headings use a navy → sky gradient clipped to the text, wrapped
in `@supports` so they degrade to solid navy rather than to invisible.

---

## 🔍 How it works

### Reveal animation

The service tiles are **visible by default**. A one-line script in `<head>` adds a `js` class
to `<html>`, and only `.js .service-item` carries `opacity: 0`. An `IntersectionObserver`
then adds `.show` as each tile scrolls into view.

The inversion is deliberate: with the hiding rule tied to JavaScript, no failure mode —
JS disabled, an observer that never fires, a crawler that never scrolls — can leave the
section blank. `prefers-reduced-motion` and a missing `IntersectionObserver` both short-circuit
to "show everything at once".

### Form submission

`main.js` intercepts submit, posts a `FormData`, and renders the server's reply into a
live-region panel using `textContent`. The form element keeps its `action` and `method`, so
without JavaScript the browser performs a normal POST and displays the plain-text response.

### Endpoint contract

`scripts/contact.php` — responses are plain text, in the language of the submitting page.

| Status | When |
|---|---|
| `200` | Message sent (also returned to honeypot hits, so bots see success) |
| `400` | Empty required field, malformed e-mail, over-length input, or CR/LF in a header field |
| `405` | Any method other than `POST` |
| `429` | More than 3 submissions from one IP within 10 minutes |
| `500` | SMTP failure, missing configuration, or missing `vendor/` |

Category values are validated against a whitelist and mapped to readable labels for the
e-mail subject. The slugs in `<option value="…">` **must** stay in sync with the `CATEGORIES`
constant in `contact.php`.

---

## ♿ Accessibility &amp; performance

- One `<h1>` per page; the header wordmark is a `<p class="wordmark">` on subpages.
- `:focus-visible` outlines on every interactive element.
- Feedback panel is a `role="status"` live region and receives focus after submission.
- Descriptive, per-language `alt` text on all thirteen images.
- `hreflang` pairs, `canonical`, Open Graph and `theme-color` on all six pages.
- Full `prefers-reduced-motion` and `forced-colors` support.
- WebP with PNG fallbacks via `<picture>`, explicit `width`/`height` (no layout shift), and
  `loading="lazy"` on below-the-fold tiles.

Total asset weight is **416 KB**, down from 2.7 MB.

---

## 🤝 Contributing

Issues and pull requests are welcome. Two things to keep in mind:

- **Every page change has a twin.** Touching `index.html` almost always means touching
  `index_is.html` too. The shared `main.js` exists specifically so behaviour can no longer
  drift between locales — please keep it that way rather than adding inline scripts.
- **The category slugs are a contract** between the HTML `<option value>` attributes and the
  `CATEGORIES` constant in `contact.php`. Change one, change the other.

Please keep pull requests scoped to one concern and describe the visible effect of the change.

---

## 📄 License

Proprietary — all rights reserved. This repository is published for reference and is not
licensed for reuse. If you intend to open-source it, add a [`LICENSE`](https://choosealicense.com/)
file and update `composer.json` accordingly.

---

<div align="center">

**SPYRJA** · Reykjavík, Iceland · <samband@spyrja.com>

</div>

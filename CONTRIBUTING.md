# Contributing to Wove

Thanks for your interest in improving Wove!

## Getting started

1. Fork and clone the repo.
2. Make sure Docker and Node are installed.
3. `npx @wordpress/env start` → develop against http://localhost:8888.

## Guidelines

- **No build step.** The theme is plain CSS/PHP/HTML block markup — keep it that way.
- **theme.json first.** Prefer `theme.json` for design; only add to `style.css` what it can't express.
- **Prefix everything** with `wove_` (PHP) / `.wove-` (CSS) / `wove/` (patterns & blocks).
- **Translatable strings.** Wrap user-facing text in the `wove` text domain. Visible text in
  block templates belongs in a PHP pattern (templates can't be translated in place). Run
  `wp i18n make-pot …` (see README) after adding strings.
- **No personal or hardcoded content.** Identity and links come from the settings option, not files.
- **Accessibility.** Maintain visible focus, sequential headings, and `prefers-reduced-motion`.

## Before opening a PR

- Activate the theme on a clean `wp-env` site and click through every template + dark mode + mobile.
- Run the **Theme Check** plugin and resolve findings.
- `php -l` your PHP; confirm no notices with `WP_DEBUG` on.
- Update `CHANGELOG.md`.

By contributing, you agree your contributions are licensed under GPLv2 or later.

# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Package Overview

`filafly/filament-logo-tools` is a Filament v4/v5 plugin that enhances panel sidebar/topbar branding. It provides icon logos (shown when sidebar is collapsed), collapse button customization, and logo section background/border styling. No database, models, or JavaScript files — purely render hooks + CSS.

## Commands

```bash
vendor/bin/pint          # Lint/format PHP (Laravel Pint)
vendor/bin/phpunit       # Run tests (no tests exist yet)
```

After changing CSS, republish assets in the consuming project:

```bash
php artisan filament:assets
```

PHP changes auto-update via symlink when installed as a path repository.

## Architecture

### File Layout

- `src/LogoToolsPlugin.php` — Main plugin class (fluent config API + render hook registration)
- `src/LogoToolsServiceProvider.php` — Registers CSS asset via `FilamentAsset`
- `src/Enums/CollapseButtonPosition.php` — Left/Right enum for collapse button placement
- `src/Enums/CollapseButtonStyle.php` — Circle/Rounded/Square enum for collapse button shape
- `resources/css/logo-tools.css` — All styling (light/dark variants, inverted mode, button positioning, edge button)

### Plugin API

The API is organized into three groups:

**Icon Logo** — compact mark shown when sidebar is collapsed:
- `iconLogo(string | Htmlable | Closure | null)` — the icon (path renders as `<img>`, Htmlable renders inline)
- `darkModeIconLogo(string | Htmlable | Closure | null)` — dark mode variant
- `iconLogoHeight(string | Closure | null)` — CSS height (default: `1.5rem`)

**Logo Section** — the topbar/sidebar area containing the logo:
- `logoSectionBackground(array | string | bool)` — inverted background (accepts Filament Color arrays, color names, hex, CSS functions, Tailwind `bg-*` classes, or `true` for default dark/light)
- `logoSectionBorder(string | Closure | null)` — border on trailing edge (default: `rgb(0 0 0 / 0.1)`)
- `logoSectionPadding(string | Closure | null)` — inline-start padding
- `collapsedLogoSectionWidth(string | Closure | null)` — override collapsed width (Filament's `--collapsed-sidebar-width` is often inaccurate)

**Collapse Button** — the sidebar expand/collapse chevron:
- `collapseButtonPosition(CollapseButtonPosition)` — Left or Right (auto-Right when iconLogo is set)
- `collapseButtonStyle(CollapseButtonStyle | string)` — `'circle'` (default), `'rounded'`, or `'square'`
- `expandButtonIcon(string | BackedEnum | null)` — override expand icon (accepts Heroicon enums or string aliases)
- `collapseButtonIcon(string | BackedEnum | null)` — override collapse icon

### How It Works

The plugin registers hooks in its `register()` method:

1. **Icon overrides** — `$panel->icons()` to override sidebar expand/collapse button icons
2. **BODY_START** — `<script>` that adds CSS classes to `<body>` (`fi-logo-tools`, `fi-logo-tools-inverted`, `fi-logo-tools-btn-right`, `fi-logo-tools-btn-{style}`) and sets CSS custom properties (`--fi-logo-tools-bg`, `--fi-logo-tools-padding`)
3. **TOPBAR_START** — Absolutely positioned background/border strip that tracks sidebar width via Alpine `$store.sidebar.isOpen`
4. **SIDEBAR_LOGO_BEFORE** — Icon logo for mobile (sidebar header is `lg:hidden` with topbar layout)
5. **TOPBAR_LOGO_BEFORE** — Icon logo for desktop + Alpine `x-effect` that toggles `fi-logo-tools-icon-active` on `fi-topbar-start` to hide the full logo and constrain section width when collapsed

### Critical: Hook Registration Timing

Render hooks **must** be registered in `register()`, not `boot()`. Filament's `Panel::boot()` calls `registerRenderHooks()` before `plugin->boot()`, so hooks added in `boot()` are never flushed to FilamentView.

### Configuration Pattern

All config methods accept `string | Htmlable | Closure` (evaluated at render time via `evaluate()`). This allows deferred computation and dependency injection in closures. The `logoSectionBackground()` method additionally accepts Filament Color arrays (oklch format in v4), Tailwind classes (`bg-gray-900`), Filament color names (`primary`), hex codes, or CSS color functions — resolved by `resolveColor()`.

### CSS Strategy

- No build step — plain CSS loaded as a Filament asset
- Light/dark switching via `.dark` parent class and `display` toggling
- Inverted mode (`.fi-logo-tools-inverted`) swaps logo variants (shows dark logo on light, light on dark)
- `--fi-logo-tools-bg` CSS variable enables custom background colors
- `--fi-logo-tools-padding` CSS variable controls logo section inline-start padding
- Button repositioning uses flexbox `order` property
- Edge button (`.fi-logo-tools-btn-right`) is absolutely positioned on the logo section border with `inset-inline-end: -0.75rem` for centering
- Topbar-start width is dynamically set via Alpine to match `--sidebar-width` / `--collapsed-sidebar-width` (or custom `collapsedLogoSectionWidth`)

### Key Filament Internals

- `fi-topbar-start` has no intrinsic width — it grows to fit content. The plugin constrains it.
- `--collapsed-sidebar-width` (4.5rem default) is defined but never used by Filament's own CSS. The collapsed sidebar sizes from content.
- Topbar has `px-4` (1rem) padding, so topbar-start content offset = 1rem from viewport edge.
- `fi-topbar-collapse-sidebar-btn-ctn` is `w-9 shrink-0` (2.25rem).
- Sidebar header is `lg:hidden` when topbar layout is active (default in v4).

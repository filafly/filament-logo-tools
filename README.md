# Logo Tools for Filament

[![Latest Version on Packagist](https://img.shields.io/packagist/v/filafly/filament-logo-tools.svg?style=flat-square)](https://packagist.org/packages/filafly/filament-logo-tools)
[![Total Downloads](https://img.shields.io/packagist/dt/filafly/filament-logo-tools.svg?style=flat-square)](https://packagist.org/packages/filafly/filament-logo-tools)

A Filament plugin that adds enhanced logo and sidebar controls to your panel. All features are implemented through Filament's native plugin system, render hooks, and CSS — no view overrides required.

## Requirements

- PHP 8.2+
- Filament v4 or v5

## Installation

You can install the package via Composer:

```bash
composer require filafly/filament-logo-tools
```

Publish the assets:

```bash
php artisan filament:assets
```

Register the plugin in your panel provider:

```php
use Filafly\LogoTools\LogoToolsPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugin(LogoToolsPlugin::make());
}
```

## Icon Logo

Display a compact icon when the sidebar is collapsed, replacing the full brand logo. Only renders when the panel has `sidebarCollapsibleOnDesktop()` enabled.

```php
LogoToolsPlugin::make()
    ->iconLogo('img/icon.svg')
    ->darkModeIconLogo('img/icon-dark.svg')
    ->iconLogoHeight('1.5rem')
```

### `iconLogo(string | Htmlable | Closure | null $logo)`

The icon shown when the sidebar is collapsed. Accepts a file path (rendered as `<img>`), an `Htmlable` instance (rendered inline), or a `Closure`.

```php
// Image path
->iconLogo('img/icon.svg')

// Inline SVG
->iconLogo(fn () => new HtmlString('<svg ...>...</svg>'))
```

### `darkModeIconLogo(string | Htmlable | Closure | null $logo)`

A separate icon for dark mode. When set, the light and dark variants swap automatically. If not set, the single `iconLogo` is used in both modes.

### `iconLogoHeight(string | Closure | null $height)`

The CSS height of the icon logo. Defaults to `1.5rem`.

```php
->iconLogoHeight('2rem')
```

## Logo Section

Control the appearance of the logo section area in the topbar and sidebar header.

### `logoSectionBackground(array | string | bool $color = true)`

Adds an inverted background to the logo section — dark background in light mode, light background in dark mode. The full brand logo's light/dark variants are swapped automatically to match.

```php
// Default dark/light inversion
->logoSectionBackground()

// Filament color (array)
->logoSectionBackground(Color::Blue)

// Filament color name
->logoSectionBackground('primary')

// Hex code
->logoSectionBackground('#1e3a5f')

// CSS color function
->logoSectionBackground('oklch(0.5 0.2 240)')

// Tailwind class
->logoSectionBackground('bg-gray-900')
```

### `logoSectionBorder(string | Closure | null $color = 'rgb(0 0 0 / 0.1)')`

Adds a vertical border on the trailing edge of the logo section. Works with or without `logoSectionBackground()`.

```php
// Default subtle black border
->logoSectionBorder()

// Custom color (e.g. for use with logoSectionBackground)
->logoSectionBorder('rgb(255 255 255 / 0.1)')
```

### `logoSectionPadding(string | Closure | null $padding)`

Adds inline-start padding to the logo section.

```php
->logoSectionPadding('1rem')
```

### `collapsedLogoSectionWidth(string | Closure | null $width)`

Overrides the collapsed width of the logo section in the topbar. Filament's `--collapsed-sidebar-width` CSS variable (`4.5rem`) is not always visually accurate, so this lets you fine-tune alignment.

```php
->collapsedLogoSectionWidth('5.5rem')
```

## Collapse Button

Customize the sidebar collapse/expand button appearance and behavior.

### `collapseButtonPosition(CollapseButtonPosition $position)`

Controls whether the collapse button appears to the left or right of the logo. Automatically set to `Right` when an icon logo is configured.

When positioned right, the button is rendered as a small edge button sitting on the border of the logo section — similar to apps like Linear and Notion.

```php
use Filafly\LogoTools\Enums\CollapseButtonPosition;

->collapseButtonPosition(CollapseButtonPosition::Right)
```

### `collapseButtonStyle(CollapseButtonStyle | string $style)`

Sets the shape of the collapse button when positioned on the logo section edge. Accepts the enum or a string (`'circle'`, `'rounded'`, `'square'`).

```php
use Filafly\LogoTools\Enums\CollapseButtonStyle;

->collapseButtonStyle(CollapseButtonStyle::Circle)   // full circle (default)
->collapseButtonStyle(CollapseButtonStyle::Rounded)  // slightly rounded corners
->collapseButtonStyle('square')                      // sharp corners
```

### `expandButtonIcon(string | BackedEnum | null $icon)`

Overrides the icon shown on the expand button (when sidebar is collapsed).

```php
use Filament\Support\Icons\Heroicon;

// Enum
->expandButtonIcon(Heroicon::OutlinedChevronRight)

// String
->expandButtonIcon('heroicon-o-arrow-right')
```

### `collapseButtonIcon(string | BackedEnum | null $icon)`

Overrides the icon shown on the collapse button (when sidebar is expanded).

```php
->collapseButtonIcon(Heroicon::OutlinedChevronLeft)
```

## Full Example

```php
use Filafly\LogoTools\LogoToolsPlugin;
use Filafly\LogoTools\Enums\CollapseButtonStyle;
use Filament\Support\Colors\Color;

->plugin(
    LogoToolsPlugin::make()
        ->iconLogo('img/icon.svg')
        ->darkModeIconLogo('img/icon-dark.svg')
        ->iconLogoHeight('1.25rem')
        ->logoSectionBackground(Color::Blue)
        ->logoSectionBorder('rgb(255 255 255 / 0.1)')
        ->logoSectionPadding('0.5rem')
        ->collapsedLogoSectionWidth('5.5rem')
        ->collapseButtonStyle('rounded')
        ->expandButtonIcon('heroicon-o-arrow-right')
        ->collapseButtonIcon('heroicon-o-arrow-left')
)
```

## Credits

- [Nolan Nordlund](https://github.com/nolanos)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

<?php

namespace Filafly\LogoTools;

use BackedEnum;
use Closure;
use Filafly\LogoTools\Enums\CollapseButtonPosition;
use Filafly\LogoTools\Enums\CollapseButtonStyle;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\View\PanelsIconAlias;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class LogoToolsPlugin implements Plugin
{
    // Icon Logo
    protected string | Htmlable | Closure | null $iconLogo = null;

    protected string | Htmlable | Closure | null $darkModeIconLogo = null;

    protected string | Closure | null $iconLogoHeight = null;

    // Logo Section
    protected array | string | bool $logoSectionBackground = false;

    protected string | Closure | null $logoSectionBorder = null;

    protected string | Closure | null $logoSectionPadding = null;

    protected string | Closure | null $collapsedLogoSectionWidth = null;

    // Collapse Button
    protected CollapseButtonPosition $collapseButtonPosition = CollapseButtonPosition::Left;

    protected CollapseButtonStyle $collapseButtonStyle = CollapseButtonStyle::Circle;

    protected string | BackedEnum | null $expandButtonIcon = null;

    protected string | BackedEnum | null $collapseButtonIcon = null;

    public function getId(): string
    {
        return 'filafly-logo-tools';
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        return filament(app(static::class)->getId());
    }

    // ── Icon Logo ───────────────────────────────────────────

    public function iconLogo(string | Htmlable | Closure | null $logo): static
    {
        $this->iconLogo = $logo;

        return $this;
    }

    public function darkModeIconLogo(string | Htmlable | Closure | null $logo): static
    {
        $this->darkModeIconLogo = $logo;

        return $this;
    }

    public function iconLogoHeight(string | Closure | null $height): static
    {
        $this->iconLogoHeight = $height;

        return $this;
    }

    public function getIconLogo(): string | Htmlable | null
    {
        return $this->evaluate($this->iconLogo);
    }

    public function getDarkModeIconLogo(): string | Htmlable | null
    {
        return $this->evaluate($this->darkModeIconLogo);
    }

    public function getIconLogoHeight(): string
    {
        return $this->evaluate($this->iconLogoHeight) ?? '1.5rem';
    }

    // ── Logo Section ────────────────────────────────────────

    public function logoSectionBackground(array | string | bool $color = true): static
    {
        $this->logoSectionBackground = $color;

        return $this;
    }

    public function logoSectionBorder(string | Closure | null $color = 'rgb(0 0 0 / 0.1)'): static
    {
        $this->logoSectionBorder = $color;

        return $this;
    }

    public function logoSectionPadding(string | Closure | null $padding): static
    {
        $this->logoSectionPadding = $padding;

        return $this;
    }

    public function collapsedLogoSectionWidth(string | Closure | null $width): static
    {
        $this->collapsedLogoSectionWidth = $width;

        return $this;
    }

    public function hasLogoSectionBackground(): bool
    {
        return $this->logoSectionBackground !== false;
    }

    public function getLogoSectionBackgroundColor(): ?string
    {
        if (is_array($this->logoSectionBackground)) {
            return $this->logoSectionBackground[500] ?? reset($this->logoSectionBackground);
        }

        if (is_string($this->logoSectionBackground)) {
            return $this->resolveColor($this->logoSectionBackground);
        }

        return null;
    }

    public function getLogoSectionBorder(): ?string
    {
        return $this->evaluate($this->logoSectionBorder);
    }

    public function hasLogoSectionBorder(): bool
    {
        return filled($this->logoSectionBorder);
    }

    public function getLogoSectionPadding(): ?string
    {
        return $this->evaluate($this->logoSectionPadding);
    }

    public function getCollapsedLogoSectionWidth(): ?string
    {
        return $this->evaluate($this->collapsedLogoSectionWidth);
    }

    // ── Collapse Button ─────────────────────────────────────

    public function collapseButtonPosition(CollapseButtonPosition $position): static
    {
        $this->collapseButtonPosition = $position;

        return $this;
    }

    public function collapseButtonStyle(CollapseButtonStyle | string $style): static
    {
        $this->collapseButtonStyle = $style instanceof CollapseButtonStyle
            ? $style
            : CollapseButtonStyle::from($style);

        return $this;
    }

    public function expandButtonIcon(string | BackedEnum | null $icon): static
    {
        $this->expandButtonIcon = $icon;

        return $this;
    }

    public function collapseButtonIcon(string | BackedEnum | null $icon): static
    {
        $this->collapseButtonIcon = $icon;

        return $this;
    }

    public function getCollapseButtonPosition(): CollapseButtonPosition
    {
        return $this->collapseButtonPosition;
    }

    // ── Plugin Registration ─────────────────────────────────

    public function register(Panel $panel): void
    {
        $this->registerIconOverrides($panel);
        $this->registerClassInjectionHook($panel);
        $this->registerTopbarLogoSectionHook($panel);
        $this->registerIconLogoHook($panel);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    protected function registerIconOverrides(Panel $panel): void
    {
        $icons = [];

        if ($this->expandButtonIcon) {
            $icons[PanelsIconAlias::SIDEBAR_EXPAND_BUTTON] = $this->expandButtonIcon;
        }

        if ($this->collapseButtonIcon) {
            $icons[PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON] = $this->collapseButtonIcon;
        }

        if ($icons) {
            $panel->icons($icons);
        }
    }

    protected function registerClassInjectionHook(Panel $panel): void
    {
        $classes = ['fi-logo-tools'];

        if ($this->hasLogoSectionBackground()) {
            $classes[] = 'fi-logo-tools-inverted';
        }

        if ($this->hasLogoSectionBorder()) {
            $classes[] = 'fi-logo-tools-border';
        }

        if ($this->collapseButtonPosition === CollapseButtonPosition::Right || $this->iconLogo) {
            $classes[] = 'fi-logo-tools-btn-right';
            $classes[] = 'fi-logo-tools-btn-' . $this->collapseButtonStyle->value;
        }

        if (count($classes) === 1 && ! $this->iconLogo) {
            return;
        }

        $classString = implode("','", $classes);

        $customColor = $this->getLogoSectionBackgroundColor();
        $colorScript = $customColor
            ? "el.style.setProperty('--fi-logo-tools-bg','" . e($customColor) . "');"
            : '';

        $padding = $this->getLogoSectionPadding();
        $paddingScript = $padding
            ? "el.style.setProperty('--fi-logo-tools-padding','" . e($padding) . "');"
            : '';

        $panel->renderHook(
            PanelsRenderHook::BODY_START,
            fn (): string => "<script>var el=document.currentScript.parentElement;el.classList.add('{$classString}');{$colorScript}{$paddingScript}</script>",
        );
    }

    protected function registerTopbarLogoSectionHook(Panel $panel): void
    {
        if (! $this->hasLogoSectionBackground() && ! $this->hasLogoSectionBorder()) {
            return;
        }

        $panel->renderHook(
            PanelsRenderHook::TOPBAR_START,
            function (): HtmlString {
                $borderColor = $this->getLogoSectionBorder();
                $borderBinding = $borderColor
                    ? ", borderInlineEnd: '1px solid " . e($borderColor) . "'"
                    : '';

                $class = $this->hasLogoSectionBackground()
                    ? 'fi-logo-tools-topbar-bg'
                    : 'fi-logo-tools-topbar-border';

                $collapsedWidth = $this->getCollapsedLogoSectionWidth();
                $collapsedExpr = $collapsedWidth
                    ? "'" . e($collapsedWidth) . "'"
                    : "'var(--collapsed-sidebar-width)'";

                return new HtmlString(
                    '<div x-data :style="{ width: $store.sidebar.isOpen ? \'var(--sidebar-width)\' : ' . $collapsedExpr . $borderBinding . ' }" class="' . $class . '"></div>'
                );
            },
        );
    }

    protected function registerIconLogoHook(Panel $panel): void
    {
        if (! $this->iconLogo) {
            return;
        }

        $iconLogoRenderer = function () use ($panel): HtmlString | string {
            if (! $panel->isSidebarCollapsibleOnDesktop()) {
                return '';
            }

            $iconLogo = $this->getIconLogo();
            $darkModeIconLogo = $this->getDarkModeIconLogo();
            $height = $this->getIconLogoHeight();
            $hasDarkVariant = filled($darkModeIconLogo);

            $html = '<div x-show="! $store.sidebar.isOpen" class="fi-logo-tools-icon-logo-ctn">';
            $html .= $this->renderIconLogo($iconLogo, $height, $hasDarkVariant ? 'fi-logo-tools-icon-logo fi-logo-tools-icon-logo-light' : 'fi-logo-tools-icon-logo');

            if ($hasDarkVariant) {
                $html .= $this->renderIconLogo($darkModeIconLogo, $height, 'fi-logo-tools-icon-logo fi-logo-tools-icon-logo-dark');
            }

            $html .= '</div>';

            return new HtmlString($html);
        };

        // Sidebar: icon logo for mobile (sidebar header is lg:hidden with topbar layout)
        $panel->renderHook(PanelsRenderHook::SIDEBAR_LOGO_BEFORE, $iconLogoRenderer);

        // Topbar: icon logo for desktop + hide full logo when collapsed
        $panel->renderHook(
            PanelsRenderHook::TOPBAR_LOGO_BEFORE,
            function () use ($panel, $iconLogoRenderer): HtmlString | string {
                $iconHtml = $iconLogoRenderer();

                if ($iconHtml === '') {
                    return '';
                }

                // Toggle class + constrain width on fi-topbar-start to match sidebar
                $collapsedWidth = $this->getCollapsedLogoSectionWidth();
                $collapsedExpr = $collapsedWidth
                    ? "'calc(" . e($collapsedWidth) . " - 1rem)'"
                    : "'calc(var(--collapsed-sidebar-width) - 1rem)'";

                $toggleScript = '<div x-data x-effect="'
                    . 'let s=$el.closest(\'.fi-topbar-start\');'
                    . 'if(s){'
                    . 's.classList.toggle(\'fi-logo-tools-icon-active\',!$store.sidebar.isOpen);'
                    . 's.style.width=$store.sidebar.isOpen?\'calc(var(--sidebar-width) - 1rem)\':' . $collapsedExpr . ';'
                    . '}'
                    . '" class="hidden"></div>';

                return new HtmlString($toggleScript . ($iconHtml instanceof HtmlString ? $iconHtml->toHtml() : $iconHtml));
            },
        );
    }

    // ── Helpers ──────────────────────────────────────────────

    protected function renderIconLogo(string | Htmlable $logo, string $height, string $class): string
    {
        $style = "height: {$height}";

        if ($logo instanceof Htmlable) {
            return '<div class="' . e($class) . '" style="' . e($style) . '">' . $logo->toHtml() . '</div>';
        }

        return '<img src="' . e($logo) . '" alt="" class="' . e($class) . '" style="' . e($style) . '" />';
    }

    protected function resolveColor(string $color): string
    {
        // Hex codes
        if (str_starts_with($color, '#')) {
            return $color;
        }

        // CSS color functions (rgb, hsl, oklch, etc.)
        if (preg_match('/^(rgb|hsl|oklch|color|lab|lch)\s*\(/', $color)) {
            return $color;
        }

        // Tailwind class (bg-gray-900) → CSS variable
        if (str_starts_with($color, 'bg-')) {
            return 'var(--color-' . substr($color, 3) . ')';
        }

        // Filament color name (primary, danger, etc.) → CSS variable
        return 'rgb(var(--' . $color . '-500))';
    }

    protected function evaluate(mixed $value): mixed
    {
        if ($value instanceof Closure) {
            return app()->call($value);
        }

        return $value;
    }
}

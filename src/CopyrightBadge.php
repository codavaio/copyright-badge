<?php

declare(strict_types=1);

namespace Codava\CopyrightBadge;

final class CopyrightBadge
{
    /** @var array<string, mixed>|null */
    private static ?array $defaults = null;

    /** @return array<string, mixed> */
    public static function defaults(): array
    {
        return self::$defaults ??= (array) json_decode((string) file_get_contents(__DIR__.'/../resources/defaults.json'), true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * The owner's copyright line, ready for your own markup.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public static function parts(array $options = []): array
    {
        $defaults = self::defaults();

        return self::build(self::party($defaults['owner'], $options['owner'] ?? null), $options['template'] ?? $defaults['templates'], $options, (string) ($options['className'] ?? $defaults['className']));
    }

    /**
     * The "Developed by" line, ready for your own markup.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public static function developerParts(array $options = []): array
    {
        $defaults = self::defaults();

        return self::build(self::party($defaults['developer'], $options['developer'] ?? null), $options['developerTemplate'] ?? $defaults['developerTemplates'], $options, (string) ($options['developerClassName'] ?? $defaults['developerClassName']));
    }

    /**
     * Whether the developer line belongs next to the copyright: by default only when the owner is someone else.
     *
     * @param  array<string, mixed>  $options
     */
    public static function showsDeveloper(array $options = []): bool
    {
        $defaults = self::defaults();
        $wanted = $options['showDeveloper'] ?? $defaults['showDeveloper'];

        if ($wanted !== 'auto') {
            return (bool) $wanted;
        }

        $owner = self::party($defaults['owner'], $options['owner'] ?? null);
        $developer = self::party($defaults['developer'], $options['developer'] ?? null);

        return $owner['url'] !== $developer['url'] || self::localized($owner['name'], 'en') !== self::localized($developer['name'], 'en');
    }

    /**
     * The copyright line as an HTML string, safe to print unescaped.
     *
     * @param  array<string, mixed>  $options
     */
    public static function render(array $options = []): string
    {
        return self::renderParts(self::parts($options));
    }

    /** @param  array<string, mixed>  $options */
    public static function renderDeveloper(array $options = []): string
    {
        return self::renderParts(self::developerParts($options));
    }

    /**
     * The owner's copyright on one side and the developer line on the other.
     *
     * @param  array<string, mixed>  $options
     */
    public static function renderCredits(array $options = []): string
    {
        $defaults = self::defaults();
        $copyright = self::parts($options);
        $style = $copyright['styles'] ? ' style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.5rem 1.5rem"' : '';

        return '<span class="'.self::escape((string) ($options['creditsClassName'] ?? $defaults['creditsClassName'])).'"'.$style.'>'
            .self::renderParts($copyright)
            .(self::showsDeveloper($options) ? self::renderParts(self::developerParts($options)) : '')
            .'</span>';
    }

    /**
     * Builds options from environment values; the reader receives a key such as `OWNER_NAME`.
     *
     * @param  callable(string): mixed  $read
     * @return array<string, mixed>
     */
    public static function optionsFromEnv(callable $read): array
    {
        $value = static function (string $key) use ($read): ?string {
            $found = $read($key);

            if (is_bool($found)) {
                return $found ? 'true' : 'false';
            }

            return $found === null || $found === '' ? null : (string) $found;
        };
        $number = static fn (string $key): ?int => is_numeric($value($key)) ? (int) $value($key) : null;
        $compact = static fn (array $items): array => array_filter($items, static fn (mixed $item): bool => $item !== null);
        $party = static function (string $side) use ($value, $number, $compact): ?array {
            $name = $value($side.'_NAME');
            $arabic = $value($side.'_NAME_AR');
            $light = $value($side.'_LOGO');
            $result = $compact([
                'name' => $arabic !== null ? ['en' => $name ?? $arabic, 'ar' => $arabic] : $name,
                'url' => $value($side.'_URL'),
                'logo' => $light !== null ? $compact(['light' => $light, 'dark' => $value($side.'_LOGO_DARK'), 'width' => $number($side.'_LOGO_WIDTH'), 'height' => $number($side.'_LOGO_HEIGHT')]) : null,
            ]);

            return $result === [] ? null : $result;
        };
        $show = $value('SHOW_DEVELOPER') === null ? null : strtolower((string) $value('SHOW_DEVELOPER'));

        return $compact([
            'owner' => $party('OWNER'),
            'developer' => $party('DEVELOPER'),
            'showDeveloper' => $show === null || $show === 'auto' ? null : in_array($show, ['1', 'true', 'yes', 'on'], true),
            'startYear' => $number('START_YEAR'),
            'theme' => $value('THEME'),
        ]);
    }

    /**
     * @param  array<string, mixed>  $base
     * @return array{name: mixed, url: ?string, logo: ?array<string, mixed>}
     */
    private static function party(array $base, mixed $given): array
    {
        $given = is_array($given) ? $given : [];
        $custom = self::present($given['name'] ?? null);
        $logo = null;

        if (! array_key_exists('logo', $given)) {
            $logo = $custom ? null : $base['logo'];
        } elseif (is_array($given['logo']) && (self::present($given['logo']['light'] ?? null) || ! $custom)) {
            $logo = $custom ? $given['logo'] : [...$base['logo'], ...$given['logo']];
        }

        return [
            'name' => $custom ? $given['name'] : $base['name'],
            'url' => self::present($given['url'] ?? null) ? (string) $given['url'] : ($custom ? null : (string) $base['url']),
            'logo' => $logo !== null && self::present($logo['light'] ?? null) ? $logo : null,
        ];
    }

    /**
     * @param  array{name: mixed, url: ?string, logo: ?array<string, mixed>}  $party
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private static function build(array $party, mixed $template, array $options, string $className): array
    {
        $defaults = self::defaults();
        $options = array_filter($options, static fn (mixed $value): bool => $value !== null);

        $locale = (string) ($options['locale'] ?? $defaults['locale']);
        $theme = (string) ($options['theme'] ?? $defaults['theme']);
        $name = self::localized($party['name'], $locale);

        $year = (int) ($options['year'] ?? date('Y'));
        $start = isset($options['startYear']) ? (int) $options['startYear'] : 0;
        $years = $start > 0 && $start < $year ? $start.'–'.$year : (string) $year;

        $pieces = explode('{logo}', self::fill(self::localized($template, $locale), [...(array) ($options['values'] ?? []), 'year' => $years, 'name' => $name]));
        $before = array_shift($pieces);

        $newTab = (bool) ($options['newTab'] ?? $defaults['newTab']);
        $logo = $party['logo'];
        $light = (string) ($logo['light'] ?? '');
        $dark = (string) ($logo['dark'] ?? $light);

        return [
            'locale' => $locale,
            'dir' => in_array($locale, $defaults['rtlLocales'], true) ? 'rtl' : 'ltr',
            'className' => $className,
            'styles' => (bool) ($options['styles'] ?? $defaults['styles']),
            'year' => $years,
            'showLogo' => $pieces !== [],
            'before' => $before,
            'after' => implode('', $pieces),
            'name' => $name,
            'link' => $party['url'] !== null ? [
                'href' => $party['url'],
                'target' => $newTab ? '_blank' : null,
                'rel' => $newTab ? 'noopener noreferrer' : null,
                'label' => $newTab ? self::fill(self::localized($options['newTabLabel'] ?? $defaults['newTabLabels'], $locale), ['name' => $name]) : $name,
            ] : null,
            'logo' => $logo !== null ? [
                'src' => $theme === 'dark' ? $dark : $light,
                'darkSrc' => $theme === 'auto' && $dark !== $light ? $dark : null,
                'alt' => $name,
                'width' => isset($logo['width']) ? (int) $logo['width'] : null,
                'height' => (int) ($logo['height'] ?? $defaults['owner']['logo']['height']),
            ] : null,
        ];
    }

    /** @param  array<string, mixed>  $parts */
    private static function renderParts(array $parts): string
    {
        $mark = '';

        if ($parts['showLogo']) {
            $link = $parts['link'];
            $logo = $parts['logo'];
            $inner = self::escape($parts['name']);

            if ($logo !== null) {
                $size = ($logo['width'] ? ' width="'.$logo['width'].'"' : '').' height="'.$logo['height'].'"';
                $fit = $logo['width'] ? 'height:auto' : 'width:auto';
                $imageStyle = $parts['styles'] ? ' style="display:inline-block;vertical-align:middle;'.$fit.';max-width:none"' : '';
                $image = '<img src="'.self::escape($logo['src']).'" alt="'.self::escape($logo['alt']).'"'.$size.' loading="lazy" decoding="async"'.$imageStyle.'>';
                $inner = $logo['darkSrc'] !== null
                    ? '<picture><source srcset="'.self::escape($logo['darkSrc']).'" media="(prefers-color-scheme: dark)">'.$image.'</picture>'
                    : $image;
            }

            if ($link !== null) {
                $target = $link['target'] !== null ? ' target="'.$link['target'].'" rel="'.$link['rel'].'"' : '';
                $linkStyle = $parts['styles'] && $logo !== null ? ' style="display:inline-block;vertical-align:middle;line-height:1"' : '';
                $mark = '<a href="'.self::escape($link['href']).'"'.$target.' aria-label="'.self::escape($link['label']).'"'.$linkStyle.'>'.$inner.'</a>';
            } else {
                $mark = $inner;
            }
        }

        return '<span class="'.self::escape($parts['className']).'" dir="'.$parts['dir'].'">'.self::escape($parts['before']).$mark.self::escape($parts['after']).'</span>';
    }

    private static function present(mixed $value): bool
    {
        return $value !== null && $value !== '';
    }

    private static function localized(mixed $value, string $locale): string
    {
        if (is_array($value)) {
            return (string) ($value[$locale] ?? $value['en'] ?? (reset($value) ?: ''));
        }

        return (string) $value;
    }

    /** @param  array<string, mixed>  $values */
    private static function fill(string $template, array $values): string
    {
        return (string) preg_replace_callback('/\{(\w+)\}/', static fn (array $match): string => array_key_exists($match[1], $values) ? (string) $values[$match[1]] : $match[0], $template);
    }

    private static function escape(string $value): string
    {
        return str_replace(['&', '<', '>', '"', "'"], ['&amp;', '&lt;', '&gt;', '&quot;', '&#39;'], $value);
    }
}

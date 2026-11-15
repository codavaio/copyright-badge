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
     * Everything a footer needs to print the copyright line with its own markup.
     *
     * @param  array<string, mixed>  $options
     * @return array{locale: string, dir: string, className: string, styles: bool, year: string, showLogo: bool, before: string, after: string, link: array{href: string, target: ?string, rel: ?string, label: string}, logo: array{src: string, darkSrc: ?string, alt: string, width: ?int, height: ?int}}
     */
    public static function parts(array $options = []): array
    {
        $defaults = self::defaults();
        $options = array_filter($options, static fn (mixed $value): bool => $value !== null);

        $locale = (string) ($options['locale'] ?? $defaults['locale']);
        $theme = (string) ($options['theme'] ?? $defaults['theme']);
        $given = (array) ($options['owner'] ?? []);
        $owner = [...$defaults['owner'], ...$given, 'logo' => [...$defaults['owner']['logo'], ...(array) ($given['logo'] ?? [])]];
        $name = self::localized($owner['name'], $locale);

        $year = (int) ($options['year'] ?? date('Y'));
        $start = isset($options['startYear']) ? (int) $options['startYear'] : 0;
        $years = $start > 0 && $start < $year ? $start.'–'.$year : (string) $year;

        $template = self::localized($options['template'] ?? $defaults['templates'], $locale);
        $text = self::fill($template, [...(array) ($options['values'] ?? []), 'year' => $years, 'name' => $name]);
        $pieces = explode('{logo}', $text);
        $before = array_shift($pieces);

        $newTab = (bool) ($options['newTab'] ?? $defaults['newTab']);
        $light = (string) $owner['logo']['light'];
        $dark = (string) ($owner['logo']['dark'] ?? $light);

        return [
            'locale' => $locale,
            'dir' => in_array($locale, $defaults['rtlLocales'], true) ? 'rtl' : 'ltr',
            'className' => (string) ($options['className'] ?? $defaults['className']),
            'styles' => (bool) ($options['styles'] ?? $defaults['styles']),
            'year' => $years,
            'showLogo' => $pieces !== [],
            'before' => $before,
            'after' => implode('', $pieces),
            'link' => [
                'href' => (string) $owner['url'],
                'target' => $newTab ? '_blank' : null,
                'rel' => $newTab ? 'noopener noreferrer' : null,
                'label' => $newTab ? self::fill(self::localized($options['newTabLabel'] ?? $defaults['newTabLabels'], $locale), ['name' => $name]) : $name,
            ],
            'logo' => [
                'src' => $theme === 'dark' ? $dark : $light,
                'darkSrc' => $theme === 'auto' && $dark !== $light ? $dark : null,
                'alt' => $name,
                'width' => isset($owner['logo']['width']) ? (int) $owner['logo']['width'] : null,
                'height' => isset($owner['logo']['height']) ? (int) $owner['logo']['height'] : null,
            ],
        ];
    }

    /**
     * The copyright line as an HTML string, safe to print unescaped.
     *
     * @param  array<string, mixed>  $options
     */
    public static function render(array $options = []): string
    {
        $parts = self::parts($options);
        $link = $parts['link'];
        $logo = $parts['logo'];

        $size = ($logo['width'] ? ' width="'.$logo['width'].'"' : '').($logo['height'] ? ' height="'.$logo['height'].'"' : '');
        $imageStyle = $parts['styles'] ? ' style="display:inline-block;vertical-align:middle;height:auto;max-width:none"' : '';
        $image = '<img src="'.self::escape($logo['src']).'" alt="'.self::escape($logo['alt']).'"'.$size.' loading="lazy" decoding="async"'.$imageStyle.'>';
        $picture = $logo['darkSrc'] !== null
            ? '<picture><source srcset="'.self::escape($logo['darkSrc']).'" media="(prefers-color-scheme: dark)">'.$image.'</picture>'
            : $image;
        $target = $link['target'] !== null ? ' target="'.$link['target'].'" rel="'.$link['rel'].'"' : '';
        $linkStyle = $parts['styles'] ? ' style="display:inline-block;vertical-align:middle;line-height:1"' : '';
        $anchor = '<a href="'.self::escape($link['href']).'"'.$target.' aria-label="'.self::escape($link['label']).'"'.$linkStyle.'>'.$picture.'</a>';

        return '<span class="'.self::escape($parts['className']).'" dir="'.$parts['dir'].'">'.self::escape($parts['before']).($parts['showLogo'] ? $anchor : '').self::escape($parts['after']).'</span>';
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

<?php

declare(strict_types=1);

namespace Codava\CopyrightBadge\Tests;

use Codava\CopyrightBadge\CopyrightBadge;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CopyrightBadgeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function shared(): array
    {
        return json_decode((string) file_get_contents(__DIR__.'/../cases.json'), true, flags: JSON_THROW_ON_ERROR);
    }

    /** @return iterable<string, array{string, array<string, mixed>, string}> */
    public static function renderCases(): iterable
    {
        foreach (self::shared()['render'] as $case) {
            yield $case['kind'].': '.$case['name'] => [$case['kind'], $case['options'], $case['html']];
        }
    }

    /** @return iterable<string, array{array<string, string>, array<string, mixed>}> */
    public static function envCases(): iterable
    {
        foreach (self::shared()['env'] as $case) {
            yield $case['name'] => [$case['values'], $case['options']];
        }
    }

    /** @param  array<string, mixed>  $options */
    #[DataProvider('renderCases')]
    public function test_it_renders_the_same_markup_as_the_javascript_side(string $kind, array $options, string $html): void
    {
        $rendered = match ($kind) {
            'copyright' => CopyrightBadge::render($options),
            'developer' => CopyrightBadge::renderDeveloper($options),
            'credits' => CopyrightBadge::renderCredits($options),
        };

        $this->assertSame($html, $rendered);
    }

    /**
     * @param  array<string, string>  $values
     * @param  array<string, mixed>  $options
     */
    #[DataProvider('envCases')]
    public function test_it_reads_the_environment_like_the_javascript_side(array $values, array $options): void
    {
        $this->assertSame($options, CopyrightBadge::optionsFromEnv(static fn (string $key): ?string => $values[$key] ?? null));
    }

    public function test_laravel_style_boolean_values_are_understood(): void
    {
        $this->assertSame(['showDeveloper' => false], CopyrightBadge::optionsFromEnv(static fn (string $key): mixed => $key === 'SHOW_DEVELOPER' ? false : null));
    }

    public function test_the_year_defaults_to_the_current_one(): void
    {
        $this->assertSame(date('Y'), CopyrightBadge::parts()['year']);
    }

    public function test_an_unknown_locale_falls_back_to_english(): void
    {
        $this->assertSame('All rights reserved ', CopyrightBadge::parts(['locale' => 'fr', 'year' => 2026])['before']);
    }
}

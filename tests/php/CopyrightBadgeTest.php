<?php

declare(strict_types=1);

namespace Codava\CopyrightBadge\Tests;

use Codava\CopyrightBadge\CopyrightBadge;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CopyrightBadgeTest extends TestCase
{
    /** @return iterable<string, array{array<string, mixed>, string}> */
    public static function cases(): iterable
    {
        $cases = json_decode((string) file_get_contents(__DIR__.'/../cases.json'), true, flags: JSON_THROW_ON_ERROR);

        foreach ($cases as $case) {
            yield $case['name'] => [$case['options'], $case['html']];
        }
    }

    /** @param  array<string, mixed>  $options */
    #[DataProvider('cases')]
    public function test_it_renders_the_same_markup_as_the_javascript_side(array $options, string $html): void
    {
        $this->assertSame($html, CopyrightBadge::render($options));
    }

    public function test_the_year_defaults_to_the_current_one(): void
    {
        $this->assertSame(date('Y'), CopyrightBadge::parts()['year']);
    }

    public function test_an_unknown_locale_falls_back_to_english(): void
    {
        $this->assertSame(' · All rights reserved', CopyrightBadge::parts(['locale' => 'fr', 'year' => 2026])['after']);
    }
}

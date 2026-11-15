<?php

declare(strict_types=1);

namespace Codava\CopyrightBadge\View;

use Codava\CopyrightBadge\CopyrightBadge;
use Illuminate\Support\HtmlString;
use Illuminate\View\Component;

final class Badge extends Component
{
    /**
     * @param  array<string, mixed>|null  $owner
     * @param  string|array<string, string>|null  $template
     * @param  array<string, string|int>|null  $values
     */
    public function __construct(
        public ?string $locale = null,
        public ?string $theme = null,
        public ?int $year = null,
        public ?int $startYear = null,
        public string|array|null $template = null,
        public ?array $values = null,
        public ?array $owner = null,
        public ?bool $newTab = null,
        public ?string $className = null,
        public ?bool $styles = null,
    ) {}

    public function render(): HtmlString
    {
        $given = array_filter([
            'locale' => $this->locale,
            'theme' => $this->theme,
            'year' => $this->year,
            'startYear' => $this->startYear,
            'template' => $this->template,
            'values' => $this->values,
            'owner' => $this->owner,
            'newTab' => $this->newTab,
            'className' => $this->className,
            'styles' => $this->styles,
        ], static fn (mixed $value): bool => $value !== null);

        $config = (array) config('copyright-badge', []);
        $options = [...$config, ...$given];

        if (isset($config['owner'], $given['owner'])) {
            $options['owner'] = array_replace_recursive((array) $config['owner'], $given['owner']);
        }

        $options['locale'] ??= substr((string) app()->getLocale(), 0, 2);

        return new HtmlString(CopyrightBadge::render($options));
    }
}

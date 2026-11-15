<?php

declare(strict_types=1);

namespace Codava\CopyrightBadge\View;

use Illuminate\Support\HtmlString;
use Illuminate\View\Component;

abstract class CreditComponent extends Component
{
    /**
     * @param  array<string, mixed>|null  $owner
     * @param  array<string, mixed>|null  $developer
     * @param  string|array<string, string>|null  $template
     * @param  string|array<string, string>|null  $developerTemplate
     * @param  array<string, string|int>|null  $values
     */
    public function __construct(
        public ?string $locale = null,
        public ?string $theme = null,
        public ?int $year = null,
        public ?int $startYear = null,
        public string|array|null $template = null,
        public string|array|null $developerTemplate = null,
        public ?array $values = null,
        public ?array $owner = null,
        public ?array $developer = null,
        public ?bool $showDeveloper = null,
        public ?bool $newTab = null,
        public ?string $className = null,
        public ?bool $styles = null,
    ) {}

    /** @param  array<string, mixed>  $options */
    abstract protected function html(array $options): string;

    public function render(): HtmlString
    {
        $given = array_filter([
            'locale' => $this->locale,
            'theme' => $this->theme,
            'year' => $this->year,
            'startYear' => $this->startYear,
            'template' => $this->template,
            'developerTemplate' => $this->developerTemplate,
            'values' => $this->values,
            'owner' => $this->owner,
            'developer' => $this->developer,
            'showDeveloper' => $this->showDeveloper,
            'newTab' => $this->newTab,
            'className' => $this->className,
            'styles' => $this->styles,
        ], static fn (mixed $value): bool => $value !== null);

        $options = [...(array) config('copyright-badge', []), ...$given];
        $options['locale'] ??= substr((string) app()->getLocale(), 0, 2);

        return new HtmlString($this->html($options));
    }
}

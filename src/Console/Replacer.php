<?php

namespace ResourceWizard\Console;

use Illuminate\Support\Str;

trait Replacer
{
    /**
     * @var string The prefix for search keys
     */
    private string $searchKeysPrefix = 'Dummy';

    /**
     * Str::singular($str)
     *
     * @var string The suffix for search keys
     */
    private string $searchKeysSingularSuffix = 'S';

    /**
     * Str::plural($str)
     *
     * @var string The suffix for search keys
     */
    private string $searchKeysPluralSuffix = 'P';

    /**
     * The replacements for files.
     */
    private array $searchKeys = [
        'Camel', // fooBar Str::camel($str)
        'Kebab', // foo-bar Str::kebab($str)
        'Slug', // foo-bar Str::slug($str)
        'Snake', // foo_bar Str::snake($str)
        'Studly', // FooBar Str::studly($str)
        'Title', // Foo Bar Str::title($str)
        'Text', // foo bar Str::lower(Str::title($str))
    ];

    private function getSearchKeys(): array
    {
        $keys = [];
        foreach ($this->searchKeys as $key) {
            $keys[] = $this->searchKeysPrefix.$key.$this->searchKeysSingularSuffix;
            $keys[] = $this->searchKeysPrefix.$key.$this->searchKeysPluralSuffix;
        }

        return $keys;
    }

    private function getSearchValues(string $singular, string $plural): array
    {
        $keys = [];
        foreach ($this->searchKeys as $key) {
            $keys[] = match ($key) {
                'Camel' => Str::camel($singular),
                'Kebab' => Str::kebab($singular),
                'Slug' => Str::slug($singular),
                'Snake' => Str::snake($singular),
                'Studly' => Str::studly($singular),
                'Title' => Str::title($singular),
                'Text' => Str::lower(Str::title($singular)),
                default => $singular,
            };
            $keys[] = match ($key) {
                'Camel' => Str::camel($plural),
                'Kebab' => Str::kebab($plural),
                'Slug' => Str::slug($plural),
                'Snake' => Str::snake($plural),
                'Studly' => Str::studly($plural),
                'Title' => Str::title($plural),
                'Text' => Str::lower(Str::title($plural)),
                default => $plural,
            };
        }

        return $keys;
    }
}

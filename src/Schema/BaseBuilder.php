<?php

declare(strict_types=1);

namespace Intent\Seo\Schema;

/**
 * Base class for all schema builders.
 *
 * Provides common functionality for building JSON-LD structured data.
 */
abstract class BaseBuilder
{
    /**
     * @var array<string, mixed>
     */
    protected array $data = [];

    protected string $type;

    public function __construct(string $type)
    {
        $this->type = $type;
        $this->data['@context'] = 'https://schema.org';
        $this->data['@type'] = $type;
    }

    /**
     * Build the schema as an array.
     *
     * @return array<string, mixed>
     */
    public function build(): array
    {
        return $this->data;
    }

    /**
     * Build as JSON string.
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?: '{}';
    }

    /**
     * Build as HTML script tag.
     *
     * @return string
     */
    public function toHtml(): string
    {
        $json = json_encode($this->data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return '<script type="application/ld+json">' . $json . '</script>';
    }

    /**
     * Alias for toHtml().
     *
     * @return string
     */
    public function toScript(): string
    {
        return $this->toHtml();
    }

    /**
     * Get the schema type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}

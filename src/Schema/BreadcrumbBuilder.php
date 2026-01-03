<?php

declare(strict_types=1);

namespace Intent\Seo\Schema;

/**
 * BreadcrumbList schema builder.
 *
 * @example
 * Schema::breadcrumb()
 *     ->add('Home', 'https://example.com/')
 *     ->add('Blog', 'https://example.com/blog')
 *     ->add('Article Title', 'https://example.com/blog/article')
 *     ->build();
 */
class BreadcrumbBuilder extends BaseBuilder
{
    /**
     * @var array<int, array{name: string, url: string}>
     */
    private array $items = [];

    public function __construct()
    {
        parent::__construct('BreadcrumbList');
    }

    /**
     * Add a breadcrumb item.
     *
     * @param string $name Item name/label
     * @param string $url Item URL
     * @return self
     */
    public function add(string $name, string $url): self
    {
        $this->items[] = [
            'name' => $name,
            'url' => $url,
        ];

        return $this;
    }

    /**
     * Set an ID for the breadcrumb list.
     *
     * @param string $id
     * @return self
     */
    public function id(string $id): self
    {
        $this->data['@id'] = $id;

        return $this;
    }

    /**
     * Build the schema as an array.
     *
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $itemListElement = [];

        foreach ($this->items as $position => $item) {
            $itemListElement[] = [
                '@type' => 'ListItem',
                'position' => $position + 1,
                'name' => $item['name'],
                'item' => $item['url'],
            ];
        }

        $this->data['itemListElement'] = $itemListElement;

        return $this->data;
    }
}

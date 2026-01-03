<?php

declare(strict_types=1);

namespace Intent\Seo\Schema;

/**
 * Article/BlogPosting schema builder.
 *
 * @example
 * Schema::article()
 *     ->headline('My Article')
 *     ->author('John Doe')
 *     ->image('https://example.com/image.jpg')
 *     ->publishDate('2026-01-04')
 *     ->build();
 */
class ArticleBuilder extends BaseBuilder
{
    /**
     * Set the headline/title.
     *
     * @param string $headline
     * @return self
     */
    public function headline(string $headline): self
    {
        $this->data['headline'] = $headline;

        return $this;
    }

    /**
     * Set the description.
     *
     * @param string $description
     * @return self
     */
    public function description(string $description): self
    {
        $this->data['description'] = $description;

        return $this;
    }

    /**
     * Set the author.
     *
     * @param string|array<string, mixed> $author Author name or Person object
     * @return self
     */
    public function author(string|array $author): self
    {
        if (is_string($author)) {
            $this->data['author'] = [
                '@type' => 'Person',
                'name' => $author,
            ];
        } else {
            $this->data['author'] = $author;
        }

        return $this;
    }

    /**
     * Set the image URL.
     *
     * @param string|array<int, string> $image Image URL or array of URLs
     * @return self
     */
    public function image(string|array $image): self
    {
        $this->data['image'] = $image;

        return $this;
    }

    /**
     * Set the publish date.
     *
     * @param string $date ISO 8601 date format (e.g., 2026-01-04)
     * @return self
     */
    public function publishDate(string $date): self
    {
        $this->data['datePublished'] = $date;

        return $this;
    }

    /**
     * Set the modified date.
     *
     * @param string $date ISO 8601 date format
     * @return self
     */
    public function modifiedDate(string $date): self
    {
        $this->data['dateModified'] = $date;

        return $this;
    }

    /**
     * Set the publisher.
     *
     * @param string $name Publisher name
     * @param string|null $logo Logo URL
     * @return self
     */
    public function publisher(string $name, ?string $logo = null): self
    {
        $publisher = [
            '@type' => 'Organization',
            'name' => $name,
        ];

        if ($logo !== null) {
            $publisher['logo'] = [
                '@type' => 'ImageObject',
                'url' => $logo,
            ];
        }

        $this->data['publisher'] = $publisher;

        return $this;
    }

    /**
     * Set the main entity of page URL.
     *
     * @param string $url
     * @return self
     */
    public function mainEntityOfPage(string $url): self
    {
        $this->data['mainEntityOfPage'] = [
            '@type' => 'WebPage',
            '@id' => $url,
        ];

        return $this;
    }

    /**
     * Set article section/category.
     *
     * @param string $section
     * @return self
     */
    public function articleSection(string $section): self
    {
        $this->data['articleSection'] = $section;

        return $this;
    }

    /**
     * Set keywords.
     *
     * @param array<int, string>|string $keywords
     * @return self
     */
    public function keywords(array|string $keywords): self
    {
        $this->data['keywords'] = is_array($keywords) ? implode(', ', $keywords) : $keywords;

        return $this;
    }

    /**
     * Set word count.
     *
     * @param int $count
     * @return self
     */
    public function wordCount(int $count): self
    {
        $this->data['wordCount'] = $count;

        return $this;
    }
}

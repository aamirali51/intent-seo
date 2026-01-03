<?php

declare(strict_types=1);

namespace Intent\Seo\Schema;

/**
 * WebPage schema builder.
 *
 * @example
 * Schema::webPage()
 *     ->name('About Us')
 *     ->description('Learn about our company')
 *     ->url('https://example.com/about')
 *     ->build();
 */
class WebPageBuilder extends BaseBuilder
{
    public function __construct()
    {
        parent::__construct('WebPage');
    }

    /**
     * Set the page name.
     *
     * @param string $name
     * @return self
     */
    public function name(string $name): self
    {
        $this->data['name'] = $name;

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
     * Set the URL.
     *
     * @param string $url
     * @return self
     */
    public function url(string $url): self
    {
        $this->data['url'] = $url;

        return $this;
    }

    /**
     * Set the primary image.
     *
     * @param string $image
     * @return self
     */
    public function image(string $image): self
    {
        $this->data['primaryImageOfPage'] = [
            '@type' => 'ImageObject',
            'url' => $image,
        ];

        return $this;
    }

    /**
     * Set the last reviewed date.
     *
     * @param string $date ISO 8601 format
     * @return self
     */
    public function lastReviewed(string $date): self
    {
        $this->data['lastReviewed'] = $date;

        return $this;
    }

    /**
     * Set breadcrumb reference.
     *
     * @param string $id Breadcrumb @id
     * @return self
     */
    public function breadcrumb(string $id): self
    {
        $this->data['breadcrumb'] = ['@id' => $id];

        return $this;
    }

    /**
     * Set the page as part of a website.
     *
     * @param string $websiteName
     * @param string $websiteUrl
     * @return self
     */
    public function isPartOf(string $websiteName, string $websiteUrl): self
    {
        $this->data['isPartOf'] = [
            '@type' => 'WebSite',
            'name' => $websiteName,
            'url' => $websiteUrl,
        ];

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace Intent\Seo\Schema;

/**
 * Product schema builder for e-commerce.
 *
 * @example
 * Schema::product()
 *     ->name('Widget Pro')
 *     ->description('The best widget ever')
 *     ->image('https://example.com/widget.jpg')
 *     ->price(99.99, 'USD')
 *     ->availability('InStock')
 *     ->build();
 */
class ProductBuilder extends BaseBuilder
{
    public function __construct()
    {
        parent::__construct('Product');
    }

    /**
     * Set the product name.
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
     * Set product image(s).
     *
     * @param string|array<int, string> $image
     * @return self
     */
    public function image(string|array $image): self
    {
        $this->data['image'] = $image;

        return $this;
    }

    /**
     * Set the SKU.
     *
     * @param string $sku
     * @return self
     */
    public function sku(string $sku): self
    {
        $this->data['sku'] = $sku;

        return $this;
    }

    /**
     * Set the brand.
     *
     * @param string $brand
     * @return self
     */
    public function brand(string $brand): self
    {
        $this->data['brand'] = [
            '@type' => 'Brand',
            'name' => $brand,
        ];

        return $this;
    }

    /**
     * Set the price and currency.
     *
     * @param float $price
     * @param string $currency ISO 4217 currency code (e.g., USD, EUR)
     * @return self
     */
    public function price(float $price, string $currency = 'USD'): self
    {
        $this->data['offers'] = [
            '@type' => 'Offer',
            'price' => number_format($price, 2, '.', ''),
            'priceCurrency' => $currency,
        ];

        return $this;
    }

    /**
     * Set availability status.
     *
     * @param string $availability InStock, OutOfStock, PreOrder, etc.
     * @return self
     */
    public function availability(string $availability): self
    {
        if (!isset($this->data['offers'])) {
            $this->data['offers'] = ['@type' => 'Offer'];
        }

        $this->data['offers']['availability'] = 'https://schema.org/' . $availability;

        return $this;
    }

    /**
     * Set the product URL.
     *
     * @param string $url
     * @return self
     */
    public function url(string $url): self
    {
        if (!isset($this->data['offers'])) {
            $this->data['offers'] = ['@type' => 'Offer'];
        }

        $this->data['offers']['url'] = $url;

        return $this;
    }

    /**
     * Set aggregate rating.
     *
     * @param float $rating Average rating
     * @param int $reviewCount Number of reviews
     * @return self
     */
    public function rating(float $rating, int $reviewCount): self
    {
        $this->data['aggregateRating'] = [
            '@type' => 'AggregateRating',
            'ratingValue' => number_format($rating, 1, '.', ''),
            'reviewCount' => $reviewCount,
        ];

        return $this;
    }
}

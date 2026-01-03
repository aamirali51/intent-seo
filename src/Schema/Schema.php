<?php

declare(strict_types=1);

namespace Intent\Seo\Schema;

/**
 * Fluent Schema.org builder factory for Intent Framework.
 *
 * Creates structured data builders for common types.
 *
 * @example
 * $schema = Schema::article()
 *     ->headline('My Article Title')
 *     ->author('John Doe')
 *     ->publishDate('2026-01-04')
 *     ->build();
 */
class Schema
{
    /**
     * Create an Article schema builder.
     *
     * @return ArticleBuilder
     */
    public static function article(): ArticleBuilder
    {
        return new ArticleBuilder('Article');
    }

    /**
     * Create a BlogPosting schema builder.
     *
     * @return ArticleBuilder
     */
    public static function blogPosting(): ArticleBuilder
    {
        return new ArticleBuilder('BlogPosting');
    }

    /**
     * Create a WebPage schema builder.
     *
     * @return WebPageBuilder
     */
    public static function webPage(): WebPageBuilder
    {
        return new WebPageBuilder();
    }

    /**
     * Create a BreadcrumbList schema builder.
     *
     * @return BreadcrumbBuilder
     */
    public static function breadcrumb(): BreadcrumbBuilder
    {
        return new BreadcrumbBuilder();
    }

    /**
     * Create an Organization schema builder.
     *
     * @return OrganizationBuilder
     */
    public static function organization(): OrganizationBuilder
    {
        return new OrganizationBuilder();
    }

    /**
     * Create a Product schema builder.
     *
     * @return ProductBuilder
     */
    public static function product(): ProductBuilder
    {
        return new ProductBuilder();
    }
}

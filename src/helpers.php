<?php

declare(strict_types=1);

/**
 * Intent SEO Helper Functions
 *
 * Add this to your helpers.php or include in your application bootstrap.
 */

use Intent\Seo\SeoManager;
use Intent\Seo\Config\SeoConfig;
use Core\Registry;

if (!function_exists('seo')) {
    /**
     * Get the SeoManager instance (singleton, lazy-loaded).
     *
     * @param SeoConfig|null $config Optional configuration for first initialization
     * @return SeoManager
     *
     * @example
     * // In handler
     * seo()->title('My Page Title')
     *      ->description('Page description')
     *      ->canonical('https://example.com/page');
     *
     * // In Twig view
     * {{ seo().render()|raw }}
     */
    function seo(?SeoConfig $config = null): SeoManager
    {
        // Check if already in Registry (set by middleware)
        if (class_exists(Registry::class) && Registry::has('seo')) {
            return Registry::get('seo');
        }

        // Lazy initialization
        static $instance = null;

        if ($instance === null) {
            $instance = new SeoManager($config);

            // Store in Registry if available
            if (class_exists(Registry::class)) {
                Registry::set('seo', $instance);
            }
        }

        return $instance;
    }
}

if (!function_exists('sitemap')) {
    /**
     * Create a new SitemapGenerator instance.
     *
     * @return \Intent\Seo\Sitemap\SitemapGenerator
     */
    function sitemap(): \Intent\Seo\Sitemap\SitemapGenerator
    {
        return new \Intent\Seo\Sitemap\SitemapGenerator();
    }
}

if (!function_exists('robots')) {
    /**
     * Create a new Robots instance.
     *
     * @return \Intent\Seo\Robots
     */
    function robots(): \Intent\Seo\Robots
    {
        return new \Intent\Seo\Robots();
    }
}

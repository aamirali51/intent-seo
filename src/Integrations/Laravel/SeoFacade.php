<?php

declare(strict_types=1);

namespace Intent\Seo\Integrations\Laravel;

use Illuminate\Support\Facades\Facade;

/**
 * Laravel facade for the SEO manager.
 *
 * @method static \Intent\Seo\SeoManager analyze(string $content, array $metadata = [])
 * @method static string generateTitle(string|null $customTitle = null)
 * @method static string generateDescription(string|null $customDescription = null)
 * @method static array generateMetaTags(array $customMeta = [])
 * @method static array generateAll(array $overrides = [])
 * @method static string renderMetaTags(array|null $seoData = null)
 * @method static \Intent\Seo\Config\SeoConfig getConfig()
 * @method static array getPageData()
 * @method static \Intent\Seo\SeoManager setPageData(array $data)
 * @method static \Intent\Seo\SeoManager withConfig(\Intent\Seo\Config\SeoConfig $config)
 *
 * @see \Intent\Seo\SeoManager
 */
class SeoFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'seo';
    }
}

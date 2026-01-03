<?php

declare(strict_types=1);

namespace Intent\Seo\Sitemap;

/**
 * XML Sitemap Generator for Intent Framework CMS applications.
 *
 * Generates valid XML sitemaps with support for images and caching.
 *
 * @example
 * $sitemap = new SitemapGenerator();
 * $sitemap->addUrl('https://example.com/', '2026-01-04', 'daily', 1.0);
 * $sitemap->addUrl('https://example.com/about', null, 'monthly', 0.8, [
 *     ['loc' => 'https://example.com/image.jpg', 'title' => 'Hero Image']
 * ]);
 * echo $sitemap->generate();
 */
class SitemapGenerator
{
    private const XML_HEADER = '<?xml version="1.0" encoding="UTF-8"?>';
    private const XMLNS = 'http://www.sitemaps.org/schemas/sitemap/0.9';
    private const XMLNS_IMAGE = 'http://www.google.com/schemas/sitemap-image/1.1';

    /**
     * @var array<int, array{loc: string, lastmod: ?string, changefreq: ?string, priority: ?float, images: array<int, array{loc: string, title?: string, caption?: string}>}>
     */
    private array $urls = [];

    private bool $hasImages = false;

    /**
     * Add a URL to the sitemap.
     *
     * @param string $loc The URL location (absolute URL)
     * @param string|null $lastmod Last modification date (W3C datetime format: YYYY-MM-DD)
     * @param string|null $changefreq Change frequency: always, hourly, daily, weekly, monthly, yearly, never
     * @param float|null $priority Priority between 0.0 and 1.0
     * @param array<int, array{loc: string, title?: string, caption?: string}> $images Image data for image sitemap
     * @return self
     */
    public function addUrl(
        string $loc,
        ?string $lastmod = null,
        ?string $changefreq = null,
        ?float $priority = null,
        array $images = []
    ): self {
        if (!empty($images)) {
            $this->hasImages = true;
        }

        $this->urls[] = [
            'loc' => $loc,
            'lastmod' => $lastmod,
            'changefreq' => $changefreq,
            'priority' => $priority,
            'images' => $images,
        ];

        return $this;
    }

    /**
     * Generate the complete XML sitemap.
     *
     * @return string Valid XML sitemap content
     */
    public function generate(): string
    {
        $xml = self::XML_HEADER . "\n";
        $xml .= '<urlset xmlns="' . self::XMLNS . '"';

        if ($this->hasImages) {
            $xml .= ' xmlns:image="' . self::XMLNS_IMAGE . '"';
        }

        $xml .= ">\n";

        foreach ($this->urls as $url) {
            $xml .= $this->generateUrlEntry($url);
        }

        $xml .= "</urlset>\n";

        return $xml;
    }

    /**
     * Generate and save sitemap to file.
     *
     * @param string $path File path to save (default: storage/cache/sitemap.xml)
     * @return bool True if saved successfully
     */
    public function save(string $path = ''): bool
    {
        if ($path === '') {
            $path = $this->getDefaultPath();
        }

        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return file_put_contents($path, $this->generate()) !== false;
    }

    /**
     * Load sitemap from cache if exists and is fresh.
     *
     * @param int $maxAge Maximum age in seconds (default: 3600 = 1 hour)
     * @param string $path File path (default: storage/cache/sitemap.xml)
     * @return string|null Cached content or null if stale/missing
     */
    public function loadCached(int $maxAge = 3600, string $path = ''): ?string
    {
        if ($path === '') {
            $path = $this->getDefaultPath();
        }

        if (!file_exists($path)) {
            return null;
        }

        $age = time() - filemtime($path);
        if ($age > $maxAge) {
            return null;
        }

        $content = file_get_contents($path);
        return $content !== false ? $content : null;
    }

    /**
     * Get sitemap from cache or generate fresh.
     *
     * @param int $maxAge Maximum cache age in seconds
     * @return string Sitemap XML content
     */
    public function getOrGenerate(int $maxAge = 3600): string
    {
        $cached = $this->loadCached($maxAge);
        if ($cached !== null) {
            return $cached;
        }

        $xml = $this->generate();
        $this->save();

        return $xml;
    }

    /**
     * Get number of URLs in sitemap.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->urls);
    }

    /**
     * Clear all URLs.
     *
     * @return self
     */
    public function clear(): self
    {
        $this->urls = [];
        $this->hasImages = false;

        return $this;
    }

    /**
     * Generate a single URL entry.
     *
     * @param array{loc: string, lastmod: ?string, changefreq: ?string, priority: ?float, images: array<int, array{loc: string, title?: string, caption?: string}>} $url
     * @return string
     */
    private function generateUrlEntry(array $url): string
    {
        $xml = "  <url>\n";
        $xml .= "    <loc>" . $this->escape($url['loc']) . "</loc>\n";

        if ($url['lastmod'] !== null) {
            $xml .= "    <lastmod>" . $this->escape($url['lastmod']) . "</lastmod>\n";
        }

        if ($url['changefreq'] !== null) {
            $xml .= "    <changefreq>" . $this->escape($url['changefreq']) . "</changefreq>\n";
        }

        if ($url['priority'] !== null) {
            $xml .= "    <priority>" . number_format($url['priority'], 1) . "</priority>\n";
        }

        foreach ($url['images'] as $image) {
            $xml .= $this->generateImageEntry($image);
        }

        $xml .= "  </url>\n";

        return $xml;
    }

    /**
     * Generate an image entry.
     *
     * @param array{loc: string, title?: string, caption?: string} $image
     * @return string
     */
    private function generateImageEntry(array $image): string
    {
        $xml = "    <image:image>\n";
        $xml .= "      <image:loc>" . $this->escape($image['loc']) . "</image:loc>\n";

        if (isset($image['title']) && $image['title'] !== '') {
            $xml .= "      <image:title>" . $this->escape($image['title']) . "</image:title>\n";
        }

        if (isset($image['caption']) && $image['caption'] !== '') {
            $xml .= "      <image:caption>" . $this->escape($image['caption']) . "</image:caption>\n";
        }

        $xml .= "    </image:image>\n";

        return $xml;
    }

    /**
     * Escape special XML characters.
     *
     * @param string $value
     * @return string
     */
    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /**
     * Get default cache path.
     *
     * @return string
     */
    private function getDefaultPath(): string
    {
        // Use BASE_PATH if defined (Intent Framework), otherwise fallback
        $basePath = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 4);

        return $basePath . '/storage/cache/sitemap.xml';
    }
}

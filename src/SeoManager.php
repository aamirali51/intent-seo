<?php

declare(strict_types=1);

namespace Intent\Seo;

use Psr\SimpleCache\CacheInterface;
use Intent\Seo\Analyzers\ContentAnalyzer;
use Intent\Seo\Analyzers\ImageAnalyzer;
use Intent\Seo\Cache\SeoCache;
use Intent\Seo\Config\SeoConfig;
use Intent\Seo\Generators\DescriptionGenerator;
use Intent\Seo\Generators\ImageAltGenerator;
use Intent\Seo\Generators\MetaTagGenerator;
use Intent\Seo\Generators\StructuredDataGenerator;
use Intent\Seo\Generators\TitleGenerator;

/**
 * Main SEO Manager class that orchestrates SEO optimization tasks.
 *
 * This class provides the primary interface for generating SEO-optimized
 * content including titles, descriptions, meta tags, and more using either
 * AI-powered analysis or manual configuration patterns.
 */
class SeoManager
{
    private SeoConfig $config;
    private ?SeoCache $cache = null;
    private ContentAnalyzer $contentAnalyzer;
    private ImageAnalyzer $imageAnalyzer;
    private TitleGenerator $titleGenerator;
    private DescriptionGenerator $descriptionGenerator;
    private MetaTagGenerator $metaTagGenerator;
    private ImageAltGenerator $imageAltGenerator;
    private StructuredDataGenerator $structuredDataGenerator;

    /**
     * @var array<string, mixed>
     */
    private array $pageData = [];

    // ===== Advanced Meta Properties =====
    private string $customTitle = '';
    private string $customDescription = '';
    private string $canonicalUrl = '';
    private string $nextUrl = '';
    private string $prevUrl = '';
    private bool $robotsIndex = true;
    private bool $robotsFollow = true;

    /**
     * @var array<string, string>
     */
    private array $openGraphTags = [];

    /**
     * @var array<string, string>
     */
    private array $twitterTags = [];

    /**
     * @var array<int, Schema\BaseBuilder>
     */
    private array $schemas = [];

    public function __construct(
        ?SeoConfig $config = null,
        ?CacheInterface $cacheImplementation = null,
        ?ContentAnalyzer $contentAnalyzer = null,
        ?ImageAnalyzer $imageAnalyzer = null,
        ?TitleGenerator $titleGenerator = null,
        ?DescriptionGenerator $descriptionGenerator = null,
        ?MetaTagGenerator $metaTagGenerator = null,
        ?ImageAltGenerator $imageAltGenerator = null,
        ?StructuredDataGenerator $structuredDataGenerator = null
    ) {
        $this->config = $config ?? new SeoConfig();

        // Initialize cache if provided
        if ($cacheImplementation !== null) {
            $this->cache = new SeoCache($this->config, $cacheImplementation);
        }

        $this->imageAnalyzer = $imageAnalyzer ?? new ImageAnalyzer($this->config);
        $this->contentAnalyzer = $contentAnalyzer ?? new ContentAnalyzer($this->config, $this->imageAnalyzer, $this->cache);
        $this->titleGenerator = $titleGenerator ?? new TitleGenerator($this->config);
        $this->descriptionGenerator = $descriptionGenerator ?? new DescriptionGenerator($this->config);
        $this->metaTagGenerator = $metaTagGenerator ?? new MetaTagGenerator($this->config);
        $this->imageAltGenerator = $imageAltGenerator ?? new ImageAltGenerator($this->config);
        $this->structuredDataGenerator = $structuredDataGenerator ?? new StructuredDataGenerator($this->config);
    }

    /**
     * Analyze page content and set it for SEO generation.
     *
     * @param string $content The HTML or text content to analyze
     * @param array<string, mixed> $metadata Additional metadata about the page
     * @return self
     */
    public function analyze(string $content, array $metadata = []): self
    {
        $this->pageData = $this->contentAnalyzer->analyze($content, $metadata);

        return $this;
    }

    /**
     * Generate an optimized title for the current page.
     *
     * @param string|null $customTitle Custom title to use instead of generated one
     * @return string
     */
    public function generateTitle(?string $customTitle = null): string
    {
        if ($customTitle !== null) {
            return $this->titleGenerator->generateCustom($customTitle, $this->pageData);
        }

        return $this->titleGenerator->generate($this->pageData);
    }

    /**
     * Generate an optimized description for the current page.
     *
     * @param string|null $customDescription Custom description to use instead of generated one
     * @return string
     */
    public function generateDescription(?string $customDescription = null): string
    {
        if ($customDescription !== null) {
            return $this->descriptionGenerator->generateCustom($customDescription, $this->pageData);
        }

        return $this->descriptionGenerator->generate($this->pageData);
    }

    /**
     * Generate all meta tags for the current page.
     *
     * @param array<string, mixed> $customMeta Custom meta tags to merge with generated ones
     * @return array<string, string>
     */
    public function generateMetaTags(array $customMeta = []): array
    {
        $generated = $this->metaTagGenerator->generate($this->pageData);

        return array_merge($generated, $customMeta);
    }

    /**
     * Generate alt text for images in the current page.
     *
     * @return array<string, string> Map of image src to generated alt text
     */
    public function generateImageAltTexts(): array
    {
        if (empty($this->pageData['images'])) {
            return [];
        }

        return $this->imageAltGenerator->generateForImages(
            $this->pageData['images'],
            $this->pageData
        );
    }

    /**
     * Generate structured data for the current page.
     *
     * @return array<array<string, mixed>>
     */
    public function generateStructuredData(): array
    {
        if (!$this->config->get('structured_data.enabled', true)) {
            return [];
        }

        $schemas = $this->structuredDataGenerator->generate($this->pageData);

        return array_map(fn ($schema) => $schema->toArray(), $schemas);
    }

    /**
     * Get structured data as HTML script tags.
     *
     * @return string
     */
    public function renderStructuredData(): string
    {
        if (!$this->config->get('structured_data.enabled', true)) {
            return '';
        }

        $schemas = $this->structuredDataGenerator->generate($this->pageData);

        return implode("\n", array_map(fn ($schema) => $schema->toHtml(), $schemas));
    }

    /**
     * Generate complete SEO data for the current page.
     *
     * @param array<string, mixed> $overrides Custom values to override generated ones
     * @return array<string, mixed>
     */
    public function generateAll(array $overrides = []): array
    {
        $seoData = [
            'title' => $this->generateTitle($overrides['title'] ?? null),
            'description' => $this->generateDescription($overrides['description'] ?? null),
            'meta_tags' => $this->generateMetaTags($overrides['meta_tags'] ?? []),
            'image_alt_texts' => $this->generateImageAltTexts(),
            'structured_data' => $this->generateStructuredData(),
            'page_data' => $this->pageData,
        ];

        return array_merge($seoData, $overrides);
    }

    /**
     * Render HTML meta tags from generated SEO data.
     *
     * @param array<string, mixed> $seoData SEO data array (optional, uses generated if not provided)
     * @return string
     */
    public function renderMetaTags(?array $seoData = null): string
    {
        if ($seoData === null) {
            $seoData = $this->generateAll();
        }

        $html = '';

        // Title tag
        if (!empty($seoData['title'])) {
            $html .= sprintf('<title>%s</title>', htmlspecialchars($seoData['title'], ENT_QUOTES, 'UTF-8')) . "\n";
        }

        // Meta description
        if (!empty($seoData['description'])) {
            $html .= sprintf(
                '<meta name="description" content="%s">',
                htmlspecialchars($seoData['description'], ENT_QUOTES, 'UTF-8')
            ) . "\n";
        }

        // Other meta tags
        if (!empty($seoData['meta_tags'])) {
            foreach ($seoData['meta_tags'] as $name => $content) {
                if (str_starts_with($name, 'og:') || str_starts_with($name, 'twitter:')) {
                    $html .= sprintf(
                        '<meta property="%s" content="%s">',
                        htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
                        htmlspecialchars($content, ENT_QUOTES, 'UTF-8')
                    ) . "\n";
                } else {
                    $html .= sprintf(
                        '<meta name="%s" content="%s">',
                        htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
                        htmlspecialchars($content, ENT_QUOTES, 'UTF-8')
                    ) . "\n";
                }
            }
        }

        return trim($html);
    }

    /**
     * Get the current configuration.
     *
     * @return SeoConfig
     */
    public function getConfig(): SeoConfig
    {
        return $this->config;
    }

    /**
     * Get the current page data.
     *
     * @return array<string, mixed>
     */
    public function getPageData(): array
    {
        return $this->pageData;
    }

    /**
     * Set custom page data.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public function setPageData(array $data): self
    {
        $this->pageData = $data;

        return $this;
    }

    /**
     * Create a new instance with a different configuration.
     *
     * @param SeoConfig $config
     * @return self
     */
    public function withConfig(SeoConfig $config): self
    {
        // Note: Cache is not preserved when creating with new config.
        // Users should use setCache() after withConfig() if they want caching.
        $imageAnalyzer = new ImageAnalyzer($config);

        return new self(
            $config,
            null,
            new ContentAnalyzer($config, $imageAnalyzer, null),
            $imageAnalyzer,
            new TitleGenerator($config),
            new DescriptionGenerator($config),
            new MetaTagGenerator($config),
            new ImageAltGenerator($config),
            new StructuredDataGenerator($config)
        );
    }

    /**
     * Set a cache implementation.
     *
     * @param CacheInterface $cacheImplementation PSR-16 cache implementation
     * @return self
     */
    public function setCache(CacheInterface $cacheImplementation): self
    {
        $this->cache = new SeoCache($this->config, $cacheImplementation);

        // Update ContentAnalyzer with the new cache
        $this->contentAnalyzer = new ContentAnalyzer($this->config, $this->imageAnalyzer, $this->cache);

        return $this;
    }

    /**
     * Get the cache instance.
     *
     * @return SeoCache|null
     */
    public function getCache(): ?SeoCache
    {
        return $this->cache;
    }

    // ===== Advanced Meta Controls =====

    /**
     * Set the page title directly.
     *
     * @param string $title
     * @return self
     */
    public function title(string $title): self
    {
        $this->customTitle = $title;

        return $this;
    }

    /**
     * Set the page description directly.
     *
     * @param string $description
     * @return self
     */
    public function description(string $description): self
    {
        $this->customDescription = $description;

        return $this;
    }

    /**
     * Set the canonical URL.
     *
     * @param string $url
     * @return self
     */
    public function canonical(string $url): self
    {
        $this->canonicalUrl = $url;

        return $this;
    }

    /**
     * Set the next page URL (for pagination).
     *
     * @param string $url
     * @return self
     */
    public function next(string $url): self
    {
        $this->nextUrl = $url;

        return $this;
    }

    /**
     * Set the previous page URL (for pagination).
     *
     * @param string $url
     * @return self
     */
    public function prev(string $url): self
    {
        $this->prevUrl = $url;

        return $this;
    }

    /**
     * Set robots meta tag.
     *
     * @param bool $index Allow indexing
     * @param bool $follow Allow following links
     * @return self
     */
    public function robots(bool $index = true, bool $follow = true): self
    {
        $this->robotsIndex = $index;
        $this->robotsFollow = $follow;

        return $this;
    }

    /**
     * Set Open Graph meta tag.
     *
     * @param string $property Property name (without og: prefix)
     * @param string $content Content value
     * @return self
     */
    public function openGraph(string $property, string $content): self
    {
        $this->openGraphTags[$property] = $content;

        return $this;
    }

    /**
     * Set Twitter Card meta tag.
     *
     * @param string $property Property name (without twitter: prefix)
     * @param string $content Content value
     * @return self
     */
    public function twitter(string $property, string $content): self
    {
        $this->twitterTags[$property] = $content;

        return $this;
    }

    /**
     * Add a Schema.org structured data builder.
     *
     * @param Schema\BaseBuilder $schema
     * @return self
     */
    public function addSchema(Schema\BaseBuilder $schema): self
    {
        $this->schemas[] = $schema;

        return $this;
    }

    /**
     * Set page image (for OG and Twitter).
     *
     * @param string $url Image URL
     * @return self
     */
    public function image(string $url): self
    {
        $this->openGraphTags['image'] = $url;
        $this->twitterTags['image'] = $url;

        return $this;
    }

    /**
     * Render all SEO tags for the <head> section.
     *
     * Usage in Twig: {{ seo().render()|raw }}
     *
     * @return string Complete HTML for <head>
     */
    public function render(): string
    {
        $html = "\n<!-- Intent SEO -->\n";

        // Title
        $title = $this->customTitle !== '' ? $this->customTitle : $this->generateTitle();
        if ($title !== '') {
            $html .= '<title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . "</title>\n";
        }

        // Description
        $desc = $this->customDescription !== '' ? $this->customDescription : $this->generateDescription();
        if ($desc !== '') {
            $html .= '<meta name="description" content="' . htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') . "\">\n";
        }

        // Canonical
        if ($this->canonicalUrl !== '') {
            $html .= '<link rel="canonical" href="' . htmlspecialchars($this->canonicalUrl, ENT_QUOTES, 'UTF-8') . "\">\n";
        }

        // Pagination links
        if ($this->prevUrl !== '') {
            $html .= '<link rel="prev" href="' . htmlspecialchars($this->prevUrl, ENT_QUOTES, 'UTF-8') . "\">\n";
        }
        if ($this->nextUrl !== '') {
            $html .= '<link rel="next" href="' . htmlspecialchars($this->nextUrl, ENT_QUOTES, 'UTF-8') . "\">\n";
        }

        // Robots
        $robotsValue = ($this->robotsIndex ? 'index' : 'noindex') . ', ' . ($this->robotsFollow ? 'follow' : 'nofollow');
        $html .= '<meta name="robots" content="' . $robotsValue . "\">\n";

        // Open Graph tags
        if (!isset($this->openGraphTags['title']) && $title !== '') {
            $this->openGraphTags['title'] = $title;
        }
        if (!isset($this->openGraphTags['description']) && $desc !== '') {
            $this->openGraphTags['description'] = $desc;
        }
        foreach ($this->openGraphTags as $property => $content) {
            $html .= '<meta property="og:' . htmlspecialchars($property, ENT_QUOTES, 'UTF-8') 
                . '" content="' . htmlspecialchars($content, ENT_QUOTES, 'UTF-8') . "\">\n";
        }

        // Twitter tags
        if (!isset($this->twitterTags['card'])) {
            $this->twitterTags['card'] = 'summary_large_image';
        }
        if (!isset($this->twitterTags['title']) && $title !== '') {
            $this->twitterTags['title'] = $title;
        }
        if (!isset($this->twitterTags['description']) && $desc !== '') {
            $this->twitterTags['description'] = $desc;
        }
        foreach ($this->twitterTags as $property => $content) {
            $html .= '<meta name="twitter:' . htmlspecialchars($property, ENT_QUOTES, 'UTF-8') 
                . '" content="' . htmlspecialchars($content, ENT_QUOTES, 'UTF-8') . "\">\n";
        }

        // Schema.org structured data
        foreach ($this->schemas as $schema) {
            $html .= $schema->toHtml() . "\n";
        }

        // Legacy structured data
        $html .= $this->renderStructuredData();

        $html .= "<!-- /Intent SEO -->\n";

        return $html;
    }

    /**
     * Get the custom title.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->customTitle !== '' ? $this->customTitle : $this->generateTitle();
    }

    /**
     * Get the custom description.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->customDescription !== '' ? $this->customDescription : $this->generateDescription();
    }
}

<?php

declare(strict_types=1);

namespace Intent\Seo;

/**
 * Robots.txt Generator for Intent Framework applications.
 *
 * @example
 * $robots = new Robots();
 * $robots->allow('/');
 * $robots->disallow('/admin');
 * $robots->disallow('/api');
 * $robots->sitemap('https://example.com/sitemap.xml');
 * echo $robots->generate();
 */
class Robots
{
    private string $userAgent = '*';

    /**
     * @var array<int, array{type: string, path: string}>
     */
    private array $rules = [];

    /**
     * @var array<int, string>
     */
    private array $sitemaps = [];

    /**
     * Set the user agent for subsequent rules.
     *
     * @param string $userAgent User agent string (default: *)
     * @return self
     */
    public function userAgent(string $userAgent = '*'): self
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * Add an Allow rule.
     *
     * @param string $path Path to allow
     * @return self
     */
    public function allow(string $path): self
    {
        $this->rules[] = [
            'type' => 'Allow',
            'path' => $path,
        ];

        return $this;
    }

    /**
     * Add a Disallow rule.
     *
     * @param string $path Path to disallow
     * @return self
     */
    public function disallow(string $path): self
    {
        $this->rules[] = [
            'type' => 'Disallow',
            'path' => $path,
        ];

        return $this;
    }

    /**
     * Add a sitemap URL.
     *
     * @param string $url Sitemap URL (absolute)
     * @return self
     */
    public function sitemap(string $url): self
    {
        $this->sitemaps[] = $url;

        return $this;
    }

    /**
     * Add crawl delay.
     *
     * @param int $seconds Delay in seconds
     * @return self
     */
    public function crawlDelay(int $seconds): self
    {
        $this->rules[] = [
            'type' => 'Crawl-delay',
            'path' => (string) $seconds,
        ];

        return $this;
    }

    /**
     * Generate the robots.txt content.
     *
     * @return string Complete robots.txt content
     */
    public function generate(): string
    {
        $lines = [];

        // User agent
        $lines[] = 'User-agent: ' . $this->userAgent;

        // Rules
        foreach ($this->rules as $rule) {
            $lines[] = $rule['type'] . ': ' . $rule['path'];
        }

        // Sitemaps (at the end)
        if (!empty($this->sitemaps)) {
            $lines[] = ''; // Empty line before sitemaps
            foreach ($this->sitemaps as $sitemap) {
                $lines[] = 'Sitemap: ' . $sitemap;
            }
        }

        return implode("\n", $lines) . "\n";
    }

    /**
     * Save robots.txt to file.
     *
     * @param string $path Path to save (default: public/robots.txt)
     * @return bool True if saved successfully
     */
    public function save(string $path = ''): bool
    {
        if ($path === '') {
            $basePath = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 4);
            $path = $basePath . '/public/robots.txt';
        }

        return file_put_contents($path, $this->generate()) !== false;
    }

    /**
     * Clear all rules and sitemaps.
     *
     * @return self
     */
    public function clear(): self
    {
        $this->rules = [];
        $this->sitemaps = [];
        $this->userAgent = '*';

        return $this;
    }

    /**
     * Create a default robots.txt for production.
     *
     * @param string $siteUrl Base site URL for sitemap
     * @return self
     */
    public static function default(string $siteUrl): self
    {
        $robots = new self();

        return $robots
            ->allow('/')
            ->disallow('/admin')
            ->disallow('/api')
            ->disallow('/storage')
            ->disallow('/*.json$')
            ->sitemap(rtrim($siteUrl, '/') . '/sitemap.xml');
    }
}

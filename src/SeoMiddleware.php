<?php

declare(strict_types=1);

namespace Intent\Seo;

use Core\Middleware;
use Core\Request;
use Core\Response;
use Core\Registry;
use Intent\Seo\Config\SeoConfig;

/**
 * SEO Middleware for Intent Framework.
 *
 * Automatically initializes SeoManager and stores it in Registry
 * for use throughout the request lifecycle.
 *
 * @example
 * // In routes.php
 * Route::get('/blog/{slug}', $handler)->middleware(SeoMiddleware::class);
 *
 * // In handler
 * $seo = Registry::get('seo');
 * $seo->title('My Page');
 */
class SeoMiddleware implements Middleware
{
    private ?SeoConfig $config;

    public function __construct(?SeoConfig $config = null)
    {
        $this->config = $config;
    }

    /**
     * Handle the middleware request.
     *
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return Response
     */
    public function handle(Request $request, Response $response, callable $next): Response
    {
        // Initialize SeoManager with config
        $config = $this->config ?? $this->loadConfig();
        $manager = new SeoManager($config);

        // Set defaults from config
        $this->applyDefaults($manager);

        // Store in Registry for global access
        Registry::set('seo', $manager);
        Registry::set('seo.manager', $manager);

        return $next($request, $response);
    }

    /**
     * Load configuration from Intent Framework config.
     *
     * @return SeoConfig
     */
    private function loadConfig(): SeoConfig
    {
        $configData = [];

        // Load from Intent's config() helper if available
        if (function_exists('config')) {
            $configData = [
                'title' => [
                    'pattern' => config('seo.title.pattern', '{title} | {site_name}'),
                    'site_name' => config('seo.title.site_name', config('app.name', 'Website')),
                    'max_length' => config('seo.title.max_length', 60),
                ],
                'description' => [
                    'max_length' => config('seo.description.max_length', 160),
                ],
                'mode' => config('seo.mode', 'manual'),
                'ai' => [
                    'provider' => config('seo.ai.provider', 'openai'),
                    'api_key' => config('seo.ai.api_key', ''),
                ],
            ];
        }

        return new SeoConfig($configData);
    }

    /**
     * Apply default SEO settings from config.
     *
     * @param SeoManager $manager
     * @return void
     */
    private function applyDefaults(SeoManager $manager): void
    {
        if (function_exists('config')) {
            // Set default robots
            $index = config('seo.robots.index', true);
            $follow = config('seo.robots.follow', true);
            $manager->robots($index, $follow);

            // Set default Open Graph type
            if ($ogType = config('seo.og.type')) {
                $manager->openGraph('type', $ogType);
            }

            // Set default Twitter card
            if ($twitterCard = config('seo.twitter.card')) {
                $manager->twitter('card', $twitterCard);
            }
        }
    }
}

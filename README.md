# Intent SEO

[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE.md)
[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://www.php.net/)
[![Intent Framework](https://img.shields.io/badge/Intent-Framework-purple.svg)](https://github.com/intent/framework)

> **AI-powered SEO optimization for Intent Framework applications.**

---

## ⚠️ Important Disclaimer

> [!CAUTION]
> **This package is a fork of [rumenx/php-seo](https://github.com/RumenDamyanov/php-seo) by [Rumen Damyanov](https://github.com/RumenDamyanov).**
> 
> It has been **modified exclusively for Intent Framework** and **will NOT work** with Laravel, Symfony, or standalone PHP projects.
> 
> **For non-Intent Framework projects, please use the original package:**
> ```bash
> composer require rumenx/php-seo
> ```
> 
> The original author deserves full credit for the excellent foundation this package is built upon. Please consider [supporting his work](https://github.com/sponsors/RumenDamyanov).

---

## ✨ Features

### 🤖 AI-Powered Automation
- **Intelligent Content Analysis**: AI reads and analyzes page content to generate optimal titles, descriptions, and meta tags
- **Image Analysis**: Automatically generates alt text and titles for images by analyzing context
- **Social Media Optimization**: Generates platform-specific meta tags (Open Graph, Twitter Cards, etc.)
- **Multi-Provider Support**: Integration with popular AI models (GPT-4o, Claude 3.5, Gemini 1.5, Grok, Ollama)

### 🔧 Manual Configuration Mode
- **Pattern-Based Generation**: Generate titles and descriptions using configurable patterns
- **Manual Override**: Full manual control over all SEO elements
- **Fallback Systems**: Graceful degradation when AI services are unavailable
- **Template-Based**: Use predefined templates for consistent SEO across pages

### 🚀 Modern PHP
- **Type-safe**: Full PHP 8.2+ type declarations and strict types
- **Fluent Interface**: Chainable methods for elegant, readable code
- **Extensible**: Plugin architecture for custom analyzers and generators
- **High Performance**: Optimized for speed with caching and lazy loading

### 💎 Advanced Features
- **PSR-16 Caching**: Built-in caching support for improved performance (80%+ faster)
- **Rate Limiting**: Automatic API throttling with token bucket algorithm
- **Structured Data**: JSON-LD generation for Schema.org (Article, WebPage, Organization, Breadcrumb)
- **Cost Optimization**: 80-90% reduction in AI API costs through intelligent caching

---

## 📦 Installation

```bash
composer require aamirali/intent-seo
```

### Requirements
- PHP 8.2 or higher
- Intent Framework
- ext-json
- ext-curl

---

## 🚀 Usage with Intent Framework

### Basic Handler Usage

```php
<?php

declare(strict_types=1);

namespace App\Handlers;

use Core\App;
use Intent\Seo\SeoManager;
use Intent\Seo\Config\SeoConfig;

class PageHandler
{
    public function show(string $slug): string
    {
        $page = App::db()->query("SELECT * FROM pages WHERE slug = ?", [$slug])->fetch();
        
        // Initialize SEO Manager
        $config = new SeoConfig([
            'title' => [
                'pattern' => '{title} | ' . config('app.name'),
                'max_length' => 60,
            ],
            'mode' => 'ai', // or 'manual', 'hybrid'
            'ai' => [
                'provider' => 'openai',
                'api_key' => config('seo.openai_api_key'),
            ],
        ]);
        
        $seo = new SeoManager($config);
        $seoData = $seo->analyze($page['content'], [
            'title' => $page['title'],
            'url' => url('/pages/' . $slug),
            'image' => $page['featured_image'] ?? null,
        ])->generateAll();
        
        return view('pages/show', [
            'page' => $page,
            'seo' => $seoData,
        ]);
    }
}
```

### Twig Template

```twig
{% extends 'layouts/base.twig' %}

{% block head %}
    <title>{{ seo.title }}</title>
    <meta name="description" content="{{ seo.description }}">
    
    {# Open Graph #}
    <meta property="og:title" content="{{ seo.og_title }}">
    <meta property="og:description" content="{{ seo.og_description }}">
    <meta property="og:image" content="{{ seo.og_image }}">
    
    {# Twitter Card #}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ seo.twitter_title }}">
    <meta name="twitter:description" content="{{ seo.twitter_description }}">
    
    {# Structured Data #}
    {{ seo.structured_data|raw }}
{% endblock %}

{% block content %}
    <article>
        <h1>{{ page.title }}</h1>
        {{ page.content|raw }}
    </article>
{% endblock %}
```

### SEO Middleware

```php
<?php

declare(strict_types=1);

namespace App\Middleware;

use Intent\Seo\SeoManager;
use Intent\Seo\Config\SeoConfig;

class SeoMiddleware
{
    public function handle(callable $next): mixed
    {
        // Set up global SEO defaults
        $config = new SeoConfig([
            'title' => [
                'site_name' => config('app.name'),
                'separator' => ' | ',
            ],
            'social' => [
                'og_site_name' => config('app.name'),
                'twitter_site' => config('seo.twitter_handle'),
            ],
        ]);
        
        // Store in registry for use by handlers
        \Core\Registry::set('seo.config', $config);
        \Core\Registry::set('seo.manager', new SeoManager($config));
        
        return $next();
    }
}
```

### Using Registry in Handlers

```php
<?php

use Intent\Seo\SeoManager;

class BlogHandler
{
    public function show(string $slug): string
    {
        $post = App::db()->query("SELECT * FROM posts WHERE slug = ?", [$slug])->fetch();
        
        /** @var SeoManager $seo */
        $seo = \Core\Registry::get('seo.manager');
        
        $seoData = $seo->analyze($post['content'], [
            'title' => $post['title'],
            'author' => $post['author_name'],
            'published_at' => $post['published_at'],
            'url' => url('/blog/' . $slug),
        ])->generateAll();
        
        return view('blog/show', compact('post', 'seoData'));
    }
}
```

---

## ⚙️ Configuration

### Configuration File (config/seo.php)

```php
<?php

return [
    // SEO mode: 'ai', 'manual', or 'hybrid'
    'mode' => 'hybrid',
    
    // AI Provider settings
    'ai.provider' => 'openai',
    'ai.api_key' => env('SEO_AI_API_KEY'),
    'ai.model' => 'gpt-4o-mini',
    
    // Title settings
    'title.pattern' => '{title} | {site_name}',
    'title.site_name' => env('APP_NAME', 'My Website'),
    'title.max_length' => 60,
    
    // Description settings
    'description.max_length' => 160,
    
    // Social media
    'social.og_site_name' => env('APP_NAME'),
    'social.twitter_site' => env('TWITTER_HANDLE'),
    
    // Caching
    'cache.enabled' => true,
    'cache.ttl' => 3600,
];
```

---

## 🤖 AI Providers

### OpenAI (GPT-4/5)

```php
$config = new SeoConfig([
    'ai' => [
        'provider' => 'openai',
        'api_key' => 'your-openai-api-key',
        'model' => 'gpt-4o-mini', // Best cost/performance
    ],
]);
```

### Anthropic (Claude)

```php
$config = new SeoConfig([
    'ai' => [
        'provider' => 'anthropic',
        'api_key' => 'your-anthropic-api-key',
        'model' => 'claude-3-5-sonnet-20241022',
    ],
]);
```

### Google (Gemini)

```php
$config = new SeoConfig([
    'ai' => [
        'provider' => 'google',
        'api_key' => 'your-google-api-key',
        'model' => 'gemini-1.5-flash',
    ],
]);
```

### Local AI (Ollama)

```php
$config = new SeoConfig([
    'ai' => [
        'provider' => 'ollama',
        'api_url' => 'http://localhost:11434',
        'model' => 'llama2',
    ],
]);
```

---

## 📝 Structured Data (JSON-LD)

Generate Schema.org structured data for rich snippets:

```php
use Intent\Seo\Schema\ArticleSchema;
use Intent\Seo\Schema\BreadcrumbListSchema;

// Article schema
$article = (new ArticleSchema())
    ->setHeadline('My Article Title')
    ->setDescription('Article description')
    ->setAuthor('John Doe')
    ->setDatePublished('2024-01-15T10:00:00Z')
    ->setImage('https://example.com/image.jpg');

echo $article->toScript(); // Outputs <script type="application/ld+json">...

// Breadcrumb schema
$breadcrumb = (new BreadcrumbListSchema())
    ->addItem('Home', '/', 1)
    ->addItem('Blog', '/blog', 2)
    ->addItem('Article', '/blog/article', 3);

echo $breadcrumb->toScript();
```

---

## 🧪 Testing

```bash
# Run all tests
composer test

# Run with coverage
composer test-coverage

# Run static analysis
composer analyze

# Check code style
composer style
```

---

## 🙏 Credits

This package is a fork of the excellent [php-seo](https://github.com/RumenDamyanov/php-seo) package by **[Rumen Damyanov](https://github.com/RumenDamyanov)**.

The original author has created an amazing, well-tested, and comprehensive SEO solution for PHP. This fork adapts it specifically for Intent Framework while maintaining the core functionality.

**Please consider supporting the original author:**
- ⭐ [Star the original repository](https://github.com/RumenDamyanov/php-seo)
- 💝 [Sponsor Rumen Damyanov](https://github.com/sponsors/RumenDamyanov)

---

## 📄 License

[MIT License](LICENSE.md)

**Original Work:** Copyright (c) Rumen Damyanov  
**Fork Modifications:** Copyright (c) Aamir Ali

---

## 🔗 Related

- **Original Package:** [rumenx/php-seo](https://github.com/RumenDamyanov/php-seo) - For Laravel, Symfony, and standalone PHP
- **Intent Framework:** [intent/framework](https://github.com/intent/framework) - The micro-framework this package is designed for

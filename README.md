# Intent SEO

[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE.md)
[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://www.php.net/)
[![Intent Framework](https://img.shields.io/badge/Intent-Framework-purple.svg)](https://github.com/aamirali51/Intent-Framework)
[![Packagist](https://img.shields.io/badge/Packagist-v2.0.0-orange.svg)](https://packagist.org/packages/intent/intent-seo)

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
- **Intelligent Content Analysis**: AI generates optimal titles, descriptions, and meta tags
- **Image Analysis**: Automatically generates alt text for images
- **Multi-Provider Support**: GPT-4o, Claude 3.5, Gemini 1.5, Grok, Ollama

### 🚀 CMS-Grade SEO (v2.0)
- **XML Sitemap Generator**: With image support and caching
- **Robots.txt Generator**: Fluent API
- **Schema.org Builders**: Article, WebPage, Breadcrumb, Organization, Product
- **Advanced Meta Controls**: Canonical, pagination, robots, Open Graph, Twitter Cards

### 💎 Intent Framework Integration
- **SeoMiddleware**: Auto-initializes SEO
- **Helper Functions**: `seo()`, `sitemap()`, `robots()`
- **Single Render**: `{{ seo().render()|raw }}` outputs all SEO tags

---

## 📦 Installation

```bash
composer require intent/intent-seo
```

### Requirements
- PHP 8.2+
- Intent Framework 0.6+

---

## 🚀 Quick Start

### 1. Add Middleware to Route

```php
use Intent\Seo\SeoMiddleware;

Route::get('/blog/{slug}', $handler)->middleware(SeoMiddleware::class);
```

### 2. Configure SEO in Handler

```php
seo()->title('My Page Title')
     ->description('Page description here')
     ->canonical('https://example.com/page')
     ->image('https://example.com/image.jpg')
     ->addSchema(Schema::article()
         ->headline('Article Title')
         ->author('Author Name')
         ->publishDate('2026-01-04')
         ->build());
```

### 3. Render in Twig

```twig
<!DOCTYPE html>
<html>
<head>
    {{ seo().render()|raw }}
</head>
<body>
    {% block content %}{% endblock %}
</body>
</html>
```

**Output:**
```html
<!-- Intent SEO -->
<title>My Page Title</title>
<meta name="description" content="Page description here">
<link rel="canonical" href="https://example.com/page">
<meta name="robots" content="index, follow">
<meta property="og:title" content="My Page Title">
<meta property="og:description" content="Page description here">
<meta property="og:image" content="https://example.com/image.jpg">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="My Page Title">
<script type="application/ld+json">{"@context":"https://schema.org"...}</script>
<!-- /Intent SEO -->
```

---

## 📖 Full API

### SeoManager Methods

```php
seo()->title('Page Title')              // Set title
     ->description('Description')       // Set meta description
     ->canonical('https://...')         // Set canonical URL
     ->robots(true, true)               // index, follow
     ->prev('/page?p=1')                // Previous page (pagination)
     ->next('/page?p=3')                // Next page (pagination)
     ->openGraph('type', 'article')     // Open Graph meta
     ->twitter('site', '@handle')       // Twitter Card meta
     ->image('/image.jpg')              // Social image (OG + Twitter)
     ->addSchema($schema)               // Add JSON-LD schema
     ->render();                        // Output all HTML for <head>
```

---

## 📝 XML Sitemap

```php
use Intent\Seo\Sitemap\SitemapGenerator;

Route::get('/sitemap.xml', function ($req, $res) {
    $sitemap = new SitemapGenerator();
    
    $sitemap->addUrl('https://example.com/', '2026-01-04', 'daily', 1.0);
    $sitemap->addUrl('https://example.com/about', null, 'monthly', 0.8);
    
    // Add from database
    $pages = DB::table('pages')->where('status', 'published')->get();
    foreach ($pages as $page) {
        $sitemap->addUrl(
            url('/pages/' . $page['slug']),
            $page['updated_at'],
            'weekly',
            0.7,
            [['loc' => $page['image'], 'title' => $page['title']]]  // Image sitemap
        );
    }
    
    return $res->header('Content-Type', 'application/xml')->send($sitemap->generate());
});
```

---

## 🤖 Robots.txt

```php
use Intent\Seo\Robots;

Route::get('/robots.txt', function ($req, $res) {
    $robots = Robots::default('https://example.com');
    // Or customize:
    // $robots = new Robots();
    // $robots->allow('/')->disallow('/admin')->sitemap('https://example.com/sitemap.xml');
    
    return $res->header('Content-Type', 'text/plain')->send($robots->generate());
});
```

---

## 📊 Schema.org Builders

```php
use Intent\Seo\Schema\Schema;

// Article
$article = Schema::article()
    ->headline('Article Title')
    ->author('Author Name')
    ->publishDate('2026-01-04')
    ->image('https://example.com/image.jpg')
    ->publisher('Site Name', 'https://example.com/logo.png');

seo()->addSchema($article);

// Breadcrumb
$breadcrumb = Schema::breadcrumb()
    ->add('Home', '/')
    ->add('Blog', '/blog')
    ->add('Article', '/blog/article');

seo()->addSchema($breadcrumb);

// Organization
$org = Schema::organization()
    ->name('Company Name')
    ->url('https://example.com')
    ->logo('https://example.com/logo.png')
    ->sameAs(['https://twitter.com/...', 'https://facebook.com/...']);

// Product (E-commerce)
$product = Schema::product()
    ->name('Product Name')
    ->description('Product description')
    ->price(99.99, 'USD')
    ->availability('InStock')
    ->rating(4.5, 120);
```

---

## ⚙️ Configuration

### config/seo.php

```php
return [
    'mode' => 'manual',  // 'ai', 'manual', 'hybrid'
    
    'title.pattern' => '{title} | {site_name}',
    'title.site_name' => env('APP_NAME'),
    'title.max_length' => 60,
    
    'description.max_length' => 160,
    
    'ai.provider' => 'openai',
    'ai.api_key' => env('SEO_AI_API_KEY'),
    'ai.model' => 'gpt-4o-mini',
];
```

---

## 🤖 AI Providers

```php
use Intent\Seo\Config\SeoConfig;

// OpenAI
$config = new SeoConfig([
    'mode' => 'ai',
    'ai' => ['provider' => 'openai', 'api_key' => '...', 'model' => 'gpt-4o-mini'],
]);

// Anthropic Claude
$config = new SeoConfig([
    'ai' => ['provider' => 'anthropic', 'api_key' => '...', 'model' => 'claude-3-5-sonnet'],
]);

// Google Gemini
$config = new SeoConfig([
    'ai' => ['provider' => 'google', 'api_key' => '...', 'model' => 'gemini-1.5-flash'],
]);

// Local Ollama
$config = new SeoConfig([
    'ai' => ['provider' => 'ollama', 'api_url' => 'http://localhost:11434', 'model' => 'llama2'],
]);
```

---

## 📚 Documentation

- [ARCHITECTURE.md](ARCHITECTURE.md) - Technical documentation
- [examples/routes.php](examples/routes.php) - Example routes
- [examples/views/](examples/views/) - Example Twig templates

---

## 🧪 Testing

```bash
composer test              # Run tests
composer analyze           # PHPStan analysis
composer style             # Check code style
```

---

## 🙏 Credits

This package is a fork of [php-seo](https://github.com/RumenDamyanov/php-seo) by **[Rumen Damyanov](https://github.com/RumenDamyanov)**.

**Please consider supporting the original author:**
- ⭐ [Star the original repository](https://github.com/RumenDamyanov/php-seo)
- 💝 [Sponsor Rumen Damyanov](https://github.com/sponsors/RumenDamyanov)

---

## 📄 License

[MIT License](LICENSE.md)

**Original Work:** Copyright (c) Rumen Damyanov  
**Fork Modifications:** Copyright (c) Aamir Ali

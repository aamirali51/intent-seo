# Intent SEO - Technical Architecture

> **Version:** 2.0.0  
> **PHP Version:** 8.2+  
> **Framework:** Intent Framework exclusive

---

## 1. Philosophy & Design Principles

| Principle | Implementation |
|-----------|----------------|
| **Intent-Native** | Designed exclusively for Intent Framework |
| **Zero Magic** | No annotations, attributes, or hidden behavior |
| **Fluent API** | Chainable methods for elegant code |
| **Strict Types** | `declare(strict_types=1)` everywhere |
| **PSR-4** | Standard Composer autoloading |
| **PSR-16** | Cache interface compatible |

---

## 2. Directory Structure

```
intent-seo/
├── src/
│   ├── SeoManager.php           # Main orchestrator class
│   ├── SeoMiddleware.php        # Intent Framework middleware
│   ├── Robots.php               # Robots.txt generator
│   ├── helpers.php              # Global helper functions
│   │
│   ├── Sitemap/
│   │   └── SitemapGenerator.php # XML sitemap with image support
│   │
│   ├── Schema/                  # Schema.org JSON-LD builders
│   │   ├── Schema.php           # Factory class
│   │   ├── BaseBuilder.php      # Abstract base
│   │   ├── ArticleBuilder.php   # Article/BlogPosting
│   │   ├── WebPageBuilder.php   # WebPage
│   │   ├── BreadcrumbBuilder.php# BreadcrumbList
│   │   ├── OrganizationBuilder.php
│   │   └── ProductBuilder.php   # E-commerce
│   │
│   ├── Config/
│   │   └── SeoConfig.php        # Configuration container
│   │
│   ├── Analyzers/               # Content analysis
│   │   ├── ContentAnalyzer.php
│   │   └── ImageAnalyzer.php
│   │
│   ├── Generators/              # Tag generation
│   │   ├── TitleGenerator.php
│   │   ├── DescriptionGenerator.php
│   │   ├── MetaTagGenerator.php
│   │   ├── ImageAltGenerator.php
│   │   └── StructuredDataGenerator.php
│   │
│   ├── Providers/               # AI providers
│   │   ├── OpenAiProvider.php
│   │   ├── AnthropicProvider.php
│   │   ├── GoogleProvider.php
│   │   ├── OllamaProvider.php
│   │   └── XaiProvider.php
│   │
│   ├── Cache/
│   │   ├── SeoCache.php
│   │   └── CacheKeyGenerator.php
│   │
│   └── Contracts/               # Interfaces
│       ├── AnalyzerInterface.php
│       ├── GeneratorInterface.php
│       └── ProviderInterface.php
│
├── examples/
│   ├── routes.php               # Example Intent routes
│   └── views/                   # Example Twig templates
│
├── tests/                       # Pest test suite
└── composer.json
```

---

## 3. Core Classes

### 3.1 SeoManager

Main orchestrator providing fluent interface for all SEO operations.

```php
use Intent\Seo\SeoManager;

$seo = new SeoManager();

// Fluent API
$seo->title('Page Title')
    ->description('Meta description')
    ->canonical('https://example.com/page')
    ->robots(true, true)  // index, follow
    ->image('https://example.com/image.jpg')
    ->render();
```

**Key Methods:**
- `title(string)` - Set page title
- `description(string)` - Set meta description
- `canonical(string)` - Set canonical URL
- `robots(bool, bool)` - Set index/follow
- `prev(string)`, `next(string)` - Pagination links
- `openGraph(string, string)` - OG meta tags
- `twitter(string, string)` - Twitter card tags
- `image(string)` - Set social image
- `addSchema(BaseBuilder)` - Add JSON-LD schema
- `render()` - Output all HTML for `<head>`
- `analyze(string, array)` - AI content analysis

---

### 3.2 SeoMiddleware

Intent Framework middleware that auto-initializes SEO.

```php
use Intent\Seo\SeoMiddleware;

Route::get('/blog/{slug}', $handler)->middleware(SeoMiddleware::class);

// In handler
$seo = Registry::get('seo');
$seo->title('My Post');
```

---

### 3.3 Schema Builders

Fluent builders for Schema.org JSON-LD structured data.

```php
use Intent\Seo\Schema\Schema;

// Article
$article = Schema::article()
    ->headline('Title')
    ->author('Author Name')
    ->publishDate('2026-01-04')
    ->image('https://...')
    ->build();

// Breadcrumb
$breadcrumb = Schema::breadcrumb()
    ->add('Home', '/')
    ->add('Blog', '/blog')
    ->add('Post', '/blog/post')
    ->build();

// Organization
$org = Schema::organization()
    ->name('Company')
    ->url('https://...')
    ->logo('https://.../logo.png')
    ->build();

// Product
$product = Schema::product()
    ->name('Widget')
    ->price(99.99, 'USD')
    ->availability('InStock')
    ->build();
```

---

### 3.4 SitemapGenerator

XML sitemap with image support and caching.

```php
use Intent\Seo\Sitemap\SitemapGenerator;

$sitemap = new SitemapGenerator();

$sitemap->addUrl('https://example.com/', '2026-01-04', 'daily', 1.0);
$sitemap->addUrl('https://example.com/page', null, 'weekly', 0.8, [
    ['loc' => 'https://example.com/image.jpg', 'title' => 'Image']
]);

// Generate XML
echo $sitemap->generate();

// Cache to file
$sitemap->save('storage/cache/sitemap.xml');
```

---

### 3.5 Robots

Robots.txt generator with fluent API.

```php
use Intent\Seo\Robots;

$robots = new Robots();
$robots->allow('/')
       ->disallow('/admin')
       ->disallow('/api')
       ->sitemap('https://example.com/sitemap.xml');

echo $robots->generate();

// Or use default preset
$robots = Robots::default('https://example.com');
```

---

## 4. Helper Functions

```php
// Get SeoManager (singleton)
seo()->title('Page Title')->render();

// Create SitemapGenerator
sitemap()->addUrl('...')->generate();

// Create Robots
robots()->allow('/')->generate();
```

---

## 5. Configuration

### config/seo.php (Intent Framework)
```php
return [
    'mode' => 'manual',  // 'ai', 'manual', 'hybrid'
    
    'title.pattern' => '{title} | {site_name}',
    'title.site_name' => env('APP_NAME'),
    'title.max_length' => 60,
    
    'description.max_length' => 160,
    
    'robots.index' => true,
    'robots.follow' => true,
    
    'ai.provider' => 'openai',
    'ai.api_key' => env('SEO_AI_API_KEY'),
    'ai.model' => 'gpt-4o-mini',
];
```

---

## 6. Integration with Intent Framework

### Route with SEO Middleware
```php
Route::get('/blog/{slug}', function ($req, $res, $params) {
    $post = DB::table('posts')->where('slug', $params['slug'])->first();
    
    seo()->title($post['title'])
         ->description($post['excerpt'])
         ->canonical(url('/blog/' . $params['slug']))
         ->addSchema(Schema::article()
             ->headline($post['title'])
             ->author($post['author'])
             ->build());
    
    return view('blog/show', ['post' => $post]);
})->middleware(SeoMiddleware::class);
```

### Twig Template
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

---

## 7. File Statistics

| Type | Count |
|------|-------|
| PHP Source Files | 38 |
| Schema Builders | 6 |
| AI Providers | 5 |
| Test Files | 30 |
| Lines of Code | ~3,500 |

---

## 8. Credits

**Original Package:** [rumenx/php-seo](https://github.com/RumenDamyanov/php-seo) by Rumen Damyanov  
**Intent Fork:** Modified exclusively for Intent Framework

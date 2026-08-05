<p align="center">
  <img src="../../assets/phpwind_mascot.jpg" alt="PHPWind Mascot" width="600" style="border-radius: 12px;" />
</p>

<p align="center">
  <strong>Zero-Node Tailwind CSS v3 & v4 CLI Integration for PHP</strong>
</p>

---

## 🌐 Language Options

[ English ] | [ [Português](../../docs/pt-br/README.md) ] | [ [Español](../../docs/es/README.md) ]

---

## 📌 Overview

**PHPWind** is a lightweight, zero-dependency PHP library designed to integrate **Tailwind CSS v4** and **v3** directly into any PHP application without requiring Node.js, `npm`, or `npx`.

It automatically detects your host Operating System and CPU architecture, downloads the official standalone Tailwind CSS CLI binary, and manages compilation, watching, and asset cache-busting.

---

## 🚀 Key Features

- 🚫 **Zero Node.js Required:** No `package.json`, `node_modules`, or `npm` installations.
- 📦 **Auto Binary Downloader:** Detects Windows, Linux, and macOS (x64 and ARM64) and fetches official GitHub Release binaries.
- 🔒 **Version-Aware Binary Cache:** Re-downloads the correct Tailwind CLI binary when you switch versions.
- ⚡ **CLI Companion:** Access `vendor/bin/phpwind` for `build`, `watch`, `init`, and `clean` tasks.
- 🧩 **Programmatic PHP API:** Build, watch, and manage assets directly from PHP code.
- 🎨 **HTML Asset Helper:** `phpwind_css()` function outputs `<link>` tags with MD5 cache-busting hashes.
- 📋 **Asset Manifest:** Optional JSON manifest for CDN-friendly, versioned asset URLs.
- ⚡ **On-Demand Middleware:** Automatically recompile CSS on HTTP requests in development.
- 🎼 **Symfony Native Bundle (`PHPWindBundle`):** Native Bundle, Twig extension `{{ phpwind_css() }}`, and Console commands (`bin/console phpwind:*`).
- 🚀 **Laravel Integration:** ServiceProvider, Artisan commands (`phpwind:build`, `phpwind:watch`, `phpwind:init`, `phpwind:clean`), and `@phpwind` Blade directive.
- 🧪 **Well Tested:** Comprehensive PHPUnit coverage for core components.

---

## 📥 Installation

```bash
composer require phpwind/phpwind
```

---

## 💻 Symfony Integration

Register `PHPWindBundle` in `config/bundles.php`:

```php
return [
    PHPWind\Symfony\PHPWindBundle::class => ['all' => true],
];
```

### Console Commands

```bash
php bin/console phpwind:init
php bin/console phpwind:build --minify
php bin/console phpwind:watch
php bin/console phpwind:clean
```

### Twig Template Function

```twig
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    {{ phpwind_css('css/app.css') }}
</head>
<body class="bg-slate-900 text-white">
    <h1 class="text-3xl font-bold text-sky-400">Symfony + PHPWind</h1>
</body>
</html>
```

---

## 🧩 Programmatic API

### Compile with structured output

```php
use PHPWind\Compiler\TailwindCompiler;
use PHPWind\Config\PHPWindConfig;

$config = new PHPWindConfig(
    inputCss: 'resources/css/app.css',
    outputCss: 'public/css/app.css',
    version: 'v4.0.0',
    minify: true
);

$compiler = new TailwindCompiler();

$exitCode = $compiler->compile($config);           // backward compatible
$result = $compiler->compileResult($config);       // structured result
```

### Version-aware binary management

```php
use PHPWind\Binary\BinaryManager;

$manager = new BinaryManager('vendor/bin/tailwind-cli');
$binaryPath = $manager->resolveBinaryPath('v4.0.0');
$manager->clearCachedBinary();
```

### Configuration validation

```php
use PHPWind\Config\PHPWindConfig;

$config = new PHPWindConfig(version: 'v4.0.0');
$config->validate();
```

---

## 📋 Asset Manifest

```php
use PHPWind\Manifest\AssetManifest;
use PHPWind\Helper\AssetHelper;

$manifest = AssetManifest::generate('public', ['css/app.css']);
$manifest->write('public/phpwind-manifest.json');

$manifest = AssetManifest::read('public/phpwind-manifest.json');
echo AssetHelper::cssFromManifest($manifest, 'css/app.css');
```

> [!NOTE]
> `AssetHelper::css()` still uses query-string cache busting by default. The manifest is opt-in.

---

## 🧪 Running Tests

```bash
vendor/bin/phpunit tests
```

---

## 📄 License

Licensed under the [MIT License](../../LICENSE).

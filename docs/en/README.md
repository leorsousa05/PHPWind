# 🌬️ PHPWind — Documentation (English)

> **Zero-Node Tailwind CSS v3 & v4 CLI Integration for PHP**

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
- ⚡ **CLI Companion:** Access `vendor/bin/phpwind` for `build` and `watch` tasks.
- 🔄 **Tailwind v3 & v4 Compatible:** Easily switch between versions (`v4.0.0`, `v3.4.17`, etc.).
- 🎨 **HTML Asset Helper:** `phpwind_css()` function outputs `<link>` tags with MD5 cache-busting hashes.
- ⚡ **On-Demand Middleware:** Automatically recompile CSS on HTTP requests in development.
- 🚀 **Laravel Integration:** ServiceProvider, Artisan commands (`phpwind:build`, `phpwind:watch`), and `@phpwind` Blade directive.

---

## 📥 Installation

```bash
composer require leorsousa05/phpwind
```

---

## 🛠️ CLI Usage

```bash
# Build production CSS with minification
vendor/bin/phpwind build -i resources/css/app.css -o public/css/app.css --minify

# Start dev watcher mode
vendor/bin/phpwind watch -i resources/css/app.css -o public/css/app.css

# Target a specific Tailwind version
vendor/bin/phpwind build -i resources/css/app.css -o public/css/app.css --version=v3.4.17
```

---

## 💻 Integration Examples

### Vanilla PHP

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PHPWind App</title>
    <?php echo phpwind_css('css/app.css'); ?>
</head>
<body class="bg-slate-900 text-white p-8">
    <h1 class="text-3xl font-bold text-sky-400">Tailwind CSS in PHP!</h1>
</body>
</html>
```

### Laravel

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    @phpwind('css/app.css')
</head>
<body class="bg-slate-900 text-white">
    <div class="p-8">
        <h1 class="text-2xl font-bold text-teal-400">Laravel + PHPWind</h1>
    </div>
</body>
</html>
```

---

## ⚙️ Configuration Reference

```php
use PHPWind\Config\PHPWindConfig;

$config = new PHPWindConfig(
    inputCss: 'resources/css/app.css',
    outputCss: 'public/css/app.css',
    binaryDir: 'vendor/bin/tailwind-cli',
    version: 'v4.0.0', // Accepts 'v4.0.0', 'v3.4.17', etc.
    minify: true,
    watch: false
);
```

---

## 📄 License

Licensed under the [MIT License](../../LICENSE).

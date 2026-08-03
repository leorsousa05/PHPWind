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
- ⚡ **CLI Companion:** Access `vendor/bin/phpwind` for `build`, `watch`, `init`, and `clean` tasks.
- 🔄 **Tailwind v3 & v4 Compatible:** Easily switch between versions (`v4.0.0`, `v3.4.17`, etc.).
- 🎨 **HTML Asset Helper:** `phpwind_css()` function outputs `<link>` tags with MD5 cache-busting hashes.
- ⚡ **On-Demand Middleware:** Automatically recompile CSS on HTTP requests in development.
- 🎼 **Symfony Native Bundle (`PHPWindBundle`):** Native Bundle, Twig extension `{{ phpwind_css() }}`, and Console commands (`bin/console phpwind:*`).
- 🚀 **Laravel Integration:** ServiceProvider, Artisan commands (`phpwind:build`, `phpwind:watch`, `phpwind:init`, `phpwind:clean`), and `@phpwind` Blade directive.

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

## 📄 License

Licensed under the [MIT License](../../LICENSE).

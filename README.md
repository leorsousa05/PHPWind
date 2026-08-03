# 🌬️ PHPWind

> **Zero-Node Tailwind CSS v4 CLI Integration for PHP**

[![Latest Stable Version](https://img.shields.io/packagist/v/phpwind/phpwind.svg?style=flat-square)](https://packagist.org/packages/phpwind/phpwind)
[![Total Downloads](https://img.shields.io/packagist/dt/phpwind/phpwind.svg?style=flat-square)](https://packagist.org/packages/phpwind/phpwind)
[![License](https://img.shields.io/packagist/l/phpwind/phpwind.svg?style=flat-square)](https://packagist.org/packages/phpwind/phpwind)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%208.1-777BB4.svg?style=flat-square)](https://php.net)

**PHPWind** is a standalone, lightweight PHP library that brings **Tailwind CSS v4** to any PHP application without requiring Node.js, `npm`, or `npx`.

It automatically detects your Operating System and CPU architecture, downloads the official standalone Tailwind CSS v4 CLI executable, and manages compilation, watching, and asset cache busting seamlessly.

---

## ✨ Features

- 🚫 **Zero Node.js Dependency:** No `package.json`, `node_modules`, or `npm` required.
- 📦 **Automatic Binary Downloader:** Detects OS (Windows, Linux, macOS) & Arch (x64, ARM64) and fetches the exact Tailwind v4 binary release.
- ⚡ **CLI Companion:** Built-in executable `vendor/bin/phpwind` with `build` and `watch` support.
- 🎨 **Smart Asset Helper:** Automatic HTML `<link>` tag generation with MD5 cache busting hashes.
- 🔄 **On-Demand Dev Middleware:** Recompiles CSS dynamically during incoming HTTP requests in development.
- 🚀 **Laravel First-Class Adapter:** ServiceProvider, Artisan commands (`phpwind:build`, `phpwind:watch`), and Blade directive `@phpwind`.
- 🧩 **Framework Agnostic:** Works flawlessly in Vanilla PHP, Symfony, Slim, CodeIgniter, or custom frameworks.

---

## 📥 Installation

Install the package in your project using Composer:

```bash
composer require phpwind/phpwind
```

---

## 🛠️ CLI Command Reference

Once installed, the CLI binary is available at `vendor/bin/phpwind`.

### Basic Build

```bash
vendor/bin/phpwind build -i resources/css/app.css -o public/css/app.css
```

### Production Minified Build

```bash
vendor/bin/phpwind build -i resources/css/app.css -o public/css/app.css --minify
```

### Watch Mode (Development)

Recompiles your CSS automatically whenever your templates or input CSS change:

```bash
vendor/bin/phpwind watch -i resources/css/app.css -o public/css/app.css
```

### CLI Flag Summary

| Flag | Short | Default | Description |
|---|---|---|---|
| `--input` | `-i` | `resources/css/app.css` | Entry point CSS file |
| `--output` | `-o` | `public/css/app.css` | Output CSS file location |
| `--minify` | `-m` | `false` | Enable minification for production |
| `--watch` | `-w` | `false` | Enable file watching mode |

---

## 💻 Usage & Integration

### 1. Vanilla PHP & Custom Frameworks

Use the global helper function in your HTML layouts:

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My PHP Application</title>
    <?php echo phpwind_css('css/app.css'); ?>
</head>
<body>
    <h1 class="text-3xl font-bold text-sky-500 underline">
        Hello Tailwind CSS v4!
    </h1>
</body>
</html>
```

This renders:
```html
<link rel="stylesheet" href="/css/app.css?v=8f4a1c2e">
```

### 2. Laravel Integration

PHPWind automatically registers its ServiceProvider in Laravel.

#### Publish Configuration File (Optional)

```bash
php artisan vendor:publish --tag=phpwind-config
```

This creates `config/phpwind.php`:

```php
return [
    'input_css' => resource_path('css/app.css'),
    'output_css' => public_path('css/app.css'),
    'binary_dir' => base_path('vendor/bin/tailwind-cli'),
    'version' => 'v4.0.0',
    'minify' => env('PHPWIND_MINIFY', false),
];
```

#### Artisan Commands

```bash
# Build CSS
php artisan phpwind:build

# Build CSS with Minification
php artisan phpwind:build --minify

# Start Watcher
php artisan phpwind:watch
```

#### Blade Directive

Use `@phpwind` in your Blade layout template:

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laravel App</title>
    @phpwind('css/app.css')
</head>
<body class="bg-slate-900 text-white flex items-center justify-center h-screen">
    <div class="p-8 bg-slate-800 rounded-xl shadow-2xl border border-slate-700">
        <h1 class="text-2xl font-bold text-teal-400">Powered by PHPWind & Tailwind v4</h1>
    </div>
</body>
</html>
```

### 3. On-Demand Dev Middleware

If you want CSS to compile automatically during development requests without running a background terminal watcher:

```php
use PHPWind\Config\PHPWindConfig;
use PHPWind\Middleware\OnDemandCompilerMiddleware;

$config = new PHPWindConfig(
    inputCss: 'resources/css/app.css',
    outputCss: 'public/css/app.css'
);

$middleware = new OnDemandCompilerMiddleware(
    config: $config,
    isDev: getenv('APP_ENV') === 'development'
);

$middleware->handle(function($request) {
    // Continue application execution
}, $request);
```

---

## 🏗️ Architecture & How It Works

```
                     ┌────────────────────────────────┐
                     │     PHPWind CLI / Helper       │
                     └───────────────┬────────────────┘
                                     │
                         Is binary installed locally?
                                     │
                     ┌───────────────┴────────────────┐
                     │ NO                             │ YES
                     ▼                                ▼
       ┌───────────────────────────┐     ┌───────────────────────────┐
       │ PlatformResolver &        │     │  Runner (proc_open)       │
       │ Downloader                │     │                           │
       │ Fetches OS/Arch binary    │     │  Executes binary with     │
       │ from GitHub Releases      │     │  -i, -o, --minify         │
       └─────────────┬─────────────┘     └─────────────┬─────────────┘
                     │                                 │
                     └─────────────────┬───────────────┘
                                       ▼
                       ┌───────────────────────────────┐
                       │ Compiled Output (app.css)     │
                       └───────────────────────────────┘
```

---

## ⚙️ Configuration Reference

```php
use PHPWind\Config\PHPWindConfig;

$config = new PHPWindConfig(
    inputCss: 'resources/css/app.css',
    outputCss: 'public/css/app.css',
    binaryDir: 'vendor/bin/tailwind-cli',
    version: 'v4.0.0',
    minify: true,
    watch: false
);
```

---

## 📋 Requirements

- **PHP:** `>= 8.1`
- **Extensions:** `curl` or `allow_url_fopen` (for automatic binary downloading)

---

## 📄 License

This project is licensed under the [MIT License](LICENSE).

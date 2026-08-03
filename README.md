<p align="center">
  <img src="assets/phpwind_mascot.jpg" alt="PHPWind Mascot" width="600" style="border-radius: 12px;" />
</p>

<p align="center">
  <strong>Zero-Node Tailwind CSS v3 & v4 CLI Integration for PHP</strong>
</p>

<p align="center">
  <a href="https://github.com/leorsousa05/PHPWind/actions"><img src="https://github.com/leorsousa05/PHPWind/actions/workflows/tests.yml/badge.svg" alt="Build Status"></a>
  <a href="https://github.com/leorsousa05/PHPWind/releases"><img src="https://img.shields.io/github/v/tag/leorsousa05/PHPWind?style=flat-square&label=version" alt="Latest Tag"></a>
  <a href="LICENSE"><img src="https://img.shields.io/github/license/leorsousa05/PHPWind?style=flat-square" alt="License"></a>
  <a href="https://php.net"><img src="https://img.shields.io/badge/PHP-%3E%3D%208.1-777BB4.svg?style=flat-square" alt="PHP Version"></a>
</p>

---

## 🌐 Select Documentation Language / Selecione o Idioma / Seleccione el Idioma

- 🇺🇸 [**English Documentation**](docs/en/README.md)
- 🇧🇷 [**Documentação em Português**](docs/pt-br/README.md)
- 🇪🇸 [**Documentación en Español**](docs/es/README.md)

---

**PHPWind** is a standalone, lightweight PHP library that brings **Tailwind CSS v4** and **v3** to any PHP application without requiring Node.js, `npm`, or `npx`.

It automatically detects your Operating System and CPU architecture, downloads the official standalone Tailwind CSS CLI executable from GitHub Releases, and manages compilation, watching, and asset cache busting seamlessly.

---

## ✨ Features

- 🚫 **Zero Node.js Dependency:** No `package.json`, `node_modules`, or `npm` required.
- 📦 **Automatic Binary Downloader:** Detects OS (Windows, Linux, macOS) & Arch (x64, ARM64) and fetches official binaries.
- ⚡ **CLI Companion:** Built-in executable `vendor/bin/phpwind` with `build`, `watch`, `init`, and `clean`.
- 🔄 **Tailwind v3 & v4 Compatible:** Seamlessly switch versions (`v4.0.0`, `v3.4.17`, etc.).
- 🎨 **Smart Asset Helper:** Automatic HTML `<link>` tag generation with MD5 cache busting.
- ⚡ **On-Demand Dev Middleware:** Recompiles CSS dynamically during incoming HTTP requests in development.
- 🚀 **Laravel First-Class Adapter:** ServiceProvider, Artisan commands (`phpwind:build`, `phpwind:watch`, `phpwind:init`, `phpwind:clean`), and Blade directive `@phpwind`.
- 🧩 **Framework Agnostic:** Works in Vanilla PHP, Symfony, Slim, CodeIgniter, or custom setups.

---

## 📥 Installation

```bash
composer require phpwind/phpwind
```

---

## 🛠️ CLI Command Reference

Once installed, the CLI binary is available at `vendor/bin/phpwind`.

### Initialize Project CSS

```bash
vendor/bin/phpwind init
```

### Basic Build

```bash
vendor/bin/phpwind build -i resources/css/app.css -o public/css/app.css
```

### Production Minified Build

```bash
vendor/bin/phpwind build -i resources/css/app.css -o public/css/app.css --minify
```

### Watch Mode (Development)

```bash
vendor/bin/phpwind watch -i resources/css/app.css -o public/css/app.css
```

### Clean Cache & Binary

```bash
vendor/bin/phpwind clean
```

### Specific Tailwind Version

```bash
vendor/bin/phpwind build -i resources/css/app.css -o public/css/app.css --version=v3.4.17
```

---

## 💻 Usage & Integration

### 1. Vanilla PHP

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
        Hello Tailwind CSS!
    </h1>
</body>
</html>
```

### 2. Laravel Integration

#### Artisan Commands

```bash
php artisan phpwind:init
php artisan phpwind:build
php artisan phpwind:watch
php artisan phpwind:clean
```

#### Blade Directive

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    @phpwind('css/app.css')
</head>
<body class="bg-slate-900 text-white flex items-center justify-center h-screen">
    <div class="p-8 bg-slate-800 rounded-xl shadow-2xl">
        <h1 class="text-2xl font-bold text-teal-400">Laravel + PHPWind</h1>
    </div>
</body>
</html>
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

## 📄 License

This project is licensed under the [MIT License](LICENSE).

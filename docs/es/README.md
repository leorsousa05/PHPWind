# 🌬️ PHPWind — Documentación (Español)

> **Integración CLI de Tailwind CSS v3 y v4 para PHP Sin Dependencia de Node.js**

---

## 🌐 Opciones de Idioma

[ [English](../../docs/en/README.md) ] | [ [Português](../../docs/pt-br/README.md) ] | [ Español ]

---

## 📌 Visión General

**PHPWind** es una biblioteca PHP ultraligera y agnóstica diseñada para integrar **Tailwind CSS v4** y **v3** directamente en cualquier aplicación PHP sin necesidad de Node.js, `npm` o `npx`.

Detecta automáticamente el Sistema Operativo y la arquitectura de la máquina, descarga el binario ejecutable oficial CLI de Tailwind CSS en GitHub Releases y gestiona la compilación, el modo observador (watch) y el control de versiones de caché para sus archivos CSS.

---

## 🚀 Características Principales

- 🚫 **Sin Dependencia de Node.js:** Sin `package.json`, `node_modules` ni `npm`.
- 📦 **Descargador Automático de Binarios:** Detecta Windows, Linux y macOS (x64 y ARM64) y descarga el binario correcto desde GitHub Releases.
- ⚡ **Ejecutable CLI:** Acceso a `vendor/bin/phpwind` para tareas de `build` y `watch`.
- 🔄 **Compatible con Tailwind v3 y v4:** Cambia fácilmente entre versiones (`v4.0.0`, `v3.4.17`, etc.).
- 🎨 **Helper HTML Inteligente:** La función `phpwind_css()` genera etiquetas `<link>` con hash MD5 automático para invalidación de caché.
- ⚡ **Middleware Bajo Demanda:** Recompila el CSS automáticamente en peticiones HTTP durante el desarrollo.
- 🚀 **Integración Nativa con Laravel:** ServiceProvider, comandos Artisan (`phpwind:build`, `phpwind:watch`) y directiva Blade `@phpwind`.

---

## 📥 Instalación

```bash
composer require leorsousa05/phpwind
```

---

## 🛠️ Uso de la CLI

```bash
# Compilar CSS minificado para producción
vendor/bin/phpwind build -i resources/css/app.css -o public/css/app.css --minify

# Iniciar modo observador en desarrollo
vendor/bin/phpwind watch -i resources/css/app.css -o public/css/app.css

# Compilar especificando una versión de Tailwind
vendor/bin/phpwind build -i resources/css/app.css -o public/css/app.css --version=v3.4.17
```

---

## 💻 Ejemplos de Integración

### Vanilla PHP

```php
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Proyecto PHPWind</title>
    <?php echo phpwind_css('css/app.css'); ?>
</head>
<body class="bg-slate-900 text-white p-8">
    <h1 class="text-3xl font-bold text-sky-400">Tailwind CSS en PHP!</h1>
</body>
</html>
```

### Laravel

```blade
<!DOCTYPE html>
<html lang="es">
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

## ⚙️ Referencia de Configuración

```php
use PHPWind\Config\PHPWindConfig;

$config = new PHPWindConfig(
    inputCss: 'resources/css/app.css',
    outputCss: 'public/css/app.css',
    binaryDir: 'vendor/bin/tailwind-cli',
    version: 'v4.0.0', // Acepta 'v4.0.0', 'v3.4.17', etc.
    minify: true,
    watch: false
);
```

---

## 📄 Licencia

Licenciado bajo la [Licencia MIT](../../LICENSE).

<p align="center">
  <img src="../../assets/phpwind_mascot.jpg" alt="PHPWind Mascot" width="600" style="border-radius: 12px;" />
</p>

<p align="center">
  <strong>Integración CLI de Tailwind CSS v3 y v4 para PHP Sin Dependencia de Node.js</strong>
</p>

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
- ⚡ **Ejecutable CLI:** Acceso a `vendor/bin/phpwind` para tareas de `build`, `watch`, `init` y `clean`.
- 🔄 **Compatible con Tailwind v3 y v4:** Cambia fácilmente entre versiones (`v4.0.0`, `v3.4.17`, etc.).
- 🎨 **Helper HTML Inteligente:** La función `phpwind_css()` genera etiquetas `<link>` con hash MD5 automático para invalidación de caché.
- ⚡ **Middleware Bajo Demanda:** Recompila el CSS automáticamente en peticiones HTTP durante el desarrollo.
- 🎼 **Bundle Nativo para Symfony (`PHPWindBundle`):** Bundle oficial, extensión Twig `{{ phpwind_css() }}` y comandos `bin/console phpwind:*`.
- 🚀 **Integración Nativa con Laravel:** ServiceProvider, comandos Artisan y directiva Blade `@phpwind`.

---

## 📥 Instalación

```bash
composer require phpwind/phpwind
```

---

## 💻 Integración con Symfony

Registre el `PHPWindBundle` en `config/bundles.php`:

```php
return [
    PHPWind\Symfony\PHPWindBundle::class => ['all' => true],
];
```

### Comandos de Symfony Console

```bash
php bin/console phpwind:init
php bin/console phpwind:build --minify
php bin/console phpwind:watch
php bin/console phpwind:clean
```

### Función en Plantillas Twig

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

## 📄 Licencia

Licenciado bajo la [Licencia MIT](../../LICENSE).

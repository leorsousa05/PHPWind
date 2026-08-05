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
- 🔒 **Caché de Binario por Versión:** Vuelve a descargar el binario correcto al cambiar de versión.
- ⚡ **Ejecutable CLI:** Acceso a `vendor/bin/phpwind` para tareas de `build`, `watch`, `init` y `clean`.
- 🧩 **API Programática en PHP:** Compile, observe y gestione assets directamente desde PHP.
- 🎨 **Helper HTML Inteligente:** La función `phpwind_css()` genera etiquetas `<link>` con hash MD5 automático para invalidación de caché.
- 📋 **Manifiesto de Assets:** Manifiesto JSON opcional para URLs versionadas compatibles con CDN.
- ⚡ **Middleware Bajo Demanda:** Recompila el CSS automáticamente en peticiones HTTP durante el desarrollo.
- 🎼 **Bundle Nativo para Symfony (`PHPWindBundle`):** Bundle oficial, extensión Twig `{{ phpwind_css() }}` y comandos `bin/console phpwind:*`.
- 🚀 **Integración Nativa con Laravel:** ServiceProvider, comandos Artisan y directiva Blade `@phpwind`.
- 🧪 **Bien Probado:** Cobertura completa con PHPUnit para los componentes principales.

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

## 🧩 API Programática

### Compilar con salida estructurada

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

$exitCode = $compiler->compile($config);           // compatible con versiones anteriores
$result = $compiler->compileResult($config);       // resultado estructurado
```

### Gestión de binario por versión

```php
use PHPWind\Binary\BinaryManager;

$manager = new BinaryManager('vendor/bin/tailwind-cli');
$binaryPath = $manager->resolveBinaryPath('v4.0.0');
$manager->clearCachedBinary();
```

### Validación de configuración

```php
use PHPWind\Config\PHPWindConfig;

$config = new PHPWindConfig(version: 'v4.0.0');
$config->validate();
```

---

## 📋 Manifiesto de Assets

```php
use PHPWind\Manifest\AssetManifest;
use PHPWind\Helper\AssetHelper;

$manifest = AssetManifest::generate('public', ['css/app.css']);
$manifest->write('public/phpwind-manifest.json');

$manifest = AssetManifest::read('public/phpwind-manifest.json');
echo AssetHelper::cssFromManifest($manifest, 'css/app.css');
```

> [!NOTE]
> `AssetHelper::css()` sigue usando cache busting por query string por defecto. El manifiesto es opcional.

---

## 🧪 Ejecutando los Tests

```bash
vendor/bin/phpunit tests
```

---

## 📄 Licencia

Licenciado bajo la [Licencia MIT](../../LICENSE).

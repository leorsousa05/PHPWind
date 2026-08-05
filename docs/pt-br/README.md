<p align="center">
  <img src="../../assets/phpwind_mascot.jpg" alt="PHPWind Mascot" width="600" style="border-radius: 12px;" />
</p>

<p align="center">
  <strong>Integração Tailwind CSS v3 & v4 CLI para PHP Sem Depender do Node.js</strong>
</p>

---

## 🌐 Opções de Idioma

[ [English](../../docs/en/README.md) ] | [ Português ] | [ [Español](../../docs/es/README.md) ]

---

## 📌 Visão Geral

O **PHPWind** é uma biblioteca PHP agnóstica e ultra-leve projetada para integrar o **Tailwind CSS v4** e **v3** diretamente em qualquer aplicação PHP sem depender do Node.js, `npm` ou `npx`.

Ele detecta automaticamente o Sistema Operacional e a arquitetura da sua máquina, baixa o binário standalone oficial da CLI do Tailwind CSS no GitHub Releases, e gerencia a compilação, o modo watcher e o versionamento de cache dos seus arquivos CSS.

---

## 🚀 Principais Recursos

- 🚫 **Zero Dependência do Node.js:** Sem necessidade de `package.json`, `node_modules` ou `npm`.
- 📦 **Downloader Automático de Binários:** Detecta Windows, Linux e macOS (x64 e ARM64) e baixa o binário correto do GitHub Releases.
- 🔒 **Cache de Binário por Versão:** Rebaixa automaticamente o binário correto ao alternar versões.
- ⚡ **Executável CLI:** Acesse `vendor/bin/phpwind` para comandos `build`, `watch`, `init` e `clean`.
- 🧩 **API Programática em PHP:** Compile, observe e gerencie assets diretamente pelo PHP.
- 🎨 **Helper HTML Inteligente:** A função `phpwind_css()` gera tags `<link>` com hash MD5 automático para cache-busting.
- 📋 **Manifesto de Assets:** Manifesto JSON opcional para URLs versionadas compatíveis com CDN.
- ⚡ **Middleware On-Demand:** Recompila o CSS automaticamente em requisições HTTP durante o desenvolvimento.
- 🎼 **Bundle Nativo para Symfony (`PHPWindBundle`):** Bundle oficial, extensão Twig `{{ phpwind_css() }}` e comandos `bin/console phpwind:*`.
- 🚀 **Integração Nativa com Laravel:** ServiceProvider, comandos Artisan e diretiva Blade `@phpwind`.
- 🧪 **Bem Testado:** Cobertura completa com PHPUnit para os componentes principais.

---

## 📥 Instalação

```bash
composer require phpwind/phpwind
```

---

## 💻 Integração com Symfony

Registre o `PHPWindBundle` em `config/bundles.php`:

```php
return [
    PHPWind\Symfony\PHPWindBundle::class => ['all' => true],
];
```

### Comandos Symfony Console

```bash
php bin/console phpwind:init
php bin/console phpwind:build --minify
php bin/console phpwind:watch
php bin/console phpwind:clean
```

### Função em Templates Twig

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

### Compilar com saída estruturada

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

$exitCode = $compiler->compile($config);           // compatível com versões anteriores
$result = $compiler->compileResult($config);       // resultado estruturado
```

### Gerenciamento de binário por versão

```php
use PHPWind\Binary\BinaryManager;

$manager = new BinaryManager('vendor/bin/tailwind-cli');
$binaryPath = $manager->resolveBinaryPath('v4.0.0');
$manager->clearCachedBinary();
```

### Validação de configuração

```php
use PHPWind\Config\PHPWindConfig;

$config = new PHPWindConfig(version: 'v4.0.0');
$config->validate();
```

---

## 📋 Manifesto de Assets

```php
use PHPWind\Manifest\AssetManifest;
use PHPWind\Helper\AssetHelper;

$manifest = AssetManifest::generate('public', ['css/app.css']);
$manifest->write('public/phpwind-manifest.json');

$manifest = AssetManifest::read('public/phpwind-manifest.json');
echo AssetHelper::cssFromManifest($manifest, 'css/app.css');
```

> [!NOTE]
> `AssetHelper::css()` continua usando cache busting por query string por padrão. O manifesto é opcional.

---

## 🧪 Executando os Testes

```bash
vendor/bin/phpunit tests
```

---

## 📄 Licença

Licenciado sob a [Licença MIT](../../LICENSE).

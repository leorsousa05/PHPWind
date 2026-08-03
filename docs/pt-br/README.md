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
- ⚡ **Executável CLI:** Acesse `vendor/bin/phpwind` para comandos `build`, `watch`, `init` e `clean`.
- 🔄 **Compatível com Tailwind v3 e v4:** Alterne facilmente entre versões (`v4.0.0`, `v3.4.17`, etc.).
- 🎨 **Helper HTML Inteligente:** A função `phpwind_css()` gera tags `<link>` com hash MD5 automático para cache-busting.
- ⚡ **Middleware On-Demand:** Recompila o CSS automaticamente em requisições HTTP durante o desenvolvimento.
- 🚀 **Integração Nativa com Laravel:** ServiceProvider, comandos Artisan (`phpwind:build`, `phpwind:watch`, `phpwind:init`, `phpwind:clean`) e diretiva Blade `@phpwind`.

---

## 📥 Instalação

```bash
composer require phpwind/phpwind
```

---

## 🛠️ Comandos CLI

```bash
# Inicializar arquivo de CSS de entrada
vendor/bin/phpwind init

# Compilar CSS de produção minificado
vendor/bin/phpwind build -i resources/css/app.css -o public/css/app.css --minify

# Iniciar modo watcher em desenvolvimento
vendor/bin/phpwind watch -i resources/css/app.css -o public/css/app.css

# Limpar binários baixados e arquivos gerados em cache
vendor/bin/phpwind clean

# Compilar apontando para uma versão específica do Tailwind
vendor/bin/phpwind build -i resources/css/app.css -o public/css/app.css --version=v3.4.17
```

---

## 💻 Exemplos de Uso

### Vanilla PHP

```php
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Projeto PHPWind</title>
    <?php echo phpwind_css('css/app.css'); ?>
</head>
<body class="bg-slate-900 text-white p-8">
    <h1 class="text-3xl font-bold text-sky-400">Tailwind CSS no PHP!</h1>
</body>
</html>
```

### Laravel

```bash
php artisan phpwind:init
php artisan phpwind:build
php artisan phpwind:watch
php artisan phpwind:clean
```

```blade
<!DOCTYPE html>
<html lang="pt-BR">
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

## ⚙️ Referência de Configuração

```php
use PHPWind\Config\PHPWindConfig;

$config = new PHPWindConfig(
    inputCss: 'resources/css/app.css',
    outputCss: 'public/css/app.css',
    binaryDir: 'vendor/bin/tailwind-cli',
    version: 'v4.0.0', // Aceita 'v4.0.0', 'v3.4.17', etc.
    minify: true,
    watch: false
);
```

---

## 📄 Licença

Licenciado sob a [Licença MIT](../../LICENSE).

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
- 🎼 **Bundle Nativo para Symfony (`PHPWindBundle`):** Bundle oficial, extensão Twig `{{ phpwind_css() }}` e comandos `bin/console phpwind:*`.
- 🚀 **Integração Nativa com Laravel:** ServiceProvider, comandos Artisan e diretiva Blade `@phpwind`.

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

## 📄 Licença

Licenciado sob a [Licença MIT](../../LICENSE).

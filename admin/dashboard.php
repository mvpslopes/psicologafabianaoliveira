<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/auth.php';
fo_require_auth();

$name = (string) ($_SESSION['fo_admin_name'] ?? 'Fabiana Oliveira');
$siteUrl = (string) fo_config('site_url', '../index.html');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Estatísticas | FO Psicologia</title>
  <link rel="icon" type="image/png" href="../logo/logo-fo-psicologia-4.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/admin.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js" defer></script>
  <script src="assets/admin.js" defer></script>
</head>
<body class="app-body" data-admin-name="<?= fo_e($name) ?>">
  <aside class="sidebar">
    <div class="sidebar__brand">
      <img src="../logo/logo-fo-psicologia-1.png" alt="FO Psicologia" />
      <span>Área interna</span>
    </div>

    <p class="sidebar__label">Menu</p>
    <nav class="sidebar__nav">
      <a class="is-active" href="dashboard.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
        Estatísticas
      </a>
      <a href="password.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
        Trocar senha
      </a>
      <a href="<?= fo_e($siteUrl) ?>" target="_blank" rel="noopener">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/></svg>
        Ver site
      </a>
    </nav>

    <div class="sidebar__footer">
      <div class="sidebar__user">
        <span class="sidebar__avatar"><?= fo_e(mb_substr($name, 0, 1)) ?></span>
        <div>
          <strong><?= fo_e($name) ?></strong>
          <span>@<?= fo_e(mb_strtolower(explode(' ', $name)[0])) ?></span>
        </div>
      </div>
      <a class="btn btn--ghost btn--block" href="api/logout.php">Sair</a>
    </div>
  </aside>

  <div class="app-main">
    <header class="app-header">
      <div>
        <p class="eyebrow">Estatísticas</p>
        <h1>Bem-vinda de volta, <?= fo_e($name) ?></h1>
      </div>
      <div class="range-tabs" data-range-tabs>
        <button type="button" data-range="today">Hoje</button>
        <button type="button" data-range="7d">7 dias</button>
        <button type="button" class="is-active" data-range="30d">30 dias</button>
        <button type="button" data-range="90d">90 dias</button>
        <button type="button" data-range="all">Todo período</button>
      </div>
    </header>

    <div class="status-bar" data-status>Carregando dados do Google Analytics…</div>

    <section class="metrics-grid" data-metrics>
      <!-- filled by JS -->
    </section>

    <section class="charts-grid">
      <article class="panel">
        <h2>Horários de Pico</h2>
        <canvas id="chart-hourly" height="180"></canvas>
      </article>
      <article class="panel">
        <h2>Atividade por Dia da Semana</h2>
        <canvas id="chart-weekday" height="180"></canvas>
      </article>
      <article class="panel panel--wide">
        <h2>Visitantes ao Longo do Tempo</h2>
        <canvas id="chart-timeline" height="120"></canvas>
      </article>
    </section>

    <section class="lists-grid">
      <article class="panel">
        <h2>Páginas Mais Visitadas</h2>
        <div class="list" data-list="topPages"></div>
      </article>
      <article class="panel">
        <h2>Dispositivos</h2>
        <div class="list" data-list="devices"></div>
      </article>
      <article class="panel">
        <h2>Navegadores</h2>
        <div class="list" data-list="browsers"></div>
      </article>
      <article class="panel">
        <h2>Sistemas Operacionais</h2>
        <div class="list" data-list="os"></div>
      </article>
      <article class="panel">
        <h2>Origem do Tráfego</h2>
        <div class="list" data-list="sources"></div>
      </article>
      <article class="panel">
        <h2>Páginas de Entrada</h2>
        <div class="list" data-list="landings"></div>
      </article>
      <article class="panel">
        <h2>Páginas Mais Acessadas (saída / rejeição)</h2>
        <div class="list" data-list="exits"></div>
      </article>
      <article class="panel">
        <h2>Acessos por País</h2>
        <div class="list" data-list="countries"></div>
      </article>
      <article class="panel panel--wide">
        <h2>Acessos por Cidade</h2>
        <div class="list list--dense" data-list="cities"></div>
      </article>
    </section>

    <p class="footnote" data-note></p>
  </div>
</body>
</html>

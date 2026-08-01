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
  <title>Trocar senha | FO Psicologia</title>
  <link rel="icon" type="image/png" href="../logo/logo-fo-psicologia-4.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/admin.css" />
</head>
<body class="app-body">
  <aside class="sidebar">
    <div class="sidebar__brand">
      <img src="../logo/logo-fo-psicologia-1.png" alt="FO Psicologia" />
      <span>Área interna</span>
    </div>
    <p class="sidebar__label">Menu</p>
    <nav class="sidebar__nav">
      <a href="dashboard.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
        Estatísticas
      </a>
      <a class="is-active" href="password.php">
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
        <p class="eyebrow">Segurança</p>
        <h1>Trocar senha</h1>
      </div>
    </header>

    <article class="panel panel--form">
      <form id="password-form" class="auth-form">
        <div class="field">
          <label for="current_password">Senha atual</label>
          <input type="password" id="current_password" required autocomplete="current-password" />
        </div>
        <div class="field">
          <label for="new_password">Nova senha</label>
          <input type="password" id="new_password" required minlength="8" autocomplete="new-password" />
        </div>
        <div class="field">
          <label for="confirm_password">Confirmar nova senha</label>
          <input type="password" id="confirm_password" required minlength="8" autocomplete="new-password" />
        </div>
        <p class="form-error" id="password-error" hidden></p>
        <p class="form-success" id="password-success" hidden></p>
        <button type="submit" class="btn btn--primary" id="password-btn">Salvar nova senha</button>
      </form>
    </article>
  </div>

  <script>
    (function () {
      var form = document.getElementById("password-form");
      var error = document.getElementById("password-error");
      var success = document.getElementById("password-success");
      var btn = document.getElementById("password-btn");

      form.addEventListener("submit", function (e) {
        e.preventDefault();
        error.hidden = true;
        success.hidden = true;
        btn.disabled = true;

        fetch("api/change-password.php", {
          method: "POST",
          headers: { "Content-Type": "application/json", Accept: "application/json" },
          body: JSON.stringify({
            current_password: document.getElementById("current_password").value,
            new_password: document.getElementById("new_password").value,
            confirm_password: document.getElementById("confirm_password").value,
          }),
        })
          .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
          .then(function (res) {
            if (!res.ok || !res.data.ok) {
              throw new Error((res.data && res.data.error) || "Falha ao alterar senha");
            }
            success.textContent = res.data.message || "Senha atualizada.";
            success.hidden = false;
            form.reset();
          })
          .catch(function (err) {
            error.textContent = err.message;
            error.hidden = false;
          })
          .finally(function () {
            btn.disabled = false;
          });
      });
    })();
  </script>
</body>
</html>

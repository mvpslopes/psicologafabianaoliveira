<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/auth.php';

if (fo_is_authenticated()) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Área interna | FO Psicologia</title>
  <link rel="icon" type="image/png" href="../logo/logo-fo-psicologia-4.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/admin.css" />
</head>
<body class="auth-page">
  <main class="auth-card">
    <div class="auth-card__brand">
      <img src="../logo/logo-fo-psicologia-1.png" alt="FO Psicologia" />
      <p class="auth-card__eyebrow">Área interna</p>
      <h1>Estatísticas do site</h1>
      <p class="auth-card__lede">Acompanhe visitas, origem do tráfego e engajamento com a identidade do consultório.</p>
    </div>

    <form class="auth-form" id="login-form" autocomplete="on">
      <div class="field">
        <label for="username">Usuário</label>
        <input type="text" id="username" name="username" required autocomplete="username" value="Fabiana" />
      </div>
      <div class="field">
        <label for="password">Senha</label>
        <input type="password" id="password" name="password" required autocomplete="current-password" />
      </div>
      <p class="form-error" id="login-error" hidden></p>
      <button type="submit" class="btn btn--primary btn--block" id="login-btn">Entrar</button>
    </form>
  </main>

  <script>
    (function () {
      var form = document.getElementById("login-form");
      var error = document.getElementById("login-error");
      var btn = document.getElementById("login-btn");

      form.addEventListener("submit", function (e) {
        e.preventDefault();
        error.hidden = true;
        btn.disabled = true;
        btn.textContent = "Entrando…";

        fetch("api/login.php", {
          method: "POST",
          headers: { "Content-Type": "application/json", Accept: "application/json" },
          body: JSON.stringify({
            username: document.getElementById("username").value,
            password: document.getElementById("password").value,
          }),
        })
          .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
          .then(function (res) {
            if (!res.ok || !res.data.ok) {
              throw new Error((res.data && res.data.error) || "Falha no login");
            }
            window.location.href = "dashboard.php";
          })
          .catch(function (err) {
            error.textContent = err.message || "Não foi possível entrar.";
            error.hidden = false;
            btn.disabled = false;
            btn.textContent = "Entrar";
          });
      });
    })();
  </script>
</body>
</html>

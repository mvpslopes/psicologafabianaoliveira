# Gera a pasta dist com os arquivos prontos para hospedagem (Hostinger / FTP)
$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $MyInvocation.MyCommand.Path
$dist = Join-Path $root "dist"

if (Test-Path $dist) {
  Remove-Item $dist -Recurse -Force
}

New-Item -ItemType Directory -Path $dist | Out-Null
Copy-Item (Join-Path $root "*.html") $dist
Copy-Item (Join-Path $root "css") (Join-Path $dist "css") -Recurse
Copy-Item (Join-Path $root "js") (Join-Path $dist "js") -Recurse

# SEO / Google Search Console
@(
  "robots.txt",
  "sitemap.xml"
) | ForEach-Object {
  $src = Join-Path $root $_
  if (Test-Path $src) {
    Copy-Item $src $dist
  }
}

# Fotos usadas no site (sem arquivos originais IMG_*)
New-Item -ItemType Directory -Path (Join-Path $dist "fotos") | Out-Null
@(
  "foto.webp",
  "hero.png",
  "consultorio-1.png",
  "consultorio-2.png",
  "IMG_1476.PNG",
  "IMG_1570.PNG"
) | ForEach-Object {
  Copy-Item (Join-Path $root "fotos\$_") (Join-Path $dist "fotos")
}

# Avatares dos depoimentos
Copy-Item (Join-Path $root "fotos\depoimentos") (Join-Path $dist "fotos\depoimentos") -Recurse

New-Item -ItemType Directory -Path (Join-Path $dist "logo") | Out-Null
Copy-Item (Join-Path $root "logo\*.png") (Join-Path $dist "logo")

# Presence heartbeat (online agora)
$presenceSrc = Join-Path $root "presence"
if (Test-Path $presenceSrc) {
  Copy-Item $presenceSrc (Join-Path $dist "presence") -Recurse
}

# Área interna (admin) — inclui config e service account locais se existirem
$adminSrc = Join-Path $root "admin"
$adminDist = Join-Path $dist "admin"
Copy-Item $adminSrc $adminDist -Recurse

# Limpa cache gerado
$cacheDir = Join-Path $adminDist "data\cache"
if (Test-Path $cacheDir) {
  Get-ChildItem $cacheDir -File | Where-Object { $_.Name -ne ".gitkeep" } | Remove-Item -Force
}

# Garante pastas protegidas
@(
  "private",
  "data",
  "data\cache"
) | ForEach-Object {
  $p = Join-Path $adminDist $_
  if (-not (Test-Path $p)) { New-Item -ItemType Directory -Path $p | Out-Null }
}

# Se config.php não existir no dist, copia o sample
$configDist = Join-Path $adminDist "config.php"
if (-not (Test-Path $configDist)) {
  Copy-Item (Join-Path $adminSrc "config.sample.php") $configDist
}

Write-Host "Build concluido: $dist"
Write-Host ""
Write-Host "IMPORTANTE (Hostinger):"
Write-Host "1. Envie a pasta dist/ para a raiz do dominio (public_html)."
Write-Host "2. Confirme admin/config.php e admin/private/ga4-service-account.json no servidor."
Write-Host "3. No GA4, conceda Visualizador a: psicologafabianaoliveira-com-b@graphic-ripsaw-461313-c3.iam.gserviceaccount.com"
Write-Host "4. Acesse https://psicologafabianaoliveira.com.br/admin/ (Fabiana / FoPsico2026!)"
Write-Host ""

Get-ChildItem -Recurse $dist -File | ForEach-Object {
  "{0} ({1:N0} bytes)" -f $_.FullName.Replace($dist + "\", ""), $_.Length
}

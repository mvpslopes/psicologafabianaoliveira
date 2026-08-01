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
  "consultorio-2.png"
) | ForEach-Object {
  Copy-Item (Join-Path $root "fotos\$_") (Join-Path $dist "fotos")
}

# Avatares dos depoimentos
Copy-Item (Join-Path $root "fotos\depoimentos") (Join-Path $dist "fotos\depoimentos") -Recurse

New-Item -ItemType Directory -Path (Join-Path $dist "logo") | Out-Null
Copy-Item (Join-Path $root "logo\*.png") (Join-Path $dist "logo")

Write-Host "Build concluido: $dist"
Get-ChildItem -Recurse $dist -File | ForEach-Object {
  "{0} ({1:N0} bytes)" -f $_.FullName.Replace($dist + "\", ""), $_.Length
}

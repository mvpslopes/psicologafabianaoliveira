# Área interna FO Psicologia — Deploy

## Acesso
- URL: `https://psicologafabianaoliveira.com.br/admin/`
- Usuário: `Fabiana`
- Senha inicial: `FoPsico2026!` (troque em Trocar senha)

## Arquivos sensíveis (não vão para o GitHub)
- `admin/config.php` — copie de `admin/config.sample.php`
- `admin/private/ga4-service-account.json` — JSON da service account Google

O `build.ps1` inclui esses arquivos no `dist/` **se existirem localmente**, para facilitar o upload via FTP.

## Pré-requisito Google Analytics
1. GA4 property `548074031` com Measurement ID `G-5T8DCCK042`
2. Em **Admin → Gerenciamento de acesso à propriedade**, adicione como **Visualizador**:
   `psicologafabianaoliveira-com-b@graphic-ripsaw-461313-c3.iam.gserviceaccount.com`
3. Ative a **Google Analytics Data API** no projeto Cloud da service account

## Hostinger
- PHP 8.0+ com extensões `curl` e `openssl`
- Envie o conteúdo de `dist/` para `public_html`
- Confirme que `.htaccess` em `admin/private/` e `admin/data/` estão no servidor

## Instrumentação do site
- gtag `G-5T8DCCK042` em todas as páginas públicas
- Evento `whatsapp_click` em links/botões WhatsApp e no formulário de contato

## Cache
Respostas da API ficam em `admin/data/cache/` por até 5 minutos.

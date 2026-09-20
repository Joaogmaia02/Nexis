# Nexis

Estrutura PHP com padrão MVC para o middleware de prompts inteligentes.

## Estrutura

- `index.php`: front controller.
- `app/Core`: classes base do MVC.
- `app/Controllers`: fluxo de páginas e API.
- `app/Models`: integração com a API externa e fallback local.
- `app/Views`: interface HTML do front-end.
- `config`: rotas e configuração da aplicação.

## Rotas

- `GET /`: carrega a interface do Nexis.
- `POST /api/prompts`: recebe o prompt cru e retorna três sugestões.

## Próximo passo

Definir a URL da API real em `config/app.php` no campo `prompt_api_url`.

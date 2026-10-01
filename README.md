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

## Configuração da Groq API

Defina a variável de ambiente `GROQ_API_KEY` com sua chave da Groq API. Nunca commit uma chave real no repositório.
O Nexis usa o endpoint compatível com OpenAI `https://api.groq.com/openai/v1/chat/completions` e o modelo `openai/gpt-oss-120b` por padrão, com esforço de raciocínio médio.

As sugestões de prompt seguem cinco elementos: persona, ação, resultado, tom e suporte/contexto.

## Planejamento de horas

A tabela de sistemas, atividades de desenvolvimento/correção, prioridades e horas estimadas está em [PLANO_HORAS_NEXIS.md](PLANO_HORAS_NEXIS.md). O documento estima 156 horas para concluir o escopo descrito e inclui critérios para registrar evidências das horas de extensão.

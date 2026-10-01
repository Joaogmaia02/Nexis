# Plano de horas de desenvolvimento do Nexis

Documento de apoio à proposta de TCC e ao registro das horas de extensão.

## Critério da estimativa

As horas abaixo são uma estimativa de esforço para uma pessoa desenvolvedora familiarizada com PHP, JavaScript, HTML e CSS. Cada valor inclui análise, implementação ou correção, testes manuais, ajustes e documentação mínima da entrega. As horas representam o trabalho necessário para concluir cada sistema, e não necessariamente horas já realizadas.

A estimativa considera o estado atual do protótipo, que já possui uma interface de chat, uma API PHP com roteamento MVC, integração com a Groq e renderização de respostas em Markdown/KaTeX.

## Tabela de sistemas e horas

| Sistema do Nexis | Situação atual | Trabalho necessário para cumprir | Desenvolver (h) | Corrigir e validar (h) | Total (h) | Prioridade |
|---|---|---|---:|---:|---:|---|
| Interface de chat e experiência do usuário | Parcialmente implementado | Consolidar entrada de mensagens, estados de carregamento, seleção/uso de sugestões, cópia, feedback, histórico da conversa e tratamento de falhas | 12 | 8 | 20 | Alta |
| Geração de prompts estruturados | Implementado com dependência da IA | Definir contrato de saída, gerar três sugestões com Persona, Ação, Resultado, Tom e Suporte, evitar repetições e tratar entradas fora do escopo técnico | 8 | 8 | 16 | Alta |
| Respostas técnicas do assistente | Implementado com dependência da IA | Aperfeiçoar instruções, respostas em português, passos numerados, adaptação ao dispositivo, avisos de segurança e validação de resposta vazia ou inválida | 10 | 8 | 18 | Alta |
| Integração com serviço de IA | Implementado | Isolar configuração, validar credenciais, tratar timeout, indisponibilidade, códigos HTTP, JSON inválido, limite de uso e mensagens amigáveis | 6 | 10 | 16 | Alta |
| Backend PHP, MVC e API | Implementado em estrutura enxuta | Fortalecer roteamento, validação de payload, códigos HTTP, respostas JSON, autoload, separação de responsabilidades e logs técnicos | 8 | 8 | 16 | Alta |
| Segurança e configuração da aplicação | Necessita correção prioritária | Remover chave exposta do código versionado, usar variáveis de ambiente, validar origem/entrada, limitar abuso e revisar exposição de erros e dados sensíveis | 6 | 12 | 18 | Crítica |
| Renderização Markdown, matemática e conteúdo | Parcialmente implementado | Corrigir casos-limite de Markdown/HTML, manter escape contra XSS, validar blocos de código, tabelas, links e fórmulas KaTeX | 6 | 8 | 14 | Média |
| Responsividade, acessibilidade e temas | Parcialmente implementado | Testar teclado, zoom, contraste, leitores de tela, mobile, tema claro/escuro, foco e reflow; corrigir incompatibilidades encontradas | 8 | 10 | 18 | Alta |
| Testes, documentação e preparação para apresentação | Inicial | Criar matriz de casos, testes dos endpoints, cenários de erro, roteiro de demonstração, documentação de instalação e critérios de aceite | 12 | 8 | 20 | Alta |
| **Total estimado** |  |  | **76** | **80** | **156** |  |

## Ordem recomendada de execução

1. Segurança e configuração da aplicação: a chave da Groq aparece atualmente em `config/app.php` e deve ser revogada/substituída antes de qualquer apresentação pública.
2. Backend, integração com IA e contratos dos endpoints: estabilizar o caminho crítico da aplicação.
3. Geração de prompts e respostas técnicas: validar qualidade, escopo e mensagens de erro.
4. Interface, acessibilidade, responsividade e renderização: finalizar a experiência apresentada ao avaliador.
5. Testes, documentação e roteiro de apresentação: reunir evidências para o TCC e para as horas de extensão.

## Critérios para contabilização das horas

Para cada atividade, recomenda-se registrar data, descrição, sistema relacionado, resultado obtido, evidência e tempo gasto. Podem servir como evidências: commits, arquivos alterados, capturas de tela, casos de teste, registros de correção, documentação e versões demonstráveis.

A soma de 156 horas é uma previsão de conclusão do escopo listado. O total efetivamente contabilizado deve ser ajustado com base no diário de desenvolvimento e nas regras da faculdade para validação das horas de extensão.

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Nexis', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="icon" type="image/x-icon" href="Imagens/ico.ico">
    <link rel="stylesheet" href="style.css?v=2">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.js"></script>
</head>
<body>
    <div class="container">
        <!-- Header com Logo -->
        <div class="header">
            <div class="logo-wrapper">
                <h1 class="logo-text">NEXIS</h1>
                <button class="theme-toggle" id="themeToggle" title="Alternar tema">
                    <svg class="sun-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="5"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                    </svg>
                    <svg class="moon-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Seção Chat -->
        <div class="chat-section">
            <div class="input-wrapper">
                <input 
                    type="text" 
                    class="input-field" 
                    id="userInput"
                    placeholder="Informe o seu problema técnico"
                    autocomplete="off"
                >
                <button class="send-button" id="sendButton">
                    <div class="play-icon"></div>
                </button>
            </div>

            <p class="description">
                O <strong>NEXIS</strong> é um protótipo para apresentação como proposta de TCC. Portanto, ele está sujeito a falhas e limitações. 
            </p>
        </div>

        <!-- Container de Mensagens -->
        <div class="messages-container" id="messagesContainer"></div>

        <!-- Container de Prompts Sugeridos -->
        <div class="prompts-container" id="promptsContainer"></div>
    </div>

    <!-- Botão de Scroll para Baixo -->
    <button class="scroll-to-bottom-button" id="scrollToBottomButton" title="Ir para o final da conversa">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <polyline points="19 12 12 19 5 12"></polyline>
        </svg>
    </button>

    <script src="script.js?v=9"></script>
</body>
</html>

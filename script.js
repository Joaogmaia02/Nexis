const userInput = document.getElementById('userInput');
const sendButton = document.getElementById('sendButton');
const messagesContainer = document.getElementById('messagesContainer');
const promptsContainer = document.getElementById('promptsContainer');
const themeToggle = document.getElementById('themeToggle');
const sunIcon = document.querySelector('.sun-icon');
const moonIcon = document.querySelector('.moon-icon');

// Flag para rastrear se estamos em modo de prompts sugeridos
let isWaitingForPromptSelection = false;
let currentUserMessage = '';
let currentPromptOffset = 0;
let currentSuggestedPrompts = [];
// Função para buscar prompts otimizados no back-end do Nexis
async function fetchSuggestedPrompts(userMessage, previousSuggestions = []) {
    const response = await fetch('api/prompts', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            prompt: userMessage,
            previous_suggestions: previousSuggestions
        })
    });

    const data = await response.json().catch(() => null);

    if (!response.ok || !data || data.success !== true || !Array.isArray(data.suggestions)) {
        const errorMessage = data && typeof data.message === 'string'
            ? data.message
            : 'O serviço de IA do Nexis não pôde gerar sugestões para este prompt.';

        throw new Error(errorMessage);
    }

    return data.suggestions;
}

// Função para exibir os prompts sugeridos com botão de refresh
function displaySuggestedPrompts(userMessage, prompts) {
    currentUserMessage = userMessage;
    currentSuggestedPrompts = prompts;
    
    promptsContainer.innerHTML = '';
    promptsContainer.classList.add('active');
    messagesContainer.classList.add('active');

    const header = document.createElement('div');
    header.className = 'prompts-header';
    header.textContent = '✨ Prompts Estruturados Sugeridos:';
    promptsContainer.appendChild(header);
    
    // Criar container para grid dos prompts + botão refresh
    const promptsGridWrapper = document.createElement('div');
    promptsGridWrapper.className = 'prompts-grid-wrapper';

    // Adicionar os 3 prompts
    prompts.forEach((prompt, index) => {
        const card = document.createElement('div');
        card.className = 'prompt-card';

        const promptNumber = document.createElement('div');
        promptNumber.className = 'prompt-number';
        promptNumber.textContent = index + 1;

        const title = document.createElement('div');
        title.className = 'prompt-title';
        title.textContent = prompt.title;

        const text = document.createElement('div');
        text.className = 'prompt-text';
        text.textContent = prompt.description;
        text.style.whiteSpace = 'pre-wrap';

        const actions = document.createElement('div');
        actions.className = 'prompt-actions';

        const copyButton = document.createElement('button');
        copyButton.className = 'prompt-button copy';
        copyButton.textContent = 'Copiar';
        copyButton.addEventListener('click', () => {
            copyToClipboard(prompt.description, copyButton);
        });

        const useButton = document.createElement('button');
        useButton.className = 'prompt-button use';
        useButton.textContent = 'Usar';
        useButton.addEventListener('click', () => {
            userInput.value = prompt.description;
            isWaitingForPromptSelection = true;
            // Limpar prompts quando usuário seleciona um
            promptsContainer.classList.remove('active');
            promptsContainer.innerHTML = '';
            userInput.focus();
        });

        actions.appendChild(copyButton);
        actions.appendChild(useButton);

        card.appendChild(promptNumber);
        card.appendChild(title);
        card.appendChild(text);
        card.appendChild(actions);

        promptsGridWrapper.appendChild(card);
    });

    // Adicionar botão de refresh circular
    const refreshButton = document.createElement('button');
    refreshButton.className = 'refresh-prompts-button';
    refreshButton.title = 'Gerar mais opções de prompts';
    refreshButton.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M23 4v6h-6"></path>
        <path d="M1 20v-6h6"></path>
        <path d="M3.51 9a9 9 0 0 1 14.85-3.36M20.49 15a9 9 0 0 1-14.85 3.36"></path>
    </svg>`;
    refreshButton.addEventListener('click', () => {
        loadSuggestedPrompts(userMessage, currentSuggestedPrompts);
    });

    promptsGridWrapper.appendChild(refreshButton);
    promptsContainer.appendChild(promptsGridWrapper);
}

// Função para carregar e exibir prompts do back-end
async function loadSuggestedPrompts(userMessage, previousSuggestions = []) {
    const loadingMessage = document.createElement('div');
    loadingMessage.className = 'message bot';
    loadingMessage.innerHTML = '<div class="message-content"><div class="loading" aria-label="Carregando sugestões"><span></span><span></span><span></span></div></div>';

    messagesContainer.appendChild(loadingMessage);
    messagesContainer.classList.add('active');
    messagesContainer.scrollTop = messagesContainer.scrollHeight;

    try {
        const prompts = await fetchSuggestedPrompts(userMessage, previousSuggestions);
        loadingMessage.remove();
        displaySuggestedPrompts(userMessage, prompts);
    } catch (error) {
        loadingMessage.remove();

        const errorMessage = document.createElement('div');
        errorMessage.className = 'message bot';
        errorMessage.innerHTML = `<div class="message-content">${escapeHtml(error.message)}</div>`;
        messagesContainer.appendChild(errorMessage);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;

        promptsContainer.innerHTML = '';
        promptsContainer.classList.remove('active');
    }
}

// Função para copiar texto ao clipboard
function copyToClipboard(text, button) {
    navigator.clipboard.writeText(text).then(() => {
        const originalText = button.textContent;
        button.textContent = '✓ Copiado!';
        button.style.background = '#2ecc71';
        button.style.color = '#ffffff';
        button.style.borderColor = '#2ecc71';

        setTimeout(() => {
            button.textContent = originalText;
            button.style.background = '';
            button.style.color = '';
            button.style.borderColor = '';
        }, 2000);

        // Feedback visual
        showCopyFeedback();
    }).catch(err => {
        console.error('Erro ao copiar:', err);
        button.textContent = 'Erro ao copiar';
    });
}

// Função para mostrar feedback de cópia
function showCopyFeedback() {
    const feedback = document.createElement('div');
    feedback.className = 'copy-feedback';
    feedback.textContent = '✓ Prompt copiado para a área de transferência!';
    document.body.appendChild(feedback);

    setTimeout(() => {
        feedback.remove();
    }, 3000);
}

async function loadAssistantResponse(prompt) {
    const loadingMessage = document.createElement('div');
    loadingMessage.className = 'message bot';
    loadingMessage.innerHTML = '<div class="message-content"><div class="loading" aria-label="Processando resposta"><span></span><span></span><span></span></div></div>';
    messagesContainer.appendChild(loadingMessage);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;

    try {
        const response = await fetch('api/respond', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ prompt })
        });
        const data = await response.json().catch(() => null);

        if (!response.ok || !data || data.success !== true || typeof data.response !== 'string') {
            const errorMessage = data && typeof data.message === 'string'
                ? data.message
                : 'O serviço de IA do Nexis não pôde responder ao prompt.';
            throw new Error(errorMessage);
        }

        loadingMessage.remove();
        const botMessageDiv = document.createElement('div');
        botMessageDiv.className = 'message bot';
        const content = document.createElement('div');
        content.className = 'message-content';
        content.innerHTML = renderMarkdown(data.response);
        botMessageDiv.appendChild(content);
        messagesContainer.appendChild(botMessageDiv);
    } catch (error) {
        loadingMessage.remove();
        const errorMessage = document.createElement('div');
        errorMessage.className = 'message bot';
        errorMessage.innerHTML = `<div class="message-content">${escapeHtml(error.message)}</div>`;
        messagesContainer.appendChild(errorMessage);
    }

    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

// Inicializar tema ao carregar
function initTheme() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.body.classList.toggle('dark-theme', savedTheme === 'dark');
    updateThemeIcons(savedTheme === 'dark');
}

// Atualizar ícones com base no tema
function updateThemeIcons(isDark) {
    if (isDark) {
        sunIcon.style.display = 'none';
        moonIcon.style.display = 'block';
    } else {
        sunIcon.style.display = 'block';
        moonIcon.style.display = 'none';
    }
}

// Alternar tema
function toggleTheme() {
    const isDark = document.body.classList.contains('dark-theme');
    document.body.classList.toggle('dark-theme');
    const newTheme = isDark ? 'light' : 'dark';
    localStorage.setItem('theme', newTheme);
    updateThemeIcons(!isDark);
}

// Event listener para o botão de tema
themeToggle.addEventListener('click', toggleTheme);

// Função para enviar mensagem
function sendMessage() {
    const message = userInput.value.trim();

    if (message === '') return;

    // Mostrar container de mensagens
    messagesContainer.classList.add('active');

    // Adicionar mensagem do usuário
    const userMessageDiv = document.createElement('div');
    userMessageDiv.className = 'message user';
    userMessageDiv.innerHTML = `<div class="message-content">${escapeHtml(message)}</div>`;
    messagesContainer.appendChild(userMessageDiv);

    // Limpar input
    userInput.value = '';

    // Scroll para a última mensagem
    messagesContainer.scrollTop = messagesContainer.scrollHeight;

    // Verificar se estamos aguardando seleção de prompts
    if (isWaitingForPromptSelection) {
        loadAssistantResponse(message);
        isWaitingForPromptSelection = false;
    } else {
        // Carregar prompts otimizados diretamente do back-end
        loadSuggestedPrompts(message);
    }
}

// Função para escapar HTML (segurança)
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function renderMarkdown(text) {
    const mathBlocks = [];
    const mathInline = [];
    let source = text.replace(/\\\[([\s\S]*?)\\\]|\$\$([\s\S]*?)\$\$/g, (_, bracket, dollars) => {
        mathBlocks.push(bracket ?? dollars);
        return `@@MATH_BLOCK_${mathBlocks.length - 1}@@`;
    });
    source = source.replace(/\\\(([^\n]*?)\\\)|\$([^$\n]+)\$/g, (_, parentheses, dollars) => {
        mathInline.push(parentheses ?? dollars);
        return `@@MATH_INLINE_${mathInline.length - 1}@@`;
    });

    let html = escapeHtml(source).replace(/\r\n?/g, '\n');
    const codeBlocks = [];

    html = html
        .replace(/(^[-*] .+)\n+(?=[-*] )/gm, '$1\n')
        .replace(/(^\d+[.)] .+)\n+(?=\d+[.)] )/gm, '$1\n');

    html = html.replace(/```(?:[a-zA-Z0-9_-]+)?\n?([\s\S]*?)```/g, (_, code) => {
        codeBlocks.push(`<pre><code>${code.trim()}</code></pre>`);
        return `@@CODE_BLOCK_${codeBlocks.length - 1}@@`;
    });

    html = html.replace(/((?:^\|.*\|(?:\n|$)){2,})/gm, block => {
        const rows = block.trim().split('\n').map(row => row.split('|').slice(1, -1).map(cell => cell.trim()));
        if (rows.length < 2 || !rows[1].every(cell => /^:?-{3,}:?$/.test(cell))) {
            return block;
        }

        const header = rows[0].map(cell => `<th>${cell}</th>`).join('');
        const body = rows.slice(2).map(row => `<tr>${row.map(cell => `<td>${cell}</td>`).join('')}</tr>`).join('');
        return `<div class="response-table"><table><thead><tr>${header}</tr></thead><tbody>${body}</tbody></table></div>`;
    });

    html = html
        .replace(/^### (.+)$/gm, '<h4>$1</h4>')
        .replace(/^## (.+)$/gm, '<h3>$1</h3>')
        .replace(/^# (.+)$/gm, '<h2>$1</h2>')
        .replace(/^[-*] (.+)$/gm, '<li>$1</li>')
        .replace(/^(\d+)[.)] (.+)$/gm, '<li>$1. $2</li>')
        .replace(/`([^`\n]+)`/g, '<code>$1</code>')
        .replace(/\*\*([^*\n]+)\*\*/g, '<strong>$1</strong>')
        .replace(/__([^_\n]+)__/g, '<strong>$1</strong>')
        .replace(/\[([^\]\n]+)\]\((https?:\/\/[^\s)]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>')
        .replace(/^---+$/gm, '<hr>')
        .replace(/^&gt; (.+)$/gm, '<blockquote>$1</blockquote>')
        .replace(/(^|\n)(<li>.*<\/li>(?:\n<li>.*<\/li>)*)/g, '$1<ul>$2</ul>')
        .replace(/\n{2,}/g, '</p><p>')
        .replace(/\n/g, '<br>');

    html = html
        .replace(/<br>(?=<\/(?:ul|li|h[234]|p|pre|div|hr|blockquote)>)/g, '')
        .replace(/(<\/(?:ul|li|h[234]|p|pre|div|hr|blockquote)>)<br>/g, '$1');

    html = `<p>${html}</p>`
        .replace(/<p>(<h[234]>)/g, '$1')
        .replace(/(<\/h[234]>)<\/p>/g, '$1')
        .replace(/<p>(<ul>)/g, '$1')
        .replace(/(<\/ul>)<\/p>/g, '$1')
        .replace(/<p>(<pre>)/g, '$1')
        .replace(/(<\/pre>)<\/p>/g, '$1');

    html = html.replace(/<p>(<div class="response-table">)/g, '$1').replace(/(<\/div>)<\/p>/g, '$1');

    return html
        .replace(/@@CODE_BLOCK_(\d+)@@/g, (_, index) => codeBlocks[index])
        .replace(/@@MATH_BLOCK_(\d+)@@/g, (_, index) => `<div class="math-placeholder math-block">${escapeHtml(mathBlocks[index])}</div>`)
        .replace(/@@MATH_INLINE_(\d+)@@/g, (_, index) => `<span class="math-placeholder">${escapeHtml(mathInline[index])}</span>`);
}

function renderMath(element) {
    element.querySelectorAll('.math-placeholder').forEach(mathElement => {
        const formula = mathElement.textContent;

        if (!window.katex) {
            return;
        }

        window.katex.render(formula, mathElement, {
            displayMode: mathElement.classList.contains('math-block'),
            throwOnError: false,
            strict: 'ignore'
        });
    });
}

// Event listeners
sendButton.addEventListener('click', sendMessage);

userInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        sendMessage();
    }
});

// Inicializar
window.addEventListener('DOMContentLoaded', () => {
    initTheme();
    userInput.focus();
    initStickyInput();
    initScrollToBottomButton();
});

// Fallback se DOMContentLoaded já passou
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTheme);
} else {
    initTheme();
}

/* ============================================================================
   SCROLL TO BOTTOM BUTTON - Mostrar/Esconder e Fazer Scroll Suave
   Aparece quando há conteúdo para rolar para baixo
   ============================================================================ */

function initScrollToBottomButton() {
    const scrollToBottomButton = document.getElementById('scrollToBottomButton');
    const messagesContainer = document.getElementById('messagesContainer');
    const promptsContainer = document.getElementById('promptsContainer');
    
    // Função para verificar se deve mostrar o botão
    function updateButtonVisibility() {
        // Verificar se há conteúdo abaixo da viewport
        const scrollableHeight = document.documentElement.scrollHeight - window.innerHeight;
        const currentScroll = window.scrollY || window.pageYOffset;
        
        // Mostrar se ainda há conteúdo abaixo (com margem de 100px)
        if (scrollableHeight - currentScroll > 100) {
            scrollToBottomButton.classList.add('visible');
        } else {
            scrollToBottomButton.classList.remove('visible');
        }
    }
    
    // Escutar eventos de scroll
    window.addEventListener('scroll', updateButtonVisibility);
    
    // Escutar mudanças no tamanho do container (quando mensagens/prompts são adicionados)
    const mutationObserver = new MutationObserver(() => {
        updateButtonVisibility();
    });
    
    // Observar alterações nos containers
    mutationObserver.observe(messagesContainer, { childList: true, subtree: true });
    mutationObserver.observe(promptsContainer, { childList: true, subtree: true });
    
    // Click para scroll suave
    scrollToBottomButton.addEventListener('click', () => {
        const scrollTarget = document.documentElement.scrollHeight;
        window.scrollTo({
            top: scrollTarget,
            behavior: 'smooth'
        });
    });
    
    // Verificar visibilidade inicial
    updateButtonVisibility();
}

/* ============================================================================
   STICKY INPUT - Mantém o campo de entrada fixo no topo quando faz scroll
   Melhora UX ao evitar que usuário tenha que voltar ao topo para digitar
   Solução: Combina Intersection Observer com detecção de scroll para evitar
   flickering durante zoom na parte superior - com zona de restrição expandida
   na área do chat section (base do container até cima)
   ============================================================================ */

function initStickyInput() {
    const inputWrapper = document.querySelector('.input-wrapper');
    const chatSection = document.querySelector('.chat-section');
    const messagesContainer = document.getElementById('messagesContainer');
    const promptsContainer = document.getElementById('promptsContainer');
    
    // Criar elemento observador (marcador invisível)
    const observerMarker = document.createElement('div');
    observerMarker.id = 'input-observer-marker';
    observerMarker.style.visibility = 'hidden';
    observerMarker.style.height = '1px';
    observerMarker.style.position = 'relative';
    
    // Inserir marcador logo após o input-wrapper
    inputWrapper.parentNode.insertBefore(observerMarker, inputWrapper.nextSibling);
    
    // Estado para rastrear sticky
    let isMarkerVisible = true;
    let scrollThreshold = 0;
    
    // Calcular threshold na primeira vez que o script roda
    function calculateScrollThreshold() {
        // Obter a posição da base do chat-section em relação ao topo da página
        const chatSectionBottom = chatSection.getBoundingClientRect().bottom + (window.scrollY || window.pageYOffset);
        // Threshold é a altura até a base do chat-section
        scrollThreshold = chatSectionBottom;
    }

    function updateStickyState() {
        const scrollY = window.scrollY || window.pageYOffset;
        const hasUserScrolledPastChatSection = scrollY > scrollThreshold;
        const shouldStick = hasUserScrolledPastChatSection && !isMarkerVisible;

        inputWrapper.classList.toggle('sticky-input-active', shouldStick);
        messagesContainer.classList.toggle('with-sticky-input', shouldStick);
        promptsContainer.classList.toggle('with-sticky-input', shouldStick);
    }
    
    // Configurar Intersection Observer
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            isMarkerVisible = entry.isIntersecting;
            updateStickyState();
        });
    }, observerOptions);
    
    // Event listener para scroll com threshold expandido
    window.addEventListener('scroll', () => {
        updateStickyState();
    });
    
    // Recalcular threshold ao redimensionar a janela
    window.addEventListener('resize', () => {
        calculateScrollThreshold();
        updateStickyState();
    });
    
    // Calcular threshold inicial
    calculateScrollThreshold();
    
    // Começar a observar o marcador
    observer.observe(observerMarker);
}

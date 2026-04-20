<div class="recovery-page-wrapper">
    <div class="recovery-main-content">
        <header class="recovery-header">
            <span class="badge-new">SEGUNDA VIA</span>
            <h1 class="main-title">Consulte suas faturas</h1>
            <p class="main-subtitle">Informe seu e-mail para localizar faturas pendentes de forma rápida e segura.</p>
        </header>

        <div class="card-glass">
            <form id="formBusca" class="search-form">
                <div class="input-container">
                    <div class="input-icon-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" id="emailInput" placeholder="exemplo@gmail.com" required>
                    </div>
                    <button type="submit" class="btn-submit">
                        <span class="btn-text">Consultar</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </form>
        </div>

        <div id="loading" class="result-placeholder d-none">
            <div class="elegant-loader">
                <div class="spinner"></div>
                <p>Buscando faturas no sistema...</p>
            </div>
        </div>

        <div id="resultado" class="result-container mt-4"></div>

        <footer class="recovery-footer">
            <div class="footer-badge">
                <i class="fas fa-shield-alt"></i>
                <span>Conexão Segura</span>
            </div>
            <div class="footer-info">
                Sua sessão está sendo registrada para segurança. IP: <span id="user-ip">...</span>
            </div>
        </footer>
    </div>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

:root {
    --primary-color: #7643C9;
    --primary-hover: #6336b1;
    --bg-page: #f8f9ff;
    --card-bg: #ffffff;
    --text-main: #1a1e26;
    --text-muted: #64748b;
    --input-bg: #f8fafc;
    --input-border: #e2e8f0;
    --input-focus: #7643C9;
    --shadow-soft: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
    --shadow-strong: 0 40px 60px -15px rgba(118, 67, 201, 0.1);
    --radius-lg: 24px;
    --radius-md: 16px;
    --radius-sm: 12px;
}

/* Forçado para modo claro se o tema pai for escuro, ou adaptativo */
.recovery-page-wrapper {
    --bg-page: #f8f9ff;
    --card-bg: #ffffff;
    --text-main: #1a1e26;
    --text-muted: #64748b;
}

@media (prefers-color-scheme: dark) {
    .recovery-page-wrapper {
        --bg-page: #0f172a;
        --card-bg: #1e293b;
        --text-main: #f8fafc;
        --text-muted: #94a3b8;
        --input-bg: #0f172a;
        --input-border: #334155;
    }
}

.recovery-page-wrapper {
    font-family: 'Plus Jakarta Sans', sans-serif;
    min-height: 480px;
    display: flex;
    justify-content: center;
    padding: 3rem 1.5rem;
    color: var(--text-main);
}

.recovery-main-content {
    width: 100%;
    max-width: 680px;
    text-align: center;
}

.badge-new {
    background: rgba(118, 67, 201, 0.1);
    color: var(--primary-color);
    padding: 6px 16px;
    border-radius: 100px;
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: 0.05em;
    display: inline-block;
    margin-bottom: 1.5rem;
}

.main-title {
    font-size: 3rem;
    font-weight: 800;
    color: var(--text-main);
    letter-spacing: -0.04em;
    line-height: 1.1;
    margin-bottom: 1.25rem;
}

.main-subtitle {
    color: var(--text-muted);
    font-size: 1.15rem;
    max-width: 520px;
    margin: 0 auto 3.5rem;
    line-height: 1.6;
}

.card-glass {
    background: var(--card-bg);
    padding: 1.5rem;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-strong);
    border: 1px solid var(--input-border);
}

.input-container {
    display: flex;
    gap: 0.75rem;
    background: var(--input-bg);
    padding: 0.6rem;
    border-radius: var(--radius-md);
    border: 1px solid var(--input-border);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.input-container:focus-within {
    border-color: var(--input-focus);
    box-shadow: 0 0 0 4px rgba(118, 67, 201, 0.15);
    background: var(--card-bg);
}

.input-icon-group {
    display: flex;
    align-items: center;
    flex: 1;
    padding-left: 1rem;
}

.input-icon-group i {
    color: var(--text-muted);
    margin-right: 14px;
    font-size: 1.2rem;
}

.input-icon-group input {
    width: 100%;
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
    padding: 14px 0;
    font-size: 1.05rem;
    font-weight: 500;
    color: var(--text-main);
    outline: none !important;
}

.btn-submit {
    background: var(--primary-color);
    color: white !important;
    border: none;
    padding: 0 2rem;
    border-radius: var(--radius-sm);
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-submit:hover {
    background: var(--primary-hover);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px -6px rgba(118, 67, 201, 0.4);
}

.btn-submit:active {
    transform: translateY(0);
}

.result-placeholder {
    margin-top: 4rem;
}

.elegant-loader {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.25rem;
}

.spinner {
    width: 56px;
    height: 56px;
    border: 5px solid rgba(118, 67, 201, 0.1);
    border-top: 5px solid var(--primary-color);
    border-radius: 50%;
    animation: spin 0.8s cubic-bezier(0.5, 0.1, 0.4, 0.9) infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.recovery-footer {
    margin-top: 5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.25rem;
}

.footer-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #059669;
    font-size: 0.85rem;
    font-weight: 700;
    background: rgba(16, 185, 129, 0.08);
    padding: 6px 16px;
    border-radius: 100px;
}

.footer-info {
    font-size: 0.8rem;
    color: var(--text-muted);
}

/* Estilos para Resultados - Injetados via PHP */
.invoice-card {
    background: var(--card-bg);
    border-radius: var(--radius-md);
    padding: 1.75rem;
    margin-bottom: 1.25rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border: 1px solid var(--input-border);
    text-align: left;
    transition: all 0.3s ease;
    animation: slideUp 0.4s ease forwards;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.invoice-card:hover {
    box-shadow: var(--shadow-soft);
    border-color: var(--primary-color);
    transform: scale(1.01);
}

.invoice-info h4 {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 800;
    color: var(--text-main);
}

.invoice-info p {
    margin: 6px 0 0;
    font-size: 0.95rem;
    color: var(--text-muted);
}

.invoice-actions {
    display: flex;
    gap: 0.75rem;
}

.btn-action {
    padding: 12px 20px;
    border-radius: var(--radius-sm);
    font-size: 0.95rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none !important;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-secondary {
    background: var(--input-bg);
    color: var(--text-main) !important;
    border: 1px solid var(--input-border);
}

.btn-primary-new {
    background: var(--primary-color);
    color: white !important;
    border: none;
}

.btn-action:hover {
    filter: brightness(1.05);
    transform: translateY(-3px);
}

.btn-primary-new:hover {
    box-shadow: 0 6px 15px -4px rgba(118, 67, 201, 0.4);
}

@media (max-width: 768px) {
    .main-title { font-size: 2.25rem; }
    .input-container { flex-direction: column; padding: 0.75rem; }
    .btn-submit { width: 100%; justify-content: center; height: 56px; }
    .invoice-card { flex-direction: column; align-items: flex-start; gap: 1.5rem; }
    .invoice-actions { width: 100%; flex-direction: column; }
    .btn-action { width: 100%; justify-content: center; }
}
</style>

<script>
$(document).ready(function() {
    // Buscar IP via API externa
    $.getJSON('https://api.ipify.org?format=json', function(data) {
        $("#user-ip").text(data.ip);
    });

    $("#formBusca").on("submit", function(e) {
        e.preventDefault();
        const $btn = $(this).find('.btn-submit');
        const $btnText = $btn.find('.btn-text');
        
        $("#resultado").hide().empty();
        $("#loading").removeClass("d-none");
        
        $btn.prop('disabled', true);
        $btnText.text('Buscando...');

        $.post("segunda-via.php", $(this).serialize(), function(data) {
            $("#loading").addClass("d-none");
            $("#resultado").html(data).fadeIn();
            $btn.prop('disabled', false);
            $btnText.text('Consultar');
        }).fail(function() {
            $("#loading").addClass("d-none");
            $("#resultado").html('<div class="alert alert-danger shadow-sm border-0 py-3"><i class="fas fa-exclamation-triangle mr-2"></i> Ops! Ocorreu um erro interno. Tente mais tarde.</div>').fadeIn();
            $btn.prop('disabled', false);
            $btnText.text('Consultar');
        });
    });

    // Feedback visual ao processar pagamento
    $(document).on("click", ".btn-action", function(e) {
        const $this = $(this);
        if($this.hasClass('btn-pay')) {
            setTimeout(function() {
                 $("#resultado").fadeOut();
                 $("#loading").removeClass("d-none").find('p').text('Encaminhando para o gateway de pagamento...');
                 
                 setTimeout(function() {
                     location.reload();
                 }, 5000);
            }, 800);
        }
    });
});
</script>

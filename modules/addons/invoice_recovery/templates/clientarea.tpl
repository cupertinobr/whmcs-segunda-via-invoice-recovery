<div class="recovery-wrapper">
    <div class="recovery-container">
        
        <header class="recovery-header text-center">
            <span class="view-badge">2ª VIA DE FATURA</span>
            <h1 class="view-title">Consulte suas faturas</h1>
            <p class="view-desc">Informe seu e-mail cadastrado para listar suas faturas pendentes.</p>
        </header>

        <div class="search-card">
            <form id="formBusca" class="search-form">
                <div class="input-box">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" id="emailInput" placeholder="seu@email.com.br" required>
                </div>
                <button type="submit" class="btn-primary-custom btn-submit">
                    <span class="btn-text">Consultar</span>
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <div id="loading" class="loading-state d-none text-center">
            <div class="loading-spinner"></div>
            <p>Estamos localizando suas faturas...</p>
        </div>

        <div id="resultado" class="results-wrapper"></div>

        <div class="security-info text-center">
            <p><i class="fas fa-lock"></i> Ambiente Seguro SSL — Seu IP (<span id="user-ip">...</span>) está registrado.</p>
        </div>

    </div>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.recovery-wrapper {
    font-family: 'Inter', sans-serif;
    padding: 40px 15px;
    display: flex;
    justify-content: center;
    color: #1a1e26;
}

/* Modo Escuro via Classe do Body (Lagom / WHMCS) */
body.dark-mode .recovery-wrapper, body.theme-dark .recovery-wrapper, body.dark .recovery-wrapper {
    color: #f8fafc;
}

.recovery-container {
    width: 100%;
    max-width: 650px;
}

.view-badge {
    background: rgba(118, 67, 201, 0.1);
    color: #7643C9;
    padding: 5px 15px;
    border-radius: 50px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1px;
    display: inline-block;
    margin-bottom: 20px;
}

.view-title {
    font-size: 32px;
    font-weight: 800;
    margin-bottom: 10px;
    letter-spacing: -1px;
}

.view-desc {
    font-size: 15px;
    color: #64748b;
    margin-bottom: 40px;
}

.search-card {
    background: #fff;
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.06);
    border: 1px solid #f1f5f9;
}

body.dark-mode .search-card, body.theme-dark .search-card, body.dark .search-card {
    background: #1e293b;
    border-color: #334155;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}

.search-form {
    display: flex;
    gap: 12px;
}

.input-box {
    flex: 1;
    display: flex;
    align-items: center;
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 0 20px;
    transition: all 0.2s;
}

body.dark-mode .input-box, body.theme-dark .input-box, body.dark .input-box {
    background: #0f172a;
    border-color: #334155;
}

.input-box:focus-within {
    border-color: #7643C9;
    box-shadow: 0 0 0 4px rgba(118, 67, 201, 0.1);
}

.input-box i {
    color: #94a3b8;
    margin-right: 15px;
}

.input-box input {
    background: transparent;
    border: none !important;
    width: 100%;
    height: 54px;
    outline: none !important;
    font-size: 15px;
    font-weight: 500;
    color: inherit;
}

.btn-primary-custom {
    background: #7643C9;
    color: #fff !important;
    border: none;
    border-radius: 12px;
    padding: 0 30px;
    font-weight: 700;
    font-size: 15px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.2s;
}

.btn-primary-custom:hover {
    background: #6336b1;
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(118, 67, 201, 0.3);
}

.loading-state {
    margin-top: 40px;
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid rgba(118, 67, 201, 0.1);
    border-top-color: #7643C9;
    border-radius: 50%;
    display: inline-block;
    animation: spin 0.8s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

.results-wrapper {
    margin-top: 30px;
}

.security-info {
    margin-top: 50px;
    color: #94a3b8;
    font-size: 13px;
}

/* Estilos de Fatura (Resultados) */
.invoice-card-custom {
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 15px;
    border: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.2s;
}

body.dark-mode .invoice-card-custom, body.theme-dark .invoice-card-custom, body.dark .invoice-card-custom {
    background: #1e293b;
    border-color: #334155;
}

.invoice-card-custom:hover {
    border-color: #7643C9;
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.05);
}

.invoice-meta h5 {
    margin: 0;
    font-weight: 800;
    font-size: 17px;
}

.invoice-meta p {
    margin: 4px 0 0;
    font-size: 14px;
    color: #64748b;
}

.invoice-btns {
    display: flex;
    gap: 8px;
}

.btn-act {
    padding: 10px 18px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none !important;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-act-light {
    background: #f1f5f9;
    color: #475569 !important;
}

body.dark-mode .btn-act-light, body.theme-dark .btn-act-light, body.dark .btn-act-light {
    background: #334155;
    color: #cbd5e1 !important;
}

.btn-act-primary {
    background: #7643C9;
    color: #fff !important;
}

@media (max-width: 600px) {
    .search-form { flex-direction: column; }
    .btn-primary-custom { height: 54px; justify-content: center; }
    .invoice-card-custom { flex-direction: column; align-items: flex-start; gap: 20px; }
    .invoice-btns { width: 100%; }
    .btn-act { flex: 1; justify-content: center; }
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
            $btnText.text('Consultar faturas agora');
        }).fail(function() {
            $("#loading").addClass("d-none");
            $("#resultado").html('<div class="alert alert-danger shadow-sm border-0"><i class="fas fa-exclamation-triangle mr-2"></i> Ops! Ocorreu um erro interno. Tente mais tarde.</div>').fadeIn();
            $btn.prop('disabled', false);
            $btnText.text('Consultar faturas agora');
        });
    });

    // Feedback visual ao processar pagamento
    $(document).on("click", ".btn-action", function(e) {
        if($(this).hasClass('btn-pay')) {
            setTimeout(function() {
                 $("#resultado").fadeOut();
                 $("#loading").removeClass("d-none").find('p').text('Encaminhando para pagamento...');
                 setTimeout(function() { location.reload(); }, 5000);
            }, 800);
        }
    });
});
</script>
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

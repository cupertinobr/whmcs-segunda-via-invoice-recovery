<div class="recovery-wrapper">
    <div class="recovery-container">
        
        <header class="recovery-header text-center">
            <span class="view-badge">2ª VIA DE FATURA</span>
            <h1 class="view-title">Consulte suas faturas</h1>
            <p class="view-desc">Informe seu CPF ou CNPJ para listar suas faturas pendentes.</p>
        </header>

        <div class="search-card">
            <form id="formBusca" class="search-form">
                <div class="input-box">
                    <i class="fas fa-id-card"></i>
                    <input type="text" name="documento" id="documentoInput" placeholder="000.000.000-00" required maxlength="18">
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

<link href="modules/addons/invoice_recovery/assets/style.css" rel="stylesheet" type="text/css" />


<script>
$(document).ready(function() {
    // Buscar IP via API externa
    $.getJSON('https://api.ipify.org?format=json', function(data) {
        $("#user-ip").text(data.ip);
    });

    // Filtro para aceitar apenas números e caracteres de formatação
    $("#documentoInput").on("input", function() {
        this.value = this.value.replace(/[^0-9.\-\/]/g, "");
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

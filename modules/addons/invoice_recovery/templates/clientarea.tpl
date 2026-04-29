<div class="recovery-wrapper">
    <div class="recovery-container">
        
        <header class="recovery-header text-center">
            <span class="view-badge">{$RECOVERY_LANG.title_recovery}</span>
            <h1 class="view-title">{$RECOVERY_LANG.search_title}</h1>
            <p class="view-desc">{$RECOVERY_LANG.search_desc}</p>
        </header>

        <div class="search-card">
            <form id="formBusca" class="search-form">
                <div class="input-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="documento" id="documentoInput" placeholder="{$RECOVERY_LANG.input_placeholder}" required>
                </div>
                <button type="submit" class="btn-primary-custom btn-submit">
                    <span class="btn-text">{$RECOVERY_LANG.btn_search}</span>
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <div id="loading" class="loading-state d-none text-center">
            <div class="loading-spinner"></div>
            <p>{$RECOVERY_LANG.loading_find}</p>
        </div>

        <div id="resultado" class="results-wrapper"></div>

        <div class="security-info text-center">
            <p><i class="fas fa-lock"></i> {$RECOVERY_LANG.security_info|replace:':ip':'<span id="user-ip">...</span>'}</p>
        </div>

    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link href="modules/addons/invoice_recovery/assets/style.css" rel="stylesheet" type="text/css" />


<script>
$(document).ready(function() {
    // Buscar IP via API externa
    $.getJSON('https://api.ipify.org?format=json', function(data) {
        $("#user-ip").text(data.ip);
    });

    // Filtro para aceitar apenas caracteres válidos para busca (números, letras, @, pontos)
    $("#documentoInput").on("input", function() {
        this.value = this.value.replace(/[^0-9a-zA-Z.@\-\/]/g, "");
    });

    $("#formBusca").on("submit", function(e) {
        e.preventDefault();
        const $btn = $(this).find('.btn-submit');
        const $btnText = $btn.find('.btn-text');
        
        $("#resultado").hide().empty();
        $("#loading").removeClass("d-none");
        
        $btn.prop('disabled', true);
        $btnText.text('{$RECOVERY_LANG.searching}');

        $.post("segunda-via.php", $(this).serialize(), function(data) {
            $("#loading").addClass("d-none");
            $("#resultado").html(data).fadeIn();
            $btn.prop('disabled', false);
            $btnText.text('{$RECOVERY_LANG.btn_search}');
        }).fail(function() {
            $("#loading").addClass("d-none");
            $("#resultado").html('<div class="alert alert-danger shadow-sm border-0 py-3"><i class="fas fa-exclamation-triangle mr-2"></i> {$RECOVERY_LANG.error_internal}</div>').fadeIn();
            $btn.prop('disabled', false);
            $btnText.text('{$RECOVERY_LANG.btn_search}');
        });
    });

    // Feedback visual ao processar pagamento
    $(document).on("click", ".btn-action", function(e) {
        const $this = $(this);
        if($this.hasClass('btn-pay')) {
            setTimeout(function() {
                 $("#resultado").fadeOut();
                 $("#loading").removeClass("d-none").find('p').text('{$RECOVERY_LANG.redirecting_gateway}');
                 
                 setTimeout(function() {
                     location.reload();
                 }, 5000);
            }, 800);
        }
    });
});
</script>

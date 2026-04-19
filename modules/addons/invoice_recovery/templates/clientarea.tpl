<div class="recovery-container">
   

    <div class="recovery-card">
        <div class="badge-container">
            <span class="recovery-badge">SEGUNDA VIA</span>
        </div>
        
        <h2 class="card-title">Consulte suas faturas</h2>
        <p class="card-subtitle">Digite o e-mail cadastrado na sua conta para visualizar faturas em aberto e realizar pagamentos.</p>

        <form id="formBusca" class="recovery-form">
            <div class="input-wrapper">
                <div class="input-group-custom">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="seu@email.com.br" required>
                </div>
                <button type="submit" class="btn-consultar">Consultar</button>
            </div>
        </form>

       <!-- <div class="security-footer">
            <span><i class="fas fa-shield-alt"></i> Ambiente seguro</span>
            <span><i class="fas fa-check-circle"></i> Dados criptografados</span>
        </div> -->
    </div>

    <div class="ip-info-banner">
        <i class="fas fa-fingerprint"></i>
        <p>Ambiente seguro — seu acesso (IPv4: <span id="user-ip">...</span>) é registrado para sua proteção.</p>
    </div>

    <div id="loading" class="text-center mt-5 d-none">
        <div class="spinner-border text-success mb-3" role="status" style="width: 3rem; height: 3rem;">
            <span class="sr-only">Carregando...</span>
        </div>
        <p class="text-muted font-italic">Localizando faturas...</p>
    </div>

    <div id="resultado" class="mt-4"></div>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

.recovery-container {
    font-family: 'Inter', sans-serif;
    max-width: 900px;
    margin: 40px auto;
    padding: 0 20px;
}

.recovery-header h1 {
    font-size: 32px;
    font-weight: 800;
    color: #111;
    margin-bottom: 5px;
}

.recovery-header p {
    font-size: 14px;
    color: #666;
    margin-bottom: 30px;
}

.recovery-card {
    background: #fff;
    border-radius: 20px;
    padding: 60px 40px;
    text-align: center;
    color: #fff;
    box-shadow: 0 20px 40px rgba(0,0,0,0.4);
}

.badge-container {
    margin-bottom: 25px;
}

.recovery-badge {
    background: rgba(118, 67, 201, 0.1);
    color: #7643C9;
    padding: 6px 16px;
    border-radius: 50px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.5px;
}

.card-title {
    font-size: 36px;
    font-weight: 800;
    margin-bottom: 15px;
    letter-spacing: -0.5px;
}

.card-subtitle {
    font-size: 16px;
    color: #999;
    max-width: 500px;
    margin: 0 auto 40px;
    line-height: 1.6;
}

.recovery-form {
    max-width: 600px;
    margin: 0 auto 30px;
}

.input-wrapper {
    display: flex;
    background: #1e1e1e;
    border: 1px solid #333;
    border-radius: 12px;
    padding: 8px;
    align-items: center;
    transition: border-color 0.3s;
}

.input-wrapper:focus-within {
    border-color: #7643C9;
}

.input-group-custom {
    display: flex;
    align-items: center;
    flex-grow: 1;
    padding-left: 15px;
}

.input-group-custom i {
    color: #666;
    margin-right: 12px;
}

.input-group-custom input {
    background: transparent;
    border: none;
    color: #fff;
    width: 100%;
    height: 45px;
    outline: none;
    font-size: 16px;
}

.btn-consultar {
    background: #7643C9;
    color: #fff;
    border: none;
    padding: 0 30px;
    height: 48px;
    border-radius: 8px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.3s, transform 0.2s;
}

.btn-consultar:hover {
    background: #690AC2;
    transform: translateY(-1px);
}

.security-footer {
    display: flex;
    justify-content: center;
    gap: 25px;
    margin-top: 10px;
}

.security-footer span {
    font-size: 13px;
    color: #7643C9;
    display: flex;
    align-items: center;
    gap: 8px;
}

.ip-info-banner {
    background: #fdfdfd;
    border: 1px solid #eee;
    border-radius: 12px;
    padding: 15px 25px;
    margin-top: 30px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.ip-info-banner i {
    color: #7643C9;
    font-size: 20px;
}

.ip-info-banner p {
    margin: 0;
    font-size: 13px;
    color: #777;
}

/* Estilos para o resultado */
#resultado .list-group-item {
    background: #fff;
    border-radius: 12px;
    margin-bottom: 10px;
    border: 1px solid #eee;
}

.spinner-border.text-success {
    color: #7643C9 !important;
}

.btn-success {
    background-color: #7643C9 !important;
    border-color: #7643C9 !important;
}

.btn-success:hover {
    background-color: #690AC2 !important;
    border-color: #690AC2 !important;
}

.text-primary {
    color: #7643C9 !important;
}

.badge-warning {
    background-color: #fff3cd;
    color: #856404;
}

@media (max-width: 600px) {
    .input-wrapper {
        flex-direction: column;
        background: transparent;
        border: none;
        padding: 0;
    }
    .input-group-custom {
        background: #1a1a1a;
        border: 1px solid #333;
        border-radius: 12px;
        margin-bottom: 10px;
        width: 100%;
    }
    .btn-consultar {
        width: 100%;
    }
    .recovery-card {
        padding: 40px 20px;
    }
    .card-title {
        font-size: 28px;
    }
}
</style>

<script>
$(document).ready(function() {
    // Buscar IP do usuário (simulação ou via serviço público já que não temos direto no template facilmente)
    $.getJSON('https://api.ipify.org?format=json', function(data) {
        $("#user-ip").text(data.ip);
    });

    $("#formBusca").on("submit", function(e) {
        e.preventDefault();
        $("#resultado").hide().empty();
        $("#loading").removeClass("d-none");
        
        $.post("ajax.php", $(this).serialize(), function(data) {
            $("#loading").addClass("d-none");
            $("#resultado").html(data).fadeIn();
        }).fail(function() {
            $("#loading").addClass("d-none");
            $("#resultado").html('<div class="alert alert-danger shadow-sm border-0"><i class="fas fa-exclamation-triangle mr-2"></i> Ops! Ocorreu um erro interno. Tente mais tarde.</div>').fadeIn();
        });
    });
});
</script>

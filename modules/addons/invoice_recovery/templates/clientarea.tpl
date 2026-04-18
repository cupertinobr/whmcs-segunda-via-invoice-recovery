<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-lg border-0 rounded-lg mt-5">
            <div class="card-header bg-primary text-white text-center py-4">
                <h3 class="mb-0 font-weight-bold">2ª Via de Pagamento</h3>
                <p class="mb-0 opacity-75">Acesso rápido às suas faturas pendentes</p>
            </div>
            <div class="card-body p-4 p-md-5">
                <form id="formBusca" class="mb-0">
                    <div class="form-group mb-4">
                        <label class="form-label font-weight-bold">E-mail da sua conta</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-right-0"><i class="fas fa-envelope text-muted"></i></span>
                            <input type="email" name="email" class="form-control border-left-0 bg-light py-2" placeholder="Digite seu e-mail cadastrado" >
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm transition-all py-3 font-weight-bold">
                        <i class="fas fa-search mr-2"></i> CONSULTAR FATURAS
                    </button>
                </form>

                <div id="loading" class="text-center mt-5 d-none">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="sr-only">Carregando...</span>
                    </div>
                    <p class="text-muted font-italic">Localizando faturas vinculadas...</p>
                </div>

                <div id="resultado" class="mt-4"></div>
            </div>
            <div class="card-footer bg-light text-center py-3 border-0">
                <p class="small text-muted mb-0"><i class="fas fa-lock mr-1"></i> Acesso seguro e verificado</p>
            </div>
        </div>
    </div>
</div>

<style>
.transition-all { transition: all 0.2s ease-in-out; }
.transition-all:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; filter: brightness(1.1); }
.opacity-75 { opacity: 0.75; }
.input-group-text { border-color: #ced4da; }
.form-control:focus { box-shadow: none; border-color: #007bff; }
</style>

<script>
$(document).ready(function() {
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

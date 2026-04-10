<?php
require_once __DIR__ . '/init.php';
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="templates/lagom2/assets/css/lagom.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="lagom">
<div class="container py-5">
<h3>2ª Via de Pagamento</h3>

<form id="formBusca">
<input name="email" class="form-control mb-2" placeholder="Email">
<input name="cpf" class="form-control mb-2" placeholder="CPF/CNPJ">
<button class="btn btn-primary w-100">Buscar</button>
</form>

<div id="resultado" class="mt-4"></div>
</div>

<script>
$('#formBusca').submit(function(e){
 e.preventDefault();
 $('#resultado').html('Buscando...');
 $.post('ajax.php', $(this).serialize(), function(data){
   $('#resultado').html(data);
 });
});
</script>
</body>
</html>

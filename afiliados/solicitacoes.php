<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Afiliados - Solicitações</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/afiliados.css">
</head>
<body>
    <?php require_once 'componentes/v1/nav.php'; ?>

    <div class="container page-content">
        <h1>Solicitações</h1>
        <p>Gerencie suas solicitações de saque e outras demandas.</p>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Solicitar Saque</h5>
                <form>
                    <div class="mb-3">
                        <label for="valor" class="form-label">Valor</label>
                        <input type="number" class="form-control" id="valor">
                    </div>
                    <button type="submit" class="btn btn-primary">Solicitar</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/temaToggle.js"></script>
</body>
</html>
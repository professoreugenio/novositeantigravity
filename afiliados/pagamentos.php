<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Afiliados - Pagamentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/afiliados.css">
</head>
<body>
    <?php require_once 'componentes/v1/nav.php'; ?>

    <div class="container page-content">
        <h1>Pagamentos</h1>
        <p>Acompanhe seus pagamentos e comissões.</p>
        <table class="table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Valor</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>01/04/2026</td>
                    <td>R$ 100,00</td>
                    <td>Pago</td>
                </tr>
                <tr>
                    <td>15/03/2026</td>
                    <td>R$ 50,00</td>
                    <td>Pendente</td>
                </tr>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/temaToggle.js"></script>
</body>
</html>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Afiliados - Extrato</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/afiliados.css">
</head>
<body>
    <?php require_once 'componentes/v1/nav.php'; ?>

    <div class="container page-content">
        <h1>Extrato</h1>
        <p>Veja o extrato de suas vendas e comissões.</p>
        <table class="table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Produto</th>
                    <th>Comissão</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>01/04/2026</td>
                    <td>Produto A</td>
                    <td>R$ 20,00</td>
                </tr>
                <tr>
                    <td>28/03/2026</td>
                    <td>Produto B</td>
                    <td>R$ 15,00</td>
                </tr>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/temaToggle.js"></script>
</body>
</html>
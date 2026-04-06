<?php
/**
 * Extração de Dados do Usuário
 * Descriptografa e formata as variáveis globais a partir do token da sessão.
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
// Valores Padrão ('Default') para evitar falhas ou warnings
$userCod = 0;
$userNome = '';
$userEmail = '';
$userNascimento = '';
$userExpiracao = 0;
$userIp = '';
// Verifica se a sessão do usuário é válida
if (isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado'] === true && !empty($_SESSION['startusuario'])) {
    // Descriptografa a string injetada no token
    $Decdadosuser = encrypt_secure($_SESSION['startusuario'], 'd');
    if (is_string($Decdadosuser) && strpos($Decdadosuser, '&') !== false) {
        // Quebra a string delimitada por "&"
        $dadosArray = explode('&', $Decdadosuser);
        // Posição 0: Código do Usuário
        $codigoUser = isset($dadosArray[0]) ? (int)$dadosArray[0] : 0;
        $codigoUser2 = isset($dadosArray[0]) ? (int)$dadosArray[0] : 0;
        // Posição 1: Nome
        $userNome = $dadosArray[1]??'0';
        $n_parts = explode(' ', trim((string)$userNome));
        $userNome = htmlspecialchars($n_parts[0], ENT_QUOTES, 'UTF-8') . (isset($n_parts[1]) ? ' ' . mb_substr(htmlspecialchars($n_parts[1], ENT_QUOTES, 'UTF-8'), 0, 1, 'UTF-8') . '.' : '');
        // Posição 2: E-mail
        $userEmail = isset($dadosArray[2]) ? trim($dadosArray[2]) : '';
        // Posição 3: Data de Nascimento
        $userNascimento = isset($dadosArray[3]) ? trim($dadosArray[3]) : '';
        // Posição 4: Timestamp de Limite/Duração de Tempo
        $userExpiracao = isset($dadosArray[4]) ? (int)$dadosArray[4] : 0;
        // Posição 5: IP do Usuário na ocasião do login
        $userIp = isset($dadosArray[5]) ? trim($dadosArray[5]) : '';


        // Último acesso
        $queryUltimoAcesso = $con->prepare("SELECT * FROM a_site_registraacessos WHERE idusuariora = :idusuario  ORDER BY datara DESC, horara DESC LIMIT 1 ");
        $queryUltimoAcesso->bindParam(":idusuario", $codigoUser);
        // Executa a consulta
        $queryUltimoAcesso->execute();
        $rwUltAcesso = $queryUltimoAcesso->fetch(PDO::FETCH_ASSOC);
        $ultimadata = isset($rwUltAcesso['datara']) ? databr($rwUltAcesso['datara']) : 'Sem registro';
        $ultihorai = isset($rwUltAcesso['horara']) ? horabr($rwUltAcesso['horara']) : 'Sem registro';
        $ultihoraf = isset($rwUltAcesso['horafinalra']) ? horabr($rwUltAcesso['horafinalra']) : 'Sem registro';
        $tempoRestante = $userExpiracao - time();
        if ($tempoRestante > 0) {
            $horas = floor($tempoRestante / 3600);
            $minutos = floor(($tempoRestante % 3600) / 60);
            $userTempoRestante = "<i class='bi bi-stopwatch text-primary me-1'></i> <span class='fw-medium'></span> {$horas}h {$minutos}m";
        }
        else {
            $userTempoRestante = "<i class='bi bi-stopwatch text-danger me-1'></i> <span class='fw-medium text-danger'>Tempo Esgotado</span>";
            sleep(5);
            header('Location: ../');
            exit;
        }
    }
}
else {
    // Redireciona o usuário para a raiz do site se a sessão não for válida ou estiver vazia
    header('Location: ../');
    exit;
}
?>

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
        $userCod = isset($dadosArray[0]) ? (int)$dadosArray[0] : 0;
        
        // Posição 1: Nome
        $userNome = isset($dadosArray[1]) ? trim($dadosArray[1]) : '';
        
        // Posição 2: E-mail
        $userEmail = isset($dadosArray[2]) ? trim($dadosArray[2]) : '';
        
        // Posição 3: Data de Nascimento
        $userNascimento = isset($dadosArray[3]) ? trim($dadosArray[3]) : '';
        
        // Posição 4: Timestamp de Limite/Duração de Tempo
        $userExpiracao = isset($dadosArray[4]) ? (int)$dadosArray[4] : 0;
        
        // Posição 5: IP do Usuário na ocasião do login
        $userIp = isset($dadosArray[5]) ? trim($dadosArray[5]) : '';
    }
} else {
    // Redireciona o usuário para a raiz do site se a sessão não for válida ou estiver vazia
    header('Location: ../');
    exit;
}
?>

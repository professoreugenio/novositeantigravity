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
if (isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado'] === true && !empty($_SESSION['startusuario']) && !empty($_SESSION['dadoscurso'])) {

    // Descriptografa a string injetada no token
    $Decdadoscurso = encrypt_secure($_SESSION['dadoscurso'], 'd');

    if (is_string($Decdadoscurso) && strpos($Decdadoscurso, '&') !== false) {
        // Quebra a string delimitada por "&"
        $dadosArray = explode('&', $Decdadoscurso);
        // Posição 0: Código do Curso
        $idCurso = isset($dadosArray[0]) ? (int) $dadosArray[0] : 0;
        // Posição 1: Código da Turma
        $idTurma = isset($dadosArray[1]) ? trim($dadosArray[1]) : '';

        // Variáveis para receber os nomes
        $QueryCursoNome = '';
        $QueryTurmaNome = '';

        try {
            if (!isset($con) || !$con instanceof PDO) {
                $con = config::connect();
            }

            $stmtQCurso = $con->prepare("
                SELECT 
                    c.nomecurso, 
                    t.nometurma 
                FROM new_sistema_cursos c
                LEFT JOIN new_sistema_cursos_turmas t ON c.codigocursos = t.codcursost AND t.codigoturma = :idTurma
                WHERE c.codigocursos = :idCurso
                LIMIT 1
            ");
            $stmtQCurso->bindValue(':idCurso', $idCurso, PDO::PARAM_INT);
            $stmtQCurso->bindValue(':idTurma', $idTurma, PDO::PARAM_STR);
            $stmtQCurso->execute();
            
            $rowQCurso = $stmtQCurso->fetch(PDO::FETCH_ASSOC);
            if ($rowQCurso) {
                $nomeCurso = trim((string) $rowQCurso['nomecurso']);
                $nomeTurma = trim((string) $rowQCurso['nometurma']);
            }
        } catch (Throwable $e) {
            // Em caso de erro na consulta, mantemos as variáveis vazias
        }
    }
} else {
    // Redireciona o usuário para a raiz do site se a sessão não for válida ou estiver vazia
    header('Location: ../');
    exit;
}
?>
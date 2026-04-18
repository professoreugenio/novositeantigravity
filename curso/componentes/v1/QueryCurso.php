<?php

/**
 * Extração de Dados do Usuário
 * Descriptografa e formata as variáveis globais a partir do token da sessão.
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Valores padrão
$idCurso = 0;
$idTurma = '';
$nomeCurso = 'Não definido';
$nomeTurma = 'Turma não definida';
$linkWhatsapp = '#';
$dataInicioST = '00/00/0000';
$dataFimST = '00/00/0000';
$totalAulas = 0;
$assistidas = 0;
$percentualCurso = 0;

// Verifica se a sessão do usuário é válida
if (
    isset($_SESSION['usuario_logado']) &&
    $_SESSION['usuario_logado'] === true &&
    !empty($_SESSION['startusuario']) &&
    !empty($_SESSION['dadoscurso'])
) {
    $Decdadoscurso = encrypt_secure($_SESSION['dadoscurso'], 'd');

    if (is_string($Decdadoscurso) && strpos($Decdadoscurso, '&') !== false) {
        $dadosArray = explode('&', $Decdadoscurso);

        $idCurso = isset($dadosArray[0]) ? (int)$dadosArray[0] : 0;
        $idTurma = isset($dadosArray[1]) ? trim((string)$dadosArray[1]) : '';

        try {
            if (!isset($con) || !$con instanceof PDO) {
                $con = config::connect();
            }

            $stmtQCurso = $con->prepare("
                SELECT 
                    c.nomecurso, 
                    t.nometurma, 
                    t.datainiciost, 
                    t.datafimst, 
                    t.linkwhatsapp 
                FROM new_sistema_cursos c
                LEFT JOIN new_sistema_cursos_turmas t 
                    ON c.codigocursos = t.codcursost 
                   AND t.codigoturma = :idTurma
                WHERE c.codigocursos = :idCurso
                LIMIT 1
            ");
            $stmtQCurso->bindValue(':idCurso', $idCurso, PDO::PARAM_INT);
            $stmtQCurso->bindValue(':idTurma', $idTurma, PDO::PARAM_STR);
            $stmtQCurso->execute();

            $rowQCurso = $stmtQCurso->fetch(PDO::FETCH_ASSOC);

            if ($rowQCurso) {
                $nomeCurso = trim((string)($rowQCurso['nomecurso'] ?? 'Não definido'));
                $nomeTurma = trim((string)($rowQCurso['nometurma'] ?? 'Turma não definida'));
                $linkWhatsapp = trim((string)($rowQCurso['linkwhatsapp'] ?? '#'));
                $dataInicioST = trim((string)($rowQCurso['datainiciost'] ?? '00/00/0000'));
                $dataFimST = trim((string)($rowQCurso['datafimst'] ?? '00/00/0000'));
            }

            $stmtTotal = $con->prepare("
                SELECT COUNT(*) AS total
                FROM a_aluno_publicacoes_cursos
                WHERE idcursopc = :idCurso
            ");
            $stmtTotal->bindValue(':idCurso', $idCurso, PDO::PARAM_INT);
            $stmtTotal->execute();
            $totalRow = $stmtTotal->fetch(PDO::FETCH_ASSOC);
            $totalAulas = (int)($totalRow['total'] ?? 0);

            $stmtAssistidas = $con->prepare("
                SELECT COUNT(DISTINCT idpublicaa) AS assistidas
                FROM a_aluno_andamento_aula
                WHERE idalunoaa = :idUser
                  AND idcursoaa = :idCurso
            ");
            $stmtAssistidas->bindValue(':idUser', $codigoUser, PDO::PARAM_INT);
            $stmtAssistidas->bindValue(':idCurso', $idCurso, PDO::PARAM_INT);
            $stmtAssistidas->execute();
            $assistidasRow = $stmtAssistidas->fetch(PDO::FETCH_ASSOC);
            $assistidas = (int)($assistidasRow['assistidas'] ?? 0);

            $percentualCurso = ($totalAulas > 0)
                ? (int)round(($assistidas / $totalAulas) * 100)
                : 0;

            if ($percentualCurso > 100) {
                $percentualCurso = 100;
            }
        } catch (Throwable $e) {
            $percentualCurso = 0;
            // Opcional para depuração:
            // error_log('Erro em QueryCurso.php: ' . $e->getMessage());
        }
    }
} else {
    header('Location: ../');
    exit;
}

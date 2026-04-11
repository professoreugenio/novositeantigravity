<?php
/**
 * Extração de Dados da Publicação (Aula)
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$idPublicacaoAtiva = 0;
$QueryPubAutor = '';
$QueryPubTitulo = '';
$QueryPubTexto = '';
$QueryPubAtividade = 0;
$QueryPubTemAtividade = false;

if (isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado'] === true && !empty($_SESSION['dadospublicacao'])) {
    $decPub = encrypt_secure($_SESSION['dadospublicacao'], 'd');
    if (!empty($decPub)) {
        $idPublicacaoAtiva = (int) $decPub;

        try {
            if (!isset($con) || !$con instanceof PDO) {
                $con = config::connect();
            }
            $stmtPub = $con->prepare("
                SELECT autor, titulo, texto, codigoatividade_sp 
                FROM new_sistema_publicacoes_PJA 
                WHERE codigopublicacoes = :idPub 
                LIMIT 1
            ");
            $stmtPub->bindValue(':idPub', $idPublicacaoAtiva, PDO::PARAM_INT);
            $stmtPub->execute();
            if ($rowPub = $stmtPub->fetch(PDO::FETCH_ASSOC)) {
                $PubAutor = $rowPub['autor'];
                $PubTitulo = $rowPub['titulo'];
                $PubTexto = $rowPub['texto'];
                $PubAtividade = (int) $rowPub['codigoatividade_sp'];
                $PubTemAtividade = ($PubAtividade > 0);

                // --- LOG DE ANDAMENTO (AULA ASSISTIDA) ---
                if (!empty($codigoUser) && !empty($idCurso)) {
                    $hj = date('Y-m-d');
                    $agora = date('H:i:s');
                    
                    $stmtCheckAndamento = $con->prepare("
                        SELECT codigoandamento FROM a_aluno_andamento_aula 
                        WHERE idalunoaa = :idUser 
                          AND idpublicaa = :idPub 
                          AND dataaa = :hj 
                        LIMIT 1
                    ");
                    $stmtCheckAndamento->bindValue(':idUser', $codigoUser, PDO::PARAM_INT);
                    $stmtCheckAndamento->bindValue(':idPub', $idPublicacaoAtiva, PDO::PARAM_INT);
                    $stmtCheckAndamento->bindValue(':hj', $hj, PDO::PARAM_STR);
                    $stmtCheckAndamento->execute();
                    
                    if ($stmtCheckAndamento->rowCount() > 0) {
                        $stmtUpdAndamento = $con->prepare("
                            UPDATE a_aluno_andamento_aula 
                            SET horaaa = :agora 
                            WHERE idalunoaa = :idUser AND idpublicaa = :idPub AND dataaa = :hj
                        ");
                        $stmtUpdAndamento->bindValue(':agora', $agora, PDO::PARAM_STR);
                        $stmtUpdAndamento->bindValue(':idUser', $codigoUser, PDO::PARAM_INT);
                        $stmtUpdAndamento->bindValue(':idPub', $idPublicacaoAtiva, PDO::PARAM_INT);
                        $stmtUpdAndamento->bindValue(':hj', $hj, PDO::PARAM_STR);
                        $stmtUpdAndamento->execute();
                    } else {
                        $pIdTurma = !empty($idTurma) ? (int)$idTurma : 0;
                        $pIdModulo = !empty($idModuloAtivo) ? (int)$idModuloAtivo : 0;
                        
                        $stmtInsAndamento = $con->prepare("
                            INSERT INTO a_aluno_andamento_aula 
                            (idalunoaa, idpublicaa, idcursoaa, idturmaaa, idmoduloaa, dataaa, horaaa) 
                            VALUES 
                            (:idUser, :idPub, :idCur, :idTur, :idMod, :hj, :agora)
                        ");
                        $stmtInsAndamento->bindValue(':idUser', $codigoUser, PDO::PARAM_INT);
                        $stmtInsAndamento->bindValue(':idPub', $idPublicacaoAtiva, PDO::PARAM_INT);
                        $stmtInsAndamento->bindValue(':idCur', $idCurso, PDO::PARAM_INT);
                        $stmtInsAndamento->bindValue(':idTur', $pIdTurma, PDO::PARAM_INT);
                        $stmtInsAndamento->bindValue(':idMod', $pIdModulo, PDO::PARAM_INT);
                        $stmtInsAndamento->bindValue(':hj', $hj, PDO::PARAM_STR);
                        $stmtInsAndamento->bindValue(':agora', $agora, PDO::PARAM_STR);
                        $stmtInsAndamento->execute();
                    }
                }
            }
        } catch (Throwable $e) {
        }
    }
}
?>
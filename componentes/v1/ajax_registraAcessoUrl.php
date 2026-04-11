<?php

declare(strict_types=1);

define('BASEPATH', true);

/*
|------------------------------------------------------------------
| Caminhos
| Supondo este arquivo em: /public_html/componentes/v1/ajax_registraAcessoUrl.php
|------------------------------------------------------------------
*/
define('PUBLIC_ROOT', dirname(__DIR__, 2));   // /public_html
define('APP_ROOT', dirname(PUBLIC_ROOT));     // pasta acima da raiz pública
define('COMPONENTES_ROOT', APP_ROOT . '/componentes');

require_once COMPONENTES_ROOT . '/v1/class.conexao.php';
require_once COMPONENTES_ROOT . '/v1/autenticacao.php';

header('Content-Type: application/json; charset=utf-8');

/** @var PDO $con */

function jsonExit(array $data): void
{
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function getDecSession(string $key): string
{
    if (empty($_SESSION[$key])) {
        return '';
    }

    $dec = encrypt_secure((string)$_SESSION[$key], 'd');
    return is_string($dec) ? trim($dec) : '';
}

function getTokenPart(string $token, int $index = 0): string
{
    if ($token === '') {
        return '';
    }

    $parts = explode('&', $token);
    return trim((string)($parts[$index] ?? ''));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonExit([
        'ok' => false,
        'msg' => 'Método inválido.'
    ]);
}

try {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    /*
    |------------------------------------------------------------------
    | Usuário logado
    |------------------------------------------------------------------
    */
    if (
        !isset($_SESSION['usuario_logado']) ||
        $_SESSION['usuario_logado'] !== true ||
        empty($_SESSION['startusuario'])
    ) {
        jsonExit([
            'ok' => false,
            'msg' => 'Usuário não autenticado.'
        ]);
    }

    $decUser = getDecSession('startusuario');
    $codigoUser = (int) getTokenPart($decUser, 0);

    if ($codigoUser <= 0) {
        jsonExit([
            'ok' => false,
            'msg' => 'Código do usuário inválido.'
        ]);
    }

    /*
    |------------------------------------------------------------------
    | Cookie registraacesso
    | chavedeacesso&data primeiro acesso&ip
    |------------------------------------------------------------------
    */
    $chaverah = '';
    if (!empty($_COOKIE['registraacesso'])) {
        $decAcesso = encrypt_secure((string)$_COOKIE['registraacesso'], 'd');
        if (is_string($decAcesso) && $decAcesso !== '') {
            $chaverah = getTokenPart($decAcesso, 0);
        }
    }

    /*
    |------------------------------------------------------------------
    | Dados do curso / turma
    |------------------------------------------------------------------
    */
    $idCurso = 0;
    $idTurma = 0;

    if (!empty($_SESSION['dadoscurso'])) {
        $decCurso = getDecSession('dadoscurso');
        $idCurso = (int) getTokenPart($decCurso, 0);
        $idTurma = (int) getTokenPart($decCurso, 1);
    }

    /*
    |------------------------------------------------------------------
    | Dados da publicação
    |------------------------------------------------------------------
    */
    $idPublicacao = 0;
    if (!empty($_SESSION['dadospublicacao'])) {
        $decPublicacao = getDecSession('dadospublicacao');
        $idPublicacao = (int) getTokenPart($decPublicacao, 0);
        if ($idPublicacao <= 0 && $decPublicacao !== '') {
            $idPublicacao = (int) $decPublicacao;
        }
    }

    if ($idPublicacao <= 0) {
        jsonExit([
            'ok' => false,
            'msg' => 'Publicação inválida.'
        ]);
    }

    /*
    |------------------------------------------------------------------
    | Dados do módulo
    |------------------------------------------------------------------
    */
    $idModulo = 0;
    if (!empty($_SESSION['dadosmodulo'])) {
        $decModulo = getDecSession('dadosmodulo');
        $idModulo = (int) getTokenPart($decModulo, 0);
        if ($idModulo <= 0 && $decModulo !== '') {
            $idModulo = (int) $decModulo;
        }
    }

    /*
    |------------------------------------------------------------------
    | Nome da página atual
    | IMPORTANTE:
    | Como o registro é via AJAX, a página precisa enviar o nome atual.
    |------------------------------------------------------------------
    */
    $urlrah = trim((string)($_POST['urlrah'] ?? ''));
    $urlrah = $urlrah !== '' ? basename($urlrah) : 'aula.php';

    /*
    |------------------------------------------------------------------
    | Dispositivo
    | Vem do autenticacao.php
    |------------------------------------------------------------------
    */
    $dispositivorah = isset($dispositivo) ? (int)$dispositivo : 0;

    /*
    |------------------------------------------------------------------
    | Buscar título da publicação
    |------------------------------------------------------------------
    */
    $titulopubah = '';

    $stmtPub = $con->prepare("
        SELECT titulo
        FROM new_sistema_publicacoes_PJA
        WHERE codigopublicacoes = :idPub
        LIMIT 1
    ");
    $stmtPub->bindValue(':idPub', $idPublicacao, PDO::PARAM_INT);
    $stmtPub->execute();

    $pub = $stmtPub->fetch(PDO::FETCH_ASSOC);
    if ($pub) {
        $titulopubah = trim((string)($pub['titulo'] ?? ''));
    }

    /*
    |------------------------------------------------------------------
    | Não permitir duplicado no mesmo dia
    |------------------------------------------------------------------
    */
    $stmtCheck = $con->prepare("
        SELECT codigoregistracessohurl
        FROM a_site_registraacessosurl
        WHERE idusuariorah = :idusuario
          AND idpublicacaoah = :idpublicacao
          AND datarah = CURDATE()
        LIMIT 1
    ");
    $stmtCheck->bindValue(':idusuario', $codigoUser, PDO::PARAM_INT);
    $stmtCheck->bindValue(':idpublicacao', $idPublicacao, PDO::PARAM_INT);
    $stmtCheck->execute();

    $jaExiste = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($jaExiste) {
        jsonExit([
            'ok' => true,
            'duplicado' => true,
            'msg' => 'Acesso já registrado hoje para esta publicação.'
        ]);
    }

    /*
    |------------------------------------------------------------------
    | Inserir acesso
    |------------------------------------------------------------------
    */
    $stmtIns = $con->prepare("
        INSERT INTO a_site_registraacessosurl
        (
            idusuariorah,
            chaverah,
            dispositivorah,
            urlrah,
            idcursoah,
            idturmaah,
            idpublicacaoah,
            idmoduloah,
            titulopubah,
            datarah,
            horarah
        ) VALUES (
            :idusuariorah,
            :chaverah,
            :dispositivorah,
            :urlrah,
            :idcursoah,
            :idturmaah,
            :idpublicacaoah,
            :idmoduloah,
            :titulopubah,
            CURDATE(),
            CURTIME()
        )
    ");

    $stmtIns->bindValue(':idusuariorah', $codigoUser, PDO::PARAM_INT);
    $stmtIns->bindValue(':chaverah', $chaverah, PDO::PARAM_STR);
    $stmtIns->bindValue(':dispositivorah', $dispositivorah, PDO::PARAM_INT);
    $stmtIns->bindValue(':urlrah', $urlrah, PDO::PARAM_STR);
    $stmtIns->bindValue(':idcursoah', $idCurso, PDO::PARAM_INT);
    $stmtIns->bindValue(':idturmaah', $idTurma, PDO::PARAM_INT);
    $stmtIns->bindValue(':idpublicacaoah', $idPublicacao, PDO::PARAM_INT);
    $stmtIns->bindValue(':idmoduloah', $idModulo, PDO::PARAM_INT);
    $stmtIns->bindValue(':titulopubah', $titulopubah, PDO::PARAM_STR);
    $stmtIns->execute();

    jsonExit([
        'ok' => true,
        'duplicado' => false,
        'msg' => 'Acesso registrado com sucesso.'
    ]);
} catch (Throwable $e) {
    jsonExit([
        'ok' => false,
        'msg' => 'Erro ao registrar acesso.',
        'erro' => $e->getMessage()
    ]);
}

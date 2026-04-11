<?php
declare(strict_types=1);
define('BASEPATH', true);
// sobe até a pasta acima da raiz pública
define('APP_ROOT', dirname(__DIR__, 4));
// componentes fora da raiz do site
define('COMPONENTES_ROOT', APP_ROOT . '/componentes');
require_once COMPONENTES_ROOT . '/v1/class.conexao.php';
require_once COMPONENTES_ROOT . '/v1/autenticacao.php';
header('Content-Type: application/json; charset=utf-8');
function jsonExit(array $data, int $httpCode = 200): void
{
    http_response_code($httpCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonExit([
            'status' => false,
            'msg' => 'Método inválido.'
        ], 405);
    }
    if (!isset($con) || !$con instanceof PDO) {
        jsonExit([
            'status' => false,
            'msg' => 'Conexão com o banco não disponível.'
        ], 500);
    }
    $codigoUsuarioAtual = (int)($codigoUser ?? $userCod ?? 0);
    if ($codigoUsuarioAtual <= 0) {
        jsonExit([
            'status' => false,
            'msg' => 'Usuário não autenticado.'
        ], 401);
    }
    $codigocadastro = (int)($_POST['codigocadastro'] ?? 0);
    if ($codigocadastro !== $codigoUsuarioAtual) {
        jsonExit([
            'status' => false,
            'msg' => 'Operação não autorizada.'
        ], 403);
    }
    $nome              = trim((string)($_POST['nome'] ?? ''));
    $email             = trim((string)($_POST['email'] ?? ''));
    $emailConfirmacao  = trim((string)($_POST['email_confirmacao'] ?? ''));
    $datanascimento_sc = trim((string)($_POST['datanascimento_sc'] ?? ''));
    $estado            = trim((string)($_POST['estado'] ?? ''));
    $celular           = trim((string)($_POST['celular'] ?? ''));
    $possuipc          = (string)($_POST['possuipc'] ?? '');
    $novaSenha         = (string)($_POST['nova_senha'] ?? '');
    $confirmarSenha    = (string)($_POST['confirmar_senha'] ?? '');
    if ($nome === '') {
        jsonExit([
            'status' => false,
            'msg' => 'Informe o nome completo.'
        ], 422);
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonExit([
            'status' => false,
            'msg' => 'Informe um e-mail válido.'
        ], 422);
    }
    if ($email !== $emailConfirmacao) {
        jsonExit([
            'status' => false,
            'msg' => 'Os e-mails informados não conferem.'
        ], 422);
    }
    $estado = mb_strtoupper($estado, 'UTF-8');
    if ($estado !== '' && !preg_match('/^[A-Z]{2}$/', $estado)) {
        jsonExit([
            'status' => false,
            'msg' => 'Estado inválido.'
        ], 422);
    }
    if ($datanascimento_sc !== '') {
        $dt = DateTime::createFromFormat('Y-m-d', $datanascimento_sc);
        if (!$dt || $dt->format('Y-m-d') !== $datanascimento_sc) {
            jsonExit([
                'status' => false,
                'msg' => 'Data de nascimento inválida.'
            ], 422);
        }
    } else {
        $datanascimento_sc = null;
    }
    $celular = preg_replace('/\D+/', '', $celular);
    if ($celular !== '' && (strlen($celular) < 10 || strlen($celular) > 11)) {
        jsonExit([
            'status' => false,
            'msg' => 'Celular inválido.'
        ], 422);
    }
    if ($possuipc !== '0' && $possuipc !== '1') {
        $possuipc = '0';
    }
    $trocarSenha = ($novaSenha !== '' || $confirmarSenha !== '');
    if ($trocarSenha) {
        if ($novaSenha !== $confirmarSenha) {
            jsonExit([
                'status' => false,
                'msg' => 'A confirmação da nova senha não confere.'
            ], 422);
        }
        if (mb_strlen($novaSenha, 'UTF-8') < 4) {
            jsonExit([
                'status' => false,
                'msg' => 'A nova senha deve ter pelo menos 4 caracteres.'
            ], 422);
        }
    }
    $stEmail = $con->prepare("
        SELECT codigocadastro
        FROM new_sistema_cadastro
        WHERE email = :email
          AND codigocadastro <> :cod
        LIMIT 1
    ");
    $stEmail->bindValue(':email', $email);
    $stEmail->bindValue(':cod', $codigoUsuarioAtual, PDO::PARAM_INT);
    $stEmail->execute();
    if ($stEmail->fetch(PDO::FETCH_ASSOC)) {
        jsonExit([
            'status' => false,
            'msg' => 'Este e-mail já está em uso por outro cadastro.'
        ], 409);
    }
    $campos = "
        nome = :nome,
        email = :email,
        datanascimento_sc = :datanascimento_sc,
        estado = :estado,
        celular = :celular,
        possuipc = :possuipc
    ";
    $params = [
        ':nome' => $nome,
        ':email' => $email,
        ':datanascimento_sc' => $datanascimento_sc,
        ':estado' => $estado !== '' ? $estado : null,
        ':celular' => $celular !== '' ? $celular : null,
        ':possuipc' => (int)$possuipc,
        ':cod' => $codigoUsuarioAtual,
    ];
    if ($trocarSenha) {
        $senhaCriptografada = encrypt_secure($email . '&' . $novaSenha, 'e');
        $campos .= ", senha = :senha";
        $params[':senha'] = $senhaCriptografada;
    }
    $sql = "UPDATE new_sistema_cadastro SET {$campos} WHERE codigocadastro = :cod";
    $st = $con->prepare($sql);
    foreach ($params as $chave => $valor) {
        if ($chave === ':cod' || $chave === ':possuipc') {
            $st->bindValue($chave, (int)$valor, PDO::PARAM_INT);
        } elseif ($valor === null) {
            $st->bindValue($chave, null, PDO::PARAM_NULL);
        } else {
            $st->bindValue($chave, $valor, PDO::PARAM_STR);
        }
    }
    $st->execute();
    jsonExit([
        'status' => true,
        'msg' => $trocarSenha
            ? 'Perfil, e-mail e senha atualizados com sucesso.'
            : 'Dados do perfil atualizados com sucesso.'
    ]);
} catch (Throwable $e) {
    jsonExit([
        'status' => false,
        'msg' => 'Erro ao atualizar os dados: ' . $e->getMessage()
    ], 500);
}
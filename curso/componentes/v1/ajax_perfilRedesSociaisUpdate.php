<?php

declare(strict_types=1);

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 4));
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

function normalizeSocialLink(?string $value, string $baseUrl): ?string
{
    $value = trim((string)$value);

    if ($value === '') {
        return null;
    }

    $value = preg_replace('/\s+/', '', $value);
    $value = (string)$value;

    if ($value === '') {
        return null;
    }

    if (preg_match('~^www\.~i', $value)) {
        $value = 'https://' . $value;
    }

    if (!preg_match('~^https?://~i', $value) && preg_match('~^[a-z0-9.-]+\.[a-z]{2,}(/.*)?$~i', $value)) {
        $value = 'https://' . $value;
    }

    if (filter_var($value, FILTER_VALIDATE_URL)) {
        return $value;
    }

    $value = ltrim($value, '@');
    $value = trim($value, '/');
    $value = preg_replace('/[^a-zA-Z0-9._-]/', '', $value);
    $value = (string)$value;

    if ($value === '') {
        return null;
    }

    return rtrim($baseUrl, '/') . '/' . $value;
}

function validarTamanho(?string $value, string $label): void
{
    if ($value !== null && mb_strlen($value, 'UTF-8') > 100) {
        jsonExit([
            'status' => false,
            'msg' => 'O campo ' . $label . ' ultrapassa o limite de 100 caracteres.'
        ], 422);
    }
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
    $codigocadastroPost = (int)($_POST['codigocadastro'] ?? 0);

    if ($codigoUsuarioAtual <= 0) {
        jsonExit([
            'status' => false,
            'msg' => 'Usuário não autenticado.'
        ], 401);
    }

    if ($codigocadastroPost !== $codigoUsuarioAtual) {
        jsonExit([
            'status' => false,
            'msg' => 'Operação não autorizada.'
        ], 403);
    }

    $facebookRaw  = trim((string)($_POST['facebook'] ?? ''));
    $instagramRaw = trim((string)($_POST['instagram'] ?? ''));
    $twitterRaw   = trim((string)($_POST['twitter'] ?? ''));
    $linkdinRaw   = trim((string)($_POST['linkdin'] ?? ''));

    $facebook  = normalizeSocialLink($facebookRaw, 'https://facebook.com');
    $instagram = normalizeSocialLink($instagramRaw, 'https://instagram.com');
    $twitter   = normalizeSocialLink($twitterRaw, 'https://twitter.com');
    $linkdin   = normalizeSocialLink($linkdinRaw, 'https://linkedin.com/in');

    validarTamanho($facebook, 'Facebook');
    validarTamanho($instagram, 'Instagram');
    validarTamanho($twitter, 'Twitter / X');
    validarTamanho($linkdin, 'LinkedIn');

    $st = $con->prepare("
        UPDATE new_sistema_cadastro
        SET
            facebook = :facebook,
            instagram = :instagram,
            twitter = :twitter,
            linkdin = :linkdin
        WHERE codigocadastro = :cod
        LIMIT 1
    ");

    if ($facebook === null) {
        $st->bindValue(':facebook', null, PDO::PARAM_NULL);
    } else {
        $st->bindValue(':facebook', $facebook, PDO::PARAM_STR);
    }

    if ($instagram === null) {
        $st->bindValue(':instagram', null, PDO::PARAM_NULL);
    } else {
        $st->bindValue(':instagram', $instagram, PDO::PARAM_STR);
    }

    if ($twitter === null) {
        $st->bindValue(':twitter', null, PDO::PARAM_NULL);
    } else {
        $st->bindValue(':twitter', $twitter, PDO::PARAM_STR);
    }

    if ($linkdin === null) {
        $st->bindValue(':linkdin', null, PDO::PARAM_NULL);
    } else {
        $st->bindValue(':linkdin', $linkdin, PDO::PARAM_STR);
    }

    $st->bindValue(':cod', $codigoUsuarioAtual, PDO::PARAM_INT);
    $st->execute();

    jsonExit([
        'status'    => true,
        'msg'       => 'Redes sociais atualizadas com sucesso.',
        'facebook'  => $facebook,
        'instagram' => $instagram,
        'twitter'   => $twitter,
        'linkdin'   => $linkdin,
    ]);
} catch (Throwable $e) {
    jsonExit([
        'status' => false,
        'msg' => 'Erro ao atualizar as redes sociais: ' . $e->getMessage()
    ], 500);
}

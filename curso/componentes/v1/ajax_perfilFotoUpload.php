<?php

declare(strict_types=1);

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 4));
define('COMPONENTES_ROOT', APP_ROOT . '/componentes');
define('SITE_PUBLIC_ROOT', dirname(__DIR__, 3));

require_once COMPONENTES_ROOT . '/v1/class.conexao.php';
require_once COMPONENTES_ROOT . '/v1/autenticacao.php';

header('Content-Type: application/json; charset=utf-8');

function jsonExit(array $data, int $httpCode = 200): void
{
    http_response_code($httpCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function normalizarNomePasta(string $valor): string
{
    $valor = trim($valor);

    if ($valor === '') {
        return '';
    }

    $map = [
        'Á' => 'A',
        'À' => 'A',
        'Ã' => 'A',
        'Â' => 'A',
        'Ä' => 'A',
        'á' => 'a',
        'à' => 'a',
        'ã' => 'a',
        'â' => 'a',
        'ä' => 'a',
        'É' => 'E',
        'È' => 'E',
        'Ê' => 'E',
        'Ë' => 'E',
        'é' => 'e',
        'è' => 'e',
        'ê' => 'e',
        'ë' => 'e',
        'Í' => 'I',
        'Ì' => 'I',
        'Î' => 'I',
        'Ï' => 'I',
        'í' => 'i',
        'ì' => 'i',
        'î' => 'i',
        'ï' => 'i',
        'Ó' => 'O',
        'Ò' => 'O',
        'Õ' => 'O',
        'Ô' => 'O',
        'Ö' => 'O',
        'ó' => 'o',
        'ò' => 'o',
        'õ' => 'o',
        'ô' => 'o',
        'ö' => 'o',
        'Ú' => 'U',
        'Ù' => 'U',
        'Û' => 'U',
        'Ü' => 'U',
        'ú' => 'u',
        'ù' => 'u',
        'û' => 'u',
        'ü' => 'u',
        'Ç' => 'C',
        'ç' => 'c'
    ];

    $valor = strtr($valor, $map);
    $valor = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $valor);
    $valor = preg_replace('/_+/', '_', (string)$valor);
    $valor = trim((string)$valor, '_-');

    return strtolower((string)$valor);
}

function carregarImagem(string $tmpPath, string $mime)
{
    return match ($mime) {
        'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($tmpPath),
        'image/png'               => @imagecreatefrompng($tmpPath),
        'image/webp'              => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($tmpPath) : false,
        'image/gif'               => @imagecreatefromgif($tmpPath),
        default                   => false,
    };
}

function corrigirOrientacaoJpeg($img, string $tmpPath, string $mime)
{
    if ($mime !== 'image/jpeg' && $mime !== 'image/jpg') {
        return $img;
    }

    if (!function_exists('exif_read_data')) {
        return $img;
    }

    $exif = @exif_read_data($tmpPath);
    $orientation = (int)($exif['Orientation'] ?? 1);

    return match ($orientation) {
        3 => imagerotate($img, 180, 0),
        6 => imagerotate($img, -90, 0),
        8 => imagerotate($img, 90, 0),
        default => $img,
    };
}

function criarQuadrado($src, int $lado)
{
    $srcW = imagesx($src);
    $srcH = imagesy($src);

    $corte = min($srcW, $srcH);
    $srcX  = (int)(($srcW - $corte) / 2);
    $srcY  = (int)(($srcH - $corte) / 2);

    $dst = imagecreatetruecolor($lado, $lado);

    $white = imagecolorallocate($dst, 255, 255, 255);
    imagefill($dst, 0, 0, $white);

    imagecopyresampled(
        $dst,
        $src,
        0,
        0,
        $srcX,
        $srcY,
        $lado,
        $lado,
        $corte,
        $corte
    );

    return $dst;
}

function salvarJpegComLimite($img, string $destino, int $maxBytes): bool
{
    $qualidadeEscolhida = 85;
    $conteudoEscolhido  = '';

    for ($qualidade = 90; $qualidade >= 5; $qualidade -= 5) {
        ob_start();
        imagejpeg($img, null, $qualidade);
        $conteudo = (string)ob_get_clean();

        if ($conteudo === '') {
            continue;
        }

        $qualidadeEscolhida = $qualidade;
        $conteudoEscolhido  = $conteudo;

        if (strlen($conteudo) <= $maxBytes) {
            break;
        }
    }

    if ($conteudoEscolhido === '') {
        return false;
    }

    return file_put_contents($destino, $conteudoEscolhido) !== false;
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

    if (!extension_loaded('gd')) {
        jsonExit([
            'status' => false,
            'msg' => 'A extensão GD do PHP não está habilitada no servidor.'
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

    if (
        !isset($_FILES['fotoPerfil']) ||
        !is_array($_FILES['fotoPerfil']) ||
        (int)($_FILES['fotoPerfil']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
    ) {
        jsonExit([
            'status' => false,
            'msg' => 'Selecione uma imagem válida.'
        ], 422);
    }

    $arquivo = $_FILES['fotoPerfil'];

    if ((int)$arquivo['size'] <= 0) {
        jsonExit([
            'status' => false,
            'msg' => 'Arquivo vazio.'
        ], 422);
    }

    if ((int)$arquivo['size'] > 8 * 1024 * 1024) {
        jsonExit([
            'status' => false,
            'msg' => 'A imagem enviada é muito grande. Envie um arquivo de até 8 MB.'
        ], 422);
    }

    $tmpPath = (string)$arquivo['tmp_name'];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = $finfo ? (string)finfo_file($finfo, $tmpPath) : '';
    if ($finfo) {
        finfo_close($finfo);
    }

    $mimesPermitidos = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/webp',
        'image/gif'
    ];

    if (!in_array($mime, $mimesPermitidos, true)) {
        jsonExit([
            'status' => false,
            'msg' => 'Formato inválido. Envie JPG, PNG, WEBP ou GIF.'
        ], 422);
    }

    $stUser = $con->prepare("
        SELECT codigocadastro, pastasc, imagem50, imagem200
        FROM new_sistema_cadastro
        WHERE codigocadastro = :cod
        LIMIT 1
    ");
    $stUser->bindValue(':cod', $codigoUsuarioAtual, PDO::PARAM_INT);
    $stUser->execute();

    $usuario = $stUser->fetch(PDO::FETCH_ASSOC);
    if (!$usuario) {
        jsonExit([
            'status' => false,
            'msg' => 'Usuário não encontrado.'
        ], 404);
    }

    $pastasc = normalizarNomePasta((string)($usuario['pastasc'] ?? ''));
    if ($pastasc === '') {
        $pastasc = 'user_' . $codigoUsuarioAtual;
    }

    $pastaFotosFisica = SITE_PUBLIC_ROOT . '/fotos/usuarios/' . $pastasc;
    if (!is_dir($pastaFotosFisica) && !mkdir($pastaFotosFisica, 0775, true) && !is_dir($pastaFotosFisica)) {
        jsonExit([
            'status' => false,
            'msg' => 'Não foi possível criar a pasta da foto do usuário.'
        ], 500);
    }

    $imgOriginal = carregarImagem($tmpPath, $mime);
    if (!$imgOriginal) {
        jsonExit([
            'status' => false,
            'msg' => 'Não foi possível processar a imagem enviada.'
        ], 422);
    }

    $imgOriginal = corrigirOrientacaoJpeg($imgOriginal, $tmpPath, $mime);

    $img50  = criarQuadrado($imgOriginal, 50);
    $img200 = criarQuadrado($imgOriginal, 200);

    $baseNome  = 'perfil_' . $codigoUsuarioAtual . '_' . time();
    $nome50    = $baseNome . '_50.jpg';
    $nome200   = $baseNome . '_200.jpg';

    $destino50  = $pastaFotosFisica . '/' . $nome50;
    $destino200 = $pastaFotosFisica . '/' . $nome200;

    $ok50  = salvarJpegComLimite($img50, $destino50, 10 * 1024);
    $ok200 = salvarJpegComLimite($img200, $destino200, 100 * 1024);

    imagedestroy($imgOriginal);
    imagedestroy($img50);
    imagedestroy($img200);

    if (!$ok50 || !$ok200) {
        @unlink($destino50);
        @unlink($destino200);

        jsonExit([
            'status' => false,
            'msg' => 'Não foi possível gerar as versões otimizadas da foto.'
        ], 500);
    }

    $imagem50Antiga  = trim((string)($usuario['imagem50'] ?? ''));
    $imagem200Antiga = trim((string)($usuario['imagem200'] ?? ''));

    $stUpdate = $con->prepare("
        UPDATE new_sistema_cadastro
        SET
            pastasc   = :pastasc,
            imagem50  = :imagem50,
            imagem200 = :imagem200
        WHERE codigocadastro = :cod
        LIMIT 1
    ");
    $stUpdate->bindValue(':pastasc', $pastasc, PDO::PARAM_STR);
    $stUpdate->bindValue(':imagem50', $nome50, PDO::PARAM_STR);
    $stUpdate->bindValue(':imagem200', $nome200, PDO::PARAM_STR);
    $stUpdate->bindValue(':cod', $codigoUsuarioAtual, PDO::PARAM_INT);
    $stUpdate->execute();

    if ($imagem50Antiga !== '' && $imagem50Antiga !== 'usuario.jpg') {
        $old50 = $pastaFotosFisica . '/' . $imagem50Antiga;
        if (is_file($old50)) {
            @unlink($old50);
        }
    }

    if ($imagem200Antiga !== '' && $imagem200Antiga !== 'usuario.jpg') {
        $old200 = $pastaFotosFisica . '/' . $imagem200Antiga;
        if (is_file($old200)) {
            @unlink($old200);
        }
    }

    jsonExit([
        'status'       => true,
        'msg'          => 'Foto de perfil atualizada com sucesso.',
        'pastasc'      => $pastasc,
        'imagem50'     => $nome50,
        'imagem200'    => $nome200,
        'url_imagem50' => '/fotos/usuarios/' . rawurlencode($pastasc) . '/' . rawurlencode($nome50),
        'url_imagem200' => '/fotos/usuarios/' . rawurlencode($pastasc) . '/' . rawurlencode($nome200)
    ]);
} catch (Throwable $e) {
    jsonExit([
        'status' => false,
        'msg' => 'Erro ao enviar a foto: ' . $e->getMessage()
    ], 500);
}

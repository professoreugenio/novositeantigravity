<?php

declare(strict_types=1);

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 4));
define('APP_ROOT_LOCAL', dirname(__DIR__, 2));
define('COMPONENTES_ROOT', APP_ROOT . '/componentes');
define('SITE_PUBLIC_ROOT', dirname(__DIR__, 3));

require_once COMPONENTES_ROOT . '/v1/class.conexao.php';
require_once COMPONENTES_ROOT . '/v1/autenticacao.php';

date_default_timezone_set('America/Fortaleza');
header('Content-Type: application/json; charset=utf-8');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}




/** @var PDO $con */

function jsonExit(bool $status, string $msg, array $extra = []): void
{
    echo json_encode(array_merge(['status' => $status, 'msg' => $msg], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

function mesAbreviadoPtBr(int $mes): string
{
    $mapa = [
        1 => 'jan',
        2 => 'fev',
        3 => 'mar',
        4 => 'abr',
        5 => 'mai',
        6 => 'jun',
        7 => 'jul',
        8 => 'ago',
        9 => 'set',
        10 => 'out',
        11 => 'nov',
        12 => 'dez'
    ];

    return $mapa[$mes] ?? 'mes';
}

function criarImagemAPartirDoArquivo(string $tmp, string $mime)
{
    return match ($mime) {
        'image/jpeg', 'image/jpg' => imagecreatefromjpeg($tmp),
        'image/png'               => imagecreatefrompng($tmp),
        'image/webp'              => imagecreatefromwebp($tmp),
        default                   => null,
    };
}

function corrigirOrientacaoSeNecessario($image, string $tmp, string $mime)
{
    if (!function_exists('exif_read_data') || $mime !== 'image/jpeg') {
        return $image;
    }

    $exif = @exif_read_data($tmp);
    $orientation = (int)($exif['Orientation'] ?? 1);

    switch ($orientation) {
        case 3:
            $image = imagerotate($image, 180, 0);
            break;
        case 6:
            $image = imagerotate($image, -90, 0);
            break;
        case 8:
            $image = imagerotate($image, 90, 0);
            break;
    }

    return $image;
}

function redimensionarImagem($src, int $maxWidth = 1800, int $maxHeight = 1800)
{
    $w = imagesx($src);
    $h = imagesy($src);

    if ($w <= $maxWidth && $h <= $maxHeight) {
        return $src;
    }

    $ratio = min($maxWidth / $w, $maxHeight / $h);
    $newW = max(1, (int)round($w * $ratio));
    $newH = max(1, (int)round($h * $ratio));

    $dst = imagecreatetruecolor($newW, $newH);
    $white = imagecolorallocate($dst, 255, 255, 255);
    imagefill($dst, 0, 0, $white);

    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);

    if ($dst !== $src) {
        imagedestroy($src);
    }

    return $dst;
}

function reduzirAte100KB($resource, string $destino, int $limiteBytes = 102400): array
{
    $qualidade = 85;
    $salvou = false;

    while ($qualidade >= 35) {
        imagejpeg($resource, $destino, $qualidade);

        clearstatcache(true, $destino);
        $size = @filesize($destino);

        if ($size !== false && $size <= $limiteBytes) {
            $salvou = true;
            return [$salvou, (int)$size];
        }

        $qualidade -= 5;
    }

    $w = imagesx($resource);
    $h = imagesy($resource);

    while ($w > 600 && $h > 600) {
        $w = (int)($w * 0.90);
        $h = (int)($h * 0.90);

        $tmp = imagecreatetruecolor($w, $h);
        $white = imagecolorallocate($tmp, 255, 255, 255);
        imagefill($tmp, 0, 0, $white);
        imagecopyresampled($tmp, $resource, 0, 0, 0, 0, $w, $h, imagesx($resource), imagesy($resource));

        imagedestroy($resource);
        $resource = $tmp;

        imagejpeg($resource, $destino, 70);
        clearstatcache(true, $destino);
        $size = @filesize($destino);

        if ($size !== false && $size <= $limiteBytes) {
            imagedestroy($resource);
            return [true, (int)$size];
        }
    }

    imagejpeg($resource, $destino, 60);
    clearstatcache(true, $destino);
    $size = @filesize($destino);
    imagedestroy($resource);

    return [true, (int)($size ?: 0)];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonExit(false, 'Método inválido.');
}


 $idPublicacao = (int)($_POST['idpublicacao'] ?? 0);
 $idModulo = (int)($_POST['idmodulo'] ?? 0);
 $idAlunoPost = (int)($_POST['idaluno'] ?? 0);

$idAlunoLogado = (int)($codigocadastro ?? $codigoUsuario ?? $codigousuario ?? $_SESSION['codigousuario'] ?? $idAlunoPost);
$pastaAluno = trim((string)($pastasc ?? $_SESSION['pastasc'] ?? ''));

if ($idPublicacao <= 0 || $idModulo <= 0 || $idAlunoLogado <= 0) {
    jsonExit(false, 'Dados inválidos para upload.');
}

if (!isset($_FILES['imagem']) || empty($_FILES['imagem']['tmp_name'])) {
    jsonExit(false, 'Selecione uma imagem.');
}

if (!is_uploaded_file($_FILES['imagem']['tmp_name'])) {
    jsonExit(false, 'Arquivo de upload inválido.');
}

$tmp = $_FILES['imagem']['tmp_name'];
$info = @getimagesize($tmp);

if ($info === false) {
    jsonExit(false, 'Arquivo enviado não é uma imagem válida.');
}

$mime = strtolower((string)($info['mime'] ?? ''));
$mimesPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

if (!in_array($mime, $mimesPermitidos, true)) {
    jsonExit(false, 'Formato não permitido. Use JPG, PNG ou WEBP.');
}

$imagem = criarImagemAPartirDoArquivo($tmp, $mime);
if (!$imagem) {
    jsonExit(false, 'Não foi possível processar a imagem.');
}

$imagem = corrigirOrientacaoSeNecessario($imagem, $tmp, $mime);
$imagem = redimensionarImagem($imagem, 1800, 1800);

$mes = mesAbreviadoPtBr((int)date('n'));
$ano = date('Y');
$time = (string)time();
$pastaNome = $mes . '_' . $ano . '_' . $idAlunoLogado . '_' . $time;

$dirBase = SITE_PUBLIC_ROOT . '/fotos/atividades';
$dirFinal = $dirBase . '/' . $pastaNome;

if (!is_dir($dirBase) && !mkdir($dirBase, 0775, true) && !is_dir($dirBase)) {
    imagedestroy($imagem);
    jsonExit(false, 'Não foi possível criar a pasta base.');
}

if (!is_dir($dirFinal) && !mkdir($dirFinal, 0775, true) && !is_dir($dirFinal)) {
    imagedestroy($imagem);
    jsonExit(false, 'Não foi possível criar a pasta da atividade.');
}

$nomeArquivo = 'print_' . $idAlunoLogado . '_' . $time . '.jpg';
$caminhoFinal = $dirFinal . '/' . $nomeArquivo;

[$okSalvar, $sizeFinal] = reduzirAte100KB($imagem, $caminhoFinal, 102400);

if (!$okSalvar || !file_exists($caminhoFinal)) {
    jsonExit(false, 'Falha ao salvar a imagem.');
}

try {
    $sql = "INSERT INTO a_curso_AtividadeAnexos
        (
            idpublicacacaoAA,
            idalulnoAA,
            idmoduloAA,
            fotoAA,
            sizeAA,
            extensaoAA,
            pastaAA,
            avaliacaoAA,
            dataenvioAA,
            horaenvioAA
        ) VALUES (
            :idpublicacao,
            :idaluno,
            :idmodulo,
            :foto,
            :size,
            :extensao,
            :pasta,
            0,
            :dataenvio,
            :horaenvio
        )";

    $stmt = $con->prepare($sql);
    $stmt->bindValue(':idpublicacao', $idPublicacao, PDO::PARAM_INT);
    $stmt->bindValue(':idaluno', $idAlunoLogado, PDO::PARAM_INT);
    $stmt->bindValue(':idmodulo', $idModulo, PDO::PARAM_INT);
    $stmt->bindValue(':foto', $nomeArquivo, PDO::PARAM_STR);
    $stmt->bindValue(':size', (string)$sizeFinal, PDO::PARAM_STR);
    $stmt->bindValue(':extensao', 'jpg', PDO::PARAM_STR);
    $stmt->bindValue(':pasta', $pastaNome, PDO::PARAM_STR);
    $stmt->bindValue(':dataenvio', date('Y-m-d'), PDO::PARAM_STR);
    $stmt->bindValue(':horaenvio', date('H:i:s'), PDO::PARAM_STR);
    $stmt->execute();

    jsonExit(true, 'Print enviado com sucesso.');
} catch (Throwable $e) {
    @unlink($caminhoFinal);
    jsonExit(false, 'Erro ao registrar o print no banco.');
}

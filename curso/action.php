<?php
declare(strict_types=1)
;
define('BASEPATH', true);
define('PUBLIC_ROOT', __DIR__);
define('RAIZ_ROOT', dirname(__DIR__, 1));
define('SESSION_TTL', 60 * 60 * 8); // 6 horas
// ✅ pasta acima do public_html (ex.: /home/usuario)
define('APP_ROOT', dirname(__DIR__, 2));
define('COMPONENTES_ROOT', APP_ROOT . '/componentes');
date_default_timezone_set('America/Fortaleza');
header('Content-Type: text/html; charset=utf-8');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => SESSION_TTL,
        'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
require_once COMPONENTES_ROOT . '/v1/class.conexao.php';
require_once COMPONENTES_ROOT . '/v1/autenticacao.php';
require_once PUBLIC_ROOT . '/componentes/v1/QueryUsuario.php';
?>
<?php
$tokemCurso = trim((string) ($_GET['tokemCurso'] ?? ''));
$tokemModulo = trim((string) ($_GET['tokemModulo'] ?? ''));
$tokemPublicacao = trim((string) ($_GET['tokemPublicacao'] ?? ''));
?>
<?php
if ($tokemCurso !== '') {
    $dec = encrypt_secure($_GET['cur'], 'd');
    $exp = explode('&', $dec);
    $_SESSION['dadoscurso'] = $_GET['cur'];
    header('Location: modulos.php');
    exit;
}
?>

<?php
if ($tokemModulo !== '') {
    $_SESSION['dadosmodulo'] = $_GET['modulo'];
    $_SESSION['dadosdia'] = $_GET['dia'];
    header('Location: modulos.php');
    exit;
}
?>

<?php
if ($tokemPublicacao !== '') {
    $_SESSION['dadospublicacao'] = $_GET['publicacao'];
    if (!empty($_GET['modulo'])) {
        $_SESSION['dadosmodulo'] = $_GET['modulo'];
    }
    header('Location: aula.php');
    exit;
}
?>



<?php
header('Location: index.php');
exit;
?>
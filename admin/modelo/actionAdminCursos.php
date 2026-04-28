<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
$sessionLifetime = 60 * 60 * 8; // 8 horas

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.gc_maxlifetime', (string)$sessionLifetime);

    session_set_cookie_params([
        'lifetime' => $sessionLifetime,
        'path'     => '/',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}
require_once APP_ROOT . '/componentes/v1/class.conexao.php';
require_once APP_ROOT . '/componentes/v1/autenticacao.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$tokemCurso  = trim((string)($_GET['tokemCurso'] ?? ''));
$tokemAluno  = trim((string)($_GET['tokemAluno'] ?? ''));
$tokemTurma  = trim((string)($_GET['tokemTurma'] ?? ''));
$tokemModulo = trim((string)($_GET['tokemModulo'] ?? ''));
$tokemModuloPub = trim((string)($_GET['tokemModuloPub'] ?? ''));

$tokemModuloEditar = trim((string)($_GET['tokemModuloEditar'] ?? ''));
$tokemPublicacaoEditar = trim((string)($_GET['tokemPublicacaoEditar'] ?? ''));
/*
|--------------------------------------------------------------------------
| Fluxo do curso
|--------------------------------------------------------------------------
| Grava apenas se a variável vier preenchida
*/
if ($tokemCurso !== '') {
    $status = trim((string)($_GET['status'] ?? ''));
    $idEnc  = trim((string)($_GET['id'] ?? ''));
    $dec = encrypt_secure($idEnc, $action = 'd');
    $query = $con->prepare("SELECT * FROM new_sistema_cursos WHERE codigocursos = :idcurso ");
    $query->bindParam(":idcurso", $dec);
    $query->execute();
    $rwCurso = $query->fetch(PDO::FETCH_ASSOC);
    $chave = $rwCurso['pasta'];
    $tipo = $rwCurso['tipocursosc'];
    if ($idEnc !== '') {
        $_SESSION['idCurso'] = $idEnc;
        $_SESSION['chaveCurso'] = $chave;
    }
    if ($status !== '') {
        $_SESSION['statusCurso'] = $_GET['status'];
    }
    if ($tipo == 3) {
        header('Location: publicacoes_modulos.php');
        exit;
    }
    header('Location: cursos_turmas.php');
    exit;
}
if ($tokemTurma !== '') {
    $idEncTurma  = trim((string)($_GET['tm'] ?? ''));
    $dec = encrypt_secure($idEncTurma, $action = 'd');
    $query = $con->prepare("SELECT * FROM new_sistema_cursos_turmas WHERE codigoturma  = :idturma ");
    $query->bindParam(":idturma", $dec);
    $query->execute();
    $rwTurma = $query->fetch(PDO::FETCH_ASSOC);
    $chave = $rwTurma['chave'];
    if ($idEncTurma !== '') {
        $_SESSION['idTurma'] = $idEncTurma;
        $_SESSION['chaveTurma'] = $chave;
    }
    header('Location: cursos_TurmasAlunos.php');
    exit;
}
// if ($tokemAluno !== '') {
//     $idEncAluno  = trim((string)($_GET['idUsuario'] ?? ''));
//     $dec = encrypt_secure($_GET['idUsuario'], $action = 'd');
//     if ($tokemAluno !== '') {
//         $_SESSION['idUsuario'] = $idEncAluno;
//     }
//     if (isset($_GET['idTurma'])) {
//         $_SESSION['idTurma'] = $_GET['idTurma'];
//     }
//     if (isset($_GET['idcurso'])) {
//         $_SESSION['idCurso'] = $_GET['idcurso'];
//     }
//     if (isset($_GET['idcurso'])) {
//         $_SESSION['idCurso'] = $_GET['idcurso'];
//     }
//     if (isset($_GET['chaveturma'])) {
//         $_SESSION['chaveTurma'] = $_GET['chaveturma'];
//     }

//     header('Location: alunoTurmas.php');
//     exit;
// }
/*
|--------------------------------------------------------------------------
| Fluxo do módulo
|--------------------------------------------------------------------------
| Grava apenas o módulo, preservando o curso já salvo
*/
if ($tokemModulo !== '') {
    $mdlEnc = trim((string)($_GET['md'] ?? ''));
    if ($mdlEnc !== '') {
        $_SESSION['idModulo'] = $mdlEnc;
    }
    header('Location: cursos_publicacoes.php');
    exit;
}
if ($tokemModuloPub !== '') {
    $mdlEnc = trim((string)($_GET['md'] ?? ''));
    if ($mdlEnc !== '') {
        $_SESSION['idModulo'] = $mdlEnc;
    }
    header('Location: publicacoes_lista.php');
    exit;
}
if ($tokemModuloEditar !== '') {
    $mdlEnc = trim((string)($_GET['md'] ?? ''));
    if ($mdlEnc !== '') {
        $_SESSION['idModulo'] = $mdlEnc;
    }
    header('Location: cursos_modulosEditar.php');
    exit;
}
if ($tokemPublicacaoEditar !== '') {
    $pubEnc = trim((string)($_GET['pub'] ?? ''));
    if ($_GET['url'] ?? '') {
        $_SESSION['urlPublicacoes'] = $_GET['url'];
    } else {
        $_SESSION['urlPublicacoes'] = "publicacoes_lista.php";
    }
    $_SESSION['idPublicacao'] = $pubEnc;
    // header('Location: publicacoes_teste.php');
    header('Location: publicacaoEditarTexto.php');
    exit;
}

if ($tokemAluno !== '') {
    $idEnc = trim((string)($_GET['idusuario'] ?? ''));
    if ($idEnc !== '') {
        $_SESSION['idUsuario'] = $idEnc;
    }
    header('Location: alunoPerfil.php');
    exit;
}



header('Location: index.php');
exit;

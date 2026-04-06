<?php
defined('BASEPATH') or exit('Acesso não permitido');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



class Config
{
    private static $local = "localhost";
    private static $banco = "appsrcc_projetoadmin";
    private static $usuario = "appsrcc_admcurso";
    private static $senha = "mastersysadmcurso2018";

    public static function connect()
    {
        $dsn = 'mysql:host=' . self::$local . ';dbname=' . self::$banco;

        try {
            $con = new PDO($dsn, self::$usuario, self::$senha);
            $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        } catch (PDOException $e) {
            self::handleError('Erro no banco de dados: ' . $e->getMessage());
        } catch (Exception $e) {
            self::handleError('Erro na página: ' . $e->getMessage());
        }

        return $con;
    }

    private static function handleError($msg)
    {
        $paginaAtual = self::getCurrentPage();
        echo "<script>alert('$msg');window.location.href='$paginaAtual';</script>";
        exit();
    }

    public static function getCurrentPage()
    {
        $raizSite = "https://" . $_SERVER['HTTP_HOST'];
        $paginaAtual = $raizSite . $_SERVER['REQUEST_URI'];
        $ip = self::getClientIP();

        if ($ip === "127.0.0.1") {
            $paginaAtual = "http://127.0.0.1/cursosnastand" . $_SERVER['REQUEST_URI'];
        }

        return $paginaAtual;
    }

    private static function getClientIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }
}

// Funções auxiliares
function validaEmail($email)
{
    $email = trim($email);
    $er = "/^(([0-9a-zA-Z]+[-._+&])*[0-9a-zA-Z]+@([-0-9a-zA-Z]+[.])+[a-zA-Z]{2,6}){0,1}$/";
    
    if (!preg_match($er, $email)) {
        $paginaAtual = Config::getCurrentPage();
        echo "<script>alert('Alerta: $email fora dos padrões.');window.location.href='$paginaAtual';</script>";
        exit();
    }
}

function validaSenha($senha)
{
    return true; // Você pode adicionar mais regras para validar a senha aqui
}
?>

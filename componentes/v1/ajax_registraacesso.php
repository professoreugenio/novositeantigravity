<?php
define('BASEPATH', true);
session_start();

require_once __DIR__ . '/class.conexao.php';
require_once __DIR__ . '/autenticacao.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_COOKIE['registraacesso'])) {
    
    $chavera = uniqid('', true);
    
    $ipra = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ipra = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipra = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ipra = $_SERVER['REMOTE_ADDR'];
    }

    $datara = date('Y-m-d');
    $horara = date('H:i:s');

    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $dispositivora = "1";
    if (preg_match('/(iPhone|iPad|Android|webOS|BlackBerry|iPod|Symbian)/i', $userAgent)) {
        $dispositivora = "2";
    }

    $navegadorra = "Desconhecido";
    if (strpos($userAgent, 'Opera') !== false || strpos($userAgent, 'OPR/') !== false) {
        $navegadorra = 'Opera';
    } elseif (strpos($userAgent, 'Edge') !== false || strpos($userAgent, 'Edg/') !== false) {
        $navegadorra = 'Edge';
    } elseif (strpos($userAgent, 'Chrome') !== false) {
        $navegadorra = 'Google';
    } elseif (strpos($userAgent, 'Safari') !== false) {
        $navegadorra = 'Safari';
    } elseif (strpos($userAgent, 'Firefox') !== false) {
        $navegadorra = 'Firefox';
    } elseif (strpos($userAgent, 'MSIE') !== false || strpos($userAgent, 'Trident/7') !== false) {
        $navegadorra = 'IE';
    }

    try {
        $con = config::connect();
        $query = $con->prepare("INSERT INTO a_site_registraacessos (ipra, chavera, dispositivora, navegadorra, datara, horara) VALUES (:ip, :chave, :disp, :nav, :dt, :hr)");
        $query->bindParam(':ip', $ipra);
        $query->bindParam(':chave', $chavera);
        $query->bindParam(':disp', $dispositivora);
        $query->bindParam(':nav', $navegadorra);
        $query->bindParam(':dt', $datara);
        $query->bindParam(':hr', $horara);
        
        if ($query->execute()) {
            $valorCookie = $chavera . '&' . $datara . '&' . $ipra;
            $cookieCodificado = encrypt_secure($valorCookie, 'e');
            
            $validade = time() + (86400 * 30 * 6); // 6 meses
            setcookie('registraacesso', $cookieCodificado, $validade, "/");

            echo json_encode(['status' => 'success', 'msg' => 'Acesso registrado']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Erro ao registrar no banco']);
        }

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
    }

} else {
    echo json_encode(['status' => 'skipped', 'msg' => 'Acesso já registrado anteriormente']);
}

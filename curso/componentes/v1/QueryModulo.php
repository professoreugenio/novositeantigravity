<?php
/**
 * Extração de Dados do Módulo
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$idModuloAtivo = 0;
$QueryModuloNome = '';
$QueryModuloBgColor = '';

if (isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado'] === true && !empty($_SESSION['dadosmodulo'])) {
    $decModulo = encrypt_secure($_SESSION['dadosmodulo'], 'd');
    if (!empty($decModulo)) {
        $idModuloAtivo = (int) $decModulo;

        try {
            if (!isset($con) || !$con instanceof PDO) {
                $con = config::connect();
            }
            $stmtModulo = $con->prepare("
                SELECT nomemodulo, bgcolorsm 
                FROM new_sistema_modulos_PJA 
                WHERE codigomodulos = :idMod 
                LIMIT 1
            ");
            $stmtModulo->bindValue(':idMod', $idModuloAtivo, PDO::PARAM_INT);
            $stmtModulo->execute();
            if ($rowMod = $stmtModulo->fetch(PDO::FETCH_ASSOC)) {
                $ModuloNome = $rowMod['nomemodulo'];
                $ModuloBgColor = $rowMod['bgcolorsm'];
            }
        } catch (Throwable $e) {
        }
    }
}
?>
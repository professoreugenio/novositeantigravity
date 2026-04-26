<?php

declare(strict_types=1);

// Tempo máximo de inatividade (2 horas)
if (!defined('SESSION_TIMEOUT')) {
  define('SESSION_TIMEOUT', 7200);
}
?>

<?php
function tipoaaparelho()
{
  $userAgent = $_SERVER['HTTP_USER_AGENT'];
  $devices = ['iPhone', 'iPad', 'Android', 'webOS', 'BlackBerry', 'iPod', 'Symbian'];
  foreach ($devices as $device) {
    if (strpos($userAgent, $device) !== false) {
      // Dispositivo detectado
      $tipo = "1";
    } else {
      $tipo = "2";
    }
  }
  return $tipo;
}
$dispositivo = tipoaaparelho();
?>

<?php
// TTL do cookie da sessão (4 horas)

$addtime = ($dispositivo == '1')
  ? (60 * 60 * 24 * 180) // 180 dias
  : (60 * 60 * 6);       // 6 horas

if (!defined('SESSION_TTL')) {
  define('SESSION_TTL', $addtime);
}

// ✅ Só configura cookie e inicia sessão se ainda NÃO existe sessão ativa
if (session_status() === PHP_SESSION_NONE) {

  session_set_cookie_params([
    'lifetime' => SESSION_TTL,
    'path' => '/',
    'domain' => '',
    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'httponly' => true,
    'samesite' => 'Lax',
  ]);

  session_start();
}
/* ===================== CONTROLE DE INATIVIDADE ===================== */

// Inicializa controle
if (!isset($_SESSION['LAST_ACTIVITY'])) {
  $_SESSION['LAST_ACTIVITY'] = time();
}

// Verifica se excedeu tempo limite
if (time() - (int) $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT) {

  session_unset();

  // Boa prática: remove cookie da sessão também
  if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
      session_name(),
      '',
      time() - 42000,
      $params['path'] ?? '/',
      $params['domain'] ?? '',
      (bool) ($params['secure'] ?? false),
      (bool) ($params['httponly'] ?? true)
    );
  }

  session_destroy();

  header('Location: index.php?expired=1');
  exit;
}



// Atualiza o tempo da última atividade
$_SESSION['LAST_ACTIVITY'] = time();
setlocale(LC_ALL, "pt_BR", "pt_BR.iso-8859-1", "pt_BR.utf-8", "portuguese");
date_default_timezone_set('America/Fortaleza');
$hr = "0";
$hora = date("H:i:s", time() - ($hr));
$data = date("Y-m-d");
$hora = date("H:i:s", time() - ($hr));
$ts = time();
$to = time() - 60;
$meses = [
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

$ano = date("Y", $ts);
$mes = (int) date("m", $ts);
$dia = date("d", $ts);

// Estrutura: ANO + MÊS(abrev) + DIA + TIMESTAMP
$pastats = $meses[$mes] . '_' . $ano . $mes . $dia . $ts;

$raizSite = "https://" . $_SERVER['HTTP_HOST'];
$raizSite = "https://professoreugenio.com";
$URL_ATUAL = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$con = config::connect(); ?>
<?php
$addtime = 60 * 60 * 24 * 180;
$duracao = time() + $addtime;
?>
<?php
$addtime3 = 60 * 60 * 3;
$duracao3h = time() + $addtime3;
?>


<?php
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
  $ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
  $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
  $ip = $_SERVER['REMOTE_ADDR'];
}
?>


<?php
function encrypt($value, $action = 'e')
{
  $secret_key = 'sistemaweb';
  $secret_iv = 'edesigner';
  $encrypt_method = "AES-256-CBC";

  $key = hash('sha256', $secret_key);
  $iv = substr(hash('sha256', $secret_iv), 0, 16);

  if ($action === 'e') {

    // ✅ garante string (resolve seu erro)
    $value = (string) $value;

    $enc = openssl_encrypt($value, $encrypt_method, $key, 0, $iv);
    if ($enc === false)
      return false;

    return base64_encode($enc);
  }

  if ($action === 'd') {

    // ✅ decrypt também deve receber string
    $value = (string) $value;

    $dec = openssl_decrypt(base64_decode($value), $encrypt_method, $key, 0, $iv);
    return ($dec === false) ? false : $dec;
  }

  return false;
}
?>



<?php

function encrypt_secure($value, $action = 'e')
{
  $secret_key = 'sistemaweb';
  $secret_iv = 'edesigner';
  $cipher = 'AES-256-CBC';

  $key = hash('sha256', $secret_key, true);
  $macKey = hash('sha256', $secret_key . '|' . $secret_iv, true);

  if ($action === 'e') {
    $value = (string) $value;
    $ivLen = openssl_cipher_iv_length($cipher);
    $iv = random_bytes($ivLen);

    $cipherRaw = openssl_encrypt($value, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    if ($cipherRaw === false)
      return false;

    $mac = hash_hmac('sha256', $iv . $cipherRaw, $macKey, true);
    return base64_encode($iv . $mac . $cipherRaw);
  }

  if ($action === 'd') {
    $value = (string) $value;
    $data = base64_decode($value, true);
    if ($data === false)
      return false;

    $ivLen = openssl_cipher_iv_length($cipher);
    $macLen = 32;

    if (strlen($data) < ($ivLen + $macLen + 1))
      return false;

    $iv = substr($data, 0, $ivLen);
    $mac = substr($data, $ivLen, $macLen);
    $cipherRaw = substr($data, $ivLen + $macLen);

    $calcMac = hash_hmac('sha256', $iv . $cipherRaw, $macKey, true);
    if (!hash_equals($mac, $calcMac))
      return false;

    $plain = openssl_decrypt($cipherRaw, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    return ($plain === false) ? false : $plain;
  }

  return false;
}


/**
 * Descriptografa valor vindo da URL
 */
function decrypt_secure_url($token)
{
  $token = trim((string) $token);
  if ($token === '') {
    return false;
  }

  $base64 = strtr($token, '-_', '+/');

  $resto = strlen($base64) % 4;
  if ($resto > 0) {
    $base64 .= str_repeat('=', 4 - $resto);
  }

  return encrypt_secure($base64, 'd');
}


?>


<?php

function encryptteste($string, $action = 'e')
{
  $secret_key = 'sistemaweb';
  $secret_iv = 'edesigner';
  $output = false;
  $encrypt_method = "aes-256-gcm";
  $key = hash('sha256', $secret_key);
  $iv = substr(hash('sha256', $secret_iv), 0, 16);
  if ($action == 'e') {
    $output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));
  } elseif ($action == 'd') {
    $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
  }
  return $output;
}
?>
<?php
if (empty($_GET['idpage'])) {
  $_GET['idpage'] = '0';
} else {
  $con = config::connect();
  $decPagina = encrypt($_GET['idpage'], $action = 'd');
  $query = $con->prepare("SELECT nomepaginapa FROM new_sistema_paginasadmin WHERE codigopaginasadmin = :cod  ");
  $query->bindParam(":cod", $decPagina);
  $query->execute();
  $rwPage = $query->fetch(PDO::FETCH_ASSOC);
  $_SESSION['idpagina'] = $decPagina;
  $_SESSION['titulopagina'] = $rwPage['nomepaginapa'];
  $tituloPagina = $_SESSION['titulopagina'];
}
?>
<?php
if ($ip != "127.0.0.1") {
  $paginaatual = $raizSite . $_SERVER['REQUEST_URI']; // nome da página do site
} else {
  $paginaatual = "http://127.0.0.1/professoreugenio.com/" . $_SERVER['REQUEST_URI']; // nome da página do site
}
?>
<?php
function databr($data)
{
  $dt = $data;
  $data = implode(".", array_reverse(explode("-", $dt)));
  return $data;
}
?>
<?php
function saudacao()
{
  $hora_atual = date('H'); // Obtém a hora atual
  if ($hora_atual >= 6 && $hora_atual < 12) {
    $saudacao = "Bom dia!";
  } elseif ($hora_atual >= 12 && $hora_atual < 18) {
    $saudacao = "Boa tarde!";
  } else {
    $saudacao = "Boa noite!";
  }
  return $saudacao;
}

$saudacao = saudacao();
?>
<?php
function diadasemanacurta($data)
{
  $diasemana = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb');
  $diasemana_numero = date('w', strtotime($data));
  $diadasemana = $diasemana[$diasemana_numero] . " ";
  $diadasemana . ", ";
  ######################
  if (!empty($data)) {
    $exp = explode("-", $data);
    $mes = $exp[1];
    if ($mes == "01") {
      $mes = "Jan";
    } else if ($mes == "02") {
      $mes = "Fev";
    } else if ($mes == "03") {
      $mes = "Mar";
    } else if ($mes == "04") {
      $mes = "Abr";
    } else if ($mes == "05") {
      $mes = "Mai";
    } else if ($mes == "06") {
      $mes = "Jun";
    } else if ($mes == "07") {
      $mes = "Jul";
    } else if ($mes == "08") {
      $mes = "Ago";
    } else if ($mes == "09") {
      $mes = "Set";
    } else if ($mes == "10") {
      $mes = "Out";
    } else if ($mes == "11") {
      $mes = "Nov";
    } else {
      $mes = "Dez";
    }
  }
  $diadasemana = $exp[2] . "." . $mes . "." . $exp[0];
  return $diadasemana;
}
?>
<?php
function diadasemana($data, $n)
{
  if (empty($data)) {
    $data = "2024-07-21";
    $n == '1';
  }
  if ($n == '1') {
    $diasemana = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb');
  } else if ($n == '2') {
    $diasemana = array('Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado');
  } else {
    $diasemana = array('Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado');
    // $diasemana = array('D', 'S', 'T', 'Q', 'Q', 'S', 'S');
  }
  $diasemana_numero = date('w', strtotime($data));
  $diadasemana = $diasemana[$diasemana_numero] . " ";
  // echo $diadasemana . ", ";
  ######################
  if (!empty($data)) {
    $exp = explode("-", $data);
    $dia = $exp[2];
    $mes = $exp[1];
    if ($n == '1') {
      if ($mes == "01") {
        $mes = "Jan";
      } else if ($mes == "02") {
        $mes = "Fev";
      } else if ($mes == "03") {
        $mes = "Mar";
      } else if ($mes == "04") {
        $mes = "Abr";
      } else if ($mes == "05") {
        $mes = "Mai";
      } else if ($mes == "06") {
        $mes = "Jun";
      } else if ($mes == "07") {
        $mes = "Jul";
      } else if ($mes == "08") {
        $mes = "Ago";
      } else if ($mes == "09") {
        $mes = "Set";
      } else if ($mes == "10") {
        $mes = "Out";
      } else if ($mes == "11") {
        $mes = "Nov";
      } else {
        $mes = "Dez";
      }
    } else {
      if ($mes == "01") {
        $mes = "Janeiro";
      } else if ($mes == "02") {
        $mes = "Fevereiro";
      } else if ($mes == "03") {
        $mes = "Março";
      } else if ($mes == "04") {
        $mes = "Abril";
      } else if ($mes == "05") {
        $mes = "Maio";
      } else if ($mes == "06") {
        $mes = "Junho";
      } else if ($mes == "07") {
        $mes = "Julho";
      } else if ($mes == "08") {
        $mes = "Agosto";
      } else if ($mes == "09") {
        $mes = "Setembro";
      } else if ($mes == "10") {
        $mes = "Outubro";
      } else if ($mes == "11") {
        $mes = "Novembro";
      } else {
        $mes = "Dezembro";
      }
    }
  }
  if ($n == 3):
    $diadasemana = $diadasemana;
  else:
    $diadasemana = $diadasemana . ", " . $dia . " de " . $mes . " de " . $exp[0];
  endif;
  return $diadasemana;
}
?>

<?php
function filtrarpalavras($msg)
{
  $palavras_obscenas = array(
    "#@!$%",
    "porra",
    "caralho",
    "CARALHO",
    "fuleragem",
    "FULERAGEM",
    "SEU MERDA",
    "seu merda",
    "vai tomar no",
    "vai tomar no seu",
    "VAI TOMAR NO SEU",
    "cabeça do meu pau",
    "cabeça do meu paul",
    "CABEÇA DO MEU PAU",
    "CABEÇA DO MEU PAUL",
    "CHUPA MEU PAL",
    "CHUPA MEU PAU",
    "chupa meu pal",
    "chupa meu pau",
    "cú",
    "CÚ",
    "KÚ",
    "KU",
    "kú",
    "ku",
    "xana",
    "XANA",
    "CHANA",
    "TABACUDA",
    "tabacuda",
    "TABACÃO",
    "tabacão",
    "TABACO",
    "tabaco",
    "PRIQUITINHO",
    "priquitinho",
    "FILHO DA PUTA",
    "senta na minha rola",
    "SENTA NA MINHA ROLA",
    "vá dar o cú",
    "vá dar o kú",
    "VÁ DAR O CÚ",
    "VÁ DAR O KÚ",
    "cuzão",
    "CUZÃO",
    "CUZINHO",
    "SENTA NA MINHA PICA",
    "senta na minha pica",
    "senta na minha piroca",
    "senta na minha piroka",
    "bolsonarita",
    "lulista",
    "pica",
    "PICA",
    "ROLA",
    "rola",
    "filho da puta",
    "filha da puta",
    "FILHO DA PUTA",
    "FILHA DA PUTA",
    "puta que o pariu",
    "puta que o pariul",
    "PUTA QUE O PARIU",
    "PUTA QUE O PARIL",
    "CUZINHO",
    "racista",
    "RACISTA",
    "NAZISTA",
    "nazista",
    "cuzinho",
    "kuzinho",
    "KUZINHO",
    "PORRA",
    "PIROCA",
    "piroca",
    "piroka",
    "pyroka",
    "PIROKA",
    "buceta",
    "busseta",
    "BOCETA",
    "arrombado",
    "arrombada",
    "ARROMBADO",
    "ARROMBADA",
    "BUCETINHA",
    "bucetinha",
    "caralho",
    "CARALHO",
    "KARALHO",
    "karalho",
    "PRIQUITO",
    "priquito"
  );
  $substituicao = "***"; // Substituição que você deseja aplicar
  $msg = str_ireplace($palavras_obscenas, $substituicao, $msg);
  $padrao = '/(https?:\/\/[^\s]+)/i';
  $subst = '<a target="_blank" href="$1">$1</a>';
  $msg = preg_replace($padrao, $subst, $msg);
  $asteriscos = '/\*(.*?)\*/';
  $troca = '<b>$1</b>';
  $msg = preg_replace($asteriscos, $troca, $msg);
  return $msg;
}
?>
<?php
function horabr($hora)
{
  if (empty($hora)) {
    $hora = "00:00";
  }
  $nvhora = "00:00";
  if (!empty($hora)) {
    $exp = explode(":", $hora);
    $nvhora = $exp[0];
    if (!empty($exp[1])) {
      $nvhora = $exp[0] . ":" . $exp[1];
    }
    return $nvhora;
  }
}
?>
<?php
function mesabreviado($data)
{
  if (!empty($data)) {
    $exp = explode("-", $data);
    $mes = $exp[1];
    if ($mes == "01") {
      $mes = "Jan";
    } else if ($mes == "02") {
      $mes = "Fev";
    } else if ($mes == "03") {
      $mes = "Mar";
    } else if ($mes == "04") {
      $mes = "Abr";
    } else if ($mes == "05") {
      $mes = "Mai";
    } else if ($mes == "06") {
      $mes = "Jun";
    } else if ($mes == "07") {
      $mes = "Jul";
    } else if ($mes == "08") {
      $mes = "Ago";
    } else if ($mes == "09") {
      $mes = "Set";
    } else if ($mes == "10") {
      $mes = "Out";
    } else if ($mes == "11") {
      $mes = "Nov";
    } else {
      $mes = "Dez";
    }
    return $mes;
  }
}
?>
<?php
function cortartexto($string, $num)
{
  $numcarac = strlen($string);
  $texto = explode(" ", substr($string, 0, $num));
  if (count($texto) > 1)
    array_pop($texto);
  $texto = implode(" ", $texto) . "";
  if ($numcarac <= $num) {
    $var = $string;
  } else {
    $var = $texto . "...";
  }
  return $var;
}
?>
<?php
function cortartextoporletras($texto, $num = 20)
{
  if (strlen($texto) > $num) {
    $texto = substr($texto, 0, $num - 3) . '...';
  }
  return $texto;
}
?>
<?php
function nome($nome, $n)
{
  $exp = explode(" ", $nome);
  $nm = $exp[0];
  if (!empty($exp[1])) {
    if ($n == "1") {
      $nm = $exp[0];
    }
    if ($n == "2") {
      if ($exp[1] == "de" || $exp[1] == "DE" || $exp[1] == "DO" || $exp[1] == "do" || $exp[1] == "DA" || $exp[1] == "da") {
        $nm = $exp[0] . " " . $exp[2];
      } else {
        $nm = $exp[0] . " " . $exp[1];
      }
    }
  }
  return $nm;
}
?>
<?php
function gerachave()
{
  $min = "1";
  $max = "10";
  $count = 5;
  $list = range($min, $max);
  shuffle($list);
  $list = array_slice($list, 0, $count);
  $length = 5;
  $list1 = array_merge(range('A', 'Z'), range(0, 3));
  shuffle($list1);
  $pass1 = substr(join($list1), 3, $length);
  $list2 = array_merge(range('0', '0'), range(0, 6));
  shuffle($list2);
  $pass2 = substr(join($list2), 0, $length);
  $rand = $pass1 . $pass2;
  return $rand;
}
?>
<?php
function gerachaveshorttag()
{
  $min = "1";
  $max = "10";
  $count = 5;
  $list = range($min, $max);
  shuffle($list);
  $list = array_slice($list, 0, $count);
  $length = 5;
  $list1 = array_merge(range('a', 'Z'), range(0, 3));
  shuffle($list1);
  $pass1 = substr(join($list1), 3, $length);
  $list2 = array_merge(range('0', '0'), range(0, 99));
  shuffle($list2);
  $pass2 = substr(join($list2), 0, $length);
  $rand = $pass1 . $pass2;
  return $rand;
}
?>
<?php
function emoji($msg)
{
  $emojiToImage = array(
    ' :)1 ' => '<img src="https://professoreugenio.com/img/emotiocon/1.png" class="emoji">',
    ' :)2 ' => '<img src="https://professoreugenio.com/img/emotiocon/2.png" class="emoji">',
    ' :)3 ' => '<img src="https://professoreugenio.com/img/emotiocon/3.png" class="emoji">',
    ' :)4 ' => '<img src="https://professoreugenio.com/img/emotiocon/4.png" class="emoji">',
    ' :)5 ' => '<img src="https://professoreugenio.com/img/emotiocon/5.png" class="emoji">',
    ' :)6 ' => '<img src="https://professoreugenio.com/img/emotiocon/6.png" class="emoji">',
    ' :)7 ' => '<img src="https://professoreugenio.com/img/emotiocon/7.png" class="emoji">',
    ' :)8 ' => '<img src="https://professoreugenio.com/img/emotiocon/8.png" class="emoji">',
    ' :)9 ' => '<img src="https://professoreugenio.com/img/emotiocon/9.png" class="emoji">',
    ' :)10 ' => '<img src="https://professoreugenio.com/img/emotiocon/10.png" class="emoji">',
    ' :)11 ' => '<img src="https://professoreugenio.com/img/emotiocon/11.png" class="emoji">',
    ' :)12 ' => '<img src="https://professoreugenio.com/img/emotiocon/12.png" class="emoji">',
    ' :)13 ' => '<img src="https://professoreugenio.com/img/emotiocon/13.png" class="emoji">',
    ' :)14 ' => '<img src="https://professoreugenio.com/img/emotiocon/14.png" class="emoji">',
    ' :)15 ' => '<img src="https://professoreugenio.com/img/emotiocon/15.png" class="emoji">',
    ' :)16 ' => '<img src="https://professoreugenio.com/img/emotiocon/16.png" class="emoji">',
    ' :)17 ' => '<img src="https://professoreugenio.com/img/emotiocon/17.png" class="emoji">',
  );
  foreach ($emojiToImage as $emoji => $imagem) {
    $msg = str_replace($emoji, $imagem, $msg);
  }
  return $msg;
}
?>
<?php
function contatividades($atividade, $idmsg)
{
  $con = config::connect();
  $query = $con->prepare("SELECT * FROM new_sistema_msg_alunos WHERE codmsgsam=:codmsg  and tiposam='5' ");
  $query->bindParam(":codmsg", $idmsg);
  $query->execute();
  $fetch = $query->fetchALL();
  $quant = count($fetch);
  $pl = "";
  if ($quant >= 1) {
    $pl = "S ";
    $return = '(' . $quant . ('<b></b>)');
  } else {
    $return = "";
  }
  return $return;
}
?>
<?php
function tempologado()
{
  if (!empty($_COOKIE['timeduracao'])) {
    $tss = strtotime($_COOKIE['timeduracao']) - time();
    $tss;
    $horas = floor($tss / 3600);
    $minutos = floor(($tss % 3600) / 60);
    $tt = $horas . ':' . $minutos;
    $return = ('<div class="contador"><i class="fa fa-clock-o" aria-hidden="true"></i> ' . $horas . ':' . $minutos . '</div>');
    return $return;
  }
}
?>
<?php
function numerodaaula($idMdl, $codTurma, $data)
{
  $con = config::connect();
  $query = $con->prepare("SELECT DISTINCT(datasam) FROM new_sistema_msg_alunos WHERE linksam IS NOT NULL and idmodulosam=:idmodulo AND idturmasam=:idturma ");
  $query->bindParam(":idmodulo", $idMdl);
  $query->bindParam(":idturma", $codTurma);
  $query->execute();
  $fetch = $query->fetchALL();
  $numAula = count($fetch);
  $querycont = $con->prepare("SELECT * FROM new_sistema_msg_alunos WHERE linksam IS NOT NULL and idmodulosam=:idmodulo AND idturmasam=:idturma AND datasam=:datasam ");
  $querycont->bindParam(":idmodulo", $idMdl);
  $querycont->bindParam(":idturma", $codTurma);
  $querycont->bindParam(":datasam", $data);
  $querycont->execute();
  $fetchcont = $querycont->fetchALL();
  $contdt = count($fetchcont);
  $numAula = $numAula;
  if ($contdt == 0) {
    $numAula = $numAula + 1;
  }
  return $numAula;
} ?>
<?php
function totalanexos($id)
{
  $con = config::connect();
  $query = $con->prepare("SELECT * FROM new_sistema_publicacoes_anexos_PJA WHERE codpublicacao = :var ");
  $query->bindParam(":var", $id);
  $query->execute();
  $fetch = $query->fetchALL();
  $quant = count($fetch);
  return $quant;
}
?>
<?php
function criar_calendarios($data_inicial, $data_final, $con)
{
  $data_atual = new DateTime($data_inicial);
  $data_final = new DateTime($data_final);
  $datahoje = date("Y-m-d");
  echo "<div style=';'>";
  echo "<div style='margin: 10px;display: flex;  justify-content:left; flex-wrap:wrap'>";
  // Loop para criar os calendários para cada mês
  while ($data_atual <= $data_final) {
    // Obter o mês e ano do objeto DateTime
    $numero_mes = $data_atual->format('m');
    $ano = $data_atual->format('Y');
    // Inicializar array com os nomes dos meses em português
    $nomesMeses = array(
      1 => 'Janeiro',
      2 => 'Fevereiro',
      3 => 'Março',
      4 => 'Abril',
      5 => 'Maio',
      6 => 'Junho',
      7 => 'Julho',
      8 => 'Agosto',
      9 => 'Setembro',
      10 => 'Outubro',
      11 => 'Novembro',
      12 => 'Dezembro'
    );
    $numero_mes = ltrim($numero_mes, '0');
    // Imprimir o cabeçalho do calendário
    // Montar a tabela do calendário
    echo ('<div id="calendario">'); //div 1
    echo ('<div id="titulosemana">' . $nomesMeses[$numero_mes] . '</div>');
    echo ('<div id="Dias">
     <div id="diaSemanaD">D*</div>
     <div id="diaSemana">S</div>
     <div id="diaSemana">T</div>
     <div id="diaSemana">Q</div>
     <div id="diaSemana">Q</div>
     <div id="diaSemana">S</div>
     <div id="diaSemana">S</div>
   </div>');
    // Obter o número de dias no mês
    $numero_de_dias = intval($data_atual->format('t'));
    // Definir o primeiro dia do mês
    $data_atual->setDate($ano, $numero_mes, 1);
    // Obter o dia da semana do primeiro dia do mês
    $primeiro_dia_semana = intval($data_atual->format('w'));
    // Calcular o número de células vazias antes do primeiro dia do mês
    echo '<div id="Dias">';
    for ($j = 0; $j < $primeiro_dia_semana; $j++) {
      echo "<div id='dia'>-</div>";
    }
    // Loop para preencher os dias do mês
    for ($dia = 1; $dia <= $numero_de_dias; $dia++) {
      $dcal = str_pad($dia, 2, '0', STR_PAD_LEFT);
      $datacal = $ano . "-" . $numero_mes . "-" . $dcal;
      $idTurma = "0";
      if (!empty($_COOKIE['navAdmin'])) {
        $decAdm = encrypt($_COOKIE['navAdmin'], $action = 'd');
        $exp = explode("&", $decAdm);
        $idTurma = $exp[7];
      }
      $queryDatas = $con->prepare("SELECT * FROM new_sistema_cursos_turma_data WHERE codigotumractd = :idturma AND dataaulactd=:datacal  ORDER BY dataaulactd ");
      $queryDatas->bindParam(":idturma", $idTurma);
      $queryDatas->bindParam(":datacal", $datacal);
      $queryDatas->execute();
      $datat = $queryDatas->fetch(PDO::FETCH_ASSOC);
      if ($datat) {
        $active = "checked";
        $classactive = (' class="checked" ');
      } else {
        $active = "";
        $classactive = ('');
      }
      $dia = str_pad($dia, 2, '0', STR_PAD_LEFT);
      $numero_mes = str_pad($numero_mes, 2, '0', STR_PAD_LEFT);
      echo ('<div ' . $classactive . ' id="dia"><input type="checkbox" name="dia_selecionado[]" value="' . $ano . "-" . $numero_mes . "-" . $dia . '" title="' . $ano . "-" . $numero_mes . "-" . $dia . '" >' . $dia . '</div>');
      // Quebrar a linha no final da semana (sábado)
      if (($primeiro_dia_semana + $dia) % 7 == 0) {
        echo "</div>";
        echo "<div id='Dias'>";
      }
    }
    // Preencher células vazias no final do mês
    while (($primeiro_dia_semana + $dia) % 7 != 0) {
      echo "<div id='dia'>-</div>";
      $dia++;
    }
    echo "</div>";
    echo ('</div>'); // fecha div 1
    // Avançar para o próximo mês
    $data_atual->add(new DateInterval('P1M'));
  }
  echo "</div>";
  echo "</div>";
}
?>
<?php
function dataprazo($data, $d)
{
  $dataprazo = date_create($data);
  $dias_a_adicionar = $d; /* dias a acrescentar! */
  date_add($dataprazo, date_interval_create_from_date_string("$dias_a_adicionar days"));
  $dataprazo = date_format($dataprazo, 'Y-m-d');
  return $dataprazo;
}
?>
<?php
function monitorar($codigoUser)
{
  if ($codigoUser == 1) {
    echo encrypt($_COOKIE['startusuario'], $action = 'd');
  }
} ?>
<?php function menusala($n)
{
  $link1 = ('<a class="spanbox bdcinza  mr5" href="../redesocial_turmas/">SALA </a>');
  $link2 = ('<a class="spanbox bdcinza  mr5" href="../redesocial_turmas/papo.php"><i class="fa fa-comment-o" aria-hidden="true"></i></a>');
  $link3 = ('<a class="spanbox bdcinza  mr5" href="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation"> <i class="fa fa-bars" aria-hidden="true"></i></a>');
  $link4 = ('<a title="Sala de vídeos" style="color:red" class="spanbox bdcinza  mr5" href="../redesocial_turmas/saladevideos.php">
  <i class="fa fa-youtube-play" aria-hidden="true"></i></a>');
  $links = array($link1, $link2, $link3, $link4);
  return $links[$n];
} ?>
<?php
function verificaString($texto)
{
  $padroes = [
    '/<\s*script\s*>/',             // Verifica a tag <script>
    '/<\s*iframe\s*>/',             // Verifica a tag <iframe>
    '/<\s*object\s*>/',             // Verifica a tag <object>
    '/<\s*embed\s*>/',              // Verifica a tag <embed>
    '/<\s*frame\s*>/',              // Verifica a tag <frame>
    '/<\s*applet\s*>/',             // Verifica a tag <applet>
    '/<\s*meta\s*http-equiv\s*=\s*[\'"]?refresh[\'"]?\s*>/i',  // Verifica meta tags com redirecionamento automático
    '/\bjavascript:/i',             // Verifica se há "javascript:" em qualquer lugar da string
    '/\bon\w+\s*=/i',               // Verifica se há eventos JavaScript inline
    '/\bon\w+\s*=/i',               // Verifica se há eventos JavaScript inline
    '/(?:=|OR\s+=\s+\')/'
  ];
  foreach ($padroes as $padrao) {
    if (preg_match($padrao, $texto)) {
      return true; // Retorna verdadeiro se uma correspondência for encontrada
    }
  }
  return false; // Retorna falso se nenhum padrão for encontrado
}
?>


<?php
// --- CONSULTA DADOS DO SITE ---
// Validação – ID inteiro


// Consulta
$sql = "SELECT * FROM a_site_dadossite LIMIT 1";
$stmt = $con->prepare($sql);
$stmt->execute();

$rw = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rw) {
  die('Registro não encontrado.');
}

/* ============
   VARIÁVEIS
   Cada campo validado e convertido
   ============ */

// int
$formpagueseguro = (int) $rw['formpagueseguro'];
$visivel = (int) $rw['visivel'];
// string básicos
$titulo = !empty($rw['titulo']) ? (string) $rw['titulo'] : '';
$autor = !empty($rw['autor']) ? (string) $rw['autor'] : '';
$imagem = !empty($rw['imagem']) ? (string) $rw['imagem'] : '';
$pasta = !empty($rw['pasta']) ? (string) $rw['pasta'] : '';
$size = !empty($rw['size']) ? (string) $rw['size'] : '';
$imglogo = !empty($rw['imglogo']) ? (string) $rw['imglogo'] : '';
$UrlLogo = "https://professoreugenio.com/img/dadosdosite/" . $imglogo;
$logosize = !empty($rw['logosize']) ? (string) $rw['logosize'] : '';
$imgcapa = !empty($rw['imgcapa']) ? (string) $rw['imgcapa'] : '';
$capasize = !empty($rw['capasize']) ? (string) $rw['capasize'] : '';
$descricao = !empty($rw['descricao']) ? (string) $rw['descricao'] : '';
$buscatitulo = !empty($rw['buscatitulo']) ? (string) $rw['buscatitulo'] : '';
$facebook = !empty($rw['facebook']) ? (string) $rw['facebook'] : '';
$instagram = !empty($rw['instagram']) ? (string) $rw['instagram'] : '';
$emailpagueseguro = !empty($rw['emailpagueseguro']) ? (string) $rw['emailpagueseguro'] : '';
$emailcontato = !empty($rw['emailcontato']) ? (string) $rw['emailcontato'] : '';
$celular = !empty($rw['celular']) ? (string) $rw['celular'] : '';
$emaildesenvolvedor = !empty($rw['emaildesenvolvedor']) ? (string) $rw['emaildesenvolvedor'] : '';
$idpagina = !empty($rw['id']) ? (string) $rw['id'] : '';
$twitter = !empty($rw['twitter']) ? (string) $rw['twitter'] : '';
$google = !empty($rw['google']) ? (string) $rw['google'] : '';
$googlemaps = !empty($rw['googlemaps']) ? (string) $rw['googlemaps'] : '';
$endereco = !empty($rw['endereco']) ? (string) $rw['endereco'] : '';

// textos longos
$metagsfacebook = !empty($rw['metagsfacebook']) ? (string) $rw['metagsfacebook'] : '';
$metagstwitter = !empty($rw['metagstwitter']) ? (string) $rw['metagstwitter'] : '';
$metatagsinstagran = !empty($rw['metatagsinstagran']) ? (string) $rw['metatagsinstagran'] : '';
$keywords = !empty($rw['keywords']) ? (string) $rw['keywords'] : '';
$googlefont = !empty($rw['googlefont']) ? (string) $rw['googlefont'] : '';
$scriptfacebook = !empty($rw['scriptfacebook']) ? (string) $rw['scriptfacebook'] : '';
$scripttwitter = !empty($rw['scripttwitter']) ? (string) $rw['scripttwitter'] : '';
$scriptgplus = !empty($rw['scriptgplus']) ? (string) $rw['scriptgplus'] : '';

// meta tags

$siteUrl = 'https://professoreugenio.com'; // ajuste conforme a URL real
$defaultImageUrl = 'https://professoreugenio.com/img/dadosdosite/' . $imgcapa;

// se $imgcapa já guardar URL absoluto, use direto; se for só nome do arquivo, ajuste o caminho
$ogImage = $defaultImageUrl;

$pageTitle = $titulo;
$metaDescription = $descricao;
$metaKeywords = $keywords;
$ogTitle = $buscatitulo ?: $pageTitle;
$ogDescription = $metaDescription;
$twitterTitle = $ogTitle;
$twitterDescription = $ogDescription;
?>

<?php
function somardias($data, $dias)
{
  // Criando um objeto DateTime com a data inicial
  $datanew = new DateTime($data);
  // Adicionando 20 dias
  $datanew->modify('+ ' . $dias . ' days');
  return $datanew->format('Y-m-d');
}
?>
<?php
function diferencadedata($data, $hora)
{
  $publicationDateTimeString = $data . ' ' . $hora;
  $publicationDateTime = new DateTime($publicationDateTimeString);
  $currentDateTime = new DateTime();
  $interval = $publicationDateTime->diff($currentDateTime);
  $seg = $interval->s;
  return $seg;
}
?>
<?php
function urlsala($url)
{
  $busca1 = "/professoreugenio.com";
  if (stripos($url, $busca1) !== false) {
    $var2 = "var=";
    if (stripos($url, $var2) !== false) {
      $exp = explode("?", $url);
      $exp = explode("=", $url);
      $varlink = $exp[1];
      $link = ('../action.php?pub=') . $varlink;
    } else {
      $link = ('#1');
    }
  } else {
    $link = ('#2');
  }
  return $link;
}
?>
<?php
/**/
function tempocorrido2($data1, $hora1, $data2, $hora2)
{
  // Combinar data e hora em um único formato
  $inicio = new DateTime("$data1 $hora1");
  $fim = new DateTime("$data2 $hora2");
  // Calcular a diferença entre as datas
  $diferenca = $inicio->diff($fim);
  // Converter a diferença em segundos
  $segundosTotais = ($fim->getTimestamp() - $inicio->getTimestamp());
  // Definir as condições para o tempo decorrido
  if ($segundosTotais < 60) {
    return $segundosTotais == 1 ? 'há 1 segundo' : "<span class='recente'> há $segundosTotais s</span> ";
  } elseif ($segundosTotais < 3600) {
    $minutos = floor($segundosTotais / 60);
    return $minutos == 1 ? 'há 1 minuto' : "<span class='recente'> há $minutos min</span>";
  } elseif ($segundosTotais < 86400) {
    $horas = floor($segundosTotais / 3600);
    return $horas == 1 ? 'há 1 hora' : "há $horas horas";
  } elseif ($segundosTotais < 604800) {
    $dias = floor($segundosTotais / 86400);
    return $dias == 1 ? 'há 1 dia' : "há $dias dias";
  } elseif ($segundosTotais < 2592000) {
    $semanas = floor($segundosTotais / 604800);
    return $semanas == 1 ? 'há 1 semana' : "há $semanas semanas";
  } elseif ($segundosTotais < 31536000) {
    $meses = floor($segundosTotais / 2592000);
    return $meses == 1 ? 'há 1 mês' : "há $meses meses";
  } else {
    $anos = floor($segundosTotais / 31536000);
    return $anos == 1 ? 'há 1 ano' : "há $anos anos";
  }
}
?>
<?php
function tempocorrido($datapost)
{
  $timestamp = strtotime($datapost); // Converte a data para um timestamp
  $currentTime = time(); // Obtém o timestamp atual
  $timeDifference = $currentTime - $timestamp; // Calcula a diferença em segundos
  // Define os valores de tempo em segundos
  $seconds = $timeDifference;
  $minutes = round($timeDifference / 60);
  $hours = round($timeDifference / 3600);
  $days = round($timeDifference / 86400);
  $weeks = round($timeDifference / 604800);
  $months = round($timeDifference / 2629440);
  $years = round($timeDifference / 31553280);
  // Verifica a diferença e retorna o tempo correspondente
  if ($seconds <= 60) {
    return "há alguns segundos";
  } else if ($minutes <= 60) {
    return $minutes == 1 ? "há 1 minuto" : "há $minutes minutos";
  } else if ($hours <= 24) {
    return $hours == 1 ? "há 1 hora" : "há $hours horas";
  } else if ($days <= 7) {
    return $days == 1 ? "há 1 dia" : "há $days dias";
  } else if ($weeks <= 4) {
    return $weeks == 1 ? "há 1 semana" : "há $weeks semanas";
  } else if ($months <= 12) {
    return $months == 1 ? "há 1 mês" : "há $months meses";
  } else {
    return $years == 1 ? "há 1 ano" : "há $years anos";
  }
}
/**/
?>
<?php
function tempoDecorrido($dataInicio, $horaInicio)
{
  // Combinar data e hora de início em um único formato de data e hora
  $dataHoraInicio = $dataInicio . ' ' . $horaInicio;
  // Criar objeto DateTime para a data e hora de início
  $dataHoraInicioObj = new DateTime($dataHoraInicio);
  // Criar objeto DateTime para a data e hora atual
  $dataHoraAtualObj = new DateTime();
  // Calcular a diferença entre as duas datas e horas
  $diferenca = $dataHoraAtualObj->diff($dataHoraInicioObj);
  // Determinar se a data e hora de início está no passado ou no futuro
  $passado = $dataHoraInicioObj < $dataHoraAtualObj;
  $prefixo = $passado ? 'há' : 'em';
  if ($diferenca->y > 0) {
    return $prefixo . ' ' . $diferenca->y . ' ' . ($diferenca->y > 1 ? 'anos' : 'ano');
  }
  if ($diferenca->m > 0) {
    return $prefixo . ' ' . $diferenca->m . ' ' . ($diferenca->m > 1 ? 'meses' : 'mês');
  }
  if ($diferenca->d > 0) {
    return $prefixo . ' ' . $diferenca->d . ' ' . ($diferenca->d > 1 ? 'dias' : 'dia');
  }
  if ($diferenca->h > 0) {
    return $prefixo . ' ' . $diferenca->h . ' ' . ($diferenca->h > 1 ? 'horas' : 'hora');
  }
  if ($diferenca->i > 0) {
    return $prefixo . ' ' . $diferenca->i . ' ' . ($diferenca->i > 1 ? 'minutos' : 'minuto');
  }
  if ($diferenca->s > 0) {
    return $prefixo . ' ' . $diferenca->s . ' ' . ($diferenca->s > 1 ? 'segundos' : 'segundo');
  }
  return 'agora mesmo';
}
// Exemplo de uso:
// $dataInicio = '2024-06-16';
// $horaInicio = '10:00:00';
// $resultado = tempoDecorrido($dataInicio, $horaInicio);
// echo $resultado;
?>
<?php
function extractWords($input)
{
  // Remover tags <script> e seu conteúdo
  $cleaned = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $input);
  $cleaned = preg_replace('/<p\b[^>]*>(.*?)<\/p>/is', '', $cleaned);
  $cleaned = preg_replace('/<b\b[^>]*>(.*?)<\/b>/is', '', $cleaned);
  // Remover tags <img> e seus parâmetros/valores
  $cleaned = preg_replace('/<img\b[^>]*>/i', '', $cleaned);
  // Remover todas as outras tags HTML mas manter o texto dentro delas
  $cleaned = preg_replace('/<[^>]+>/', '', $cleaned);
  // Decodificar entidades HTML
  $cleaned = html_entity_decode($cleaned);
  // Remover valores entre aspas duplas
  $cleaned = preg_replace('/"[^"]*"/', '', $cleaned);
  // Remover caracteres especiais que não são letras ou números
  $cleaned = preg_replace('/[^\p{L}\p{N}\s]/u', '', $cleaned);
  $cleaned = str_replace(['pp', 'span', '<p>', 'pp', 'br', 'h3', 'h4', 'h2', 'h1', 'blockquote', 'img', 'style', '', '', '', ''], '', $cleaned);
  // Lista de preposições e pronomes a serem removidos
  $stopWords = ['de', 'para', 'com', 'este', 'é', 'e', 'ao', 'no', 'um', 'uma', 'os', 'as', 'da', 'do', 'das', 'dos', 'em', 'na', 'no', 'não', 'às', 'à', 'a', 'o', 'que', 'se', 'por', 'mais', 'ou', 'dos', 'das', 'são', 'como', 'mas', 'eles', 'elas', 'ele', 'ela', 'este', 'esta', 'estes', 'estas', 'deste', 'desta'];
  // Transformar a string em um array de palavras
  $words = preg_split('/[\s]+/', $cleaned, -1, PREG_SPLIT_NO_EMPTY);
  // Remover preposições e pronomes
  $filteredWords = array_filter($words, function ($word) use ($stopWords) {
    return !in_array(strtolower($word), $stopWords);
  });
  // Remover duplicatas
  $uniqueWords = array_unique($filteredWords);
  // Ordenar palavras em ordem alfabética
  sort($uniqueWords);
  // Transformar array em string separada por vírgulas
  $result = implode(', ', $uniqueWords);
  return $result;
}
?>
<?php
function explodedec($dec)
{
  $parts = explode('&', $dec);
  $index = 0;
  foreach ($parts as $part) {
    if (!empty($part)) { // Verificar se a parte não está vazia
      echo "{ " . "$index. $part" . " } ";
      $index++;
    }
  }
}
?>
<?php
function validadeCodigo($post)
{
  $chave = trim($post);
  $chave = strip_tags($chave);
  $chave = htmlspecialchars($chave, ENT_QUOTES, 'UTF-8');
  // $chave = substr($chave, 0, $max);
  if (!preg_match('/^[a-zA-Z0-9-_]+$/', $chave)) {
    echo exit();
    echo "Chave inválida: contém caracteres não permitidos.";
  } else {
  }
}
?>
<?php
function identificarPeriodo($datasha, $horasha)
{
  $hora = (int) date('H', strtotime($horasha));
  if ($hora >= 5 && $hora < 12) {
    $periodo = 'Manhã';
    $cor = '#4B0082';
  } elseif ($hora >= 12 && $hora < 18) {
    $periodo = 'Tarde';
    $cor = '#FF4500';
  } else {
    $periodo = 'Noite';
    $cor = '#1C1C1C';
  }
  return "<div style='color: $cor;'>Período: $periodo</div>";
  // Exemplo de uso
  // echo identificarPeriodo('2025-02-04', '14:30:00');
}
?>
<?php
function pagina($paginaatual)
{
  // Verifica se a URL é válida
  if (filter_var($paginaatual, FILTER_VALIDATE_URL)) {
    // Analisa a URL e obtém o caminho
    $path = parse_url($paginaatual, PHP_URL_PATH);
    // Obtém o nome da página a partir do caminho
    $pageName = basename($path);
    return $pageName;
  } else {
    return "URL inválida";
  }
}
// Exemplo de uso
// $url = "https://www.exemplo.com/pagina-exemplo.php";
// echo getPageName($url); 
?>
<?php
function removerparametros($html, $parametros = [])
{
  // Se nenhum parâmetro for passado, remove apenas 'background-color'
  if (empty($parametros)) {
    $parametros[] = 'background-color';
  }
  // Loop para remover todos os parâmetros passados
  foreach ($parametros as $parametro) {
    $pattern = '/' . preg_quote($parametro, '/') . '\s*:\s*[^;"]*;?/i';
    $html = preg_replace($pattern, '', $html);
  }
  // Remove o atributo style se estiver vazio
  $html = preg_replace('/style="\s*"/i', '', $html);
  return $html;
}
?>
<?php
function removerArtigoseAdverbios($texto)
{
  // Lista de preposições, artigos e advérbios a serem removidos
  $palavrasIndesejadas = [
    // Artigos e Preposições
    'a',
    'o',
    'as',
    'os',
    'é',
    'mais',
    'menos',
    'ser',
    'está',
    'até',
    'um',
    'uma',
    'uns',
    'umas',
    'de',
    'do',
    'da',
    'dos',
    'das',
    'em',
    'no',
    'na',
    'nos',
    'nas',
    'por',
    'para',
    'com',
    'sem',
    'sobre',
    'entre',
    'e',
    'ou',
    'mas',
    'que',
    'se',
    'ao',
    'à',
    'às',
    // Advérbios comuns
    'muito',
    'pouco',
    'bastante',
    'demais',
    'tanto',
    'quase',
    'apenas',
    'já',
    'ainda',
    'logo',
    'sempre',
    'nunca',
    'jamais',
    'ontem',
    'hoje',
    'amanhã',
    'cedo',
    'tarde',
    'bem',
    'mal',
    'melhor',
    'pior',
    'longe',
    'perto',
    'acima',
    'abaixo',
    'dentro',
    'fora',
    'aqui',
    'ali',
    'lá',
    'assim',
    'calmamente',
    'rapidamente',
    'facilmente',
    'dificilmente',
    'extremamente',
    'provavelmente',
    'certamente',
    'claramente',
    'realmente',
    'simplesmente',
    // Conjunções
    'e',
    'ou',
    'mas',
    'porque',
    'pois',
    'portanto',
    'contudo',
    'entretanto',
    'todavia',
    'porém',
    'logo',
    'assim',
    'como',
    'que',
    'se',
    'quando',
    'enquanto',
    'embora',
    'apesar',
    'ainda',
    'já',
    'tanto',
    'quanto',
    'como',
    'assim',
    'também',
    'nem',
    'não',
    'sim',
    'talvez',
    'porquanto',
    'conquanto',
    'senão',
    'ora',
    'quer',
    'seja',
    'caso',
    'desde',
    'conforme',
    'consoante',
    'segundo',
    'conquanto',
    'embora',
    'ainda',
    'mesmo',
    'que',
    'posto',
    'que',
    'porquanto',
    'porque',
    'pois',
    'porquanto',
    'porém',
    'contudo',
    'entretanto',
    'todavia',
    'não',
    'obstante',
    'senão',
    'logo',
    'portanto',
    'por',
    'conseguinte',
    'assim',
    'então',
    'pois',
    'porquanto',
    'porém',
    'contudo',
    'entretanto',
    'todavia',
    'não',
    'obstante',
    'senão',
    'logo',
    'portanto',
    'por',
    'conseguinte',
    'assim',
    'então',
    'pois'
  ];
  // Passo 1: Remove pontuação (. , ;)
  $textoLimpo = preg_replace('/[.,;]/', '', $texto);
  // Passo 2: Força a codificação para UTF-8 antes de converter para minúsculas
  $textoLimpo = mb_convert_encoding($textoLimpo, 'UTF-8', 'auto');
  $textoLimpo = mb_strtolower($textoLimpo, 'UTF-8');
  // Passo 3: Separa as palavras em um array
  $palavras = preg_split('/\s+/', $textoLimpo); // Aceita múltiplos espaços
  // Passo 4: Remove preposições, artigos, advérbios e palavras duplicadas
  $tagsFiltradas = array_unique(array_diff($palavras, $palavrasIndesejadas));
  // Passo 5: Converte o array de volta para uma string separada por vírgulas
  return implode(',', $tagsFiltradas);
}
?>
<?php
function dataaula($datamsg, $data)
{
  $datahj = "";
  $load = ('<div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>');
  if ($datamsg == $data) {
    $datast = ('<p class="date"><span style="background-color:#ff0080; color:#ffffff;padding: 2px 10px;border-radius:5px; font-weight:600;font-size:12px;display:inline-block">Hoje</span></p>');
  } else {
    $datapost = diadasemana($datamsg, $n = "1");
    $datast = (' <p class="date">' . $datapost . " " . tempocorrido($datamsg) . '</p>');
  }
  echo $datast;
} ?>
<?php
function gerarChaveUnica($tamanho = 8)
{
  return bin2hex(random_bytes($tamanho / 2));
}
$chave = gerarChaveUnica();
?>
<?php
function getBrowser($userAgent)
{
  if (strpos($userAgent, 'Opera') || strpos($userAgent, 'OPR/')) {
    return 'Opera';
  } elseif (strpos($userAgent, 'Edge')) {
    return 'Microsoft Edge';
  } elseif (strpos($userAgent, 'Chrome')) {
    return 'Google Chrome';
  } elseif (strpos($userAgent, 'Safari')) {
    return 'Safari';
  } elseif (strpos($userAgent, 'Firefox')) {
    return 'Mozilla Firefox';
  } elseif (strpos($userAgent, 'MSIE') || strpos($userAgent, 'Trident/7')) {
    return 'Internet Explorer';
  }
  return 'Navegador desconhecido';
}
$userAgent = $_SERVER['HTTP_USER_AGENT'];
$browser = getBrowser($userAgent);
function getUserIP()
{
  if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    return $_SERVER['HTTP_CLIENT_IP'];
  } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    return $_SERVER['HTTP_X_FORWARDED_FOR'];
  } else {
    return $_SERVER['REMOTE_ADDR'];
  }
}
$cookieName = "user_ip";
$cookieChaveUnica = "chaveunica";
$nmpage = basename($_SERVER['PHP_SELF']);
?>
<?php
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
function getDeviceType($user_agent)
{
  $user_agent = strtolower($user_agent);

  // Busca por palavras-chave comuns de mobile/tablet
  if (preg_match('/(mobile|android|iphone|ipod|blackberry|iemobile|opera mini|windows phone)/', $user_agent)) {
    return "Mobile";
  } elseif (preg_match('/(ipad|tablet)/', $user_agent)) {
    return "Tablet"; // Você pode trocar para "Tablet" se quiser distinguir
  } elseif (preg_match('/(windows|macintosh|linux|cros)/', $user_agent)) {
    return "Desktop";
  } else {
    return "Indefinido";
  }
}

$dispositoAcesso = getDeviceType($user_agent);
function detectaNavegador($user_agent)
{
  if (strpos($user_agent, 'Edge') !== false) {
    return 'Microsoft Edge';
  } elseif (strpos($user_agent, 'Chrome') !== false) {
    return 'Google Chrome';
  } elseif (strpos($user_agent, 'Firefox') !== false) {
    return 'Mozilla Firefox';
  } elseif (strpos($user_agent, 'MSIE') !== false || strpos($user_agent, 'Trident') !== false) {
    return 'Internet Explorer';
  } elseif (strpos($user_agent, 'Safari') !== false) {
    return 'Apple Safari';
  } else {
    return 'Navegador desconhecido';
  }
}
$Navegador = detectaNavegador($user_agent);
?>
<?php
function removerAcentos($string)
{
  return strtr($string, [
    'á' => 'a',
    'à' => 'a',
    'ã' => 'a',
    'â' => 'a',
    'ä' => 'a',
    'é' => 'e',
    'è' => 'e',
    'ê' => 'e',
    'ë' => 'e',
    'í' => 'i',
    'ì' => 'i',
    'î' => 'i',
    'ï' => 'i',
    'ó' => 'o',
    'ò' => 'o',
    'õ' => 'o',
    'ô' => 'o',
    'ö' => 'o',
    'ú' => 'u',
    'ù' => 'u',
    'û' => 'u',
    'ü' => 'u',
    'ç' => 'c',
    'ñ' => 'n',
    'Á' => 'A',
    'À' => 'A',
    'Ã' => 'A',
    'Â' => 'A',
    'Ä' => 'A',
    'É' => 'E',
    'È' => 'E',
    'Ê' => 'E',
    'Ë' => 'E',
    'Í' => 'I',
    'Ì' => 'I',
    'Î' => 'I',
    'Ï' => 'I',
    'Ó' => 'O',
    'Ò' => 'O',
    'Õ' => 'O',
    'Ô' => 'O',
    'Ö' => 'O',
    'Ú' => 'U',
    'Ù' => 'U',
    'Û' => 'U',
    'Ü' => 'U',
    'Ç' => 'C',
    'Ñ' => 'N'
  ]);
}
?>


<?php

function icoemail($lock)
{
  if ($lock == '1') {
    $icoemail = ('<span style="color:red"> ¹<i class="bi bi-envelope-slash"></i></span>');
  } else {
    $icoemail = ('<span style="color:green"> ¹<i class="bi bi-envelope-slash"></i></span>');
  }

  echo $icoemail;
}

function icouser($vis)
{
  if ($vis == '1') {
    $icovis = ('<span style="color:green"> ²<i class="bi bi-person"></i></span>');
  } else {
    $icovis = ('<span style="color:red"> ²<i class="bi bi-person-x"></i></span>');
  }

  echo $icovis;
}

function lockemail($lock)
{
  if ($lock == '1') {
    $icolock = ('<span style="color:red"> ³<i class="fa fa-lock" aria-hidden="true"></i></span>');
  } else {
    $icolock = ('<span style="color:green"> ³<i class="fa fa-unlock" aria-hidden="true"></i></span>');
  }

  echo $icolock;
}

function lockid($vis)
{
  if ($vis == '1') {
    $vis = ('<span style="color:green"> ¹¹<i class="fa fa-unlock" aria-hidden="true"></i></span>');
  } else {
    $vis = ('<span style="color:red"> ¹¹<i class="fa fa-lock" aria-hidden="true"></i></span>');
  }

  echo $vis;
}

?>

<?php
function temPermissao($nivelUsuario, $permitidos = [])
{
  return in_array($nivelUsuario, $permitidos);
}
?>

<?php

function gerarChaveFormulario(): string
{
  $data = new DateTime('now', new DateTimeZone('America/Fortaleza'));

  $ano = $data->format('Y');          // 2025
  $mes = $data->format('m');          // 06
  $dia = $data->format('d');          // 23
  $mesAbrev = $data->format('M');     // Jun
  $time = time();       // 172035

  return "{$ano}{$mes}{$dia}_{$mesAbrev}_{$time}";
}

?>



<?php

function gerarTags($texto)
{


  // Remove HTML, pontuação e deixa tudo minúsculo
  $limpo = html_entity_decode($texto);
  $limpo = strip_tags($limpo);
  $limpo = removerAcentos($limpo);
  $limpo = strtolower($limpo);
  $limpo = mb_convert_encoding($limpo, 'UTF-8', 'auto'); // Força UTF-8
  // $limpo = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $limpo);
  $limpo = preg_replace('/[^\p{L}\s]/u', ' ', $limpo);


  // Separa palavras
  $palavras = preg_split('/\s+/', $limpo);

  // Lista de palavras irrelevantes (stopwords)
  $stopwords = [
    'a',
    'o',
    'as',
    'os',
    'um',
    'uma',
    'uns',
    'umas',
    'de',
    'do',
    'da',
    'dos',
    'das',
    'em',
    'no',
    'na',
    'nos',
    'nas',
    'por',
    'para',
    'com',
    'sem',
    'sobre',
    'entre',
    'até',
    'após',
    'antes',
    'e',
    'ou',
    'mas',
    'porque',
    'que',
    'quando',
    'onde',
    'como',
    'qual',
    'também',
    'muito',
    'pouco',
    'já',
    'ainda',
    'então',
    'logo',
    'se',
    'não',
    'sim',
    'tão',
    'só',
    'mais',
    'menos',
    'todo',
    'toda',
    'ser',
    'estar',
    'ter',
    'fazer',
    'vai',
    'foi',
    'são',
    'estão',
    'estava',
    'nós',
    'vós',
    'eles',
    'elas',
    'eu',
    'tu',
    'ele',
    'ela',
    'nbs',
    // Você pode expandir esta lista conforme necessário
  ];

  // Remove palavras curtas e irrelevantes
  $filtradas = array_filter($palavras, function ($palavra) use ($stopwords) {
    return strlen($palavra) > 4 && !in_array($palavra, $stopwords);
  });

  // Remove duplicadas
  $unicas = array_unique($filtradas);

  // Ordena por ordem alfabética
  sort($unicas);

  // Junta como string separada por vírgulas
  return implode(', ', $unicas);
}

?>


<?php
// =====================================
// AUTH por COOKIE (ADMIN > ALUNO)
// =====================================

// Defaults
$codigoUser = 0;
$idUser = 0;
$nmUser = '';
$imgUser = rtrim((string) $raizSite, '/') . '/fotos/usuarios/usuario.png';
$emailuser = '';
$isAdmin = false;

// --------------------------------------------------
// 1) Escolhe o cookie (ADMIN primeiro, depois ALUNO)
// --------------------------------------------------
$dec = '';

if (!empty($_COOKIE['adminuserstart'])) {

  $dec = encrypt_secure((string) $_COOKIE['adminuserstart'], 'd');
  $isAdmin = true;
} elseif (!empty($_COOKIE['startusuario'])) {

  $dec = encrypt_secure((string) $_COOKIE['startusuario'], 'd');
  $isAdmin = false;
}

// --------------------------------------------------
// 2) Se não conseguiu decrypt, sai deslogado
// --------------------------------------------------
$dec = trim((string) $dec);
if ($dec === '') {
  // mantém defaults e encerra
  $codigoUser = 0;
  $idUser = 0;
  return;
}

// --------------------------------------------------
// 3) Extrai o ID do payload: "id&nome&..."
// --------------------------------------------------
$exp = explode('&', $dec);
$id0 = trim((string) ($exp[0] ?? ''));

if ($id0 === '' || !ctype_digit($id0) || (int) $id0 <= 0) {
  $codigoUser = 0;
  $idUser = 0;
  return;
}

$idUser = (int) $id0;

// --------------------------------------------------
// 4) Garante conexão $con (se necessário)
// --------------------------------------------------
if (!isset($con) || !$con instanceof PDO) {
  if (class_exists('config') && method_exists('config', 'connect')) {
    $con = config::connect();
  }
}
if (!isset($con) || !$con instanceof PDO) {
  $codigoUser = 0;
  $idUser = 0;
  return;
}

// --------------------------------------------------
// 5) Busca no banco (ADMIN ou ALUNO)
// --------------------------------------------------
if ($isAdmin) {

  // ADMIN: new_sistema_usuario
  $st = $con->prepare("
        SELECT codigousuario, nome, email, imagem200, imagem50, pastasu, liberado
        FROM new_sistema_usuario
        WHERE codigousuario = :id
        LIMIT 1
    ");
  $st->bindValue(':id', $idUser, PDO::PARAM_INT);
  $st->execute();
  $rw = $st->fetch(PDO::FETCH_ASSOC);

  if (!$rw) {
    $codigoUser = 0;
    $idUser = 0;
    return;
  }

  // bloqueado?
  if ((int) ($rw['liberado'] ?? 1) !== 1) {
    $codigoUser = 0;
    $idUser = 0;
    return;
  }

  $codigoUser = (int) ($rw['codigousuario'] ?? 0);
  $nmUser = trim((string) ($rw['nome'] ?? ''));
  $emailuser = trim((string) ($rw['email'] ?? ''));

  $foto = trim((string) (($rw['imagem200'] ?? '') !== '' ? $rw['imagem200'] : ($rw['imagem50'] ?? '')));
  $pasta = trim((string) ($rw['pastasu'] ?? ''));

  if ($foto !== '' && $pasta !== '') {
    $imgUser = rtrim((string) $raizSite, '/') . '/fotos/usuarios/' . rawurlencode($pasta) . '/' . rawurlencode($foto);
  }
} else {

  // ALUNO: new_sistema_cadastro
  $st = $con->prepare("
        SELECT codigocadastro, nome, email, imagem200, imagem50, pastasc
        FROM new_sistema_cadastro
        WHERE codigocadastro = :id
        LIMIT 1
    ");
  $st->bindValue(':id', $idUser, PDO::PARAM_INT);
  $st->execute();
  $rw = $st->fetch(PDO::FETCH_ASSOC);

  if (!$rw) {
    $codigoUser = 0;
    $idUser = 0;
    return;
  }

  $codigoUser = (int) ($rw['codigocadastro'] ?? 0);
  $nmUser = trim((string) ($rw['nome'] ?? ''));
  $emailuser = trim((string) ($rw['email'] ?? ''));

  $foto = trim((string) (($rw['imagem200'] ?? '') !== '' ? $rw['imagem200'] : ($rw['imagem50'] ?? '')));
  $pasta = trim((string) ($rw['pastasc'] ?? ''));

  if ($foto !== '' && $pasta !== '') {
    $imgUser = rtrim((string) $raizSite, '/') . '/fotos/usuarios/' . rawurlencode($pasta) . '/' . rawurlencode($foto);
  }
}

// --------------------------------------------------
// 6) Fallback final de imagem (se vazio)
// --------------------------------------------------
if (trim((string) $imgUser) === '') {
  $imgUser = rtrim((string) $raizSite, '/') . '/fotos/usuarios/usuario.png';
}
?>

<?php
// =====================================
// AUTH por SESSÃO + COOKIE (ADMIN > ALUNO)
// - Prioriza SESSÃO e cai para COOKIE
// - Mantém seu padrão de variáveis de saída
// =====================================

// Defaults (saída)
$codigoUser = 0;
$idUser = 0;
$nmUser = '';
$imgUser = rtrim((string) $raizSite, '/') . '/fotos/usuarios/usuario.png';
$emailuser = '';
$isAdmin = false;

// -------------------------------------
// Helpers
// -------------------------------------
if (!function_exists('auth_zero')) {
  function auth_zero(): void
  {
    global $codigoUser, $idUser, $nmUser, $imgUser, $emailuser, $isAdmin, $raizSite;
    $codigoUser = 0;
    $idUser = 0;
    $nmUser = '';
    $emailuser = '';
    $isAdmin = false;
    $imgUser = rtrim((string) $raizSite, '/') . '/fotos/usuarios/usuario.png';
  }
}

if (!function_exists('auth_parse_id_from_payload')) {
  function auth_parse_id_from_payload(string $dec): int
  {
    $dec = trim($dec);
    if ($dec === '')
      return 0;

    $exp = explode('&', $dec);
    $id0 = trim((string) ($exp[0] ?? ''));

    if ($id0 === '' || !ctype_digit($id0))
      return 0;

    $id = (int) $id0;
    return ($id > 0) ? $id : 0;
  }
}

if (!function_exists('auth_get_con')) {
  function auth_get_con(): ?PDO
  {
    global $con;
    if (isset($con) && $con instanceof PDO)
      return $con;

    if (class_exists('config') && method_exists('config', 'connect')) {
      $tmp = config::connect();
      if ($tmp instanceof PDO)
        return $tmp;
    }
    return null;
  }
}

// -------------------------------------
// 0) Garante sessão iniciada (se ainda não)
// -------------------------------------
if (session_status() !== PHP_SESSION_ACTIVE) {
  @session_start();
}

// -------------------------------------
// 1) Escolhe fonte (SESSÃO > COOKIE) (ADMIN > ALUNO)
// -------------------------------------
$dec = '';

// --- ADMIN via SESSÃO ---
if (!empty($_SESSION['adminuserstart'])) {
  $dec = encrypt_secure((string) $_SESSION['adminuserstart'], 'd');
  $isAdmin = true;

  // (fallback) isadmin "true" e payload em session adminuserstart
} elseif (!empty($_SESSION['isadmin']) && (int) $_SESSION['isadmin'] === 1 && !empty($_SESSION['adminuserstart'])) {
  $dec = encrypt_secure((string) $_SESSION['adminuserstart'], 'd');
  $isAdmin = true;

  // --- ALUNO via SESSÃO ---
} elseif (!empty($_SESSION['startusuario'])) {
  $dec = encrypt_secure((string) $_SESSION['startusuario'], 'd');
  $isAdmin = false;

  // --- ADMIN via COOKIE ---
} elseif (!empty($_COOKIE['adminuserstart'])) {
  $dec = encrypt_secure((string) $_COOKIE['adminuserstart'], 'd');
  $isAdmin = true;

  // --- ALUNO via COOKIE ---
} elseif (!empty($_COOKIE['startusuario'])) {
  $dec = encrypt_secure((string) $_COOKIE['startusuario'], 'd');
  $isAdmin = false;
}

// -------------------------------------
// 2) Se não conseguiu decrypt, sai deslogado
// -------------------------------------
$dec = trim((string) $dec);
if ($dec === '') {
  auth_zero();
  return;
}

// -------------------------------------
// 3) Extrai o ID do payload: "id&nome&..."
// -------------------------------------
$idUser = auth_parse_id_from_payload($dec);
if ($idUser <= 0) {
  auth_zero();
  return;
}

// -------------------------------------
// 4) Conexão PDO
// -------------------------------------
$conAuth = auth_get_con();
if (!$conAuth instanceof PDO) {
  auth_zero();
  return;
}

// -------------------------------------
// 5) Busca no banco (ADMIN ou ALUNO)
// -------------------------------------
if ($isAdmin) {

  // ADMIN: new_sistema_usuario
  $st = $conAuth->prepare("
      SELECT codigousuario, nome, email, imagem200, imagem50, pastasu, liberado
      FROM new_sistema_usuario
      WHERE codigousuario = :id
      LIMIT 1
  ");
  $st->bindValue(':id', $idUser, PDO::PARAM_INT);
  $st->execute();
  $rw = $st->fetch(PDO::FETCH_ASSOC);

  if (!$rw) {
    auth_zero();
    return;
  }

  // bloqueado?
  if ((int) ($rw['liberado'] ?? 1) !== 1) {
    auth_zero();
    return;
  }

  $codigoUser = (int) ($rw['codigousuario'] ?? 0);
  $nmUser = trim((string) ($rw['nome'] ?? ''));
  $emailuser = trim((string) ($rw['email'] ?? ''));

  $foto = trim((string) (($rw['imagem200'] ?? '') !== '' ? $rw['imagem200'] : ($rw['imagem50'] ?? '')));
  $pasta = trim((string) ($rw['pastasu'] ?? ''));

  if ($foto !== '' && $pasta !== '') {
    $imgUser = rtrim((string) $raizSite, '/') . '/fotos/usuarios/' . rawurlencode($pasta) . '/' . rawurlencode($foto);
  }

  // ✅ sincroniza estado em sessão (opcional mas útil)
  $_SESSION['isadmin'] = 1;
} else {

  // ALUNO: new_sistema_cadastro
  $st = $conAuth->prepare("
      SELECT codigocadastro, nome, email, imagem200, imagem50, pastasc
      FROM new_sistema_cadastro
      WHERE codigocadastro = :id
      LIMIT 1
  ");
  $st->bindValue(':id', $idUser, PDO::PARAM_INT);
  $st->execute();
  $rw = $st->fetch(PDO::FETCH_ASSOC);

  if (!$rw) {
    auth_zero();
    return;
  }

  $codigoUser = (int) ($rw['codigocadastro'] ?? 0);
  $nmUser = trim((string) ($rw['nome'] ?? ''));
  $emailuser = trim((string) ($rw['email'] ?? ''));

  $foto = trim((string) (($rw['imagem200'] ?? '') !== '' ? $rw['imagem200'] : ($rw['imagem50'] ?? '')));
  $pasta = trim((string) ($rw['pastasc'] ?? ''));

  if ($foto !== '' && $pasta !== '') {
    $imgUser = rtrim((string) $raizSite, '/') . '/fotos/usuarios/' . rawurlencode($pasta) . '/' . rawurlencode($foto);
  }

  // ✅ garante que não fique "admin" por engano
  $_SESSION['isadmin'] = 0;
}

// -------------------------------------
// 6) Fallback final de imagem (se vazio)
// -------------------------------------
if (trim((string) $imgUser) === '') {
  $imgUser = rtrim((string) $raizSite, '/') . '/fotos/usuarios/usuario.png';
}
?>
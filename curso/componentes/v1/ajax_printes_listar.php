<?php

declare(strict_types=1);

define('BASEPATH', true);
define('PUBLIC_ROOT', dirname(__DIR__, 2));
define('APP_ROOT', dirname(PUBLIC_ROOT, 2));
define('COMPONENTES_ROOT', APP_ROOT . '/componentes');

date_default_timezone_set('America/Fortaleza');
header('Content-Type: text/html; charset=utf-8');

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

require_once COMPONENTES_ROOT . '/v1/class.conexao.php';
require_once COMPONENTES_ROOT . '/v1/autenticacao.php';

/** @var PDO $con */

function e(string $txt): string
{
    return htmlspecialchars($txt, ENT_QUOTES, 'UTF-8');
}

$idPublicacao = (int)($_POST['idpublicacao'] ?? 0);
$idModulo = (int)($_POST['idmodulo'] ?? 0);
$idAluno = (int)($_POST['idaluno'] ?? 0);
$nomeTurma = trim((string)($_POST['nometurma'] ?? 'Turma atual'));
$isProfessor = !empty($_SESSION['admin_logado']) || !empty($_SESSION['usuario_logado']);

if ($idPublicacao <= 0 || $idModulo <= 0 || $idAluno <= 0) {
    echo '<div class="col-12"><div class="empty-state"><i class="bi bi-exclamation-circle fs-1 text-warning d-block mb-2"></i><div class="fw-semibold">Não foi possível identificar a atividade.</div></div></div>';
    exit;
}

$sql = "SELECT 
            a.codigoatividadeanexos,
            a.idalulnoAA,
            a.fotoAA,
            a.pastaAA,
            a.avaliacaoAA,
            a.dataenvioAA,
            a.horaenvioAA,
            u.nome,
            u.pastasc,
            u.imagem50
        FROM a_curso_AtividadeAnexos a
        LEFT JOIN new_sistema_cadastro u ON u.codigocadastro = a.idalulnoAA
        WHERE a.idpublicacacaoAA = :idpublicacao
          AND a.idmoduloAA = :idmodulo
          AND a.idalulnoAA = :idaluno
        ORDER BY a.codigoatividadeanexos DESC";

$stmt = $con->prepare($sql);
$stmt->bindValue(':idpublicacao', $idPublicacao, PDO::PARAM_INT);
$stmt->bindValue(':idmodulo', $idModulo, PDO::PARAM_INT);
$stmt->bindValue(':idaluno', $idAluno, PDO::PARAM_INT);
$stmt->execute();

$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$itens) {
    echo '
    <div class="col-12">
        <div class="empty-state">
            <i class="bi bi-images fs-1 text-secondary d-block mb-2"></i>
            <div class="fw-semibold mb-1">Nenhum print enviado ainda.</div>
            <div class="text-muted">Clique em <strong>Enviar print</strong> para adicionar a primeira imagem.</div>
        </div>
    </div>';
    exit;
}

foreach ($itens as $item) {
    $id = (int)$item['codigoatividadeanexos'];
    $avaliacao = (int)($item['avaliacaoAA'] ?? 0);

    $fotoPerfil = '/fotos/usuarios/usuario.png';
    if (!empty($item['pastasc']) && !empty($item['imagem50'])) {
        $fotoPerfil = '/fotos/usuarios/' . rawurlencode((string)$item['pastasc']) . '/' . rawurlencode((string)$item['imagem50']);
    }

    $fotoPrint = '/fotos/atividades/' . rawurlencode((string)$item['pastaAA']) . '/' . rawurlencode((string)$item['fotoAA']);

    $sqlComentarios = "SELECT 
            c.codigoatividadecomentario,
            c.iduserdeAAC,
            c.textoAAC,
            c.dataAAC,
            c.horaAAC,
            u.nome AS nomecomentador,
            u.pastasc AS pastacomentador,
            u.imagem50 AS imgcomentador
        FROM a_curso_AtividadeComentario c
        LEFT JOIN new_sistema_cadastro u ON u.codigocadastro = c.iduserdeAAC
        WHERE c.idfileAnexoAAC = :idfile
        ORDER BY c.codigoatividadecomentario DESC";

    $stmtC = $con->prepare($sqlComentarios);
    $stmtC->bindValue(':idfile', $id, PDO::PARAM_INT);
    $stmtC->execute();
    $comentarios = $stmtC->fetchAll(PDO::FETCH_ASSOC);

    echo '<div class="col-12 col-lg-6">';
    echo '  <div class="card print-card">';

    echo '      <div class="print-header">';
    echo '          <div class="d-flex align-items-center gap-3">';
    echo '              <img src="' . e($fotoPerfil) . '" alt="' . e((string)($item['nome'] ?? 'Aluno')) . '" class="print-user-photo">';
    echo '              <div>';
    echo '                  <div class="fw-bold">' . e((string)($item['nome'] ?? 'Aluno')) . '</div>';
    echo '                  <div class="small text-white-50">' . e($nomeTurma) . '</div>';
    echo '                  <div class="small text-white-50">';
    echo                        e(date('d/m/Y', strtotime((string)$item['dataenvioAA']))) . ' às ' . e(substr((string)$item['horaenvioAA'], 0, 5));
    echo '                  </div>';
    echo '              </div>';
    echo '          </div>';
    echo '      </div>';

    echo '      <div class="row g-0 flex-lg-nowrap">';
    echo '          <div class="col-lg-7">';
    echo '              <div class="print-media-wrap">';
    echo '                  <div class="print-image-box">';
    echo '                      <a href="' . e($fotoPrint) . '" class="abrir-lightbox w-100 h-100">';
    echo '                          <img src="' . e($fotoPrint) . '" alt="Print da atividade">';
    echo '                      </a>';
    echo '                  </div>';

    echo '                  <div class="print-actions">';
    echo '                      <button type="button" class="btn btn-outline-danger rounded-pill btn-excluir-print" data-id="' . $id . '">';
    echo '                          <i class="bi bi-trash-fill me-1"></i> Excluir';
    echo '                      </button>';

    echo '                      <div class="rating-box">';
    echo '                          <span class="small fw-semibold me-1">Avaliação:</span>';

    for ($i = 1; $i <= 5; $i++) {
        $classAtiva = $i <= $avaliacao ? ' active' : '';
        $classProfessor = $isProfessor ? ' can-rate' : '';
        echo '<button type="button" class="rating-star' . $classAtiva . $classProfessor . '" data-id="' . $id . '" data-star="' . $i . '" ' . (!$isProfessor ? 'disabled' : '') . '>';
        echo '<i class="bi bi-star-fill"></i>';
        echo '</button>';
    }

    echo '                      </div>';
    echo '                  </div>';
    echo '              </div>';
    echo '          </div>';

    echo '          <div class="col-lg-5 comments-col">';
    echo '              <div class="comments-wrap">';
    echo '                  <div class="fw-semibold mb-3"><i class="bi bi-chat-left-text me-1"></i> Comentários</div>';
    echo '                  <div class="comments-list">';

    if ($comentarios) {
        foreach ($comentarios as $comentario) {
            $nomeComentador = trim((string)($comentario['nomecomentador'] ?? ''));
            if ($nomeComentador === '') {
                $nomeComentador = ((int)($comentario['iduserdeAAC'] ?? 0) === $idAluno) ? 'Aluno' : 'Professor';
            }

            echo '      <div class="comment-item">';
            echo '          <div class="comment-meta">';
            echo                e($nomeComentador) . ' • ' . e(date('d/m/Y', strtotime((string)$comentario['dataAAC']))) . ' ' . e(substr((string)$comentario['horaAAC'], 0, 5));
            echo '          </div>';
            echo '          <div>' . nl2br(e((string)($comentario['textoAAC'] ?? ''))) . '</div>';
            echo '      </div>';
        }
    } else {
        echo '<div class="text-muted small">Nenhum comentário enviado ainda.</div>';
    }

    echo '                  </div>';

    echo '                  <div class="mt-3">';
    echo '                      <textarea id="comentario_' . $id . '" class="form-control comment-textarea mb-2" maxlength="300" placeholder="Digite um comentário..."></textarea>';
    echo '                      <button type="button" class="btn btn-success w-100 rounded-pill btn-comentar-print" data-id="' . $id . '" data-aluno="' . (int)$item['idalulnoAA'] . '">';
    echo '                          <i class="bi bi-send-fill me-1"></i> Comentar';
    echo '                      </button>';
    echo '                  </div>';
    echo '              </div>';
    echo '          </div>';
    echo '      </div>';

    echo '  </div>';
    echo '</div>';
}

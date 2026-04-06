<?php
declare(strict_types = 1)
;
define('BASEPATH', true);
define('PUBLIC_ROOT', __DIR__);
// ✅ pasta acima do public_html (ex.: /home/usuario)
define('APP_ROOT', dirname(__DIR__, 1));
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
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrativo | Professor Eugênio</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --bg-color: #f3f4f6;
            --card-bg: #ffffff;
            --text-color: #1f2937;
            --text-muted: #6b7280;
            --border-color: #e5e7eb;
            --error-color: #ef4444;
            --success-color: #10b981;
        }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            background-image: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1550751827-4bd374c3f58b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: var(--text-color);
        }

        .login-container {
            background: var(--card-bg);
            padding: 2.5rem;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            width: 100%;
            max-width: 400px;
            box-sizing: border-box;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .admin-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(79, 70, 229, 0.1);
            color: var(--primary-color);
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .login-header h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 0.875rem;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            color: var(--text-muted);
        }

        .input-with-icon {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 3rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            outline: none;
            transition: all 0.2s;
            font-size: 1rem;
            box-sizing: border-box;
        }

        .input-with-icon:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .input-with-icon.error {
            border-color: var(--error-color);
        }

        .input-with-icon.success {
            border-color: var(--success-color);
        }

        .toggle-password {
            position: absolute;
            right: 1rem;
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toggle-password:hover {
            color: var(--text-color);
        }

        .error-message {
            display: none;
            align-items: center;
            gap: 0.25rem;
            color: var(--error-color);
            font-size: 0.75rem;
            margin-top: 0.5rem;
        }

        .error-message.show {
            display: flex;
        }

        .btn-login {
            width: 100%;
            padding: 0.875rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 1rem;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .btn-login:hover {
            background: var(--primary-hover);
        }

        .btn-login.loading {
            opacity: 0.7;
            cursor: not-allowed;
            position: relative;
        }

        .btn-login.loading::after {
            content: "";
            position: absolute;
            width: 1.2rem;
            height: 1.2rem;
            border: 2px solid white;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .forgot-password {
            text-align: center;
            margin-top: 1.5rem;
        }

        .forgot-password a {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            transition: color 0.2s;
        }

        .forgot-password a:hover {
            color: var(--primary-color);
        }

        .alert {
            display: none;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }

        .alert.show {
            display: flex;
        }

        .alert-error {
            background: #fef2f2;
            color: var(--error-color);
            border: 1px solid #fee2e2;
        }

        .alert-success {
            background: #ecfdf5;
            color: var(--success-color);
            border: 1px solid #d1fae5;
        }

        /* SVG icons inside inputs / buttons */
        .input-icon, .toggle-password svg {
            pointer-events: none;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-header">
            <div class="admin-badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                </svg>
                Área Administrativa

            </div>
            <h1>Bem-vindo de volta</h1>

        </div>

        

        <form id="idformlogin" novalidate>
            <div class="form-group">
                <label for="emailuser">E-mail</label>
                <div class="input-wrapper">
                    <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                        <polyline points="22,6 12,13 2,6" />
                    </svg>
                    <input
                        type="email"
                        id="emailuser"
                        name="emailuser"
                        class="input-with-icon"
                        placeholder="admin@exemplo.com"
                        autocomplete="email">
                </div>
                <div class="error-message" id="emailError">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                    <span>Por favor, insira um e-mail válido</span>
                </div>
            </div>

            <div class="form-group">
                <label for="senhauser">Senha</label>
                <div class="input-wrapper password-wrapper">
                    <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                    <input
                        type="password"
                        id="senhauser"
                        name="senhauser"
                        class="input-with-icon"
                        placeholder="••••••••"
                        autocomplete="current-password">
                    <button type="button" class="toggle-password" id="togglePassword" aria-label="Mostrar senha">
                        <svg id="eyeIcon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <svg id="eyeOffIcon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                            <line x1="1" y1="1" x2="23" y2="23" />
                        </svg>
                    </button>
                </div>
                <div class="error-message" id="passwordError">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                    <span>Por favor, insira sua senha</span>
                </div>
            </div>

            <div id="alertMessage" class="alert">
            <svg class="icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                <polyline points="22 4 12 14.01 9 11.01" />
            </svg>
            <span id="alertText"></span>
        </div>

            <button type="submit" id="btn_loginAluno" class="btn-login">
                Acessar Sistema
            </button>
        </form>

        <div class="forgot-password">
            <a href="#" onclick="handleForgotPassword(event)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 7a3 3 0 1 0-6 0 3 3 0 0 0 6 0z" />
                    <path d="M12 14v7" />
                    <path d="M9 21h6" />
                </svg>
                Esqueci a senha
            </a>
        </div>
    </div>

    <script src="componentes/scripts/loginAdmin.js?<?= time() ?>"></script>
</body>
</html>

// Theme Toggle Logic
const toggleBtn = document.getElementById('theme-toggle');
const sunIcon = document.querySelector('.sun-icon');
const moonIcon = document.querySelector('.moon-icon');
const htmlElement = document.documentElement;

const savedTheme = localStorage.getItem('theme') || 'light';
htmlElement.setAttribute('data-bs-theme', savedTheme);
if (savedTheme === 'dark' && sunIcon && moonIcon) {
    sunIcon.classList.add('d-none');
    moonIcon.classList.remove('d-none');
}

if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
        const currentTheme = htmlElement.getAttribute('data-bs-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';

        htmlElement.setAttribute('data-bs-theme', newTheme);
        localStorage.setItem('theme', newTheme);

        if (newTheme === 'dark') {
            if (sunIcon) sunIcon.classList.add('d-none');
            if (moonIcon) moonIcon.classList.remove('d-none');
        } else {
            if (sunIcon) sunIcon.classList.remove('d-none');
            if (moonIcon) moonIcon.classList.add('d-none');
        }
    });
}

const form = document.getElementById('idformlogin');
const emailInput = document.getElementById('emailuser');
const passwordInput = document.getElementById('senhauser');
const togglePasswordBtn = document.getElementById('togglePassword');
const eyeIcon = document.getElementById('eyeIcon');
const eyeOffIcon = document.getElementById('eyeOffIcon');
const emailError = document.getElementById('emailError');
const passwordError = document.getElementById('passwordError');
const alertMessage = document.getElementById('alertMessage');
const alertText = document.getElementById('alertText');
const submitBtn = document.getElementById('btn_loginAluno');

// Toggle visibility da senha
togglePasswordBtn.addEventListener('click', function () {
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);

    if (type === 'text') {
        eyeIcon.style.display = 'none';
        eyeOffIcon.style.display = 'block';
        togglePasswordBtn.setAttribute('aria-label', 'Ocultar senha');
    } else {
        eyeIcon.style.display = 'block';
        eyeOffIcon.style.display = 'none';
        togglePasswordBtn.setAttribute('aria-label', 'Mostrar senha');
    }
});

// Validações
emailInput.addEventListener('blur', validateEmail);
emailInput.addEventListener('input', function () {
    if (this.classList.contains('error')) validateEmail();
});

passwordInput.addEventListener('blur', validatePassword);
passwordInput.addEventListener('input', function () {
    if (this.classList.contains('error')) validatePassword();
});

function validateEmail() {
    const email = emailInput.value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (email === '') {
        showError(emailInput, emailError, 'O campo e-mail é obrigatório');
        return false;
    } else if (!emailRegex.test(email)) {
        showError(emailInput, emailError, 'Por favor, insira um e-mail válido');
        return false;
    } else {
        showSuccess(emailInput, emailError);
        return true;
    }
}

function validatePassword() {
    const password = passwordInput.value;
    if (password === '') {
        showError(passwordInput, passwordError, 'O campo senha é obrigatório');
        return false;
    } else if (password.length < 6) {
        showError(passwordInput, passwordError, 'A senha deve ter pelo menos 6 caracteres');
        return false;
    } else {
        showSuccess(passwordInput, passwordError);
        return true;
    }
}

function showError(input, errorElement, message) {
    input.classList.remove('success');
    input.classList.add('error');
    errorElement.querySelector('span').textContent = message;
    errorElement.classList.add('show');
}

function showSuccess(input, errorElement) {
    input.classList.remove('error');
    input.classList.add('success');
    errorElement.classList.remove('show');
}

function clearValidation() {
    emailInput.classList.remove('error', 'success');
    passwordInput.classList.remove('error', 'success');
    emailError.classList.remove('show');
    passwordError.classList.remove('show');
}

function showAlert(message, type) {
    alertMessage.className = 'alert alert-' + type + ' show';
    alertText.textContent = message;

    setTimeout(() => {
        alertMessage.classList.remove('show');
    }, 6000);
}

function setLoading(isLoading) {
    if (isLoading) {
        submitBtn.classList.add('loading');
        submitBtn.textContent = '';
        submitBtn.disabled = true;
    } else {
        submitBtn.classList.remove('loading');
        submitBtn.textContent = 'Acessar Cursos';
        submitBtn.disabled = false;
    }
}

async function postAjax(payload) {
    const fd = new FormData();
    Object.keys(payload).forEach(k => fd.append(k, payload[k]));

    // Exemplo de endpoint (ajuste conforme a estrutura do servidor)
    const res = await fetch('/componentes/v1/ajax_loginUserServer.php', {
        method: 'POST',
        body: fd,
        credentials: 'same-origin'
    });

    const text = await res.text();
    try {
        return JSON.parse(text);
    } catch (e) {
        return {
            status: 'erro',
            msg: 'Resposta inválida do servidor.',
            debug: text.slice(0, 400)
        };
    }
}

// Submeter Formulário (LOGIN)
form.addEventListener('submit', async function (e) {
    e.preventDefault();
    clearValidation();

    const isEmailValid = validateEmail();
    const isPasswordValid = validatePassword();

    if (!isEmailValid || !isPasswordValid) {
        showAlert('Por favor, corrija os erros acima para continuar.', 'error');
        return;
    }

    setLoading(true);

    const json = await postAjax({
        acao: 'login_aluno',
        emailuser: emailInput.value.trim(),
        senhauser: passwordInput.value
    });

    setLoading(false);

    if (json.status === 'ok') {
        showAlert(json.msg || 'Acesso liberado! Redirecionando...', 'success');
        setTimeout(() => {
            window.location.href = json.redirect || 'aluno/';
        }, 900);
        return;
    }

    showAlert(json.msg || 'Não foi possível entrar. Verifique seus dados.', 'error');
});

// ESQUECI A SENHA
async function handleForgotPassword(e) {
    e.preventDefault();

    clearValidation();
    if (!validateEmail()) {
        showAlert('Informe seu e-mail para recuperar a senha.', 'error');
        return;
    }

    setLoading(true);

    const json = await postAjax({
        acao: 'forgot_aluno',
        emailuser: emailInput.value.trim()
    });

    setLoading(false);

    if (json.status === 'ok') {
        showAlert(json.msg || 'Se o e-mail existir, enviaremos o link de recuperação.', 'success');
    } else {
        showAlert(json.msg || 'Não foi possível enviar a recuperação.', 'error');
    }
}

window.handleForgotPassword = handleForgotPassword;

// Enter navega nos inputs
form.addEventListener('keypress', function (e) {
    if (e.key === 'Enter' && e.target.tagName !== 'BUTTON') {
        const inputs = Array.from(form.querySelectorAll('input'));
        const currentIndex = inputs.indexOf(e.target);
        if (currentIndex < inputs.length - 1) {
            inputs[currentIndex + 1].focus();
            e.preventDefault();
        }
    }
});
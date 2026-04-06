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

        // Toggle password visibility
        togglePasswordBtn.addEventListener('click', function() {
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

        // Real-time validation
        emailInput.addEventListener('blur', validateEmail);
        emailInput.addEventListener('input', function() {
            if (this.classList.contains('error')) validateEmail();
        });

        passwordInput.addEventListener('blur', validatePassword);
        passwordInput.addEventListener('input', function() {
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
                submitBtn.textContent = 'Acessar Sistema';
                submitBtn.disabled = false;
            }
        }

        async function postAjax(payload) {
            const fd = new FormData();
            Object.keys(payload).forEach(k => fd.append(k, payload[k]));

            const res = await fetch('/componentes/v1/ajax_loginAdminServer.php', {
                method: 'POST',
                body: fd,
                credentials: 'same-origin'
            });

            // Se o PHP quebrar e devolver HTML, isso evita crash do JS
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

        // Form submission (LOGIN)
        form.addEventListener('submit', async function(e) {
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
                acao: 'login',
                emailuser: emailInput.value.trim(),
                senhauser: passwordInput.value
            });

            setLoading(false);

            if (json.status === 'ok') {
                showAlert(json.msg || 'Acesso liberado! Redirecionando...', 'success');
                setTimeout(() => {
                    window.location.href = json.redirect || 'admin/';
                }, 900);
                return;
            }

            showAlert(json.msg || 'Não foi possível entrar. Verifique seus dados.', 'error');
        });

        // ESQUECI A SENHA (aproveita o seu link)
        async function handleForgotPassword(e) {
            e.preventDefault();

            // usa o mesmo input de email
            clearValidation();
            if (!validateEmail()) {
                showAlert('Informe seu e-mail para recuperar a senha.', 'error');
                return;
            }

            setLoading(true);

            const json = await postAjax({
                acao: 'forgot',
                emailuser: emailInput.value.trim()
            });

            setLoading(false);

            if (json.status === 'ok') {
                showAlert(json.msg || 'Se o e-mail existir, enviaremos o link de recuperação.', 'success');
            } else {
                showAlert(json.msg || 'Não foi possível enviar a recuperação.', 'error');
            }
        }

        // deixa acessível no onclick do seu <a>
        window.handleForgotPassword = handleForgotPassword;

        // UX: Enter navega inputs
        form.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && e.target.tagName !== 'BUTTON') {
                const inputs = Array.from(form.querySelectorAll('input'));
                const currentIndex = inputs.indexOf(e.target);
                if (currentIndex < inputs.length - 1) {
                    inputs[currentIndex + 1].focus();
                    e.preventDefault();
                }
            }
        });
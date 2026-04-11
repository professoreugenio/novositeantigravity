  // Init Welcome Modal
        window.addEventListener('load', () => {
            const welcomeModal = new bootstrap.Modal(document.getElementById('welcomeModal'));
            setTimeout(() => {
                welcomeModal.show();
            }, 500);
        });

        // Handle WhatsApp Form
        const waForm = document.getElementById('whatsapp-form');
        if (waForm) {
            waForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const name = document.getElementById('wa-name').value;
                const message = document.getElementById('wa-message').value;
                const currentUrl = window.location.href;
                
                const text = `Bom dia,%0A${message}%0A%0AAtt _${name}_%0A${currentUrl}`;
                const targetNumber = '5585996537577';
                window.open(`https://wa.me/${targetNumber}?text=${text}`, '_blank');
                
                const waModalEl = document.getElementById('whatsappModal');
                if (waModalEl) {
                    const waModal = bootstrap.Modal.getInstance(waModalEl);
                    if(waModal) waModal.hide();
                }
            });
        }

        // Theme Toggle Logic
        const toggleBtn = document.getElementById('theme-toggle');
        const sunIcon = document.querySelector('.sun-icon');
        const moonIcon = document.querySelector('.moon-icon');
        const htmlElement = document.documentElement;

        const savedTheme = localStorage.getItem('theme') || 'light';
        htmlElement.setAttribute('data-bs-theme', savedTheme);
        if(savedTheme === 'dark' && sunIcon && moonIcon) {
            sunIcon.classList.add('d-none');
            moonIcon.classList.remove('d-none');
        }

        if(toggleBtn) {
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

        // Social Share Links Logic
        function initSocialShare() {
            const currentUrl = encodeURIComponent(window.location.href);
            const pageTitle = encodeURIComponent(document.title);
            
            // Facebook
            document.querySelector('.share-facebook').addEventListener('click', () => {
                window.open(`https://www.facebook.com/sharer/sharer.php?u=${currentUrl}`, '_blank', 'width=600,height=400');
            });
            
            // Twitter / X
            document.querySelector('.share-twitter').addEventListener('click', () => {
                window.open(`https://twitter.com/intent/tweet?url=${currentUrl}&text=${pageTitle}`, '_blank', 'width=600,height=400');
            });
            
            // LinkedIn
            document.querySelector('.share-linkedin').addEventListener('click', () => {
                window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${currentUrl}`, '_blank', 'width=600,height=600');
            });
            
            // WhatsApp (Standard Share)
            document.querySelector('.share-whatsapp').addEventListener('click', () => {
                window.open(`https://api.whatsapp.com/send?text=${pageTitle}%20${currentUrl}`, '_blank');
            });
        }
        
        // Initialize shares on load
        window.addEventListener('load', initSocialShare);
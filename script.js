document.addEventListener('DOMContentLoaded', () => {

    /* -----------------------------------------------
       1. NAV ATIVO — marca o link da página atual
    ----------------------------------------------- */
    const navLinks = document.querySelectorAll('header nav a');
    const paginaAtual = window.location.pathname.split('/').pop() || 'index.html';

    navLinks.forEach(link => {
        link.classList.remove('ativo');
        if (link.getAttribute('href') === paginaAtual) {
            link.classList.add('ativo');
        }
    });

    /* -----------------------------------------------
       2. HEADER — scroll sombra dinâmica
    ----------------------------------------------- */
    const header = document.querySelector('header');
    if (header) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 20) {
                header.style.boxShadow = '0 4px 30px rgba(0,0,0,0.4)';
            } else {
                header.style.boxShadow = '0 2px 20px rgba(0,0,0,0.25)';
            }
        }, { passive: true });
    }

    /* -----------------------------------------------
       3. ANIMAÇÃO DE ENTRADA — fade + slide-up
    ----------------------------------------------- */
    const animarElementos = document.querySelectorAll(
        '.card, .passo, .banner-cta, section h1, section h2, section p, .lista-check'
    );

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, i) => {
                if (entry.isIntersecting) {
                    const delay = (i % 6) * 80;
                    entry.target.style.transitionDelay = `${delay}ms`;
                    entry.target.classList.add('visivel');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });

        animarElementos.forEach(el => {
            el.classList.add('fade-in');
            observer.observe(el);
        });
    } else {
        animarElementos.forEach(el => el.classList.add('visivel'));
    }

    /* -----------------------------------------------
       4. MENU MOBILE — hambúrguer
    ----------------------------------------------- */
    const nav = document.querySelector('header nav');
    if (header && nav) {
        const btnMenu = document.createElement('button');
        btnMenu.className = 'btn-menu';
        btnMenu.setAttribute('aria-label', 'Abrir menu');
        btnMenu.innerHTML = `
            <span></span>
            <span></span>
            <span></span>
        `;
        header.appendChild(btnMenu);

        btnMenu.addEventListener('click', () => {
            const aberto = nav.classList.toggle('nav-aberta');
            btnMenu.classList.toggle('ativo');
            btnMenu.setAttribute('aria-label', aberto ? 'Fechar menu' : 'Abrir menu');
        });

        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                nav.classList.remove('nav-aberta');
                btnMenu.classList.remove('ativo');
            });
        });

        document.addEventListener('click', (e) => {
            if (!header.contains(e.target)) {
                nav.classList.remove('nav-aberta');
                btnMenu.classList.remove('ativo');
            }
        });
    }

    /* -----------------------------------------------
       5. FORMULÁRIO — validação e feedback visual
    ----------------------------------------------- */
    const form = document.querySelector('form');
    if (form) {
        const camposObrigatorios = form.querySelectorAll('[required]');

        camposObrigatorios.forEach(campo => {
            campo.addEventListener('blur', () => validarCampo(campo));
            campo.addEventListener('input', () => {
                if (campo.classList.contains('erro')) validarCampo(campo);
            });
        });

        function validarCampo(campo) {
            const campoForm = campo.closest('.campo-form');
            removerErro(campoForm);

            if (!campo.value.trim()) {
                mostrarErro(campoForm, 'Este campo é obrigatório.');
                return false;
            }

            if (campo.type === 'email') {
                const emailValido = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(campo.value);
                if (!emailValido) {
                    mostrarErro(campoForm, 'Informe um e-mail válido.');
                    return false;
                }
            }

            campo.classList.remove('erro');
            campo.classList.add('valido');
            return true;
        }

        function mostrarErro(campoForm, msg) {
            const input = campoForm.querySelector('input, select, textarea');
            input.classList.add('erro');
            input.classList.remove('valido');

            let msgEl = campoForm.querySelector('.msg-erro');
            if (!msgEl) {
                msgEl = document.createElement('span');
                msgEl.className = 'msg-erro';
                campoForm.appendChild(msgEl);
            }
            msgEl.textContent = msg;
        }

        function removerErro(campoForm) {
            const msgEl = campoForm.querySelector('.msg-erro');
            if (msgEl) msgEl.remove();
        }

        form.addEventListener('submit', (e) => {
            e.preventDefault();

            let valido = true;
            camposObrigatorios.forEach(campo => {
                if (!validarCampo(campo)) valido = false;
            });

            if (valido) {
                const btnSubmit = form.querySelector('[type="submit"]');
                btnSubmit.disabled = true;
                btnSubmit.textContent = 'Enviando...';
                form.submit(); // Envia realmente para o PHP
            }
        });
    }

    /* -----------------------------------------------
       6. SMOOTH SCROLL — links internos com #
    ----------------------------------------------- */
    document.querySelectorAll('a[href^="#"]').forEach(link => {
        link.addEventListener('click', (e) => {
            const alvo = document.querySelector(link.getAttribute('href'));
            if (alvo) {
                e.preventDefault();
                alvo.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    /* -----------------------------------------------
       7. BOTÃO VOLTAR AO TOPO
    ----------------------------------------------- */
    const btnTopo = document.createElement('button');
    btnTopo.className = 'btn-topo';
    btnTopo.setAttribute('aria-label', 'Voltar ao topo');
    btnTopo.innerHTML = '↑';
    document.body.appendChild(btnTopo);

    window.addEventListener('scroll', () => {
        if (window.scrollY > 400) {
            btnTopo.classList.add('visivel');
        } else {
            btnTopo.classList.remove('visivel');
        }
    }, { passive: true });

    btnTopo.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    /* -----------------------------------------------
       8. ANO NO FOOTER
    ----------------------------------------------- */
    const spanAno = document.getElementById('ano');
    if (spanAno) spanAno.textContent = new Date().getFullYear();

    /* -----------------------------------------------
       9. STATUS DO FORMULÁRIO PHP
    ----------------------------------------------- */
    const params = new URLSearchParams(window.location.search);
    const status = params.get('status');
    const aviso = document.getElementById('aviso-form');

    if (aviso && status === 'sucesso') {
        aviso.innerHTML = `<span>✅</span><div><strong>Avaliação enviada com sucesso!</strong><p>Entraremos em contato em breve.</p></div>`;
        aviso.className = 'aviso-form aviso-sucesso';
        aviso.style.display = 'flex';
        aviso.scrollIntoView({ behavior: 'smooth', block: 'center' });
        window.history.replaceState({}, '', 'avaliacao.html');
    }

    if (aviso && status === 'erro') {
        aviso.innerHTML = `<span>⚠️</span><div><strong>Erro ao enviar.</strong><p>Tente novamente ou entre em contato pelo WhatsApp.</p></div>`;
        aviso.className = 'aviso-form aviso-erro';
        aviso.style.display = 'flex';
        aviso.scrollIntoView({ behavior: 'smooth', block: 'center' });
        window.history.replaceState({}, '', 'avaliacao.html');
    }

});

/* =============================================
   ESTILOS DINÂMICOS INJETADOS PELO JS
   ============================================= */
const estilosJS = document.createElement('style');
estilosJS.textContent = `

    /* --- Animações fade-in --- */
    .fade-in {
        opacity: 0;
        transform: translateY(24px);
        transition: opacity 0.55s ease, transform 0.55s ease;
    }
    .fade-in.visivel {
        opacity: 1;
        transform: translateY(0);
    }

    /* --- Menu hambúrguer --- */
    .btn-menu {
        display: none;
        flex-direction: column;
        justify-content: center;
        gap: 5px;
        background: none;
        border: none;
        cursor: pointer;
        padding: 6px;
        border-radius: 6px;
        transition: background 0.2s;
    }
    .btn-menu:hover { background: rgba(255,255,255,0.1); }
    .btn-menu span {
        display: block;
        width: 24px;
        height: 2px;
        background: #C9A0A8;
        border-radius: 2px;
        transition: all 0.3s ease;
    }
    .btn-menu.ativo span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
    .btn-menu.ativo span:nth-child(2) { opacity: 0; transform: scaleX(0); }
    .btn-menu.ativo span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

    @media (max-width: 768px) {
        .btn-menu { display: flex; }

        header nav {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #29030D;
            flex-direction: column;
            padding: 1rem;
            gap: 0.25rem;
            box-shadow: 0 8px 24px rgba(0,0,0,0.3);
            z-index: 99;
        }
        header nav.nav-aberta { display: flex; }
        header nav a { padding: 0.75rem 1rem; font-size: 1rem; }

        header { position: relative; flex-wrap: wrap; }
    }

    /* --- Validação de formulário --- */
    .campo-form input.erro,
    .campo-form select.erro,
    .campo-form textarea.erro {
        border-color: #EF4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
    }
    .campo-form input.valido,
    .campo-form select.valido,
    .campo-form textarea.valido {
        border-color: #10B981;
    }
    .msg-erro {
        font-size: 0.82rem;
        color: #EF4444;
        margin-top: 2px;
    }

    /* --- Botão voltar ao topo --- */
    .btn-topo {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        width: 44px;
        height: 44px;
        background: linear-gradient(135deg, #431222, #29030D);
        color: white;
        border: none;
        border-radius: 50%;
        font-size: 1.2rem;
        cursor: pointer;
        opacity: 0;
        pointer-events: none;
        transform: translateY(12px);
        transition: opacity 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
        box-shadow: 0 4px 16px rgba(67, 18, 34, 0.4);
        z-index: 200;
    }
    .btn-topo.visivel {
        opacity: 1;
        pointer-events: auto;
        transform: translateY(0);
    }
    .btn-topo:hover {
        box-shadow: 0 8px 24px rgba(67, 18, 34, 0.55);
        transform: translateY(-3px);
    }
`;
document.head.appendChild(estilosJS);

// =============================================
// VALIDAÇÃO EXTRA DO FORMULÁRIO
// =============================================

// Garante que o formulário de avaliação tenha validação robusta
const formAvaliacao = document.getElementById('form-avaliacao');

if (formAvaliacao) {
    // Remove qualquer event listener anterior
    const novoForm = formAvaliacao.cloneNode(true);
    formAvaliacao.parentNode.replaceChild(novoForm, formAvaliacao);

    novoForm.addEventListener('submit', function(e) {
        let valido = true;
        const camposObrigatorios = novoForm.querySelectorAll('[required]');

        camposObrigatorios.forEach(campo => {
            const campoForm = campo.closest('.campo-form');
            const msgErroAntiga = campoForm?.querySelector('.msg-erro');

            if (msgErroAntiga) {
                msgErroAntiga.remove();
            }

            campo.classList.remove('erro', 'valido');

            if (!campo.value.trim()) {
                valido = false;
                campo.classList.add('erro');

                const msgErro = document.createElement('span');
                msgErro.className = 'msg-erro';
                msgErro.textContent = 'Este campo é obrigatório.';
                campoForm?.appendChild(msgErro);
            } else if (campo.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(campo.value)) {
                valido = false;
                campo.classList.add('erro');

                const msgErro = document.createElement('span');
                msgErro.className = 'msg-erro';
                msgErro.textContent = 'Informe um e-mail válido.';
                campoForm?.appendChild(msgErro);
            } else {
                campo.classList.add('valido');
            }
        });

        if (!valido) {
            e.preventDefault();

            // Scroll para o primeiro erro
            const primeiroErro = novoForm.querySelector('.erro');
            if (primeiroErro) {
                primeiroErro.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        } else {
            const submitBtn = novoForm.querySelector('[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Enviando...';
            }
        }
    });
}
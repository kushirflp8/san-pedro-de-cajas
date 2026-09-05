// ====================================
// FUNCIONALIDAD DEL MENÚ MÓVIL
// ====================================
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menuToggle');
    const nav = document.getElementById('nav');

    // Alternar menú
    menuToggle.addEventListener('click', function() {
        nav.classList.toggle('active');
        menuToggle.classList.toggle('active');
    });

    // Cerrar menú al hacer clic en un enlace
    const navLinks = document.querySelectorAll('.nav a');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            nav.classList.remove('active');
            menuToggle.classList.remove('active');
        });
    });

    // Cerrar menú al hacer clic fuera
    document.addEventListener('click', function(event) {
        const isClickInsideNav = nav.contains(event.target);
        const isClickInsideToggle = menuToggle.contains(event.target);

        if (!isClickInsideNav && !isClickInsideToggle && nav.classList.contains('active')) {
            nav.classList.remove('active');
            menuToggle.classList.remove('active');
        }
    });
});

// ====================================
// EFECTOS DE SCROLL
// ====================================
window.addEventListener('scroll', function() {
    const header = document.querySelector('.header');
    if (window.scrollY > 50) {
        header.style.boxShadow = '0 8px 32px rgba(0, 0, 0, 0.2)';
    } else {
        header.style.boxShadow = '0 4px 16px rgba(0, 0, 0, 0.15)';
    }
});

// ====================================
// FORMULARIO DE CONTACTO
// ====================================
document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.querySelector('.contact-form');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Obtener valores del formulario
            const nombre = contactForm.querySelector('input[type="text"]').value;
            const email = contactForm.querySelector('input[type="email"]').value;
            const mensaje = contactForm.querySelector('textarea').value;

            // Validación básica
            if (nombre.trim() === '' || email.trim() === '' || mensaje.trim() === '') {
                alert('Por favor, completa todos los campos.');
                return;
            }

            // Validar email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Por favor, ingresa un email válido.');
                return;
            }

            // Simulación de envío (en producción, conectar con backend)
            console.log('Formulario enviado:', {
                nombre: nombre,
                email: email,
                mensaje: mensaje,
                fecha: new Date()
            });

            // Mostrar mensaje de éxito
            alert('¡Gracias por tu mensaje! Nos pondremos en contacto pronto.');

            // Limpiar formulario
            contactForm.reset();
        });
    }
});

// ====================================
// ANIMACIÓN DE CARGA DE ELEMENTOS
// ====================================
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -100px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

// Aplicar observador a elementos de galería y tarjetas
document.addEventListener('DOMContentLoaded', function() {
    const elements = document.querySelectorAll(
        '.gallery-item, .content-card, .landscape-item, .person-card'
    );

    elements.forEach(element => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(30px)';
        element.style.transition = 'all 0.6s ease-out';
        observer.observe(element);
    });
});

// ====================================
// REPRODUCTOR DE AUDIO - MEJORAS
// ====================================
document.addEventListener('DOMContentLoaded', function() {
    const audioPlayer = document.querySelector('audio');
    
    if (audioPlayer) {
        // Manejar errores de conexión
        audioPlayer.addEventListener('error', function(e) {
            console.error('Error al cargar el stream de audio:', e);
        });

        // Log cuando empieza a reproducir
        audioPlayer.addEventListener('play', function() {
            console.log('🎙️ Reproduciendo Radio San Pedro en Vivo');
        });

        // Log cuando se pausa
        audioPlayer.addEventListener('pause', function() {
            console.log('⏸️ Radio pausada');
        });
    }
});

// ====================================
// UTILIDADES
// ====================================

// Función para suavizar scroll hacia elementos
function smoothScroll(target) {
    const element = document.querySelector(target);
    if (element) {
        element.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
}

// Función para validar email
function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

// Función para detectar dispositivo móvil
function isMobileDevice() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

// ====================================
// INICIALIZACIÓN EN CARGA
// ====================================
window.addEventListener('load', function() {
    console.log('🏔️ San Pedro de Cajas - Página cargada exitosamente');
    console.log('Dispositivo móvil:', isMobileDevice());
    console.log('Resolución:', window.innerWidth + 'x' + window.innerHeight);
});
/**
 * global.js — Runs on ALL pages (not a module).
 * Handles: scroll-to-top button, nav active states on standalone pages.
 * NOTE: Mobile menu toggle is now handled in header.php inline script.
 */
document.addEventListener('DOMContentLoaded', () => {

    // ---- Scroll-to-Top Button ----
    const scrollBtn = document.createElement('button');
    scrollBtn.id = 'scroll-to-top';
    scrollBtn.setAttribute('aria-label', 'Scroll to top');
    scrollBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>`;
    Object.assign(scrollBtn.style, {
        position: 'fixed', bottom: '24px', right: '24px', zIndex: '998',
        width: '44px', height: '44px', borderRadius: '50%',
        background: 'linear-gradient(135deg, #E42278, #ED7BAB)',
        color: 'white', border: 'none', cursor: 'pointer',
        display: 'flex', alignItems: 'center', justifyContent: 'center',
        boxShadow: '0 8px 20px -4px rgba(228,34,120,0.4)',
        opacity: '0', transform: 'translateY(12px) scale(0.9)',
        transition: 'opacity 0.3s ease, transform 0.3s ease',
        pointerEvents: 'none',
    });
    document.body.appendChild(scrollBtn);

    window.addEventListener('scroll', () => {
        const show = window.scrollY > 400;
        scrollBtn.style.opacity = show ? '1' : '0';
        scrollBtn.style.transform = show ? 'translateY(0) scale(1)' : 'translateY(12px) scale(0.9)';
        scrollBtn.style.pointerEvents = show ? 'auto' : 'none';
    }, { passive: true });

    scrollBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

    // ---- Fix standalone page nav links ----
    // On non-index.php pages, ensure data-tab hrefs point to index.php
    const currentPage = window.location.pathname.split('/').pop();
    if (currentPage !== 'index.php' && currentPage !== '') {
        document.querySelectorAll('a[data-tab]').forEach(link => {
            if (!link.href.includes('index.php')) {
                const tab = link.getAttribute('data-tab');
                link.href = `index.php#${tab}`;
            }
        });
    }

    // ---- Highlight current page's nav link ----
    if (currentPage) {
        document.querySelectorAll('.nav-link').forEach(link => {
            const href = link.getAttribute('href') || '';
            if (href && !href.includes('#') && href.includes(currentPage)) {
                link.classList.add('active');
            }
        });
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const loginForm   = document.getElementById('login-form');
    const errorDiv    = document.getElementById('error-message');
    const errorText   = document.getElementById('error-text') || errorDiv;
    const submitBtn   = document.getElementById('submit-btn');
    const spinner     = document.getElementById('loading-spinner');
    const arrowIcon   = document.getElementById('arrow-icon');

    if (!loginForm) {
        console.error('Login form not found!');
        return;
    }

    function setLoading(loading) {
        if (spinner)  spinner.classList.toggle('hidden', !loading);
        if (arrowIcon) arrowIcon.classList.toggle('hidden', loading);
        if (submitBtn) {
            submitBtn.disabled = loading;
            submitBtn.classList.toggle('opacity-80', loading);
            submitBtn.classList.toggle('cursor-not-allowed', loading);
        }
    }

    function showError(msg) {
        if (!errorDiv) return;
        if (errorText && errorText !== errorDiv) errorText.textContent = msg;
        else errorDiv.textContent = msg;
        errorDiv.classList.remove('hidden');
    }

    function hideError() {
        if (errorDiv) errorDiv.classList.add('hidden');
    }

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        hideError();
        setLoading(true);

        const formData = new FormData(loginForm);
        formData.append('action', 'login');

        try {
            const response = await fetch('api.php', {
                method: 'POST',
                body: formData,
            });

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server returned an unexpected response. Please try again.');
            }

            const result = await response.json();

            if (result.success) {
                // Show success state briefly before redirect
                if (submitBtn) {
                    submitBtn.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>`;
                    submitBtn.style.background = 'linear-gradient(135deg, #10b981, #059669)';
                    submitBtn.style.boxShadow = '0 8px 20px -4px rgba(16,185,129,0.4)';
                }
                setTimeout(() => { window.location.href = 'index.php'; }, 500);
            } else {
                throw new Error(result.message || 'Invalid username or password.');
            }
        } catch (error) {
            showError(error.message);
            setLoading(false);
        }
    });
});

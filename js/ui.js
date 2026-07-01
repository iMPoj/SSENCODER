

export function showMessage(text, isError = false) {
    if (typeof window.Toastify === 'undefined') {
        console.error("Toastify is not loaded! Falling back to alert.");
        alert(text);
        return;
    }

    window.Toastify({
        text: text,
        duration: 3500,
        close: true,
        gravity: "top",
        position: "right",
        stopOnFocus: true,
        className: isError ? 'toastify-error' : 'toastify-success',
        style: {
            background: isError
                ? "linear-gradient(135deg, #ef4444 0%, #b91c1c 100%)"
                : "linear-gradient(135deg, #10b981 0%, #059669 100%)",
            borderRadius: "12px",
            padding: "12px 18px",
            fontFamily: "'Inter', sans-serif",
            fontWeight: "600",
            fontSize: "0.875rem",
            boxShadow: isError
                ? "0 8px 24px -4px rgba(239,68,68,0.4)"
                : "0 8px 24px -4px rgba(16,185,129,0.4)",
            maxWidth: "360px",
        }
    }).showToast();
}

export function showConfirmation(text, onConfirm) {
    const confirmModal = document.getElementById('confirmModal');
    const confirmText = document.getElementById('confirmModalText');
    const yesBtn = document.getElementById('confirmModalYesBtn');
    const noBtn = document.getElementById('confirmModalNoBtn');

    if (!confirmModal || !confirmText || !yesBtn || !noBtn) {
        if (confirm(text)) {
            if (typeof onConfirm === 'function') onConfirm();
        }
        return;
    }

    confirmText.textContent = text;

    const newYesBtn = yesBtn.cloneNode(true);
    const newNoBtn = noBtn.cloneNode(true);
    yesBtn.parentNode.replaceChild(newYesBtn, yesBtn);
    noBtn.parentNode.replaceChild(newNoBtn, noBtn);

    const close = () => {
        confirmModal.classList.add('closing');
        setTimeout(() => {
            confirmModal.classList.add('hidden');
            confirmModal.classList.remove('closing');
        }, 200);
    };

    newYesBtn.addEventListener('click', () => {
        if (typeof onConfirm === 'function') onConfirm();
        close();
    });

    newNoBtn.addEventListener('click', close);

    confirmModal.classList.remove('hidden');
}
export function showLoader() {
    return;
}

export function hideLoader() {
    return;
}
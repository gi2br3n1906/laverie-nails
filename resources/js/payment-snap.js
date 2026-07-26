const loadSnapScript = (url, clientKey) => new Promise((resolve, reject) => {
    if (window.snap) {
        resolve(window.snap);
        return;
    }

    const existingScript = document.querySelector(`script[src="${url}"]`);

    if (existingScript) {
        existingScript.addEventListener('load', () => resolve(window.snap), { once: true });
        existingScript.addEventListener('error', reject, { once: true });
        return;
    }

    const script = document.createElement('script');
    script.src = url;
    script.dataset.clientKey = clientKey;
    script.addEventListener('load', () => resolve(window.snap), { once: true });
    script.addEventListener('error', reject, { once: true });
    document.head.append(script);
});

const initPaymentSnap = (root) => {
    const payButton = root.querySelector('[data-pay-button]');
    const status = root.querySelector('[data-payment-status]');
    const { snapToken, snapUrl, clientKey } = root.dataset;

    if (!payButton || !status || !snapToken || !snapUrl || !clientKey) {
        return;
    }

    payButton.addEventListener('click', async () => {
        payButton.disabled = true;
        status.textContent = 'Menyiapkan pembayaran aman…';

        try {
            const snap = await loadSnapScript(snapUrl, clientKey);
            snap.pay(snapToken, {
                onSuccess: () => {
                    status.textContent = 'Pembayaran berhasil. Status pesanan akan diperbarui otomatis.';
                },
                onPending: () => {
                    status.textContent = 'Pembayaran menunggu penyelesaian.';
                },
                onError: () => {
                    status.textContent = 'Pembayaran belum berhasil. Silakan coba kembali.';
                    payButton.disabled = false;
                },
                onClose: () => {
                    status.textContent = 'Jendela pembayaran ditutup. Anda dapat melanjutkan kapan saja.';
                    payButton.disabled = false;
                },
            });
        } catch {
            status.textContent = 'Layanan pembayaran belum dapat dimuat. Silakan coba kembali.';
            payButton.disabled = false;
        }
    });
};

document.querySelectorAll('[data-payment-snap]').forEach(initPaymentSnap);
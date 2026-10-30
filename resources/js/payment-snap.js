import { t } from './i18n';

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
    const { snapToken, snapUrl, clientKey, paymentStatusUrl } = root.dataset;

    if (!payButton || !status || !snapToken || !snapUrl || !clientKey) {
        return;
    }

    let pollTimer = null;
    let paymentSucceeded = false;
    let redirecting = false;

    const setStatus = (key) => {
        status.textContent = t(key);
    };

    const redirectIfPaid = async () => {
        if (!paymentStatusUrl || redirecting) {
            return false;
        }

        try {
            const response = await fetch(paymentStatusUrl, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
                cache: 'no-store',
            });

            if (!response.ok) {
                return false;
            }

            const data = await response.json();

            if (data.paid && typeof data.redirect_url === 'string' && data.redirect_url !== '') {
                redirecting = true;
                window.location.assign(data.redirect_url);

                return true;
            }

            if (data.payment_status === 'failed' || data.payment_status === 'expired') {
                redirecting = true;
                window.location.reload();

                return true;
            }
        } catch {
            return false;
        }

        return false;
    };

    const pollStatus = (attempt = 0) => {
        window.clearTimeout(pollTimer);
        pollTimer = window.setTimeout(async () => {
            if (await redirectIfPaid()) {
                return;
            }

            if (attempt + 1 >= 15) {
                setStatus('js.payment.still_pending');

                return;
            }

            pollStatus(attempt + 1);
        }, 2000);
    };

    payButton.addEventListener('click', async () => {
        payButton.disabled = true;
        setStatus('js.payment.preparing');

        try {
            const snap = await loadSnapScript(snapUrl, clientKey);
            snap.pay(snapToken, {
                onSuccess: () => {
                    paymentSucceeded = true;
                    setStatus('js.payment.verifying');

                    void (async () => {
                        if (!(await redirectIfPaid())) {
                            pollStatus();
                        }
                    })();
                },
                onPending: () => {
                    setStatus('js.payment.pending');
                    payButton.disabled = false;
                },
                onError: () => {
                    setStatus('js.payment.error');
                    payButton.disabled = false;
                },
                onClose: () => {
                    if (paymentSucceeded || redirecting) {
                        return;
                    }

                    setStatus('js.payment.checking');

                    void (async () => {
                        if (await redirectIfPaid()) {
                            return;
                        }

                        setStatus('js.payment.closed');
                        payButton.disabled = false;
                    })();
                },
            });
        } catch {
            setStatus('js.payment.load_failed');
            payButton.disabled = false;
        }
    });

    if (root.dataset.paymentCheck === '1') {
        void (async () => {
            if (!(await redirectIfPaid())) {
                pollStatus();
            }
        })();
    }
};

document.querySelectorAll('[data-payment-snap]').forEach(initPaymentSnap);
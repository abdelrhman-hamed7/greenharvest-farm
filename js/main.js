document.addEventListener('DOMContentLoaded', function () {
    function getToastContainer() {
        var container = document.querySelector('.app-toast-container');

        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container app-toast-container';
            document.body.appendChild(container);
        }

        return container;
    }

    function showToast(type, message) {
        var container = getToastContainer();
        var toastElement = document.createElement('div');
        var safeType = ['success', 'danger', 'warning', 'info'].includes(type) ? type : 'success';

        toastElement.className = 'toast align-items-center text-bg-' + safeType + ' border-0';
        toastElement.setAttribute('role', 'alert');
        toastElement.setAttribute('aria-live', 'assertive');
        toastElement.setAttribute('aria-atomic', 'true');
        toastElement.setAttribute('data-bs-delay', '2800');

        var wrapper = document.createElement('div');
        wrapper.className = 'd-flex';

        var body = document.createElement('div');
        body.className = 'toast-body';

        var icon = document.createElement('i');
        icon.className = safeType === 'success' ? 'bi bi-check-circle me-2' : 'bi bi-info-circle me-2';

        var text = document.createTextNode(message);
        body.appendChild(icon);
        body.appendChild(text);

        var closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = 'btn-close btn-close-white me-2 m-auto';
        closeButton.setAttribute('data-bs-dismiss', 'toast');
        closeButton.setAttribute('aria-label', 'Close');

        wrapper.appendChild(body);
        wrapper.appendChild(closeButton);
        toastElement.appendChild(wrapper);
        container.appendChild(toastElement);

        var toast = bootstrap.Toast.getOrCreateInstance(toastElement);
        toast.show();

        toastElement.addEventListener('hidden.bs.toast', function () {
            toastElement.remove();
        });
    }

    var tooltipElements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipElements.forEach(function (element) {
        new bootstrap.Tooltip(element);
    });

    var alerts = document.querySelectorAll('.alert[data-auto-dismiss="true"]');
    alerts.forEach(function (alertElement) {
        setTimeout(function () {
            var alert = bootstrap.Alert.getOrCreateInstance(alertElement);
            alert.close();
        }, 3500);
    });

    var toastElements = document.querySelectorAll('.toast');
    toastElements.forEach(function (toastElement) {
        var toast = bootstrap.Toast.getOrCreateInstance(toastElement);
        toast.show();
    });

    var paymentRadios = document.querySelectorAll('input[name="payment_method"]');
    var mobileMoneyField = document.querySelector('.js-mobile-money-field');
    var paymentPhoneInput = document.querySelector('#payment_phone');
    var selectedPaymentLabel = document.querySelector('.js-selected-payment-label');

    function updatePaymentMethodUi() {
        var selected = document.querySelector('input[name="payment_method"]:checked');
        if (!selected) {
            return;
        }

        var isCash = selected.value === 'Cash on Delivery';

        if (mobileMoneyField) {
            mobileMoneyField.classList.toggle('d-none', isCash);
        }

        if (paymentPhoneInput) {
            paymentPhoneInput.required = !isCash;
        }

        if (selectedPaymentLabel) {
            selectedPaymentLabel.textContent = selected.dataset.paymentLabel || selected.value;
        }
    }

    paymentRadios.forEach(function (radio) {
        radio.addEventListener('change', updatePaymentMethodUi);
    });
    updatePaymentMethodUi();

    var addToCartForms = document.querySelectorAll('.js-add-to-cart-form');
    addToCartForms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            event.stopPropagation();

            var submitButton = form.querySelector('button[type="submit"]');
            var originalButtonHtml = submitButton ? submitButton.innerHTML : '';
            var formData = new FormData(form);

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Adding';
            }

            var requestUrl = new URL(form.getAttribute('action'), window.location.href);

            fetch(requestUrl.href, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Server returned status ' + response.status + '.');
                    }

                    return response.text();
                })
                .then(function (text) {
                    try {
                        return JSON.parse(text);
                    } catch (error) {
                        throw new Error('The server did not return JSON. Open the project using PHP server, not Live Server.');
                    }
                })
                .then(function (data) {
                    if (!data.success) {
                        showToast(data.type, data.message);
                        return;
                    }

                    document.querySelectorAll('.js-cart-count').forEach(function (badge) {
                        badge.textContent = data.cart_count;
                    });

                    showToast(data.type, data.message);
                })
                .catch(function (error) {
                    var openedFromLivePreview = window.location.port === '3000' || window.location.pathname.includes('/Users/');
                    var message = openedFromLivePreview
                        ? 'Open the site with PHP server: http://localhost:8000/products.php'
                        : error.message;

                    showToast('danger', message);
                })
                .finally(function () {
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalButtonHtml;
                    }
                });
        });
    });

    var clickableCards = document.querySelectorAll('.clickable-card[data-href]');
    clickableCards.forEach(function (card) {
        card.addEventListener('click', function (event) {
            if (event.target.closest('a, button, input, select, textarea, form, .card-action')) {
                return;
            }

            window.location.href = card.dataset.href;
        });

        card.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                window.location.href = card.dataset.href;
            }
        });
    });
});

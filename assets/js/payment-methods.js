(function () {
    'use strict';

    function initPaymentMethods() {
        const mpesaRadio = document.getElementById('mpesa-radio');
        const paypalRadio = document.getElementById('paypal-radio');
        const freeRadio = document.getElementById('free-radio');
        const mpesaInstructions = document.getElementById('mpesa-instructions');
        const paypalContainer = document.getElementById('paypal-button-container');
        const placeOrderBtn = document.getElementById('place-order-btn');

        if (!mpesaRadio || !paypalRadio || !freeRadio) {
            return;
        }

        function updateDisplay() {
            // Hide all first
            if (mpesaInstructions) mpesaInstructions.style.display = 'none';
            if (paypalContainer) paypalContainer.style.display = 'none';
            if (placeOrderBtn) placeOrderBtn.style.display = 'block';

            // Remove all active classes
            document.querySelectorAll('.payment-method-card').forEach(card => {
                card.classList.remove('active');
            });

            // Show the selected one
            if (mpesaRadio.checked) {
                if (mpesaInstructions) mpesaInstructions.style.display = 'block';
                const mpesaCard = document.getElementById('card-mpesa');
                if (mpesaCard) mpesaCard.classList.add('active');
            } else if (paypalRadio.checked) {
                if (paypalContainer) paypalContainer.style.display = 'block';
                if (placeOrderBtn) placeOrderBtn.style.display = 'none';
                const paypalCard = document.getElementById('card-paypal');
                if (paypalCard) paypalCard.classList.add('active');
                renderPayPalButtons();
            } else if (freeRadio.checked) {
                const freeCard = document.getElementById('card-free');
                if (freeCard) freeCard.classList.add('active');
            }
        }

        // Attach change listeners to radio buttons
        [mpesaRadio, paypalRadio, freeRadio].forEach(radio => {
            radio.addEventListener('change', updateDisplay);
        });

        // Attach click listeners to cards
        document.querySelectorAll('.payment-method-card').forEach(card => {
            card.addEventListener('click', function (e) {
                e.preventDefault();
                const radio = this.querySelector('input[type="radio"]');
                if (radio && !radio.checked) {
                    radio.checked = true;
                    updateDisplay();
                }
            });
        });

        // PayPal integration
        let paypalRendered = false;
        window.renderPayPalButtons = function () {
            if (paypalRendered || typeof paypal === 'undefined') return;

            const cartTotal = parseFloat(document.getElementById('paypal-amount')?.value || 0);

            paypal.Buttons({
                createOrder: function (data, actions) {
                    return actions.order.create({
                        purchase_units: [{
                            amount: {
                                value: (cartTotal / 129).toFixed(2)
                            }
                        }]
                    });
                },
                onApprove: function (data, actions) {
                    return actions.order.capture().then(function (details) {
                        const form = document.getElementById('checkoutForm');
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'payment_method';
                        hiddenInput.value = 'paypal';
                        form.appendChild(hiddenInput);
                        form.submit();
                    });
                }
            }).render('#paypal-button-container');

            paypalRendered = true;
        };

        // Initial display
        updateDisplay();
    }

    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPaymentMethods);
    } else {
        initPaymentMethods();
    }
})();

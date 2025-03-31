// Payment handler class
class PaymentHandler {
    constructor() {
        this.payments = [];
        this.totalAmount = 0;
        this.paidAmount = 0;
        this.transportFee = 0;
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Listen for payment type changes
        document.getElementById('payment_type').addEventListener('change', (e) => {
            this.handlePaymentTypeChange(e.target.value);
        });

        // Listen for transport fee changes
        document.getElementById('transport_fee').addEventListener('input', (e) => {
            this.updateTransportFee(parseFloat(e.target.value) || 0);
        });
    }

    handlePaymentTypeChange(paymentType) {
        const cardDetailsContainer = document.getElementById('card_details_container');
        if (paymentType === 'card_payment') {
            cardDetailsContainer.style.display = 'flex';
        } else {
            cardDetailsContainer.style.display = 'none';
        }
    }

    updateTransportFee(fee) {
        this.transportFee = fee;
        this.recalculateNetAmount();
    }

    recalculateNetAmount() {
        const totalValue = parseFloat(document.getElementById('total_amount2').value) || 0;
        const netAmount = totalValue + this.transportFee;
        
        document.getElementById('net_amount').value = netAmount.toFixed(2);
        document.getElementById('total_amount').value = netAmount.toFixed(2);
        this.updateBalance();
    }

    addPayment(paymentDetails) {
        this.payments.push(paymentDetails);
        this.updateTotalPaid();
        this.updatePaymentDisplay();
    }

    updateTotalPaid() {
        this.paidAmount = this.payments.reduce((total, payment) => total + parseFloat(payment.amount), 0);
        this.updateBalance();
    }

    updateBalance() {
        const netAmount = parseFloat(document.getElementById('net_amount').value) || 0;
        let due = netAmount - this.paidAmount;
        
        // Ensure due is never negative
        due = Math.max(0, due);
        
        const balance = this.paidAmount - netAmount;
        
        document.getElementById('payment_due').value = due.toFixed(2);
        document.getElementById('balance').value = balance.toFixed(2);

        // Update deduction alert if exists
        const deductionAlert = document.getElementById('deduction-alert');
        if (deductionAlert) {
            deductionAlert.textContent = `-${this.paidAmount.toFixed(2)}`;
        }
    }

    updatePaymentDisplay() {
        const container = document.getElementById('payments_list');
        container.innerHTML = '';
        
        this.payments.forEach((payment, index) => {
            const paymentElement = document.createElement('div');
            paymentElement.className = 'payment-item';
            paymentElement.innerHTML = `
                <span>${payment.type} - LKR ${payment.amount}</span>
                ${payment.cardDetails ? ` (Card: ${payment.cardDetails.lastFourDigits} - ${payment.cardDetails.cardType})` : ''}
                <button onclick="paymentHandler.removePayment(${index})" class="remove-payment-btn">×</button>
            `;
            container.appendChild(paymentElement);
        });
    }

    removePayment(index) {
        this.payments.splice(index, 1);
        this.updateTotalPaid();
        this.updatePaymentDisplay();
    }

    validateCardDetails(lastFourDigits, cardType) {
        return /^\d{4}$/.test(lastFourDigits) && ['VISA', 'MASTER'].includes(cardType);
    }

    // getPaymentsData() {
    //     return {
    //         payments: this.payments,
    //         transportFee: this.transportFee,
    //         totalPaid: this.paidAmount
    //     };
    // }

    getPaymentsData() {
        return this.payments;
    }
}
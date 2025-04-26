document.addEventListener('DOMContentLoaded', function() {
    // Update room number when user is selected
    document.getElementById('userSelect').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const roomNumber = selectedOption.getAttribute('data-room') || 'N/A';
        document.getElementById('roomSelect').value = roomNumber;
    });
    
    // Handle product selection
    document.querySelectorAll('.product-item').forEach(item => {
        const minusBtn = item.querySelector('.minus-btn');
        const plusBtn = item.querySelector('.plus-btn');
        const quantityInput = item.querySelector('.quantity-input');
        const noteInput = item.querySelector('.note-input');
        
        minusBtn.addEventListener('click', () => {
            const currentVal = parseInt(quantityInput.value);
            if (currentVal > 0) {
                quantityInput.value = currentVal - 1;
                updateOrderSummary();
            }
        });
        
        plusBtn.addEventListener('click', () => {
            quantityInput.value = parseInt(quantityInput.value) + 1;
            updateOrderSummary();
        });
        
        quantityInput.addEventListener('change', function() {
            this.value = Math.max(0, parseInt(this.value) || 0);
            updateOrderSummary();
        });
    });
    
    // Update order summary
    function updateOrderSummary() {
        const orderItemsContainer = document.getElementById('orderItems');
        const totalAmountElement = document.getElementById('totalAmount');
        let total = 0;
        let itemsHTML = '';
        let itemsData = [];
        
        document.querySelectorAll('.product-item').forEach(item => {
            const productId = item.dataset.id;
            const productName = item.querySelector('.card-title').textContent;
            const productPrice = parseFloat(item.dataset.price);
            const quantity = parseInt(item.querySelector('.quantity-input').value) || 0;
            const note = item.querySelector('.note-input').value;
            
            if (quantity > 0) {
                const itemTotal = productPrice * quantity;
                total += itemTotal;
                
                itemsHTML += `
                    <div class="d-flex justify-content-between mb-2">
                        <span>${productName} x${quantity}</span>
                        <span>${itemTotal.toFixed(2)} EGP</span>
                    </div>
                    ${note ? `<div class="small text-muted">Note: ${note}</div>` : ''}
                `;
                
                itemsData.push({
                    product_id: productId,
                    quantity: quantity,
                    price: productPrice,
                    note: note
                });
            }
        });
        
        orderItemsContainer.innerHTML = itemsHTML || '<p class="text-muted text-center">No items selected yet</p>';
        totalAmountElement.textContent = total.toFixed(2) + ' EGP';
        
        // Add hidden input with items data
        let itemsInput = document.querySelector('input[name="items"]');
        if (!itemsInput) {
            itemsInput = document.createElement('input');
            itemsInput.type = 'hidden';
            itemsInput.name = 'items';
            document.getElementById('orderForm').appendChild(itemsInput);
        }
        itemsInput.value = JSON.stringify(itemsData);
    }
    
    // Initialize order summary
    updateOrderSummary();
});
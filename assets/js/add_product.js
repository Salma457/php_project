document.addEventListener('DOMContentLoaded', function() {
    // Image preview functionality
    const productImage = document.getElementById('productImage');
    const imagePreview = document.getElementById('imagePreview');
    
    productImage.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                imagePreview.style.display = 'block';
                imagePreview.src = e.target.result;
            }
            
            reader.readAsDataURL(this.files[0]);
        } else {
            imagePreview.style.display = 'none';
            imagePreview.src = '#';
        }
    });
    
    // Add new category functionality
    const saveCategoryBtn = document.getElementById('saveCategoryBtn');
    const categoryNameInput = document.getElementById('categoryName');
    const categorySelect = document.getElementById('productCategory');
    
    saveCategoryBtn.addEventListener('click', function() {
        const categoryName = categoryNameInput.value.trim();
        
        if (!categoryName) {
            alert('Please enter a category name');
            return;
        }
        
        // Send AJAX request to add category
        fetch('add_category.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `name=${encodeURIComponent(categoryName)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Add new category to select
                const option = document.createElement('option');
                option.value = data.id;
                option.textContent = categoryName;
                option.selected = true;
                categorySelect.appendChild(option);
                
                // Reset form and close modal
                document.getElementById('categoryForm').reset();
                bootstrap.Modal.getInstance(document.getElementById('addCategoryModal')).hide();
            } else {
                alert('Error: ' + (data.message || 'Failed to add category'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while adding the category');
        });
    });
    
    // Form validation
    const productForm = document.getElementById('productForm');
    
    productForm.addEventListener('submit', function(e) {
        const price = parseFloat(document.getElementById('productPrice').value);
        
        if (price <= 0) {
            e.preventDefault();
            alert('Price must be greater than 0');
            return false;
        }
        
        return true;
    });
});
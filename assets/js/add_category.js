document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const categoryForm = document.getElementById('categoryForm');
    
    categoryForm.addEventListener('submit', function(e) {
        const categoryName = document.getElementById('categoryName').value.trim();
        
        if (!categoryName) {
            e.preventDefault();
            alert('Please enter a category name');
            return false;
        }
        
        // Check for minimum length
        if (categoryName.length < 2) {
            e.preventDefault();
            alert('Category name must be at least 2 characters');
            return false;
        }
        
        return true;
    });
    
    // Confirmation for delete actions
    const deleteButtons = document.querySelectorAll('.btn-outline-danger');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this category?')) {
                e.preventDefault();
            }
        });
    });
});
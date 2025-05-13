 
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.querySelector('#search-form');
    const searchInput = document.querySelector('#search-input');
    const resultsContainer = document.querySelector('#search-results');
    
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const query = searchInput.value.trim();
            
            if (query.length > 2) {
                fetch(`/products/search.php?q=${encodeURIComponent(query)}`)
                    .then(response => response.text())
                    .then(html => {
                        resultsContainer.innerHTML = html;
                    });
            }
        });
    }
    
    // Filtres dynamiques
    document.querySelectorAll('.filter-input').forEach(input => {
        input.addEventListener('change', function() {
            document.querySelector('#filters-form').submit();
        });
    });
});
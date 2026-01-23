/**
 * POS Live Search
 * Real-time product search without page reload
 */

(function() {
    'use strict';

    const searchInput = document.getElementById('live-search-input');
    const searchResults = document.getElementById('search-results-container');
    const loadingIndicator = document.getElementById('search-loading');
    let searchTimeout = null;

    if (!searchInput || !searchResults) {
        console.warn('Live search elements not found');
        return;
    }

    // Debounced search function
    function performSearch(query) {
        // Clear previous timeout
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        // Show loading
        if (loadingIndicator) {
            loadingIndicator.style.display = 'block';
        }

        // Debounce search
        searchTimeout = setTimeout(() => {
            if (query.length === 0) {
                searchResults.innerHTML = '';
                if (loadingIndicator) {
                    loadingIndicator.style.display = 'none';
                }
                return;
            }

            // Perform AJAX search
            fetch(`/pos/search?q=${encodeURIComponent(query)}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (loadingIndicator) {
                    loadingIndicator.style.display = 'none';
                }

                if (data.success) {
                    renderSearchResults(data.products);
                }
            })
            .catch(error => {
                console.error('Search error:', error);
                if (loadingIndicator) {
                    loadingIndicator.style.display = 'none';
                }
                searchResults.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center text-danger">
                            Terjadi kesalahan saat mencari produk.
                        </td>
                    </tr>
                `;
            });
        }, 300); // 300ms debounce
    }

    // Render search results
    function renderSearchResults(products) {
        if (products.length === 0) {
            searchResults.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        Tidak ada produk yang ditemukan.
                    </td>
                </tr>
            `;
            return;
        }

        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenMeta ? csrfTokenMeta.content : '';

        if (!csrfToken) {
            console.error('CSRF token not found');
        }

        let html = '';
        products.forEach((product, index) => {
            const imageUrl = product.product_image 
                ? `/storage/products/${product.product_image}` 
                : '/assets/images/product/default.webp';

            html += `
                <tr>
                    <td>${index + 1}</td>
                    <td>
                        <img class="avatar-60 rounded" src="${imageUrl}" alt="${product.product_name}">
                    </td>
                    <td>${product.product_name}</td>
                    <td>${product.selling_price}</td>
                    <td>
                        <form action="/pos/add" method="POST" style="margin-bottom: 5px">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <input type="hidden" name="id" value="${product.id}">
                            <input type="hidden" name="name" value="${product.product_name}">
                            <input type="hidden" name="price" value="${product.selling_price}">
                            <button type="submit" class="btn btn-primary border-none" data-toggle="tooltip" data-placement="top" title="Tambah">
                                <i class="far fa-plus mr-0"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            `;
        });

        searchResults.innerHTML = html;
        
        // Re-initialize tooltips
        if (typeof $ !== 'undefined' && $.fn.tooltip) {
            $('[data-toggle="tooltip"]').tooltip();
        }
    }

    // Event listener for search input
    searchInput.addEventListener('input', function(e) {
        const query = e.target.value.trim();
        performSearch(query);
    });

    // Clear search button
    const clearSearchBtn = document.getElementById('clear-search-btn');
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function(e) {
            e.preventDefault();
            searchInput.value = '';
            searchResults.innerHTML = '';
            searchInput.focus();
        });
    }
})();

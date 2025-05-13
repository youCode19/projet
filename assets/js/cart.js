 
document.addEventListener('DOMContentLoaded', function() {
    // Ajout au panier
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const form = this.closest('form');
            const formData = new FormData(form);
            
            fetch('/cart/add.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mise à jour du compteur
                    updateCartCount(data.cart_count);
                    
                    // Animation de confirmation
                    const btn = this;
                    const originalText = btn.innerHTML;
                    btn.innerHTML = '<i class="bi bi-check"></i> Ajouté';
                    btn.classList.add('btn-success');
                    
                    setTimeout(() => {
                        btn.innerHTML = originalText;
                        btn.classList.remove('btn-success');
                    }, 2000);
                } else {
                    alert('Erreur: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
    
    // Mise à jour des quantités dans le panier
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            const productId = this.dataset.id;
            const quantity = this.value;
            
            fetch('/cart/update.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCartCount(data.cart_count);
                    // Mettre à jour les totaux
                    document.querySelectorAll('.item-total').forEach(el => {
                        if (el.dataset.id === productId) {
                            el.textContent = data.item_total + ' €';
                        }
                    });
                    document.getElementById('cart-total').textContent = data.cart_total + ' €';
                }
            });
        });
    });
    
    // Suppression d'un article
    document.querySelectorAll('.remove-item').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (confirm('Supprimer cet article du panier ?')) {
                const productId = this.dataset.id;
                
                fetch('/cart/remove.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `product_id=${productId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateCartCount(data.cart_count);
                        // Supprimer la ligne du tableau
                        this.closest('tr').remove();
                        // Mettre à jour le total
                        document.getElementById('cart-total').textContent = data.cart_total + ' €';
                    }
                });
            }
        });
    });
    
    function updateCartCount(count) {
        document.querySelectorAll('.cart-count').forEach(el => {
            el.textContent = count;
        });
        document.dispatchEvent(new CustomEvent('cartUpdated', { detail: { count } }));
    }
});
document.addEventListener('DOMContentLoaded', function () {
    // Support Widget Toggle
    const supportBubble = document.querySelector('.support-bubble');
    const supportContent = document.querySelector('.support-content');
    const closeSupport = document.querySelector('.close-support');

    if (supportBubble && supportContent) {
        supportBubble.addEventListener('click', () => {
            supportContent.classList.toggle('active');
        });

        closeSupport.addEventListener('click', () => {
            supportContent.classList.remove('active');
        });
    }

    // Update Cart Count
    updateCartCount();
});

function updateCartCount() {
    // In a real app, this would fetch from an API or check a cookie/local storage
    // For this PHP session-based cart, we might need to inject the count via PHP or fetch it
    // For now, let's assume PHP renders the initial count, and AJAX updates it
}

function addToCart(productId) {
    // Simple AJAX add to cart
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('product_id', productId);
    formData.append('quantity', 1);

    fetch(BASE_URL + 'cart.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // alert('Added to cart!'); // Optional: Remove alert for smoother experience
                const badge = document.getElementById('cart-count');
                if (badge) badge.innerText = data.count;

                // Show a toast or small notification instead of alert if possible
                // For now, just update the badge
            } else {
                alert('Error adding to cart');
            }
        })
        .catch(error => console.error('Error:', error));
}

function toggleWishlist(id, btn) {
    const formData = new FormData();
    formData.append('product_id', id);
    fetch(BASE_URL + 'wishlist_ajax.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (data.action === 'added') btn.style.color = '#e10600';
                else btn.style.color = '#fff';
            } else {
                if (data.message === 'Login required') window.location.href = 'auth/login.php';
                else alert(data.message);
            }
        })
        .catch(e => console.error(e));
}

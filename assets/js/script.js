// ================================================
// TechElectronics - Custom JavaScript
// ================================================

// ================================================
// Notification System (Modern Toast) - GLOBAL
// ================================================
function showNotification(message, type = 'success') {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.toast-notification');
    existingToasts.forEach(t => t.remove());

    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;

    // Icons
    let icon = '';
    if (type === 'success') {
        icon = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17 8.5L10.5 15L7 11.5" stroke="black" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="11" stroke="black" stroke-width="2"/></svg>';
    } else if (type === 'error') {
        icon = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15 9L9 15M9 9L15 15" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="11" stroke="white" stroke-width="2"/></svg>';
    } else {
        icon = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 7V13M12 17H12.01" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="11" stroke="white" stroke-width="2"/></svg>';
    }

    toast.innerHTML = `
        <span class="content-wrapper">
            ${icon} 
            ${message}
        </span>
        <button type="button" class="close-btn" onclick="this.parentElement.remove()">&times;</button>
    `;

    document.body.appendChild(toast);

    // Trigger animation
    setTimeout(() => toast.classList.add('show'), 10);

    // Auto remove
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
    }, 4000);
}

// Make it globally available immediately
window.showNotification = showNotification;

document.addEventListener('DOMContentLoaded', function () {
    // Order placed toast
    window.showOrderToast = function () {
        var toast = document.getElementById('order-toast');
        if (toast) {
            toast.style.display = 'block';
            setTimeout(function () { toast.style.display = 'none'; }, 3000);
        }
    }

    // ================================================
    // Toast Auto-hide
    // ================================================
    const toasts = document.querySelectorAll('.toast');
    toasts.forEach(toast => {
        setTimeout(() => {
            const bsToast = bootstrap.Toast.getOrCreateInstance(toast);
            bsToast.hide();
        }, 3000); // Hide after 3 seconds
    });

    // ================================================
    // Smooth Scrolling for Anchor Links
    // ================================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href === '#') return; // For scroll-to-top handle separately or ignore

            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // ================================================
    // Scroll to Top Button
    // ================================================
    const scrollTopBtn = document.getElementById('back-to-top-pk');
    if (scrollTopBtn) {
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                scrollTopBtn.classList.add('visible');
            } else {
                scrollTopBtn.classList.remove('visible');
            }
        });

        scrollTopBtn.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // ================================================
    // Testimonials Slider/Carousel
    // ================================================
    const testimonialSlider = document.querySelector('.testimonial-slider');
    if (testimonialSlider) {
        let currentSlide = 0;
        const slides = testimonialSlider.querySelectorAll('.testimonial-slide');
        const totalSlides = slides.length;

        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.style.display = i === index ? 'block' : 'none';
            });
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % totalSlides;
            showSlide(currentSlide);
        }

        function prevSlide() {
            currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
            showSlide(currentSlide);
        }

        // Show first slide
        showSlide(0);

        // Auto-advance every 5 seconds
        setInterval(nextSlide, 5000);

        // Next/Prev buttons
        const nextBtn = document.querySelector('.testimonial-next');
        const prevBtn = document.querySelector('.testimonial-prev');

        if (nextBtn) nextBtn.addEventListener('click', nextSlide);
        if (prevBtn) prevBtn.addEventListener('click', prevSlide);
    }

    // ================================================
    // Cart Functionality
    // ================================================

    // Add to favorites buttons
    document.querySelectorAll('.btn-add-favorite').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');

            fetch(`favorites_handler.php?action=add&id=${productId}&ajax=1`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        updateFavoritesCount(data.fav_count);
                    } else {
                        showNotification(data.message || 'Failed to add to favorites', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred', 'error');
                });
        });
    });

    // Add to cart buttons (Shop Page - only those with data-product-id attribute)
    document.querySelectorAll('.btn-add-to-cart[data-product-id]').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');
            const productName = this.getAttribute('data-product-name');

            // Send AJAX request to add to cart
            fetch('cart_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=add&product_id=${productId}&csrf_token=${window.csrfToken || ''}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(`${productName} added to cart!`, 'success');
                        updateCartCount(data.cart_count);
                    } else {
                        showNotification(data.message || 'Failed to add to cart', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred', 'error');
                });
        });
    });

    // Add to cart form (Product Details Page)
    const addToCartForm = document.getElementById('addToCartForm');
    if (addToCartForm) {
        addToCartForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            // Convert FormData to URLSearchParams for x-www-form-urlencoded
            const params = new URLSearchParams(formData);

            fetch('cart_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: params.toString()
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Product added to cart!', 'success');
                        updateCartCount(data.cart_count);
                    } else {
                        showNotification(data.message || 'Failed to add to cart', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred. Please try again.', 'error');
                });
        });
    }

    // Update quantity in cart
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function () {
            const productId = this.getAttribute('data-product-id');
            const quantity = this.value;

            if (quantity < 1) {
                this.value = 1;
                return;
            }

            fetch('cart_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update&product_id=${productId}&quantity=${quantity}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload(); // Reload to update totals
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    });

    // Remove from cart
    document.querySelectorAll('.btn-remove-cart').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');

            if (confirm('Remove this item from cart?')) {
                fetch('cart_action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=remove&product_id=${productId}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        });
    });

    // Quantity increase/decrease buttons
    document.querySelectorAll('.quantity-increase').forEach(button => {
        button.addEventListener('click', function () {
            const productId = this.getAttribute('data-product-id');
            const input = document.querySelector(`.quantity-input[data-product-id="${productId}"]`);
            const newQuantity = parseInt(input.value) + 1;
            updateCartQuantity(productId, newQuantity);
        });
    });

    document.querySelectorAll('.quantity-decrease').forEach(button => {
        button.addEventListener('click', function () {
            const productId = this.getAttribute('data-product-id');
            const input = document.querySelector(`.quantity-input[data-product-id="${productId}"]`);
            const currentQuantity = parseInt(input.value);
            if (currentQuantity > 1) {
                const newQuantity = currentQuantity - 1;
                updateCartQuantity(productId, newQuantity);
            }
        });
    });

    function updateCartQuantity(productId, quantity) {
        fetch('cart_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update&product_id=${productId}&quantity=${quantity}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // Clear cart button
    const clearCartBtn = document.getElementById('clear-cart-btn');
    if (clearCartBtn) {
        clearCartBtn.addEventListener('click', function () {
            if (confirm('Are you sure you want to clear your entire cart?')) {
                fetch('cart_action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=clear'
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        });
    }

    // ================================================
    // Checkout Validation
    // ================================================
    window.proceedToCheckout = function () {
        const shippingLocation = document.getElementById('shipping-location');
        const locationError = document.getElementById('location-error');

        if (!shippingLocation.value) {
            locationError.style.display = 'block';
            shippingLocation.focus();
            return;
        }

        locationError.style.display = 'none';
        window.location.href = 'checkout.php?location=' + shippingLocation.value;
    };

    // Enable/disable checkout button based on location selection
    const shippingLocation = document.getElementById('shipping-location');
    if (shippingLocation) {
        shippingLocation.addEventListener('change', function () {
            const locationError = document.getElementById('location-error');
            if (this.value) {
                locationError.style.display = 'none';
            }

            // Update shipping cost and total
            const selectedOption = this.options[this.selectedIndex];
            const shippingFee = parseFloat(selectedOption.getAttribute('data-fee')) || 0;
            const shippingCostElement = document.getElementById('shipping-cost');
            const orderTotalElement = document.getElementById('order-total');

            if (shippingCostElement && orderTotalElement) {
                // Format shipping cost
                shippingCostElement.textContent = 'KSh ' + shippingFee.toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                // Calculate and update total
                const subtotal = parseFloat(orderTotalElement.getAttribute('data-subtotal')) || 0;
                const newTotal = subtotal + shippingFee;
                orderTotalElement.textContent = 'KSh ' + newTotal.toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        });
    }

    // ================================================
    // Notification System (Modern Toast)
    // ================================================
    // Notification function moved to global scope

    // ================================================
    // Update Cart Count Badge
    // ================================================
    function updateCartCount(count) {
        // Update all badges (mobile/desktop if duplicated)
        document.querySelectorAll('.nav-badge').forEach(badge => {
            // Check if this is the cart badge by checking parent's href or icon
            const parent = badge.closest('a');
            if (parent && parent.getAttribute('href').includes('cart')) {
                badge.textContent = count;
                if (count > 0) {
                    badge.style.display = 'flex'; // Flex to center content
                } else {
                    badge.style.display = 'none';
                }
            }
        });
    }

    // ================================================
    // Update Favorites Count Badge
    // ================================================
    function updateFavoritesCount(count) {
        document.querySelectorAll('.nav-badge').forEach(badge => {
            const parent = badge.closest('a');
            if (parent && parent.getAttribute('href').includes('favorites')) {
                badge.textContent = count;
                if (count > 0) {
                    badge.style.display = 'flex';
                } else {
                    badge.style.display = 'none';
                }
            }
        });
    }

    // ================================================
    // Product Search/Filter
    // ================================================
    const searchInput = document.getElementById('product-search');
    const categoryFilter = document.getElementById('category-filter');

    if (searchInput) {
        searchInput.addEventListener('input', filterProducts);
    }

    if (categoryFilter) {
        categoryFilter.addEventListener('change', filterProducts);
    }

    function filterProducts() {
        const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
        const category = categoryFilter ? categoryFilter.value : '';

        document.querySelectorAll('.product-card').forEach(card => {
            const productName = card.querySelector('.card-title').textContent.toLowerCase();
            const productCategory = card.getAttribute('data-category');

            const matchesSearch = productName.includes(searchTerm);
            const matchesCategory = category === '' || productCategory === category;

            if (matchesSearch && matchesCategory) {
                card.closest('.col-md-4, .col-lg-3').style.display = 'block';
            } else {
                card.closest('.col-md-4, .col-lg-3').style.display = 'none';
            }
        });
    }

    // ================================================
    // Form Validation
    // ================================================
    const forms = document.querySelectorAll('.needs-validation');

    forms.forEach(form => {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // ================================================
    // Image Gallery for Product Details
    // ================================================
    const thumbnails = document.querySelectorAll('.product-thumbnail');
    const mainImage = document.getElementById('product-main-image');

    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', function () {
            const newSrc = this.getAttribute('data-image');
            if (mainImage) {
                mainImage.src = newSrc;
            }

            thumbnails.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // ================================================
    // Back to Top Button
    // ================================================
    const backToTopButton = document.createElement('button');
    backToTopButton.innerHTML = '<i class="fas fa-arrow-up"></i>';
    backToTopButton.className = 'btn btn-primary back-to-top';
    backToTopButton.style.cssText = `
        position: fixed;
        bottom: 30px;
        right: 30px;
        display: none;
        z-index: 999;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
    `;

    document.body.appendChild(backToTopButton);

    window.addEventListener('scroll', function () {
        if (window.pageYOffset > 300) {
            backToTopButton.style.display = 'block';
        } else {
            backToTopButton.style.display = 'none';
        }
    });

    backToTopButton.addEventListener('click', function () {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // ================================================
    // Loading Animation
    // ================================================
    window.addEventListener('load', function () {
        const loader = document.querySelector('.page-loader');
        if (loader) {
            loader.style.display = 'none';
        }
    });

    // ================================================
    // Auto-hide alerts after 5 seconds
    // ================================================
    const alerts = document.querySelectorAll('.alert:not(.notification)');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // ================================================
    // Admin - Delete Confirmation
    // ================================================
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function (e) {
            if (!confirm('Are you sure you want to delete this item?')) {
                e.preventDefault();
            }
        });
    });

    // ================================================
    // Toggle Password Visibility
    // ================================================
    const passwordToggles = document.querySelectorAll('.toggle-password');
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', function () {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // ================================================
    // Dark Mode Toggle (Desktop & Mobile)
    // ================================================
    const darkModeToggles = [
        document.getElementById('darkModeToggle'),
        document.getElementById('darkModeToggleMobile')
    ];

    function updateDarkModeUI(isDark) {
        if (isDark) {
            document.body.classList.add('dark-mode');
        } else {
            document.body.classList.remove('dark-mode');
        }

        darkModeToggles.forEach(toggle => {
            if (toggle) {
                const icon = toggle.querySelector('i');
                if (isDark) {
                    icon.classList.remove('fa-moon', 'far');
                    icon.classList.add('fa-sun', 'fas');
                } else {
                    icon.classList.remove('fa-sun', 'fas');
                    icon.classList.add('fa-moon', 'far');
                }
            }
        });
    }

    const isDarkMode = localStorage.getItem('darkMode') === 'true';
    updateDarkModeUI(isDarkMode);

    darkModeToggles.forEach(toggle => {
        if (toggle) {
            toggle.addEventListener('click', function (e) {
                e.preventDefault();
                const isNowDark = !document.body.classList.contains('dark-mode');
                localStorage.setItem('darkMode', isNowDark);
                updateDarkModeUI(isNowDark);
            });
        }
    });

    // ================================================
    // Product Image Slider
    // ================================================
    if (typeof productImages !== 'undefined' && productImages.length > 1) {
        let currentImageIndex = 0;
        const mainImage = document.getElementById('product-main-image');
        const prevBtn = document.getElementById('prevImage');
        const nextBtn = document.getElementById('nextImage');
        const counter = document.getElementById('imageCounter');
        const thumbnails = document.querySelectorAll('.product-thumbnail');

        function updateImage(index) {
            currentImageIndex = index;
            mainImage.src = productImages[index];
            counter.textContent = `${index + 1} / ${productImages.length}`;

            // Update thumbnails
            thumbnails.forEach((thumb, i) => {
                thumb.classList.toggle('active', i === index);
            });
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                const newIndex = (currentImageIndex - 1 + productImages.length) % productImages.length;
                updateImage(newIndex);
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                const newIndex = (currentImageIndex + 1) % productImages.length;
                updateImage(newIndex);
            });
        }

        // Thumbnail click
        thumbnails.forEach((thumb, index) => {
            thumb.addEventListener('click', () => {
                updateImage(index);
            });
        });

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft' && prevBtn) prevBtn.click();
            if (e.key === 'ArrowRight' && nextBtn) nextBtn.click();
        });
    }
});

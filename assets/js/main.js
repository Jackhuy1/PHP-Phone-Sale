/**
 * JavaScript cho Website Shop Điện Thoại
 * File này chứa các chức năng JavaScript cho website
 */

// ======================
// Form Validation
// ======================

document.addEventListener('DOMContentLoaded', function() {
    // Validation cho form tìm kiếm
    const searchForms = document.querySelectorAll('.search-form');
    searchForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const searchInput = this.querySelector('input[name="search"]');
            if (searchInput.value.trim() === '') {
                e.preventDefault();
                alert('Vui lòng nhập từ khóa tìm kiếm!');
            }
        });
    });

    // Validation cho form đăng nhập
    const loginForm = document.querySelector('.login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const username = this.querySelector('input[name="username"]').value.trim();
            const password = this.querySelector('input[name="password"]').value;
            
            if (username.length < 3) {
                alert('Tên đăng nhập phải có ít nhất 3 ký tự!');
                e.preventDefault();
                return;
            }
            
            if (password.length < 6) {
                alert('Mật khẩu phải có ít nhất 6 ký tự!');
                e.preventDefault();
                return;
            }
        });
    }

    // Validation cho form thanh toán
    const checkoutForm = document.querySelector('.checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            const address = this.querySelector('textarea[name="address"]').value.trim();
            const phone = this.querySelector('input[name="phone"]').value.trim();
            
            if (address.length < 10) {
                alert('Vui lòng nhập địa chỉ đầy đủ!');
                e.preventDefault();
                return;
            }
            
            if (phone.length !== 10 || !/^\d+$/.test(phone)) {
                alert('Vui lòng nhập số điện thoại hợp lệ (10 số)!');
                e.preventDefault();
                return;
            }
        });
    }

    // Validation cho form thêm sản phẩm
    const adminForm = document.querySelector('.admin-form-inner');
    if (adminForm) {
        adminForm.addEventListener('submit', function(e) {
            const name = this.querySelector('input[name="name"]').value.trim();
            const price = this.querySelector('input[name="price"]').value.trim();
            const stock = this.querySelector('input[name="stock"]').value.trim();
            
            if (name.length < 3) {
                alert('Tên sản phẩm không hợp lệ!');
                e.preventDefault();
                return;
            }
            
            if (price < 0) {
                alert('Giá không được âm!');
                e.preventDefault();
                return;
            }
            
            if (stock < 0) {
                alert('Số lượng không được âm!');
                e.preventDefault();
                return;
            }
        });
    }
});

// ======================
// Cart Functionality
// ======================

// Kiểm tra giỏ hàng khi tải trang
document.addEventListener('DOMContentLoaded', function() {
    const cartLink = document.querySelector('.cart-link');
    if (cartLink) {
        updateCartCount();
    }
});

function updateCartCount() {
    const cartItemCount = getCartItemCount();
    const cartLink = document.querySelector('.cart-link');
    
    if (cartLink) {
        const cartText = cartLink.textContent;
        const cartMatch = cartText.match(/\((\d+)\)/);
        
        if (cartMatch) {
            cartLink.textContent = '🛒 Giỏ hàng (' + cartItemCount + ')';
        }
    }
}

function getCartItemCount() {
    let count = 0;
    
    if (isLoggedIn()) {
        const stmt = getDBConnection().prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
        stmt.execute([$_SESSION['user_id']]);
        const result = stmt.fetch();
        count = result['count'];
    }
    
    return count;
}

// ======================
// Product Image Lazy Load
// ======================

document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('img[loading="lazy"]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('loading');
                    observer.unobserve(img);
                }
            });
        });
        
        images.forEach(img => {
            imageObserver.observe(img);
        });
    }
});

// ======================
// Smooth Scroll
// ======================

document.addEventListener('DOMContentLoaded', function() {
    const smoothScrollLinks = document.querySelectorAll('a[href^="#"]');
    
    smoothScrollLinks.forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                e.preventDefault();
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});

// ======================
// Auto Focus on Search
// ======================

document.addEventListener('DOMContentLoaded', function() {
    const searchInputs = document.querySelectorAll('.search-form input');
    
    searchInputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.focus();
        });
    });
});

// ======================
// Confirmation Dialog
// ======================

function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// ======================
// Form Auto Complete
// ======================

document.addEventListener('DOMContentLoaded', function() {
    const formInputs = document.querySelectorAll('input, textarea');
    
    formInputs.forEach(input => {
        if (input.type !== 'submit' && input.type !== 'button') {
            input.addEventListener('input', function() {
                // Xóa dấu cách đầu và cuối
                this.value = this.value.trim();
            });
        }
    });
});

// ======================
// Utility Functions
// ======================

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
        minimumFractionDigits: 0
    }).format(price);
}

// ======================
// Print Functionality
// ======================

window.print = function() {
    const printWindow = window.open('', '_blank');
    printWindow.document.write('<html><head><title>In ấn</title>');
    printWindow.document.write('<style>');
    printWindow.document.write('body { font-family: Arial, sans-serif; }');
    printWindow.document.write('.page-break { page-break-after: always; }');
    printWindow.document.write('</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write(document.body.innerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
};

// ======================
// Loading Animation
// ======================

function showLoading(element, show = true) {
    if (show) {
        element.classList.add('loading');
    } else {
        element.classList.remove('loading');
    }
}

// ======================
// Toast Notification
// ======================

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('show');
    }, 100);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

// ======================
// Form Submit with Loading
// ======================

function submitWithLoading(form, loadingElement, successCallback) {
    const originalText = loadingElement.textContent;
    
    // Show loading
    loadingElement.disabled = true;
    loadingElement.textContent = 'Đang xử lý...';
    
    // Submit form
    form.submit();
    
    // Hide loading after page reload
    setTimeout(() => {
        loadingElement.disabled = false;
        loadingElement.textContent = originalText;
    }, 1000);
    
    // Call success callback if provided
    if (successCallback) {
        successCallback();
    }
}

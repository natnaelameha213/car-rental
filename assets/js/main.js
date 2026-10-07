/**
 * EliteDrive Car Rental - Main JS
 */

document.addEventListener('DOMContentLoaded', function () {
    // Navbar scroll effect
    const nav = document.querySelector('.main-nav');
    if (nav) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 50) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
        });
    }

    // Gallery thumbnail click
    const thumbs = document.querySelectorAll('.gallery-thumbs img');
    const mainImg = document.querySelector('.gallery-main img');
    if (thumbs.length && mainImg) {
        thumbs.forEach(thumb => {
            thumb.addEventListener('click', function () {
                mainImg.src = this.src;
                thumbs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }

    // Booking date validation
    const pickupDate = document.getElementById('pickup_date');
    const returnDate = document.getElementById('return_date');
    if (pickupDate && returnDate) {
        const today = new Date().toISOString().split('T')[0];
        pickupDate.min = today;
        pickupDate.addEventListener('change', function () {
            returnDate.min = this.value;
            if (returnDate.value && returnDate.value < this.value) {
                returnDate.value = this.value;
            }
            calculateDays();
        });
        returnDate.addEventListener('change', calculateDays);
    }

    function calculateDays() {
        const daysEl = document.getElementById('rental_days');
        const totalEl = document.getElementById('total_price');
        const dailyRate = parseFloat(document.getElementById('daily_rate')?.value || 0);
        if (pickupDate && returnDate && pickupDate.value && returnDate.value) {
            const start = new Date(pickupDate.value);
            const end = new Date(returnDate.value);
            const days = Math.max(1, Math.ceil((end - start) / (1000 * 60 * 60 * 24)));
            if (daysEl) daysEl.textContent = days;
            if (totalEl) totalEl.textContent = (days * dailyRate).toLocaleString('en-US', { minimumFractionDigits: 2 }) + ' $';
        }
    }

    // Filter form auto-submit on change (optional)
    const filterSelects = document.querySelectorAll('.filter-sidebar select');
    filterSelects.forEach(sel => {
        sel.addEventListener('change', function () {
            // Uncomment to auto-submit: this.form.submit();
        });
    });

    // Confirm delete
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function (e) {
            if (!confirm(this.dataset.confirm || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });

    // Print invoice / agreement
    const printBtn = document.getElementById('printBtn');
    if (printBtn) {
        printBtn.addEventListener('click', function () {
            window.print();
        });
    }
});

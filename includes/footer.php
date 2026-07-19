<footer class="mt-5">
    <div class="container">
        <div class="row g-4 mb-4">
            <div class="col-lg-4">
                <h5 class="text-white mb-3"><i class="fas fa-hotel text-warning me-2"></i>Grand Horizon Hotel</h5>
                <p class="small text-light-50">Experience unparalleled luxury, top-tier hospitality, and world-class amenities at Grand Horizon Hotel. Your comfort is our priority.</p>
                <div class="d-flex gap-3 text-warning fs-5">
                    <a href="#" class="text-warning"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-warning"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-warning"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-warning"><i class="fab fa-tripadvisor"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-md-4">
                <h6 class="text-white mb-3">Quick Links</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="/index.php">Home</a></li>
                    <li class="mb-2"><a href="/rooms.php">Rooms & Suites</a></li>
                    <li class="mb-2"><a href="/about.php">About Us</a></li>
                    <li class="mb-2"><a href="/contact.php">Contact Us</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-4">
                <h6 class="text-white mb-3">Room Types</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="/rooms.php?type=Standard">Standard Suite</a></li>
                    <li class="mb-2"><a href="/rooms.php?type=Deluxe">Deluxe King Room</a></li>
                    <li class="mb-2"><a href="/rooms.php?type=Suite">Executive Suite</a></li>
                    <li class="mb-2"><a href="/rooms.php?type=Luxury">Luxury Penthouse</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-4">
                <h6 class="text-white mb-3">Contact Info</h6>
                <ul class="list-unstyled small text-light-50">
                    <li class="mb-2"><i class="fas fa-map-marker-alt me-2 text-warning"></i>100 Luxury Boulevard, Suite 500</li>
                    <li class="mb-2"><i class="fas fa-phone me-2 text-warning"></i>+1 (555) 019-2831</li>
                    <li class="mb-2"><i class="fas fa-envelope me-2 text-warning"></i>reservations@grandhorizon.com</li>
                </ul>
            </div>
        </div>
        <hr class="border-secondary">
        <div class="row align-items-center small text-light-50 py-2">
            <div class="col-md-6 text-center text-md-start">
                &copy; <?= date('Y') ?> Grand Horizon Hotel Management System. All Rights Reserved.
            </div>
            <div class="col-md-6 text-center text-md-end">
                <a href="#" class="me-3">Privacy Policy</a>
                <a href="#">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>

<!-- jQuery JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="/js/main.js"></script>
<script>
    // Adjust script source path if loaded from subfolder
    if (window.location.pathname.includes('/customer/')) {
        const scripts = document.getElementsByTagName('script');
        for (let s of scripts) {
            if (s.src.includes('/js/main.js')) {
                s.src = '../js/main.js';
            }
        }
    }
</script>
</body>
</html>

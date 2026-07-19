<?php
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-dark text-white py-5 mb-5 position-relative" style="background: linear-gradient(rgba(11,21,40,0.85), rgba(11,21,40,0.85)), url('https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1600&q=80') center/cover;">
    <div class="container text-center py-4">
        <h1 class="display-4 fw-bold">About Grand Horizon</h1>
        <p class="lead text-warning mb-0">Defining Excellence in Luxury Hospitality Since 2012</p>
    </div>
</div>

<div class="container py-4">
    <div class="row align-items-center g-5 mb-5">
        <div class="col-lg-6">
            <span class="text-warning fw-bold text-uppercase">Our Story</span>
            <h2 class="display-6 fw-bold mb-4">A Legacy of Comfort & Unmatched Luxury</h2>
            <p class="text-muted">Founded over a decade ago, Grand Horizon Hotel was born from a singular vision: to create a sanctuary where sophisticated elegance meets effortless warmth. Nestled in the heart of the metropolitan district, our hotel offers guests an sanctuary from the everyday bustle.</p>
            <p class="text-muted">From our meticulously designed suites to our bespoke concierge services, every detail is engineered to provide unforgettable stays for business executives and leisure travelers alike.</p>
            
            <div class="row g-4 mt-3">
                <div class="col-6">
                    <div class="border-start border-4 border-warning ps-3">
                        <h3 class="fw-bold mb-0 text-navy">50+</h3>
                        <p class="text-muted small mb-0">Luxury Rooms & Suites</p>
                    </div>
                </div>
                <div class="col-6">
                    <div class="border-start border-4 border-warning ps-3">
                        <h3 class="fw-bold mb-0 text-navy">99.4%</h3>
                        <p class="text-muted small mb-0">Guest Satisfaction Rate</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="position-relative">
                <img src="https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=800&q=80" alt="Hotel Interior" class="img-fluid rounded-4 shadow-lg">
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

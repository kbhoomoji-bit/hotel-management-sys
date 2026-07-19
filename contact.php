<?php
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/header.php';

$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');

    if (!empty($name) && !empty($email) && !empty($message)) {
        set_flash_message('success', 'Thank you for reaching out! Our reception desk will contact you shortly.');
        header('Location: contact.php');
        exit;
    }
}
?>

<div class="bg-dark text-white py-5 mb-5 position-relative" style="background: linear-gradient(rgba(11,21,40,0.85), rgba(11,21,40,0.85)), url('https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1600&q=80') center/cover;">
    <div class="container text-center py-3">
        <h1 class="display-4 fw-bold">Contact Grand Horizon</h1>
        <p class="lead text-warning mb-0">We are here to assist you 24 hours a day, 7 days a week</p>
    </div>
</div>

<div class="container py-4">
    <div class="row g-5">
        <div class="col-lg-5">
            <h3 class="fw-bold mb-4">Get In Touch</h3>
            <p class="text-muted">Have a question about your reservation, special events, or room amenities? Feel free to contact our guest experience team.</p>

            <div class="d-flex align-items-start mb-4">
                <div class="stat-icon bg-warning-subtle text-warning me-3" style="width:50px; height:50px;">
                    <i class="fas fa-map-marker-alt fs-4"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-1">Our Location</h6>
                    <p class="text-muted small mb-0">100 Luxury Boulevard, Suite 500, Financial District</p>
                </div>
            </div>

            <div class="d-flex align-items-start mb-4">
                <div class="stat-icon bg-warning-subtle text-warning me-3" style="width:50px; height:50px;">
                    <i class="fas fa-phone fs-4"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-1">Phone & Concierge</h6>
                    <p class="text-muted small mb-0">+1 (555) 019-2831 / Toll-Free: 1-800-HORIZON</p>
                </div>
            </div>

            <div class="d-flex align-items-start mb-4">
                <div class="stat-icon bg-warning-subtle text-warning me-3" style="width:50px; height:50px;">
                    <i class="fas fa-envelope fs-4"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-1">Email Reservations</h6>
                    <p class="text-muted small mb-0">reservations@grandhorizon.com</p>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card card-custom p-4 shadow-sm border-0">
                <h4 class="fw-bold mb-4">Send Us a Message</h4>
                
                <form action="contact.php" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-medium">Your Name</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="John Doe" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label fw-medium">Your Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="john@example.com" required>
                        </div>
                        <div class="col-12">
                            <label for="subject" class="form-label fw-medium">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" placeholder="e.g. Reservation Inquiry">
                        </div>
                        <div class="col-12">
                            <label for="message" class="form-label fw-medium">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" placeholder="How can we help you?" required></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-navy py-2 px-4 fw-bold">
                                <i class="fas fa-paper-plane me-2"></i> Send Message
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

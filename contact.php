<?php
$page_title = "Contact";
require_once 'config/database.php';
include 'includes/header.php';
include 'includes/navbar.php';

// Fetch dynamic contact info
$address = get_setting($con, 'contact_address', 'Huprachaur, Hetauda-4, Makwanpur, Nepal');
$email = get_setting($con, 'contact_email', 'concierge@velorajewelry.com');
$phone = get_setting($con, 'contact_phone', '+977 057 91827');
$hours = get_setting($con, 'contact_hours', 'Sunday - Friday, 10:00 AM - 6:00 PM');

$status_msg = "";
$status_type = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $client_email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $client_phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    if (empty($name) || empty($client_email) || empty($message)) {
        $status_msg = "Please fill in all required fields.";
        $status_type = "danger";
    } elseif (!filter_var($client_email, FILTER_VALIDATE_EMAIL)) {
        $status_msg = "Invalid email address format.";
        $status_type = "danger";
    } else {
        // Secure Prepared statement to insert contact message
        $stmt = mysqli_prepare($con, "INSERT INTO contact_messages (name, email, phone, message) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssss", $name, $client_email, $client_phone, $message);
            if (mysqli_stmt_execute($stmt)) {
                $status_msg = "Your inquiry has been registered. Our concierge team will reach out to you.";
                $status_type = "success";
                
                // Clear fields
                $name = $client_email = $client_phone = $message = "";
            } else {
                $status_msg = "Failed to submit. Please try again later.";
                $status_type = "danger";
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<section class="section-padding" style="background-color: var(--color-soft-ivory); text-align: center;">
    <div class="container">
        <span class="hero-tag">Connect With Us</span>
        <h1 style="margin-bottom: 20px; text-transform: uppercase; letter-spacing: 0.1em;">Request Consultation</h1>
        <p class="section-subtitle" style="margin-bottom: 0;">Speak directly to our design curators or submit a custom inquiry below.</p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="contact-box" style="margin-bottom: 60px;">
            <div class="contact-info-pane" style="padding: 50px;">
                <h3>Visit Our Studio</h3>
                <ul class="contact-details" style="margin-bottom: 0;">
                    <li><strong>Address:</strong> <?php echo xss_clean($address); ?></li>
                    <li><strong>Tel:</strong> <?php echo xss_clean($phone); ?></li>
                    <li><strong>Email:</strong> <?php echo xss_clean($email); ?></li>
                    <li><strong>Hours:</strong> <?php echo xss_clean($hours); ?></li>
                </ul>
            </div>
            <div class="contact-map-pane">
                <img src="designs/map.png" alt="Velora Showroom Map">
            </div>
        </div>

        <div style="max-width: 650px; margin: 0 auto; background-color: var(--color-soft-ivory); border: 1px solid var(--color-border-gray); padding: 40px; border-radius: 4px;">
            <h3 style="text-align: center; margin-bottom: 30px; font-family: var(--font-body); font-weight:600; font-size:1.1rem; text-transform:uppercase; letter-spacing:0.15em;">Direct Inquiry Form</h3>
            
            <?php if (!empty($status_msg)): ?>
                <div class="alert alert-<?php echo $status_type; ?>"><?php echo xss_clean($status_msg); ?></div>
            <?php endif; ?>

            <form action="" method="post">
                <div class="form-group">
                    <label for="name">Your Name *</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Eleanor Vance" value="<?php echo isset($name) ? xss_clean($name) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="e.g. eleanor@example.com" value="<?php echo isset($client_email) ? xss_clean($client_email) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" placeholder="e.g. 9865480193" value="<?php echo isset($client_phone) ? xss_clean($client_phone) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="message">Message / Specifications *</label>
                    <textarea id="message" name="message" class="form-control" placeholder="Detail your custom requirements or general inquiries..." required><?php echo isset($message) ? xss_clean($message) : ''; ?></textarea>
                </div>
                
                <button type="submit" class="btn" style="width: 100%;">Submit Inquiry</button>
            </form>
        </div>
    </div>
</section>

<?php
include 'includes/footer.php';
?>

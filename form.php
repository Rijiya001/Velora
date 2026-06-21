<?php
$page_title = "Register Custom Order";
require_once 'config/database.php';
include 'includes/header.php';
include 'includes/navbar.php';

// Get query parameters for quick inquiry from catalog
$prefill_code = isset($_GET['code']) ? htmlspecialchars($_GET['code']) : '';
$prefill_item = isset($_GET['item']) ? htmlspecialchars($_GET['item']) : '';
?>

<section class="form-section">
    <div class="form-container">
        <h1>Register Order</h1>
        <p class="form-subtitle">Register your interest or submit a custom jewelry design. Our concierge team will connect with you within 24 hours.</p>
        
        <form action="userinfo.php" method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
            
            <div class="form-group">
                <label for="username">Full Name</label>
                <input type="text" id="username" name="user" class="form-control" placeholder="e.g. Eleanor Vance" required autocomplete="name">
            </div>
            
            <div class="form-group">
                <label for="emailid">Email Address</label>
                <input type="email" id="emailid" name="email" class="form-control" placeholder="e.g. eleanor@example.com" required autocomplete="email">
            </div>
            
            <div class="form-group">
                <label for="mobile">Mobile Number</label>
                <input type="tel" id="mobile" name="mobile" class="form-control" placeholder="e.g. 9865480193" required autocomplete="tel">
            </div>
            
            <div class="form-group">
                <label for="address">Delivery / Consultation Address</label>
                <input type="text" id="address" name="address" class="form-control" placeholder="e.g. Huprachaur, Hetauda-4" required autocomplete="street-address">
            </div>
            
            <div class="form-group">
                <label for="code">Product Code (if catalog item)</label>
                <input type="text" id="code" name="code" class="form-control" placeholder="e.g. #01" value="<?php echo $prefill_code; ?>">
            </div>
            
            <div class="form-group">
                <label for="jewellery">Category of Interest</label>
                <select id="jewellery" name="jewellery" class="form-control">
                    <option value="">Select Category</option>
                    <option value="Necklace" <?php echo ($prefill_item == 'Necklace') ? 'selected' : ''; ?>>Necklace</option>
                    <option value="Bangles" <?php echo ($prefill_item == 'Bangles') ? 'selected' : ''; ?>>Bangles</option>
                    <option value="Bracelets" <?php echo ($prefill_item == 'Bracelets') ? 'selected' : ''; ?>>Bracelets</option>
                    <option value="Rings" <?php echo ($prefill_item == 'Rings') ? 'selected' : ''; ?>>Rings</option>
                    <option value="Ear Rings" <?php echo ($prefill_item == 'Ear Rings') ? 'selected' : ''; ?>>Ear Rings</option>
                    <option value="Naugedi" <?php echo ($prefill_item == 'Naugedi') ? 'selected' : ''; ?>>Naugedi</option>
                    <option value="Chandrama" <?php echo ($prefill_item == 'Chandrama') ? 'selected' : ''; ?>>Chandrama</option>
                    <option value="Anklets" <?php echo ($prefill_item == 'Anklets') ? 'selected' : ''; ?>>Anklets</option>
                    <option value="Mangalsutra" <?php echo ($prefill_item == 'Mangalsutra') ? 'selected' : ''; ?>>Mangalsutra</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="image">Upload Custom Sketch / Layout (Optional)</label>
                <input type="file" id="image" name="image" class="form-control" accept="image/*">
                <small style="color: var(--color-warm-gray); font-size: 0.75rem; margin-top: 5px; display: block;">Supported formats: JPEG, PNG, WebP.</small>
            </div>
            
            <div class="form-group">
                <label for="comments">Custom Specifications & Instructions</label>
                <textarea id="comments" name="comments" class="form-control" placeholder="Specify desired caret purity, gemstone customizations, sizing requirements, or other custom inquiries..."></textarea>
            </div>
            
            <button type="submit" name="upload" class="btn">Submit Inquiry</button>
        </form>
    </div>
</section>

<script type="text/javascript">
    function validateForm() {
        const email = document.getElementById('emailid').value;
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        
        if (!email.match(emailRegex)) {
            alert("Please enter a valid email address.");
            return false;
        }
        return true;
    }
</script>

<?php
include 'includes/footer.php';
?>

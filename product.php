<?php
require_once 'config/database.php';

$product_code = isset($_GET['code']) ? trim($_GET['code']) : '';

if (empty($product_code)) {
    header("Location: showcase.php");
    exit();
}

// Prepare statement to query product
$stmt = mysqli_prepare($con, "SELECT p.*, c.name as collection_name FROM products p 
                              LEFT JOIN collections c ON p.collection_id = c.id 
                              WHERE p.product_code = ? AND p.status = 'active' LIMIT 1");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $product_code);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $prod = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

if (!$prod) {
    header("Location: showcase.php");
    exit();
}

$page_title = $prod['name'];
include 'includes/header.php';
include 'includes/navbar.php';
?>

<section class="section-padding">
    <div class="container">
        
        <div style="margin-bottom: 30px; font-family: var(--font-body); font-size: 0.8rem; letter-spacing: 0.1em; text-transform: uppercase;">
            <a href="showcase.php" style="color: var(--color-warm-gray);">&larr; Back to Showcase</a>
        </div>

        <div class="row" style="gap: 50px; align-items: flex-start;">
            
            <!-- Left: Product Images Pane -->
            <div class="col" style="flex: 1.2;">
                <div style="border: 1px solid var(--color-border-gray); padding: 15px; border-radius: 4px; background-color: var(--color-soft-ivory); margin-bottom: 20px;">
                    <img src="<?php echo xss_clean($prod['main_image']); ?>" alt="<?php echo xss_clean($prod['name']); ?>" style="width: 100%; height: auto; object-fit: cover; border-radius: 2px;" id="main-product-preview">
                </div>
                
                <!-- Secondary thumbnails if present -->
                <div style="display: flex; gap: 15px;">
                    <div style="width: 80px; height: 80px; border: 1px solid var(--color-champagne-gold); padding: 5px; cursor: pointer; border-radius:2px;" onclick="switchPreview('<?php echo xss_clean($prod['main_image']); ?>')">
                        <img src="<?php echo xss_clean($prod['main_image']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <?php
                    $imgs_query = mysqli_query($con, "SELECT image_path FROM product_images WHERE product_id = " . intval($prod['id']));
                    while ($img_data = mysqli_fetch_assoc($imgs_query)):
                    ?>
                        <div style="width: 80px; height: 80px; border: 1px solid var(--color-border-gray); padding: 5px; cursor: pointer; border-radius:2px;" onclick="switchPreview('<?php echo xss_clean($img_data['image_path']); ?>')">
                            <img src="<?php echo xss_clean($img_data['image_path']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Right: Product Specifications Panel -->
            <div class="col" style="flex: 1;">
                <span class="hero-tag" style="margin-bottom: 10px;"><?php echo xss_clean($prod['material']); ?></span>
                <h1 style="font-size: 3rem; margin-bottom: 15px; line-height: 1.2; text-transform: uppercase;"><?php echo xss_clean($prod['name']); ?></h1>
                
                <div style="border-bottom: 1px solid var(--color-border-gray); padding-bottom: 25px; margin-bottom: 25px;">
                    <table style="width: 100%; font-size: 0.9rem; color: var(--color-deep-charcoal);">
                        <tr style="height: 35px;">
                            <td style="color: var(--color-warm-gray); width: 140px;">Collection:</td>
                            <td style="font-weight: 500;"><?php echo xss_clean($prod['collection_name'] ?? 'General Collection'); ?></td>
                        </tr>
                        <tr style="height: 35px;">
                            <td style="color: var(--color-warm-gray);">Category:</td>
                            <td style="font-weight: 500; text-transform: capitalize;"><?php echo xss_clean($prod['category']); ?></td>
                        </tr>
                        <tr style="height: 35px;">
                            <td style="color: var(--color-warm-gray);">Weight Purity:</td>
                            <td style="font-weight: 500;"><?php echo xss_clean($prod['weight']); ?></td>
                        </tr>
                        <tr style="height: 35px;">
                            <td style="color: var(--color-warm-gray);">Catalog Code:</td>
                            <td><code style="background-color: var(--color-light-beige); padding: 3px 8px; border-radius: 2px; font-weight: 600;"><?php echo xss_clean($prod['product_code']); ?></code></td>
                        </tr>
                    </table>
                </div>

                <div style="margin-bottom: 35px;">
                    <h3 style="font-family: var(--font-body); font-weight: 600; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 12px; color: var(--color-warm-gray);">Design Description</h3>
                    <p style="font-size: 0.95rem; line-height: 1.7; text-align: justify;"><?php echo nl2br(xss_clean($prod['description'])); ?></p>
                </div>

                <div style="display: flex; gap: 20px;">
                    <button class="btn" style="flex: 1;" onclick="openConciergeDM()">Inquire via DM Chat</button>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Concierge Sliding Chat Drawer (DM Section) -->
<div id="chat-drawer" class="chat-drawer">
    <div class="chat-header">
        <div class="agent-profile">
            <span class="agent-avatar">V</span>
            <div>
                <h4>Velora Concierge</h4>
                <span class="agent-status">● Live Agent Available</span>
            </div>
        </div>
        <button class="close-chat-btn" onclick="closeConciergeDM()">&times;</button>
    </div>
    
    <div class="chat-messages" id="chat-messages">
        <!-- Greetings -->
        <div class="chat-bubble agent">
            <p>Welcome to Velora Concierge! We are online and ready to assist you.</p>
        </div>
        <div class="chat-bubble agent">
            <p>We see you are looking at the <strong><?php echo xss_clean($prod['name']); ?></strong> (Code: <?php echo xss_clean($prod['product_code']); ?>). How can our master goldsmiths customize this piece for you?</p>
        </div>
    </div>

    <!-- Inquiry Form -->
    <form class="chat-form" onsubmit="submitConciergeInquiry(event)">
        <input type="hidden" id="inq-prod-code" value="<?php echo xss_clean($prod['product_code']); ?>">
        
        <?php if (!isset($_SESSION['user_id'])): ?>
            <!-- Show name and email inputs if guest -->
            <div class="chat-user-info" id="chat-user-fields">
                <input type="text" id="inq-name" placeholder="Your Full Name" class="chat-input-field" required>
                <input type="email" id="inq-email" placeholder="Your Email Address" class="chat-input-field" required>
            </div>
        <?php endif; ?>

        <div class="chat-input-area">
            <textarea id="inq-message" placeholder="Type your message here..." required></textarea>
            <button type="submit" class="chat-send-btn">➔</button>
        </div>
    </form>
</div>

<script>
function switchPreview(path) {
    document.getElementById('main-product-preview').src = path;
}

function openConciergeDM() {
    document.getElementById('chat-drawer').classList.add('active');
    setTimeout(() => {
        document.getElementById('inq-message').focus();
    }, 300);
}

function closeConciergeDM() {
    document.getElementById('chat-drawer').classList.remove('active');
}

function appendChatBubble(sender, text) {
    const container = document.getElementById('chat-messages');
    const bubble = document.createElement('div');
    bubble.className = `chat-bubble ${sender}`;
    bubble.innerHTML = `<p>${text}</p>`;
    container.appendChild(bubble);
    container.scrollTop = container.scrollHeight;
}

function submitConciergeInquiry(e) {
    e.preventDefault();
    const productCode = document.getElementById('inq-prod-code').value;
    const message = document.getElementById('inq-message').value;
    
    const nameInput = document.getElementById('inq-name');
    const emailInput = document.getElementById('inq-email');
    
    const name = nameInput ? nameInput.value : '<?php echo isset($_SESSION['fullname']) ? xss_clean($_SESSION['fullname']) : ""; ?>';
    const email = emailInput ? emailInput.value : '<?php echo isset($_SESSION['email']) ? xss_clean($_SESSION['email']) : ""; ?>';

    // Show client message in chat
    appendChatBubble('user', message);
    document.getElementById('inq-message').value = '';

    // Create typing indicator
    const container = document.getElementById('chat-messages');
    const typing = document.createElement('div');
    typing.className = 'chat-bubble agent typing-indicator';
    typing.innerHTML = '<p><span>.</span><span>.</span><span>.</span></p>';
    container.appendChild(typing);
    container.scrollTop = container.scrollHeight;

    // Send request via AJAX
    const formData = new FormData();
    formData.append('name', name);
    formData.append('email', email);
    formData.append('product_code', productCode);
    formData.append('message', message);

    fetch('api/inquire.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        typing.remove();
        if (data.status === 'success') {
            appendChatBubble('agent', `Thank you, ${name}! Your inquiry has been logged in our system. A concierge agent will follow up at <strong>${email}</strong> shortly.`);
            
            // Hide fields
            const fields = document.getElementById('chat-user-fields');
            if (fields) {
                fields.style.maxHeight = '0';
                fields.style.opacity = '0';
            }
        } else {
            appendChatBubble('agent', `Sorry, we failed to log your inquiry: ${data.message}`);
        }
    })
    .catch(err => {
        typing.remove();
        console.error(err);
        appendChatBubble('agent', "An error occurred. Please contact concierge@velorajewelry.com.");
    });
}
</script>

<?php
include 'includes/footer.php';
?>

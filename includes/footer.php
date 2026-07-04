<footer>
    <div class="container">
        <div class="footer-grid">
            <!-- Column 1: App Downloads -->
            <div class="footer-col">
                <h3>Mobile Catalog</h3>
                <p>Access exclusive collections and private notifications on our dedicated catalog app.</p>
                <div class="footer-app-logos">
                    <!-- Dynamic relative path used to avoid breaking image routes -->
                    <img src="<?php echo $rel_path; ?>designs/app-store.png" alt="App Store">
                    <img src="<?php echo $rel_path; ?>designs/play-store.png" alt="Google Play">
                </div>
            </div>
            
            <!-- Column 2: Logo and Socials -->
            <div class="footer-col footer-brand">
                <div class="logo"><?php echo xss_clean($brand_name); ?><span>.</span></div>
                <p>Crafting timeless elegance and everyday luxury since 2003. Premium designs inspired by high-end jewelry, made accessible for all.</p>
                <ul class="footer-socials">
                    <li><a href="#" title="Facebook">📘</a></li>
                    <li><a href="#" title="Instagram">📸</a></li>
                    <li><a href="#" title="Twitter">🐦</a></li>
                    <li><a href="#" title="YouTube">📺</a></li>
                </ul>
            </div>
            
            <!-- Column 3: Newsletter Sign-up (Database Integrated) -->
            <div class="footer-col footer-col-right">
                <h3>The <?php echo xss_clean($brand_name); ?> Club</h3>
                <p>Subscribe to receive instant notifications of private showings and new catalog listings.</p>
                
                <div class="newsletter-status" id="newsletter-status-msg" style="display:none; font-size:0.8rem; margin-bottom:10px; color:var(--color-champagne-gold);"></div>
                
                <form class="newsletter-form" onsubmit="handleAjaxSubscribe(event)">
                    <input type="text" id="sub-name" placeholder="Your Name" class="newsletter-input" style="border-radius: 2px 0 0 2px; margin-bottom:5px; display:block; width:100%; border-right:1px solid #444;" required>
                    <div style="display:flex; width:100%;">
                        <input type="email" id="sub-email" placeholder="Your Email Address" class="newsletter-input" style="border-radius: 2px 0 0 2px;" required>
                        <button type="submit" class="newsletter-btn">Join</button>
                    </div>
                </form>
            </div>
        </div>
        
        <hr>
        
        <!-- Copyright and secondary links -->
        <div class="copyright-bar">
            <p>&copy; <?php echo date('Y'); ?> <?php echo xss_clean($brand_name); ?> Luxury Showcase. All rights reserved.</p>
            <ul class="copyright-links">
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Terms of Service</a></li>
            </ul>
        </div>
    </div>
</footer>

<script>
    // Header shadow on scroll effect
    window.addEventListener('scroll', function() {
        const header = document.getElementById('main-header');
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });

    // AJAX newsletter subscription handler
    function handleAjaxSubscribe(e) {
        e.preventDefault();
        const name = document.getElementById('sub-name').value;
        const email = document.getElementById('sub-email').value;
        const msgDiv = document.getElementById('newsletter-status-msg');
        
        msgDiv.style.display = 'block';
        msgDiv.textContent = 'Submitting...';
        msgDiv.style.color = 'var(--color-champagne-gold)';

        const formData = new FormData();
        formData.append('name', name);
        formData.append('email', email);

        fetch('<?php echo $rel_path; ?>api/subscribe.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                msgDiv.textContent = data.message;
                msgDiv.style.color = 'var(--color-success)';
                document.getElementById('sub-name').value = '';
                document.getElementById('sub-email').value = '';
            } else {
                msgDiv.textContent = data.message;
                msgDiv.style.color = 'var(--color-error)';
            }
        })
        .catch(error => {
            console.error('Subscription Error:', error);
            msgDiv.textContent = 'A system error occurred. Please try again.';
            msgDiv.style.color = 'var(--color-error)';
        });
    }
</script>
</body>
</html>

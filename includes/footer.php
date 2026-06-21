<footer>
    <div class="container">
        <div class="footer-grid">
            <!-- Column 1: App Downloads -->
            <div class="footer-col">
                <h3>Our Mobile Experience</h3>
                <p>Access exclusive collections, early pricing guides, and luxury concierge support on our mobile application.</p>
                <div class="footer-app-logos">
                    <!-- Standard path checking to prevent broken files if run on subdirectory -->
                    <img src="designs/app-store.png" alt="App Store">
                    <img src="designs/play-store.png" alt="Google Play">
                </div>
            </div>
            
            <!-- Column 2: Logo and Socials -->
            <div class="footer-col footer-brand">
                <div class="logo">Velora<span>.</span></div>
                <p>Crafting timeless elegance and quiet luxury since 2003. Certified authentic materials sourced conflict-free.</p>
                <ul class="footer-socials">
                    <li><a href="#" title="Facebook"><span>📘</span></a></li>
                    <li><a href="#" title="Instagram"><span>📸</span></a></li>
                    <li><a href="#" title="Twitter"><span>🐦</span></a></li>
                    <li><a href="#" title="YouTube"><span>📺</span></a></li>
                </ul>
            </div>
            
            <!-- Column 3: Newsletter Sign-up -->
            <div class="footer-col footer-col-right">
                <h3>The Velora Club</h3>
                <p>Subscribe to receive early announcements of collection launches, private showings, and metal market analyses.</p>
                <div class="newsletter-form">
                    <input type="email" placeholder="Your Email Address" class="newsletter-input" aria-label="Email address">
                    <button class="newsletter-btn">Join</button>
                </div>
            </div>
        </div>
        
        <hr>
        
        <!-- Copyright and secondary links -->
        <div class="copyright-bar">
            <p>&copy; <?php echo date('Y'); ?> Velora Luxury Jewelry. All rights reserved.</p>
            <ul class="copyright-links">
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Terms of Service</a></li>
                <li><a href="#">Sourcing Authenticity</a></li>
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
</script>
</body>
</html>

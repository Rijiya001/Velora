// ==========================================================================
// VELORA LUXURY JEWELRY - INTERACTIVE FRONT-END CONTROLLER
// ==========================================================================

// Global State
let wishlist = JSON.parse(localStorage.getItem('velora_wishlist')) || [];
let hasIntroduced = false;

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    updateWishlistUI();
    
    // Check if user is already a newsletter subscriber
    if (localStorage.getItem('velora_subscribed') === 'true') {
        const newsletterInput = document.querySelector('.newsletter-input');
        const newsletterBtn = document.querySelector('.newsletter-btn');
        if (newsletterInput && newsletterBtn) {
            newsletterInput.placeholder = "You are subscribed!";
            newsletterInput.disabled = true;
            newsletterBtn.textContent = "Saved";
            newsletterBtn.disabled = true;
        }
    }

    // Scroll header effect
    window.addEventListener('scroll', () => {
        const header = document.getElementById('main-header');
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });
});

// ==========================================================================
// CATALOG FILTERING LOGIC
// ==========================================================================
function filterCatalog(category, buttonElement) {
    // Update active tab styling
    const tabs = document.querySelectorAll('.filter-tab');
    tabs.forEach(tab => tab.classList.remove('active'));
    buttonElement.classList.add('active');

    // Filter items
    const cards = document.querySelectorAll('.catalog-grid .product-card');
    cards.forEach(card => {
        const cardCategory = card.getAttribute('data-category');
        const cardMaterial = card.getAttribute('data-material');
        
        if (category === 'all') {
            card.style.display = 'flex';
        } else if (category === 'gold') {
            card.style.display = (cardMaterial === 'gold') ? 'flex' : 'none';
        } else if (category === 'silver') {
            card.style.display = (cardMaterial === 'silver') ? 'flex' : 'none';
        } else if (category === 'necklaces') {
            card.style.display = (cardCategory === 'necklaces') ? 'flex' : 'none';
        } else if (category === 'rings') {
            card.style.display = (cardCategory === 'rings') ? 'flex' : 'none';
        }
    });
}

// ==========================================================================
// WISHLIST LOGIC (LOCAL STORAGE PERSISTED)
// ==========================================================================
function toggleWishlist(button, code, name) {
    button.classList.toggle('active');
    
    const index = wishlist.findIndex(item => item.code === code);
    if (index > -1) {
        // Remove from wishlist
        wishlist.splice(index, 1);
        showNotification(`Removed "${name}" from your collection`);
    } else {
        // Add to wishlist
        wishlist.push({ code, name });
        showNotification(`Saved "${name}" to your collection`);
        
        // Trigger small heart click micro-animation
        button.style.transform = 'scale(1.3)';
        setTimeout(() => button.style.transform = '', 200);
    }
    
    localStorage.setItem('velora_wishlist', JSON.stringify(wishlist));
    updateWishlistUI();
}

function updateWishlistUI() {
    // Update count indicator
    const countElement = document.getElementById('wishlist-count');
    if (countElement) {
        countElement.textContent = wishlist.length;
        if (wishlist.length > 0) {
            countElement.classList.add('active');
        } else {
            countElement.classList.remove('active');
        }
    }

    // Update heart icons of currently active wishlist items
    const hearts = document.querySelectorAll('.product-wishlist');
    hearts.forEach(heart => {
        const card = heart.closest('.product-card');
        const codeElement = card.querySelector('.product-specs li:nth-child(2) span');
        if (codeElement) {
            const code = codeElement.textContent.trim();
            const isInWishlist = wishlist.some(item => item.code === code);
            if (isInWishlist) {
                heart.classList.add('active');
            } else {
                heart.classList.remove('active');
            }
        }
    });

    // Update Wishlist Items Modal List
    const listContainer = document.getElementById('wishlist-items-list');
    if (!listContainer) return;

    if (wishlist.length === 0) {
        listContainer.innerHTML = '<p class="empty-wishlist-msg">No pieces saved yet.</p>';
    } else {
        let listHTML = '';
        wishlist.forEach(item => {
            listHTML += `
                <div class="wishlist-item">
                    <div>
                        <h4>${item.name}</h4>
                        <small>Catalog Code: ${item.code}</small>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button onclick="triggerInquiry('${item.code}', '${item.name}')" class="wishlist-inquiry-btn">Inquire</button>
                        <button onclick="removeWishlistItem('${item.code}')" class="wishlist-remove-btn">✕</button>
                    </div>
                </div>
            `;
        });
        listContainer.innerHTML = listHTML;
    }
}

function removeWishlistItem(code) {
    wishlist = wishlist.filter(item => item.code !== code);
    localStorage.setItem('velora_wishlist', JSON.stringify(wishlist));
    updateWishlistUI();
    showNotification('Item removed');
}

function toggleWishlistModal() {
    const modal = document.getElementById('wishlist-modal');
    modal.classList.toggle('active');
}

// ==========================================================================
// DIRECT MESSAGE (DM) / CONCIERGE CHAT DRAWER LOGIC
// ==========================================================================
function openChatDrawer() {
    const drawer = document.getElementById('chat-drawer');
    drawer.classList.add('active');
    
    // Auto-focus textarea
    setTimeout(() => {
        document.getElementById('chat-textarea').focus();
    }, 400);

    // Dynamic greeting based on time of day
    if (!hasIntroduced) {
        const hour = new Date().getHours();
        let greeting = "Good afternoon";
        if (hour < 12) greeting = "Good morning";
        else if (hour > 18) greeting = "Good evening";
        
        appendMessage('agent', `${greeting}! I am the Velora virtual assistant. Let me know if you would like custom adjustments on any of our listed jewelry, or submit a sketch to begin a bespoke design.`);
        hasIntroduced = true;
    }
}

function closeChatDrawer() {
    const drawer = document.getElementById('chat-drawer');
    drawer.classList.remove('active');
}

// Trigger DM inquiry with product contextual loading
function triggerInquiry(productCode, productName) {
    // Fill out product context inputs
    document.getElementById('inquiry-product-code').value = productCode;
    document.getElementById('inquiry-product-name').value = productName;
    
    // Open the drawer
    openChatDrawer();
    
    // Insert automated chat message indicating interest
    appendMessage('user', `Hello! I would like to inquire about the "${productName}" (Catalog Code: ${productCode}).`);
    
    // Add typing indicator simulation
    simulateAgentTyping(() => {
        appendMessage('agent', `Excellent selection! The "${productName}" is one of our signatures. Please fill in your name and email above so our consultant can reply directly to your mailbox, and type any details you would like to know.`);
    });
}

// Append bubble to chat messages list
function appendMessage(sender, text) {
    const messagesContainer = document.getElementById('chat-messages');
    if (!messagesContainer) return;
    
    const bubble = document.createElement('div');
    bubble.className = `chat-bubble ${sender}`;
    bubble.innerHTML = `<p>${text}</p>`;
    messagesContainer.appendChild(bubble);
    
    // Scroll chat to bottom
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

// Typing simulation for premium micro-experience
function simulateAgentTyping(callback) {
    const messagesContainer = document.getElementById('chat-messages');
    
    const typingBubble = document.createElement('div');
    typingBubble.className = 'chat-bubble agent typing-indicator';
    typingBubble.innerHTML = '<p><span>.</span><span>.</span><span>.</span></p>';
    messagesContainer.appendChild(typingBubble);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
    
    setTimeout(() => {
        typingBubble.remove();
        if (callback) callback();
    }, 1500);
}

// Submit inquiry using AJAX to Netlify forms
function submitChatInquiry(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    // Add netlify form attributes dynamically
    formData.append('form-name', 'velora-inquiries');

    const clientName = formData.get('client_name');
    const clientMessage = formData.get('client_message');
    const prodName = document.getElementById('inquiry-product-name').value;
    
    // Append client's typed message in chat UI
    appendMessage('user', clientMessage);
    
    // Reset message input immediately to avoid double clicks
    document.getElementById('chat-textarea').value = '';

    // POST form to Netlify serverlessly
    fetch('/', {
        method: 'POST',
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams(formData).toString()
    })
    .then(() => {
        simulateAgentTyping(() => {
            appendMessage('agent', `Thank you, ${clientName}! Your inquiry about ${prodName || 'custom design'} has been received. Our concierge is available and will review this details shortly.`);
            // Collapse user fields after success
            const userFields = document.getElementById('chat-user-fields');
            if (userFields) {
                userFields.style.maxHeight = '0';
                userFields.style.opacity = '0';
                userFields.style.pointerEvents = 'none';
            }
        });
    })
    .catch(error => {
        console.error('Inquiry Submission Error:', error);
        appendMessage('agent', 'System error occurred sending your message. Please email concierge@velorajewelry.com directly.');
    });
}

// Submit newsletter subscription using AJAX to Netlify
function submitNewsletter(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    formData.append('form-name', 'velora-newsletter');
    
    const email = formData.get('subscriber_email');

    fetch('/', {
        method: 'POST',
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams(formData).toString()
    })
    .then(() => {
        localStorage.setItem('velora_subscribed', 'true');
        showNotification('Thank you! You are now subscribed for instant arrivals updates.');
        
        // Disable form inputs
        const newsletterInput = form.querySelector('.newsletter-input');
        const newsletterBtn = form.querySelector('.newsletter-btn');
        if (newsletterInput && newsletterBtn) {
            newsletterInput.value = '';
            newsletterInput.placeholder = "You are subscribed!";
            newsletterInput.disabled = true;
            newsletterBtn.textContent = "Saved";
            newsletterBtn.disabled = true;
        }
    })
    .catch(error => {
        console.error('Newsletter Submission Error:', error);
        showNotification('Failed to subscribe. Please try again.');
    });
}

// ==========================================================================
// NOTIFICATION UTILITY
// ==========================================================================
function showNotification(message) {
    const notification = document.getElementById('notification');
    const msgElement = document.getElementById('notification-message');
    
    if (notification && msgElement) {
        msgElement.textContent = message;
        notification.classList.add('active');
        
        setTimeout(() => {
            notification.classList.remove('active');
        }, 3000);
    }
}

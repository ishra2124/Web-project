/**
 * SkillBridge - UI Components
 * Handles dropdowns, modals, tabs, filters, and role toggles
 */

/* ===== DROPDOWNS ===== */
function initDropdowns() {
    const dropdownTriggers = document.querySelectorAll('[data-dropdown-trigger]');
    
    dropdownTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            const targetId = this.getAttribute('data-dropdown-trigger');
            const dropdown = document.getElementById(targetId);
            
            if (dropdown) {
                // Close all other dropdowns
                document.querySelectorAll('.dropdown-menu.active').forEach(menu => {
                    if (menu.id !== targetId) {
                        menu.classList.remove('active');
                    }
                });
                
                dropdown.classList.toggle('active');
            }
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu.active').forEach(menu => {
            menu.classList.remove('active');
        });
    });
}

/* ===== MODALS ===== */
function initModals() {
    // Open modal triggers
    const modalTriggers = document.querySelectorAll('[data-modal-open]');
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const modalId = this.getAttribute('data-modal-open');
            openModal(modalId);
        });
    });
    
    // Close modal triggers
    const closeTriggers = document.querySelectorAll('[data-modal-close]');
    closeTriggers.forEach(trigger => {
        trigger.addEventListener('click', function() {
            const modal = this.closest('.modal-overlay');
            if (modal) {
                closeModal(modal.id);
            }
        });
    });
    
    // Close on overlay click
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });
    
    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const activeModal = document.querySelector('.modal-overlay:not(.hidden)');
            if (activeModal) {
                closeModal(activeModal.id);
            }
        }
    });
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

/* ===== TABS ===== */
function initTabs() {
    const tabContainers = document.querySelectorAll('[data-tabs]');
    
    tabContainers.forEach(container => {
        const tabs = container.querySelectorAll('[data-tab]');
        const panels = container.querySelectorAll('[data-tab-panel]');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const targetPanel = this.getAttribute('data-tab');
                
                // Update active tab
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Show target panel, hide others
                panels.forEach(panel => {
                    if (panel.getAttribute('data-tab-panel') === targetPanel) {
                        panel.classList.remove('hidden');
                    } else {
                        panel.classList.add('hidden');
                    }
                });
            });
        });
    });
}

/* ===== FILTER CHIPS ===== */
function initFilterChips() {
    const filterGroups = document.querySelectorAll('.filter-chips');
    
    filterGroups.forEach(group => {
        const chips = group.querySelectorAll('.filter-chip');
        const isMultiSelect = group.hasAttribute('data-multi-select');
        
        chips.forEach(chip => {
            chip.addEventListener('click', function() {
                if (isMultiSelect) {
                    // Toggle individual chip
                    this.classList.toggle('active');
                } else {
                    // Single select - deactivate others
                    chips.forEach(c => c.classList.remove('active'));
                    this.classList.add('active');
                }
            });
        });
    });
}

/* ===== ROLE TOGGLE (Landing Page) ===== */
function initRoleToggle() {
    const roleToggle = document.getElementById('roleToggle');
    if (!roleToggle) return;
    
    const heroTitle = document.getElementById('heroTitle');
    const heroSubtitle = document.getElementById('heroSubtitle');
    const searchInput = document.getElementById('searchInput');
    const browseBtn = document.getElementById('browseBtn');
    const heroImage = document.getElementById('heroImage');
    const heroImageOver = document.getElementById('heroImageOver');
    
    const heroToggle = document.querySelector('.hero-toggle');
    const toggleLabels = heroToggle ? heroToggle.querySelectorAll('span:not(.toggle-switch)') : [];
    
    function updateContent() {
        const isFreelancer = roleToggle.checked;
        
        if (isFreelancer) {
            // Freelancer mode
            if (heroTitle) heroTitle.textContent = "Find the best projects and grow your career.";
            if (heroSubtitle) heroSubtitle.textContent = "What kind of work are you looking for?";
            if (searchInput) searchInput.placeholder = "Search for projects, categories, or skills...";
            if (browseBtn) {
                browseBtn.textContent = "Browse Top Projects";
                browseBtn.href = "pages/freelancer/browse-projects.html";
            }
        } else {
            // Hiring mode
            if (heroTitle) heroTitle.textContent = "Connect with world-class talent and grow your business.";
            if (heroSubtitle) heroSubtitle.textContent = "What are you looking for?";
            if (searchInput) searchInput.placeholder = "Search for skills, services, or freelancers...";
            if (browseBtn) {
                browseBtn.textContent = "Browse Top Freelancers";
                browseBtn.href = "pages/client/browse-freelancers.html";
            }
        }
        
        // Update hero images
        if (heroImage && heroImageOver) {
            heroImage.style.zIndex = isFreelancer ? "1" : "2";
            heroImageOver.style.zIndex = isFreelancer ? "2" : "1";
        }
        
        // Update toggle labels
        updateToggleLabels(isFreelancer);
    }
    
    function updateToggleLabels(isFreelancer) {
        if (toggleLabels.length >= 2) {
            toggleLabels[0].style.fontWeight = isFreelancer ? '400' : '600';
            toggleLabels[0].style.color = isFreelancer ? 'var(--gray-600)' : 'var(--gray-900)';
            toggleLabels[1].style.fontWeight = isFreelancer ? '600' : '400';
            toggleLabels[1].style.color = isFreelancer ? 'var(--gray-900)' : 'var(--gray-600)';
        }
    }
    
    // Listen for toggle changes
    roleToggle.addEventListener('change', updateContent);
    
    // Initialize on load
    updateContent();
}

/* ===== DASHBOARD DATA LOADING ===== */
async function loadDashboardData() {
    // Only load if on a dashboard page
    const pathname = window.location.pathname;
    
    // 1. Load General Stats
    if (pathname.includes('dashboard.html')) {
         try {
            const result = await apiCall('/dashboard/getStats.php');
            if (result.success) {
                const stats = result.data;
                
                // Admin Stats
                if (stats.pending_freelancers !== undefined) {
                     updateText('pending-freelancers-count', stats.pending_freelancers);
                     updateText('pending-clients-count', stats.pending_clients);
                     updateText('total-verifications-count', stats.pending_verifications);
                }
                
                // Freelancer Stats
                if (stats.wallet_balance !== undefined) {
                    updateText('wallet-balance', '$' + parseFloat(stats.wallet_balance).toFixed(2));
                    updateText('active-projects-count', stats.active_projects);
                    updateText('completed-projects-count', stats.completed_projects);
                    updateText('average-rating', stats.rating);
                }
                
                // Client Stats
                if (stats.pending_proposals !== undefined) {
                    updateText('active-projects-count', stats.active_projects);
                    updateText('pending-projects-count', stats.pending_proposals); // Using pending proposals as pending projects proxy
                    updateText('total-projects-count', parseInt(stats.active_projects) + parseInt(stats.completed_projects || 0)); // Approx
                }
            }
         } catch (e) {
             console.error('Failed to load dashboard stats', e);
         }
    }

    // 2. Load Portfolio Review Data (Admin)
    if (pathname.includes('admin-portfolio-review.html')) {
        loadPortfolioReviews();
    }
    
    // 3. Load Freelancer Profile Data (for Sidebar/Header)
    if (pathname.includes('freelancer/') && !pathname.includes('login') && !pathname.includes('signup')) {
         // Optionally load profile to show name/avatar if not in localStorage
    }

    // 4. Load Project Ratings (Freelancer)
    const ratingsContainer = document.getElementById('project-ratings-container');
    if (ratingsContainer) {
        try {
            const result = await apiCall('/freelancer/getRatings.php');
            if (result.success && result.data.length > 0) {
                ratingsContainer.innerHTML = result.data.map(review => `
                    <div style="border-bottom: 1px solid var(--gray-200); padding: 15px 0;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                            <h4 style="margin: 0; font-size: 1rem;">${review.project_title}</h4>
                            <span style="color: #f59e0b; font-weight: bold;">★ ${review.rating}.0</span>
                        </div>
                        <p style="margin: 0 0 5px 0; font-size: 0.9rem; color: var(--gray-700);">"${review.comment}"</p>
                        <div style="font-size: 0.8rem; color: var(--gray-500);">
                            by ${review.client_name} • ${new Date(review.created_at).toLocaleDateString()}
                        </div>
                    </div>
                `).join('');
            }
        } catch (e) {
            console.error('Error loading ratings', e);
        }
    }
}

async function loadPortfolioReviews() {
    const grid = document.getElementById('portfolioGrid');
    if (!grid) return;

    try {
        const result = await apiCall('/admin/getPortfolios.php?status=pending');
        if (result.success && result.data.length > 0) {
            grid.innerHTML = result.data.map(item => `
                <div class="portfolio-card" data-portfolio-id="${item.portfolio_id}">
                    <div class="portfolio-header">
                        <div class="avatar">${getInitials(item.full_name)}</div>
                        <div>
                            <h3 style="margin: 0 0 0.25rem;">${item.full_name}</h3>
                            <span class="badge status-${item.status}">${item.status}</span>
                        </div>
                    </div>
                    <div class="portfolio-meta">
                        <div style="width: 100%;">
                            <h4 style="margin: 0.5rem 0;">${item.title}</h4>
                            <p style="font-size: 0.9rem; color: var(--gray-600);">${item.description || 'No description'}</p>
                        </div>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <p style="font-size: 0.875rem; color: var(--gray-600); margin-bottom: 0.5rem;">
                            <strong>Link:</strong>
                            <a href="${item.project_link}" class="btn-link" target="_blank">View Project</a>
                        </p>
                    </div>
                    <div class="portfolio-actions">
                        <button type="button" class="btn btn-success btn-sm" onclick="approvePortfolio(${item.portfolio_id}, 'approved')">
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="approvePortfolio(${item.portfolio_id}, 'rejected')">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    </div>
                </div>
            `).join('');
            
            // Hide empty state
            const emptyState = document.getElementById('emptyState');
            if (emptyState) emptyState.classList.add('hidden');
        } else {
             grid.innerHTML = '';
             const emptyState = document.getElementById('emptyState');
             if (emptyState) emptyState.classList.remove('hidden');
        }
    } catch (e) {
        console.error('Error loading portfolios', e);
    }
}

// Helper: Update text safely
function updateText(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
}

// Helper: Initials
function getInitials(name) {
    return name ? name.split(' ').map(n => n[0]).join('').substring(0,2).toUpperCase() : 'U';
}

// Global action for portfolio
window.approvePortfolio = async function(id, status) {
    if (!confirm(`Are you sure you want to ${status} this portfolio?`)) return;
    
    const result = await apiCall('/admin/approvePortfolio.php', 'POST', {
        portfolio_id: id,
        status: status
    });
    
    if (result.success) {
        alert('Portfolio updated');
        loadPortfolioReviews(); // Reload
    } else {
        alert(result.message || 'Error updating portfolio');
    }
};


/* ===== SEARCH & FILTERS ===== */
function initSearchAndFilters() {
    const searchForm = document.getElementById('searchForm');
    const filterButtons = document.querySelectorAll('[data-action="apply-filters"]');
    const clearButtons = document.querySelectorAll('[data-action="clear-filters"]');

    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const query = this.querySelector('input').value;
            console.log('Searching for:', query);
            // TODO: Navigate to browse page with query
        });
    }

    // ... (Keep existing filter logic or update as needed)
    // For now, keeping the console logs as requested unless implementing full search
}


/* ===== CHAT INTEGRATION ===== */
async function initChat() {
    // Only on chat pages
    if (!window.location.pathname.includes('chat.html')) return;

    const listContainer = document.getElementById('freelancer-list');
    const messagesContainer = document.getElementById('messages-list');
    const chatHeader = document.getElementById('chat-header');
    const input = document.querySelector('.card input[type="text"]'); // Better to use ID but relying on structure for now
    const sendBtn = document.querySelector('.card button.btn-primary'); // Same here

    if (!listContainer || !messagesContainer) return;

    let activeUserId = null;

    // 1. Load Conversations
    try {
        const result = await apiCall('/chat/getConversations.php');
        if (result.success && result.data.length > 0) {
            listContainer.innerHTML = result.data.map(user => `
                <div class="chat-user-item" data-user-id="${user.user_id}" 
                     style="display: flex; gap: 10px; align-items: center; padding: 10px; border-radius: 8px; cursor: pointer; transition: background 0.2s;">
                    <div style="width: 40px; height: 40px; background: #ddd; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                        ${getInitials(user.full_name)}
                    </div>
                    <div>
                        <h4 style="margin: 0; font-size: 0.95rem;">${user.full_name}</h4>
                        <span style="font-size: 0.8rem; color: var(--gray-500);">${user.role}</span>
                    </div>
                </div>
            `).join('');

            // Add Click Listeners
            listContainer.querySelectorAll('.chat-user-item').forEach(item => {
                item.addEventListener('click', function() {
                    // Highlight
                    listContainer.querySelectorAll('.chat-user-item').forEach(i => i.style.background = 'transparent');
                    this.style.background = 'var(--gray-100)';
                    
                    const userId = this.getAttribute('data-user-id');
                    const userName = this.querySelector('h4').textContent;
                    
                    loadConversation(userId, userName);
                });
            });
        } else {
            listContainer.innerHTML = '<p style="padding: 20px; text-align: center; color: var(--gray-500);">No contacts found.</p>';
        }
    } catch (e) {
        console.error('Error loading conversations', e);
    }

    // 2. Load Conversation Messages
    async function loadConversation(userId, userName) {
        activeUserId = userId;
        chatHeader.style.display = 'flex';
        document.getElementById('chat-user-name').textContent = userName;
        document.getElementById('chat-avatar-initials').textContent = getInitials(userName);
        
        messagesContainer.innerHTML = '<p style="text-align: center;">Loading...</p>';

        try {
            const result = await apiCall(`/chat/fetchMessages.php?user_id=${userId}`);
            if (result.success) {
                renderMessages(result.data);
            }
        } catch (e) {
            console.error('Error loading messages', e);
        }
    }

    // 3. Render Messages
    function renderMessages(messages) {
        if (messages.length === 0) {
            messagesContainer.innerHTML = '<p style="text-align: center; color: var(--gray-500); margin-top: 2rem;">No messages yet.</p>';
            return;
        }

        const currentUserId = JSON.parse(localStorage.getItem('user'))?.user_id;

        messagesContainer.innerHTML = messages.map(msg => {
            const isMe = msg.sender_id == currentUserId;
            // Simple style for message bubble
            return `
                <div style="display: flex; justify-content: ${isMe ? 'flex-end' : 'flex-start'};">
                    <div style="max-width: 70%; padding: 10px 15px; border-radius: 12px; 
                                background: ${isMe ? 'var(--blue-600)' : '#f3f4f6'}; 
                                color: ${isMe ? 'white' : 'black'};">
                        <p style="margin: 0;">${msg.message}</p>
                        <span style="font-size: 0.7rem; opacity: 0.7; display: block; text-align: right; margin-top: 5px;">
                            ${new Date(msg.sent_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                        </span>
                    </div>
                </div>
            `;
        }).join('');
        
        // Scroll to bottom
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // 4. Send Message
    if (sendBtn && input) {
        sendBtn.addEventListener('click', sendMessage);
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') sendMessage();
        });

        async function sendMessage() {
            const text = input.value.trim();
            if (!text || !activeUserId) return;

            const originalText = sendBtn.innerText;
            sendBtn.innerText = '...';
            sendBtn.disabled = true;

            try {
                const result = await apiCall('/chat/sendMessage.php', 'POST', {
                    receiver_id: activeUserId,
                    message: text
                });

                if (result.success) {
                    input.value = '';
                    // Reload conversation to show new message
                    // In a real app, I'd just append it, but reloading ensures sync
                    const messagesResult = await apiCall(`/chat/fetchMessages.php?user_id=${activeUserId}`);
                    if (messagesResult.success) {
                        renderMessages(messagesResult.data);
                    }
                } else {
                    alert('Failed to send');
                }
            } catch (e) {
                console.error('Error sending message', e);
            } finally {
                sendBtn.innerText = originalText;
                sendBtn.disabled = false;
            }
        }
    }
}


// Auto-initialize components
document.addEventListener('DOMContentLoaded', () => {
    initDropdowns();
    initModals();
    initTabs();
    initFilterChips();
    initRoleToggle();
    initSearchAndFilters();
    setActiveNavLink();
    initNavbar();
    
    // Load Data
    loadDashboardData();
    initChat();
});

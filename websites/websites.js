// Modal functions
function openModal() {
    document.getElementById('websiteModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('websiteModal').style.display = 'none';
    document.getElementById('addWebsiteForm').reset();
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('websiteModal');
    if (event.target === modal) {
        closeModal();
    }
}

// Add website with AJAX
document.getElementById('addWebsiteForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    
    // Show loading state
    btnLoader(submitBtn, true);
    
    try {
        const response = await fetch('../api/website.php?action=add', {
            method: 'POST',
            body: formData,
        });
        
        const result = await response.json();
        if (result.success) {
            showNotification('Website added successfully!', 'success');
            closeModal();
            // Reload the page to show new website
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotification(result.error || 'Failed to add website', 'error');
        }
    } catch (error) {
        showNotification('Network error. Please try again.', 'error');
        console.error('Error:', error);
    } finally {
        // Restore button state
       btnLoader(submitBtn, false);
    }
});

// Sync website
async function syncWebsite(websiteId) {
    
    const syncBtn = document.querySelector(`.website-card[data-id="${websiteId}"] .btn-icon`);
    syncBtn.classList.add('loading');

    const formData = new FormData();
    formData.append('website_id', websiteId);
    
    try {
        const response = await fetch('../api/website.php?action=sync', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Website synced successfully!', 'success');
            // Update the UI with new sync time
            const lastSyncEl = document.querySelector(`.website-card[data-id="${websiteId}"] .last-sync`);
            const syncStatus = document.querySelector(`.website-card[data-id="${websiteId}"] .status-badge`);
            if (lastSyncEl && syncStatus) {
                lastSyncEl.textContent = 'Last sync: Just now';
                syncStatus.textContent = 'Connected';
                syncStatus.classList.remove('status-pending');
                syncStatus.classList.add('status-connected');
            }
        } else {
            showNotification(result.error || 'Failed to sync website', 'error');
        }
    } catch (error) {
        showNotification('Network error. Please try again.', 'error');
        console.error('Error:', error);
    } finally {
        // Restore button state
       syncBtn.classList.remove('loading');
    }
}

// Delete website
async function deleteWebsite(websiteId) {
     Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then(async (result) => {
            if (result.isConfirmed) {
            await performDelete(websiteId);
        }
    });
}

async function performDelete(websiteId) {
    // Disable the card to prevent multiple clicks
    
    const websiteCard = document.querySelector(`.website-card[data-id="${websiteId}"]`);
    websiteCard.style.opacity = '0.5';
    websiteCard.style.pointerEvents = 'none';

    const formData = new FormData();
    formData.append('website_id', websiteId);
    
    try {
        const response = await fetch('../api/website.php?action=delete', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Website deleted successfully!', 'success');
            // Remove the card from UI
            websiteCard.remove();
            
            // Check if no websites left
            const remainingCards = document.querySelectorAll('.website-card');
            if (remainingCards.length === 0) {
                window.location.reload();
            }
        } else {
            showNotification(result.error || 'Failed to delete website', 'error');
            websiteCard.style.opacity = '1';
            websiteCard.style.pointerEvents = 'auto';
        }
    } catch (error) {
        showNotification('Network error. Please try again.', 'error');
        console.error('Error:', error);
        websiteCard.style.opacity = '1';
        websiteCard.style.pointerEvents = 'auto';
    }
}

// Notification function
function showNotification(message, type = 'info') {
    const Toast = Swal.mixin({
  toast: true,
  position: "top-end",
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true,
  didOpen: (toast) => {
    toast.onmouseenter = Swal.stopTimer;
    toast.onmouseleave = Swal.resumeTimer;
  }
});
Toast.fire({
  icon: type,
  title: message
})
}
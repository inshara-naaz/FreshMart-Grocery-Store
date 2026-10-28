<?php
// ============================================
// admin/includes/admin-footer.php
// Admin Panel - Footer + Scripts
// ============================================
?>

    </div>
    <!-- End Content -->
    
</div>
<!-- End Main -->

<!-- ================= SCRIPTS ================= -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
// AOS Init
AOS.init({ duration: 600, once: true, offset: 50 });

// Sidebar Toggle (Mobile)
const sidebarToggle = document.getElementById('sidebarToggle');
const adminSidebar = document.getElementById('adminSidebar');
if (sidebarToggle && adminSidebar) {
    sidebarToggle.addEventListener('click', () => {
        adminSidebar.classList.toggle('open');
    });
    // Click outside to close
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 991 && 
            adminSidebar.classList.contains('open') &&
            !adminSidebar.contains(e.target) &&
            !sidebarToggle.contains(e.target)) {
            adminSidebar.classList.remove('open');
        }
    });
}

// ================= TOAST NOTIFICATION =================
function adminToast(message, type = 'success') {
    // Purane toast remove karo
    const oldToast = document.getElementById('adminToast');
    if (oldToast) oldToast.remove();
    
    const toast = document.createElement('div');
    toast.id = 'adminToast';
    toast.className = 'admin-toast';
    if (type === 'error') toast.classList.add('toast-error');
    if (type === 'warning') toast.classList.add('toast-warning');
    
    const icon = type === 'error' ? 'fa-exclamation-circle' : 
                 type === 'warning' ? 'fa-exclamation-triangle' : 
                 'fa-check-circle';
    
    toast.innerHTML = `<i class="fas ${icon}"></i> ${message}`;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
    }, 3000);
}

// ================= CONFIRM DELETE =================
function confirmDelete(url, itemName = 'this item') {
    if (confirm(`Are you sure you want to delete ${itemName}? This action cannot be undone.`)) {
        window.location.href = url;
    }
}

// ================= COPY TO CLIPBOARD =================
function copyText(text, successMsg = 'Copied!') {
    navigator.clipboard.writeText(text).then(() => {
        adminToast(successMsg);
    }).catch(() => {
        adminToast('Failed to copy', 'error');
    });
}
</script>
</body>
</html>
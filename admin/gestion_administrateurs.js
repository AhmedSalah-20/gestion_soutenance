document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable with 5 rows per page and fixed height
    $('#administrateursTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json'
        },
        pageLength: 5,
        lengthChange: false,
        pagingType: 'simple_numbers',
        scrollY: 'calc(100vh - 350px)',
        scrollCollapse: true
    });

    // User dropdown functionality
    const userDropdown = document.getElementById('userDropdown');
    const dropdownMenu = document.getElementById('dropdownMenu');
    
    userDropdown.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdownMenu.classList.toggle('show');
    });
    
    document.addEventListener('click', function() {
        dropdownMenu.classList.remove('show');
    });
    
    dropdownMenu.addEventListener('click', function(e) {
        e.stopPropagation();
    });
    
    // Password toggle
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('mot_de_passe');
    
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });
    }
    
    // Edit button functionality
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const adminModal = new bootstrap.Modal(document.getElementById('adminModal'));
            document.getElementById('adminModalLabel').textContent = 'Modifier un administrateur';
            document.getElementById('adminId').value = this.dataset.id;
            document.getElementById('login').value = this.dataset.login;
            document.getElementById('mot_de_passe').value = this.dataset.password;
            document.getElementById('email').value = this.dataset.email;
            document.getElementById('nom').value = this.dataset.nom;
            document.getElementById('prenom').value = this.dataset.prenom;
            adminModal.show();
        });
    });
    
    // Reset modal when closed
    document.getElementById('adminModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('adminModalLabel').textContent = 'Ajouter un administrateur';
        this.querySelector('form').reset();
    });

    // Prevent anchor scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
        });
    });
});
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable with 5 rows per page and fixed height
    $('#soutenancesTable').DataTable({
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
    
    // Edit button functionality
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const soutenanceModal = new bootstrap.Modal(document.getElementById('soutenanceModal'));
            document.getElementById('soutenanceModalLabel').textContent = 'Modifier une soutenance';
            document.getElementById('soutenanceId').value = this.dataset.id;
            document.getElementById('id_etudiant').value = this.dataset.id_etudiant;
            document.getElementById('date_soutenance').value = this.dataset.date_soutenance.replace(' ', 'T');
            document.getElementById('salle').value = this.dataset.salle;
            document.getElementById('theme').value = this.dataset.theme;
            soutenanceModal.show();
        });
    });
    
    // Reset modal when closed
    document.getElementById('soutenanceModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('soutenanceModalLabel').textContent = 'Ajouter une soutenance';
        this.querySelector('form').reset();
    });

    // Prevent anchor scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
        });
    });
});
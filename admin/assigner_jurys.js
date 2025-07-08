document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable with 5 rows per page and fixed height
    $('#juryTable').DataTable({
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
            const juryModal = new bootstrap.Modal(document.getElementById('juryModal'));
            document.getElementById('juryModalLabel').textContent = 'Modifier une attribution';
            document.getElementById('juryId').value = this.dataset.id;
            document.getElementById('id_soutenance').value = this.dataset.id_soutenance;

            // Fetch existing jurys for this soutenance (simplified; assumes single jury per id_jury)
            const matriculeEnseignant = this.dataset.matricule_enseignant;
            const checkboxes = document.querySelectorAll('.jury-checkbox');
            checkboxes.forEach(cb => cb.checked = false);
            if (matriculeEnseignant) {
                const checkbox = document.querySelector(`#enseignant_${matriculeEnseignant}`);
                if (checkbox) checkbox.checked = true;
            }
            updateJurySelection(); // Update validation after setting checkboxes
            juryModal.show();
        });
    });
    
    // Reset modal when closed
    document.getElementById('juryModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('juryModalLabel').textContent = 'Ajouter une attribution';
        this.querySelector('form').reset();
        const messageDiv = document.getElementById('juryCountMessage');
        if (messageDiv) messageDiv.classList.remove('text-danger', 'text-success');
        messageDiv.textContent = 'Sélectionnez 2 à 3 enseignants.';
        updateJurySelection();
    });

    // Jury selection validation
    const juryCheckboxes = document.querySelectorAll('.jury-checkbox');
    const saveJuryBtn = document.getElementById('saveJuryBtn');
    const juryCountMessage = document.getElementById('juryCountMessage');

    function updateJurySelection() {
        const selectedCount = document.querySelectorAll('.jury-checkbox:checked').length;
        if (selectedCount >= 2 && selectedCount <= 3) {
            saveJuryBtn.disabled = false;
            juryCountMessage.textContent = `Sélection : ${selectedCount} enseignant(s).`;
            juryCountMessage.classList.remove('text-danger');
            juryCountMessage.classList.add('text-success');
        } else {
            saveJuryBtn.disabled = true;
            juryCountMessage.textContent = `Sélectionnez entre 2 et 3 enseignants (actuellement : ${selectedCount}).`;
            juryCountMessage.classList.remove('text-success');
            juryCountMessage.classList.add('text-danger');
        }
    }

    // Add event listeners to checkboxes
    juryCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateJurySelection);
    });

    // Initial check
    updateJurySelection();

    // Prevent anchor scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
        });
    });
});
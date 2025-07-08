document.addEventListener('DOMContentLoaded', function () {
    // Initialisation de DataTable
    $('table').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json'
        },
        pageLength: 5,
        lengthChange: false,
        pagingType: 'simple_numbers'
    });

    // Gestion du menu déroulant utilisateur
    const userDropdown = document.getElementById('userDropdown');
    const dropdownMenu = document.getElementById('dropdownMenu');

    userDropdown.addEventListener('click', function (e) {
        e.stopPropagation();
        dropdownMenu.classList.toggle('show');
    });

    document.addEventListener('click', function () {
        dropdownMenu.classList.remove('show');
    });

    dropdownMenu.addEventListener('click', function (e) {
        e.stopPropagation();
    });
});
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable with 5 rows per page and fixed height
    const table = $('#enseignantsTable').DataTable({
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

    // Form elements
    const form = document.querySelector('#enseignantModal form');
    const matriculeInput = document.getElementById('matricule');
    const loginInput = document.getElementById('login');
    const nomInput = document.getElementById('nom');
    const prenomInput = document.getElementById('prenom');
    const photoInput = document.getElementById('photo_profil');

    // Validation feedback elements
    const createFeedbackElement = (inputId) => {
        let feedback = document.getElementById(`${inputId}Feedback`);
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.id = `${inputId}Feedback`;
            feedback.className = 'invalid-feedback';
            document.getElementById(inputId).parentNode.appendChild(feedback);
        }
        return feedback;
    };

    // Validation functions
    const validateName = (name) => {
        const nameRegex = /^[a-zA-Z\s-]{2,50}$/;
        return nameRegex.test(name);
    };

    const validatePassword = (password) => {
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/;
        return passwordRegex.test(password);
    };

    const validatePhoto = (file) => {
        if (!file) return true; // Photo is optional
        const maxSize = 2 * 1024 * 1024; // 2MB
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        return allowedTypes.includes(file.type) && file.size <= maxSize;
    };

    // Client-side duplicate check using DataTable data
    const checkDuplicate = (field, value, originalValue = '') => {
        if (value === originalValue) return true;
        let columnIndex;
        if (field === 'matricule') {
            columnIndex = 1; // Matricule is in the second column
        } else if (field === 'login') {
            columnIndex = 4; // Login is in the fifth column
        } else {
            return true;
        }

        let isUnique = true;
        table.rows().every(function() {
            const data = this.data();
            if (data[columnIndex] === value) {
                isUnique = false;
                return false; // Break the loop
            }
        });
        return isUnique;
    };

    // Real-time validation
    const validateInput = (input, validateFn, errorMessage, checkDuplicateField = null, originalValue = '') => {
        const feedback = createFeedbackElement(input.id);
        if (!input.value) {
            input.classList.add('is-invalid');
            feedback.textContent = 'Ce champ est requis';
            return false;
        }
        
        if (validateFn && !validateFn(input.value)) {
            input.classList.add('is-invalid');
            feedback.textContent = errorMessage;
            return false;
        }

        if (checkDuplicateField) {
            const isUnique = checkDuplicate(checkDuplicateField, input.value, originalValue);
            if (!isUnique) {
                input.classList.add('is-invalid');
                feedback.textContent = `Ce ${checkDuplicateField} existe déjà`;
                return false;
            }
        }

        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        feedback.textContent = '';
        return true;
    };

    // Form validation on submit
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        let isValid = true;

        // Validate matricule
        const originalMatricule = document.getElementById('enseignantMatricule').value;
        isValid &= validateInput(
            matriculeInput, 
            (value) => /^\d{8}$/.test(value),
            'Le matricule doit contenir exactement 8 chiffres',
            'matricule',
            originalMatricule
        );

        // Validate login
        const originalLogin = loginInput.dataset.original || '';
        isValid &= validateInput(
            loginInput, 
            (value) => /^[a-zA-Z0-9]{4,20}$/.test(value),
            'Le login doit contenir 4 à 20 caractères alphanumériques',
            'login',
            originalLogin
        );

        // Validate nom
        isValid &= validateInput(
            nomInput, 
            validateName,
            'Le nom doit contenir uniquement des lettres, espaces ou tirets (2-50 caractères)'
        );

        // Validate prenom
        isValid &= validateInput(
            prenomInput, 
            validateName,
            'Le prénom doit contenir uniquement des lettres, espaces ou tirets (2-50 caractères)'
        );

        // Validate password
        isValid &= validateInput(
            passwordInput, 
            validatePassword,
            'Le mot de passe doit contenir au moins 8 caractères, incluant une majuscule, une minuscule et un chiffre'
        );

        // Validate photo
        const photoFeedback = createFeedbackElement('photo_profil');
        if (photoInput.files[0] && !validatePhoto(photoInput.files[0])) {
            photoInput.classList.add('is-invalid');
            photoFeedback.textContent = 'La photo doit être au format JPEG/PNG/GIF et ne pas dépasser 2MB';
            isValid = false;
        } else {
            photoInput.classList.remove('is-invalid');
            photoInput.classList.add('is-valid');
            photoFeedback.textContent = '';
        }

        if (isValid) {
            form.submit();
        }
    });

    // Real-time input validation
    [matriculeInput, loginInput, nomInput, prenomInput, passwordInput].forEach(input => {
        input.addEventListener('input', () => {
            if (input === matriculeInput) {
                validateInput(
                    matriculeInput, 
                    (value) => /^\d{8}$/.test(value),
                    'Le matricule doit contenir exactement 8 chiffres',
                    'matricule',
                    document.getElementById('enseignantMatricule').value
                );
            } else if (input === loginInput) {
                validateInput(
                    loginInput, 
                    (value) => /^[a-zA-Z0-9]{4,20}$/.test(value),
                    'Le login doit contenir 4 à 20 caractères alphanumériques',
                    'login',
                    loginInput.dataset.original || ''
                );
            } else if (input === nomInput) {
                validateInput(
                    nomInput, 
                    validateName,
                    'Le nom doit contenir uniquement des lettres, espaces ou tirets (2-50 caractères)'
                );
            } else if (input === prenomInput) {
                validateInput(
                    prenomInput, 
                    validateName,
                    'Le prénom doit contenir uniquement des lettres, espaces ou tirets (2-50 caractères)'
                );
            } else if (input === passwordInput) {
                validateInput(
                    passwordInput, 
                    validatePassword,
                    'Le mot de passe doit contenir au moins 8 caractères, incluant une majuscule, une minuscule et un chiffre'
                );
            }
        });
    });

    // Photo input validation
    photoInput.addEventListener('change', () => {
        const photoFeedback = createFeedbackElement('photo_profil');
        if (photoInput.files[0] && !validatePhoto(photoInput.files[0])) {
            photoInput.classList.add('is-invalid');
            photoFeedback.textContent = 'La photo doit être au format JPEG/PNG/GIF et ne pas dépasser 2MB';
        } else {
            photoInput.classList.remove('is-invalid');
            photoInput.classList.add('is-valid');
            photoFeedback.textContent = '';
        }
    });

    // Edit button functionality
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const enseignantModal = new bootstrap.Modal(document.getElementById('enseignantModal'));
            document.getElementById('enseignantModalLabel').textContent = 'Modifier un enseignant';
            document.getElementById('enseignantMatricule').value = this.dataset.matricule;
            document.getElementById('matricule').value = this.dataset.matricule;
            document.getElementById('nom').value = this.dataset.nom;
            document.getElementById('prenom').value = this.dataset.prenom;
            document.getElementById('login').value = this.dataset.login;
            document.getElementById('mot_de_passe').value = this.dataset.password;
            document.getElementById('matricule').readOnly = true;
            loginInput.dataset.original = this.dataset.login;
            enseignantModal.show();
        });
    });
    
    // Reset modal when closed
    document.getElementById('enseignantModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('enseignantModalLabel').textContent = 'Ajouter un enseignant';
        document.getElementById('matricule').readOnly = false;
        this.querySelector('form').reset();
        // Clear validation states
        [matriculeInput, loginInput, nomInput, prenomInputtsk, passwordInput, photoInput].forEach(input => {
            input.classList.remove('is-valid', 'is-invalid');
        });
        delete loginInput.dataset.original;
    });

    // Prevent anchor scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
        });
    });
});
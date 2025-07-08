// Validation des formulaires
document.addEventListener('DOMContentLoaded', function() {
    // Validation en temps réel
    const validateField = (field, regex, errorMessage) => {
        if (!regex.test(field.value)) {
            field.classList.add('is-invalid');
            field.nextElementSibling.textContent = errorMessage;
            return false;
        } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            return true;
        }
    };

    // Validation des noms/prénoms
    const nameRegex = /^[a-zA-ZÀ-ÿ\s\-']+$/;
    document.querySelectorAll('input[name="nom"], input[name="prenom"], input[name="nom_ens"], input[name="prenom_ens"]').forEach(input => {
        input.addEventListener('input', () => {
            validateField(input, nameRegex, "Seules les lettres et espaces sont autorisés");
        });
    });

    // Validation des dates
    const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
    document.querySelectorAll('input[type="date"]').forEach(input => {
        input.addEventListener('change', () => {
            validateField(input, dateRegex, "Format de date invalide (YYYY-MM-DD)");
        });
    });

    // Validation des notes
    const noteRegex = /^(20(\.0{1,2})?|([0-1]?\d)(\.\d{1,2})?)$/;
    document.querySelectorAll('input[name="note"]').forEach(input => {
        input.addEventListener('input', () => {
            validateField(input, noteRegex, "Note doit être entre 0 et 20");
        });
    });

    // Empêcher l'envoi du formulaire si des champs sont invalides
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', (e) => {
            let isValid = true;
            
            // Vérifier tous les champs requis
            form.querySelectorAll('[required]').forEach(field => {
                if (field.value.trim() === '') {
                    field.classList.add('is-invalid');
                    field.nextElementSibling.textContent = "Ce champ est obligatoire";
                    isValid = false;
                }
            });
            
            // Vérifier les champs avec validation spécifique
            form.querySelectorAll('.is-invalid').forEach(field => {
                isValid = false;
            });
            
            if (!isValid) {
                e.preventDefault();
                alert("Veuillez corriger les erreurs avant de soumettre le formulaire.");
            }
        });
    });
});

// Fonction pour confirmer les suppressions
function confirmDelete() {
    return confirm("Êtes-vous sûr de vouloir supprimer cet élément ? Cette action est irréversible.");
}
/**
 * Validation cote client (complementaire a la validation PHP serveur).
 * Aucune bibliotheque / framework : JavaScript natif uniquement.
 */

document.addEventListener('DOMContentLoaded', function () {
    // ---- Bascule du menu lateral en mode mobile ----
    var toggleBtn = document.querySelector('.menu-toggle');
    var sidebar = document.querySelector('.sidebar');
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('open');
        });
    }

    // ---- Validation generique de tous les formulaires marques data-validate ----
    var forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var valid = validateForm(form);
            if (!valid) {
                e.preventDefault();
            }
        });
    });

    // ---- Gestion dynamique des lignes de medicaments dans le formulaire d'ordonnance ----
    var addItemBtn = document.getElementById('add-item-btn');
    if (addItemBtn) {
        addItemBtn.addEventListener('click', function () {
            var container = document.getElementById('items-container');
            var template = document.getElementById('item-template');
            var clone = template.content.cloneNode(true);
            container.appendChild(clone);
        });
    }
    document.addEventListener('click', function (e) {
        if (e.target && e.target.classList.contains('remove-item-btn')) {
            var row = e.target.closest('.item-row');
            if (row) { row.remove(); }
        }
    });

    // ---- Bascule mode clair / mode sombre ----
    // Sans choix explicite, le site suit la preference du systeme.
    // Un clic ecrit data-theme sur <html> et memorise le choix.
    var racine = document.documentElement;
    var boutonsTheme = document.querySelectorAll('[data-theme-toggle]');

    function themeActuel() {
        var explicite = racine.getAttribute('data-theme');
        if (explicite === 'dark' || explicite === 'light') {
            return explicite;
        }
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    function rafraichirBoutons(theme) {
        boutonsTheme.forEach(function (bouton) {
            var icone = bouton.querySelector('.theme-icon');
            var libelle = bouton.querySelector('.theme-label');
            if (icone) { icone.textContent = theme === 'dark' ? '☀' : '🌙'; }
            if (libelle) { libelle.textContent = theme === 'dark' ? 'Mode clair' : 'Mode sombre'; }
            bouton.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
        });
    }

    if (boutonsTheme.length) {
        rafraichirBoutons(themeActuel());

        boutonsTheme.forEach(function (bouton) {
            bouton.addEventListener('click', function () {
                var nouveau = themeActuel() === 'dark' ? 'light' : 'dark';
                racine.setAttribute('data-theme', nouveau);
                try {
                    localStorage.setItem('pharmacie-theme', nouveau);
                } catch (e) {
                    /* navigation privee : le choix vaut pour la page courante */
                }
                rafraichirBoutons(nouveau);
            });
        });
    }
    // ---- Impression / export PDF via le navigateur ----
    document.querySelectorAll('[data-print]').forEach(function (el) {
        el.addEventListener('click', function () {
            window.print();
        });
    });

    // ---- Confirmation avant suppression ----
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            var msg = el.getAttribute('data-confirm') || 'Confirmez-vous cette action ?';
            if (!confirm(msg)) {
                e.preventDefault();
            }
        });
    });
});

function validateForm(form) {
    var isValid = true;
    clearErrors(form);

    // Champs obligatoires
    form.querySelectorAll('[required]').forEach(function (field) {
        if (!field.value || !field.value.toString().trim()) {
            markInvalid(field, 'Ce champ est obligatoire.');
            isValid = false;
        }
    });

    // Emails
    form.querySelectorAll('input[type=email]').forEach(function (field) {
        if (field.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value)) {
            markInvalid(field, 'Adresse email invalide.');
            isValid = false;
        }
    });

    // Mots de passe : min 6 caracteres
    form.querySelectorAll('input[data-min-length]').forEach(function (field) {
        var min = parseInt(field.getAttribute('data-min-length'), 10);
        if (field.value && field.value.length < min) {
            markInvalid(field, 'Doit contenir au moins ' + min + ' caracteres.');
            isValid = false;
        }
    });

    // Confirmation de mot de passe
    var pwd = form.querySelector('[name=mot_de_passe]');
    var confirm = form.querySelector('[name=confirmation]');
    if (pwd && confirm && confirm.value && pwd.value !== confirm.value) {
        markInvalid(confirm, 'Les mots de passe ne correspondent pas.');
        isValid = false;
    }

    // Nombres positifs (prix, stock, quantite ...)
    form.querySelectorAll('input[data-positive-number]').forEach(function (field) {
        if (field.value !== '' && (isNaN(field.value) || parseFloat(field.value) < 0)) {
            markInvalid(field, 'Veuillez saisir un nombre positif.');
            isValid = false;
        }
    });

    return isValid;
}

function markInvalid(field, message) {
    field.classList.add('invalid');
    var error = document.createElement('div');
    error.className = 'field-error';
    error.textContent = message;
    field.insertAdjacentElement('afterend', error);
}

function clearErrors(form) {
    form.querySelectorAll('.invalid').forEach(function (f) { f.classList.remove('invalid'); });
    form.querySelectorAll('.field-error').forEach(function (e) { e.remove(); });
}

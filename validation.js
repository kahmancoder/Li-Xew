/* ============================================
   validation.js — ActuSite
   Validation côté client de tous les formulaires
   Projet lixew — Personne 3
   ============================================ */

/* ── UTILITAIRES ──────────────────────────── */

function showError(fieldId, message) {
  const group = document.getElementById(fieldId).closest(".form-group");
  group.classList.add("has-error");
  group.classList.remove("has-success");
  group.querySelector(".error-msg").textContent = message;
  group.querySelector(".error-msg").style.display = "block";
}

function showSuccess(fieldId) {
  const group = document.getElementById(fieldId).closest(".form-group");
  group.classList.remove("has-error");
  group.classList.add("has-success");
  const errMsg = group.querySelector(".error-msg");
  if (errMsg) errMsg.style.display = "none";
}

function clearErrors(formId) {
  const form = document.getElementById(formId);
  if (!form) return;
  form.querySelectorAll(".form-group").forEach((group) => {
    group.classList.remove("has-error", "has-success");
    const errMsg = group.querySelector(".error-msg");
    if (errMsg) errMsg.style.display = "none";
  });
}

function isEmpty(value) {
  return value.trim() === "";
}

/* ── VALIDATION CONNEXION ─────────────────── */
/*
   Formulaire : #form-connexion
   Champs     : #login, #password
*/
function validerConnexion() {
  clearErrors("form-connexion");
  let valide = true;

  const login = document.getElementById("login");
  const password = document.getElementById("password");

  if (!login || !password) return true;

  if (isEmpty(login.value)) {
    showError("login", "Le login est obligatoire.");
    valide = false;
  } else if (login.value.trim().length < 3) {
    showError("login", "Le login doit contenir au moins 3 caractères.");
    valide = false;
  } else {
    showSuccess("login");
  }

  if (isEmpty(password.value)) {
    showError("password", "Le mot de passe est obligatoire.");
    valide = false;
  } else if (password.value.length < 4) {
    showError(
      "password",
      "Le mot de passe doit contenir au moins 4 caractères.",
    );
    valide = false;
  } else {
    showSuccess("password");
  }

  return valide;
}

/* ── VALIDATION ARTICLE ───────────────────── */
/*
   Formulaire : #form-article
   Champs     : #titre, #description_courte, #contenu_complet, #categorie_id
   Table BD   : article (titre, description_courte, contenu_complet, categorie_id)
*/
function validerArticle() {
  clearErrors("form-article");
  let valide = true;

  const titre = document.getElementById("titre");
  const description = document.getElementById("description_courte");
  const contenu = document.getElementById("contenu_complet");
  const categorie = document.getElementById("categorie_id");

  if (!titre) return true;

  if (isEmpty(titre.value)) {
    showError("titre", "Le titre est obligatoire.");
    valide = false;
  } else if (titre.value.trim().length > 50) {
    showError("titre", "Le titre ne peut pas dépasser 50 caractères.");
    valide = false;
  } else {
    showSuccess("titre");
  }

  if (description && !isEmpty(description.value)) {
    if (description.value.trim().length > 300) {
      showError(
        "description_courte",
        "La description courte ne peut pas dépasser 300 caractères.",
      );
      valide = false;
    } else {
      showSuccess("description_courte");
    }
  }

  if (contenu) {
    if (isEmpty(contenu.value)) {
      showError("contenu_complet", "Le contenu complet est obligatoire.");
      valide = false;
    } else if (contenu.value.trim().length < 20) {
      showError(
        "contenu_complet",
        "Le contenu doit contenir au moins 20 caractères.",
      );
      valide = false;
    } else {
      showSuccess("contenu_complet");
    }
  }

  if (categorie) {
    if (categorie.value === "" || categorie.value === "0") {
      showError("categorie_id", "Veuillez sélectionner une catégorie.");
      valide = false;
    } else {
      showSuccess("categorie_id");
    }
  }

  return valide;
}

/* ── VALIDATION CATEGORIE ─────────────────── */
/*
   Formulaire : #form-categorie
   Champs     : #nom
   Table BD   : categorie (nom)
*/
function validerCategorie() {
  clearErrors("form-categorie");
  let valide = true;

  const nom = document.getElementById("nom");
  if (!nom) return true;

  if (isEmpty(nom.value)) {
    showError("nom", "Le nom de la catégorie est obligatoire.");
    valide = false;
  } else if (nom.value.trim().length < 2) {
    showError("nom", "Le nom doit contenir au moins 2 caractères.");
    valide = false;
  } else if (nom.value.trim().length > 100) {
    showError("nom", "Le nom ne peut pas dépasser 100 caractères.");
    valide = false;
  } else {
    showSuccess("nom");
  }

  return valide;
}

/* ── VALIDATION UTILISATEUR ───────────────── */
/*
   Formulaire : #form-utilisateur
   Champs     : #nom_user, #prenom, #login_user, #password_user, #role
   Table BD   : utilisateur (nom, prenom, login, password, role)
*/
function validerUtilisateur(estModification = false) {
  clearErrors("form-utilisateur");
  let valide = true;

  const nom = document.getElementById("nom_user");
  const prenom = document.getElementById("prenom");
  const login = document.getElementById("login_user");
  const password = document.getElementById("password_user");
  const role = document.getElementById("role");

  if (!nom) return true;

  if (isEmpty(nom.value)) {
    showError("nom_user", "Le nom est obligatoire.");
    valide = false;
  } else if (nom.value.trim().length > 50) {
    showError("nom_user", "Le nom ne peut pas dépasser 50 caractères.");
    valide = false;
  } else {
    showSuccess("nom_user");
  }

  if (prenom) {
    if (isEmpty(prenom.value)) {
      showError("prenom", "Le prénom est obligatoire.");
      valide = false;
    } else if (prenom.value.trim().length > 255) {
      showError("prenom", "Le prénom est trop long.");
      valide = false;
    } else {
      showSuccess("prenom");
    }
  }

  if (login) {
    if (isEmpty(login.value)) {
      showError("login_user", "Le login est obligatoire.");
      valide = false;
    } else if (login.value.trim().length < 3) {
      showError("login_user", "Le login doit contenir au moins 3 caractères.");
      valide = false;
    } else if (login.value.trim().length > 255) {
      showError("login_user", "Le login est trop long.");
      valide = false;
    } else {
      showSuccess("login_user");
    }
  }

  // Mot de passe : obligatoire à la création, optionnel en modification
  if (password) {
    if (!estModification && isEmpty(password.value)) {
      showError("password_user", "Le mot de passe est obligatoire.");
      valide = false;
    } else if (!isEmpty(password.value) && password.value.length < 6) {
      showError(
        "password_user",
        "Le mot de passe doit contenir au moins 6 caractères.",
      );
      valide = false;
    } else if (!isEmpty(password.value)) {
      showSuccess("password_user");
    }
  }

  if (role) {
    if (role.value === "") {
      showError("role", "Veuillez sélectionner un rôle.");
      valide = false;
    } else {
      showSuccess("role");
    }
  }

  return valide;
}

/* ── COMPTEUR DE CARACTÈRES ───────────────── */
/*
   Ajoute un compteur sous un champ texte
   Usage : ajouterCompteur('titre', 50)
*/
function ajouterCompteur(fieldId, maxLength) {
  const field = document.getElementById(fieldId);
  if (!field) return;

  const counter = document.createElement("small");
  counter.style.cssText =
    "color:#999; font-size:12px; float:right; margin-top:3px;";
  counter.textContent = "0 / " + maxLength;
  field.closest(".form-group").appendChild(counter);

  field.addEventListener("input", function () {
    const len = this.value.length;
    counter.textContent = len + " / " + maxLength;
    counter.style.color = len > maxLength ? "#D85A30" : "#999";
  });
}

/* ── CONFIRMATION SUPPRESSION ─────────────── */
/*
   Demande confirmation avant de soumettre
   Usage : <form onsubmit="return confirmerSuppression()">
*/
function confirmerSuppression(message) {
  const msg =
    message ||
    "Êtes-vous sûr de vouloir supprimer cet élément ? Cette action est irréversible.";
  return confirm(msg);
}

/* ── INITIALISATION AU CHARGEMENT ─────────── */
document.addEventListener("DOMContentLoaded", function () {
  // Formulaire connexion
  const formConnexion = document.getElementById("form-connexion");
  if (formConnexion) {
    formConnexion.addEventListener("submit", function (e) {
      if (!validerConnexion()) e.preventDefault();
    });
  }

  // Formulaire article
  const formArticle = document.getElementById("form-article");
  if (formArticle) {
    formArticle.addEventListener("submit", function (e) {
      if (!validerArticle()) e.preventDefault();
    });
    // Compteurs de caractères
    ajouterCompteur("titre", 50);
    ajouterCompteur("description_courte", 300);
  }

  // Formulaire catégorie
  const formCategorie = document.getElementById("form-categorie");
  if (formCategorie) {
    formCategorie.addEventListener("submit", function (e) {
      if (!validerCategorie()) e.preventDefault();
    });
    ajouterCompteur("nom", 100);
  }

  // Formulaire utilisateur (création)
  const formUser = document.getElementById("form-utilisateur");
  if (formUser) {
    const estModif = formUser.dataset.mode === "modification";
    formUser.addEventListener("submit", function (e) {
      if (!validerUtilisateur(estModif)) e.preventDefault();
    });
  }

  // Validation en temps réel sur les champs (feedback immédiat)
  document
    .querySelectorAll(
      ".form-group input, .form-group textarea, .form-group select",
    )
    .forEach(function (field) {
      field.addEventListener("blur", function () {
        if (this.value.trim() !== "") {
          showSuccess(this.id);
        }
      });
    });
});

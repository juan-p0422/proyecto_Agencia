// Manejo de sesión simple con localStorage.
// Guardamos el objeto usuario tal cual devuelve la API (ej: { IdUsuario, Nombre, Correo })

function getCurrentUser() {
  try {
    return JSON.parse(localStorage.getItem("usuario"));
  } catch (e) {
    return null;
  }
}

function renderMenu() {
  const menu = document.getElementById("menu");
  if (!menu) return;
  const user = getCurrentUser();
  if (user) {
    menu.innerHTML = `
      <span class="menu-username">Hola, ${user.Nombre}</span>
      <a href="perfil.html">Mi perfil</a>
      <a href="hoteles.html">Hoteles</a>
      <a href="#" id="logoutLink">Cerrar sesión</a>
    `;
    document.getElementById("logoutLink").addEventListener("click", (e) => {
      e.preventDefault();
      logout();
    });
  } else {
    menu.innerHTML = `
      <a href="login.html">Iniciar sesión</a>
      <a href="register.html">Registrarse</a>
      <a href="hoteles.html">Hoteles</a>
    `;
  }
}

function logout() {
  localStorage.removeItem("usuario");
  location.href = "index.html";
}

/* Login simple por correo (usa GET /api/usuarios y busca por Correo) */
document.addEventListener("DOMContentLoaded", () => {
  renderMenu();

  const loginForm = document.getElementById("loginForm");
  if (loginForm) {
    loginForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const correo = document.getElementById("correo").value.trim();
      try {
        const usuarios = await apiGet("usuarios");
        const found = usuarios.find(u => String(u.Correo).toLowerCase() === correo.toLowerCase());
        if (!found) {
          alert("Correo no registrado. Por favor regístrate.");
          return;
        }
        localStorage.setItem("usuario", JSON.stringify(found));
        location.href = "index.html";
      } catch (err) {
        console.error(err);
        alert("Error intentando iniciar sesión.");
      }
    });
  }

  const registerForm = document.getElementById("registerForm");
  if (registerForm) {
    registerForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const nombre = document.getElementById("nombre").value.trim();
      const correo = document.getElementById("correo").value.trim();
      try {
        const created = await apiPost("usuarios", { Nombre: nombre, Correo: correo });
        // asumimos la API regresa el usuario creado con IdUsuario
        localStorage.setItem("usuario", JSON.stringify(created));
        location.href = "index.html";
      } catch (err) {
        console.error(err);
        alert("Error registrando usuario.");
      }
    });
  }
});

// Exponer utilidades globalmente
window.getCurrentUser = getCurrentUser;
window.renderMenu = renderMenu;
window.logout = logout;

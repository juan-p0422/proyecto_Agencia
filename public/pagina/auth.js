function getCurrentUser() {
    try {
        return JSON.parse(localStorage.getItem("usuario"));
    } catch {
        return null;
    }
}

// Actualiza menú según estado auth
function renderMenu() {
    const menu = document.getElementById("menu");
    if (!menu) return;

    const user = getCurrentUser();

    if (user) {
        menu.innerHTML = `
      <span>Hola, ${user.Nombre}</span>
      <a href="perfil.html">Mi perfil</a>
      <a href="#" onclick="logout()">Cerrar sesión</a>
    `;
    } else {
        menu.innerHTML = `
      <a href="login.html">Iniciar Sesión</a>
      <a href="register.html">Registrarse</a>
    `;
    }
}

function logout() {
    localStorage.removeItem("usuario");
    location.href = "index.html";
}

document.addEventListener("DOMContentLoaded", () => {

    renderMenu();

    const loginForm = document.getElementById("loginForm");
    if (loginForm) {
        loginForm.addEventListener("submit", async e => {
            e.preventDefault();

            const correo = document.getElementById("correo").value;
            const password = document.getElementById("password").value;

            try {
                const usuario = await apiPost("login", {
                    Correo: correo,
                    Password: password
                });

                localStorage.setItem("usuario", JSON.stringify(usuario));
                location.href = "index.html";

            } catch (err) {
                alert("Credenciales incorrectas");
            }
        });
    }

    const registerForm = document.getElementById("registerForm");
    if (registerForm) {
        registerForm.addEventListener("submit", async e => {
            e.preventDefault();

            const nombre = document.getElementById("nombre").value;
            const correo = document.getElementById("correo").value;
            const password = document.getElementById("password").value;
            const password2 = document.getElementById("password2").value;

            if (password !== password2) {
                alert("Las contraseñas no coinciden");
                return;
            }

            try {
                const usuario = await apiPost("usuarios", {
                    Nombre: nombre,
                    Correo: correo,
                    Password: password
                });

                // Se auto loguea
                localStorage.setItem("usuario", JSON.stringify(usuario));
                location.href = "index.html";

            } catch (err) {
                try {
                    const errorData = await err.json();

                    // Detectar si el error es por longitud de contraseña
                    if (errorData.errors && errorData.errors.Password) {
                        alert("La contraseña debe tener al menos 8 caracteres.");
                        return;
                    }

                    // Detectar si el correo ya está registrado
                    if (errorData.errors && errorData.errors.Correo) {
                        alert("El correo ya está registrado.");
                        return;
                    }

                    // Otros errores de validación
                    if (errorData.message) {
                        alert(errorData.message);
                    } else {
                        alert("Error al registrarse. Verifica tus datos.");
                    }

                } catch {
                    alert("Error al registrarse. Verifica tus datos.");
                }
            }
        });
    }
});

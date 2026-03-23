
function showMsg(t) {
  const el = document.getElementById("msg");
  if (el) el.textContent = t || "";
}

function hidePanels() {
  const panelEnroll = document.getElementById("panelEnroll");
  const panelLogin2FA = document.getElementById("panelLogin2FA");
  const qrBox = document.getElementById("qrBox");

  if (panelEnroll) panelEnroll.style.display = "none";
  if (panelLogin2FA) panelLogin2FA.style.display = "none";
  if (qrBox) qrBox.innerHTML = "";
}

function reset2faStorage() {
  localStorage.removeItem("pending_2fa");
  localStorage.removeItem("enroll_2fa");
}

function finishSession(resp) {
  // Esperamos token+usuario (tu backend ya te deja entrar)
  if (resp?.token) localStorage.setItem("token", resp.token);
  if (resp?.usuario) localStorage.setItem("usuario", JSON.stringify(resp.usuario));

  reset2faStorage();
  location.href = "index.html";
}

/* =========
   QR (qrcode.js)
   ========= */
async function drawQR(otpauthUrl) {
  const qrBox = document.getElementById("qrBox");
  if (!qrBox) throw new Error("No existe #qrBox en el HTML.");

  qrBox.innerHTML = "";

  if (!window.QRCode || typeof window.QRCode !== "function") {
    throw new Error("No cargó qrcode.js. Orden correcto: api.js -> qrcode.js -> auth.js");
  }

  new window.QRCode(qrBox, {
    text: otpauthUrl,
    width: 220,
    height: 220,
    colorDark: "#000000",
    colorLight: "#ffffff",
    correctLevel: window.QRCode.CorrectLevel.M,
  });
}

/* =========
   Reanudar flujos por storage
   ========= */
async function showEnrollFromStorageIfAny() {
  const raw = localStorage.getItem("enroll_2fa");
  if (!raw) return false;

  try {
    const payload = JSON.parse(raw);
    if (!payload?.enroll_token || !payload?.otpauthUrl) return false;

    hidePanels();
    document.getElementById("panelEnroll").style.display = "block";
    await drawQR(payload.otpauthUrl);
    showMsg("Escanea el QR y confirma con tu código.");
    return true;
  } catch {
    return false;
  }
}

function showPending2faIfAny() {
  const pending = localStorage.getItem("pending_2fa");
  if (!pending) return false;

  hidePanels();
  document.getElementById("panelLogin2FA").style.display = "block";
  showMsg("Ingresa tu código 2FA.");
  return true;
}

/* =========
   Inicio
   ========= */
document.addEventListener("DOMContentLoaded", async () => {
  try {
    if (await showEnrollFromStorageIfAny()) return;
  } catch (e) {
    console.error(e);
    showMsg(e.message || "Error mostrando QR.");
    reset2faStorage();
  }

  if (showPending2faIfAny()) return;

  // LOGIN
  const loginForm = document.getElementById("loginForm");
  if (loginForm) {
    loginForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      showMsg("");
      hidePanels();

      const correo = document.getElementById("correo").value.trim();
      const password = document.getElementById("password").value;

      try {
        const resp = await apiPost("login", { Correo: correo, Password: password });

        if (resp.two_factor_required) {
          localStorage.setItem("pending_2fa", resp.pending_token);
          document.getElementById("panelLogin2FA").style.display = "block";
          showMsg("Ingresa tu código 2FA.");
          return;
        }

        if (resp.enroll_required) {
          localStorage.setItem("enroll_2fa", JSON.stringify({
            enroll_token: resp.enroll_token,
            otpauthUrl: resp.otpauthUrl,
          }));

          document.getElementById("panelEnroll").style.display = "block";
          await drawQR(resp.otpauthUrl);
          showMsg("Escanea el QR y confirma con tu código.");
          return;
        }

        if (resp.token) {
          finishSession(resp);
          return;
        }

        showMsg("Respuesta inesperada del servidor.");
      } catch (err) {
        console.error("Login error:", err);
        showMsg(err.message || "Error al iniciar sesión.");
      }
    });
  }

  // CONFIRMAR ENROLAMIENTO
  const enrollForm = document.getElementById("enrollForm");
  if (enrollForm) {
    enrollForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      showMsg("");

      let enroll_token = null;
      try { enroll_token = JSON.parse(localStorage.getItem("enroll_2fa"))?.enroll_token; } catch {}

      const raw = document.getElementById("enrollCode").value;
      const code = raw.replace(/\D/g, "").slice(0, 6);

      if (!enroll_token) {
        showMsg("No existe enroll_token. Vuelve a iniciar sesión para generar el QR.");
        hidePanels();
        reset2faStorage();
        return;
      }

      try {
        const resp = await apiPost("2fa/enroll/confirm", { enroll_token, code });
        finishSession(resp);
      } catch (err) {
        console.error("Enroll confirm error:", err);
        showMsg(err.message || "Código inválido o expirado.");
      }
    });
  }

  // VERIFICACIÓN 2FA EN LOGIN
  const login2faForm = document.getElementById("login2faForm");
  if (login2faForm) {
    login2faForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      showMsg("");

      const pending_token = localStorage.getItem("pending_2fa");
      const raw = document.getElementById("login2faCode").value;
      const code = raw.replace(/\D/g, "").slice(0, 6);

      if (!pending_token) {
        showMsg("No existe pending_token. Vuelve a iniciar sesión.");
        hidePanels();
        reset2faStorage();
        return;
      }

      try {
        const resp = await apiPost("login/2fa", { pending_token, code });
        finishSession(resp);
      } catch (err) {
        console.error("Login2FA error:", err);
        showMsg(err.message || "Código inválido o expirado.");
      }
    });
  }
});

/* =========
   REGISTRO (sin auto-login)
   ========= */
document.addEventListener("DOMContentLoaded", () => {
  const registerForm = document.getElementById("registerForm");
  if (!registerForm) return;

  registerForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const nombre = document.getElementById("nombre")?.value?.trim() ?? "";
    const correo = document.getElementById("correo")?.value?.trim() ?? "";
    const password = document.getElementById("password")?.value ?? "";
    const password2 = document.getElementById("password2")?.value ?? "";

    if (!nombre || !correo || !password || !password2) {
      alert("Completa todos los campos.");
      return;
    }

    if (password !== password2) {
      alert("Las contraseñas no coinciden");
      return;
    }

    try {
      await apiPost("usuarios", {
        Nombre: nombre,
        Correo: correo,
        Password: password,
        // por si tu backend valida confirmación estilo Laravel
        Password_confirmation: password2,
      });

      alert("Registro realizado correctamente. Ahora inicia sesión.");
      registerForm.reset();
    } catch (err) {
      const body = err?.body;

      if (body?.errors?.Password) {
        alert("La contraseña debe tener al menos 8 caracteres.");
        return;
      }
      if (body?.errors?.Correo) {
        alert("El correo ya está registrado.");
        return;
      }

      alert(err?.message || body?.message || "Error al registrarse. Verifica tus datos.");
    }
  });
});

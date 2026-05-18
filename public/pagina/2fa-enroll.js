const btnActivar2FA = document.getElementById("btnActivar2FA");
const qrBox = document.getElementById("qrBox");
const confirmForm = document.getElementById("confirm2faForm");
const msg = document.getElementById("msg");

btnActivar2FA.addEventListener("click", async () => {
  msg.textContent = "";
  qrBox.innerHTML = "";
  confirmForm.style.display = "none";

  const Correo = document.getElementById("correo").value.trim();
  const Password = document.getElementById("password").value.trim();

  if (!Correo || !Password) {
    msg.textContent = "Escribe correo y contraseña primero (para validar tu identidad).";
    return;
  }

  try {
    // Endpoint público (sin token)
    const data = await apiPost("2fa/enroll/start", { Correo, Password });

    localStorage.setItem("enroll_2fa_token", data.enroll_token);

    QRCode.toCanvas(data.otpauthUrl, { width: 220, margin: 1 }, (err, canvas) => {
      if (err) {
        msg.textContent = "Error generando el QR.";
        return;
      }
      qrBox.appendChild(canvas);
      confirmForm.style.display = "block";
      msg.textContent = "Escanea el QR en tu app y escribe el código de 6 dígitos.";
    });

  } catch (e) {
    msg.textContent = e?.message || "Error iniciando 2FA. Revisa credenciales o backend.";
  }
});

confirmForm.addEventListener("submit", async (e) => {
  e.preventDefault();
  msg.textContent = "";

  const enroll_token = localStorage.getItem("enroll_2fa_token");
  const code = document.getElementById("code2fa").value.trim();

  if (!enroll_token) {
    msg.textContent = "Primero genera el QR.";
    return;
  }

  try {
    // Endpoint público (sin token)
    const resp = await apiPost("2fa/enroll/confirm", { enroll_token, code });

    // Si el backend devuelve token, ya queda logueado
    if (resp.token) localStorage.setItem("token", resp.token);
    if (resp.usuario) localStorage.setItem("usuario", JSON.stringify(resp.usuario));

    localStorage.removeItem("enroll_2fa_token");

    msg.textContent = "2FA activado. Ya puedes entrar.";
    // opcional: redirigir si ya devolviste token
    // location.href = "index.html";

  } catch (e) {
    msg.textContent = e?.message || "Código inválido o enrolamiento expirado.";
  }
});
/* =========
   Arranque Global de Seguridad
   ========= */
document.addEventListener("DOMContentLoaded", () => {
  if (getToken()) {
    // si no tiene la fecha de expiración guardada, la calculamos
    if (!localStorage.getItem("session_expires_at")) {
      startSessionTimer(getToken());
    }
    //revisamos si ya expiró
    checkSessionInBackground();
    
    // bucle para que revise cada 5 segundos
    initBackgroundSessionCheck();
  }
});

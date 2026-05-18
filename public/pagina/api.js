window.API_URL = window.API_URL || "http://127.0.0.1:8000/api/";
//window.API_URL = window.API_URL || "http://192.168.1.10:8000/api/";

/* =========
   Prevencion XSS
   ========= */
window.escapeHTML = function(str) {
  if (str === null || str === undefined) return "";
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
};

/* =========
   Auth / sesión
   ========= */
function getToken() {
  return localStorage.getItem("token");
}

function authHeaders() {
  const token = getToken();
  return token ? { Authorization: `Bearer ${token}` } : {};
}

function getCurrentUser() {
  try {
    return JSON.parse(localStorage.getItem("usuario"));
  } catch {
    return null;
  }
}

function decodeJwtPayload(token) {
  if (!token || typeof token !== "string") return null;

  try {
    const payload = token.split(".")[1];
    if (!payload) return null;

    let base64 = payload.replace(/-/g, "+").replace(/_/g, "/");
    while (base64.length % 4) base64 += "=";
    const json = decodeURIComponent(
      atob(base64)
        .split("")
        .map((char) => `%${(`00${char.charCodeAt(0).toString(16)}`).slice(-2)}`)
        .join("")
    );

    return JSON.parse(json);
  } catch {
    return null;
  }
}

function startSessionTimer(token = getToken()) {
  const payload = decodeJwtPayload(token);
  const startedAt = payload?.iat ? payload.iat * 1000 : Date.now();
  const expiresAt = payload?.exp ? payload.exp * 1000 : null;

  localStorage.setItem("session_start", String(startedAt));
  if (expiresAt) localStorage.setItem("session_expires_at", String(expiresAt));
  else localStorage.removeItem("session_expires_at");
}

function getSessionStart() {
  const raw = Number(localStorage.getItem("session_start"));
  if (Number.isFinite(raw) && raw > 0) return raw;

  if (getToken()) {
    startSessionTimer();
    return Number(localStorage.getItem("session_start"));
  }

  return null;
}

function getSessionElapsed() {
  const startedAt = getSessionStart();
  return startedAt ? Math.max(Date.now() - startedAt, 0) : 0;
}

function formatSessionTime(ms) {
  const totalSeconds = Math.floor(ms / 1000);
  const hours = Math.floor(totalSeconds / 3600);
  const minutes = Math.floor((totalSeconds % 3600) / 60);
  const seconds = totalSeconds % 60;

  return [hours, minutes, seconds]
    .map((value) => String(value).padStart(2, "0"))
    .join(":");
}

function getSessionExpiration() {
  const raw = Number(localStorage.getItem("session_expires_at"));
  if (Number.isFinite(raw) && raw > 0) return raw;

  if (getToken()) {
    startSessionTimer();
    const expiresAt = Number(localStorage.getItem("session_expires_at"));
    return Number.isFinite(expiresAt) && expiresAt > 0 ? expiresAt : null;
  }

  return null;
}

function isSessionExpired() {
  const expiresAt = getSessionExpiration();
  return expiresAt ? Date.now() >= expiresAt : false;
}

function checkSessionInBackground() {
  if (getToken() && isSessionExpired()) {
    logout();
    return true;
  }

  return false;
}

function initBackgroundSessionCheck() {
  checkSessionInBackground();
  if (window.sessionCheckInterval) clearInterval(window.sessionCheckInterval);
  window.sessionCheckInterval = setInterval(checkSessionInBackground, 5000);
}

function logout() {
  localStorage.removeItem("token");
  localStorage.removeItem("usuario");
  localStorage.removeItem("pending_2fa");
  localStorage.removeItem("enroll_2fa");
  localStorage.removeItem("session_start");
  localStorage.removeItem("session_expires_at");
  if (window.sessionCheckInterval) clearInterval(window.sessionCheckInterval);
  location.href = "index.html";
}

function renderMenu() {
  const menu = document.getElementById("menu");
  if (!menu) return;

  const user = getCurrentUser();

  if (user) {
    initBackgroundSessionCheck();
    menu.innerHTML = `
      <span>Hola, ${window.escapeHTML(user.Nombre ?? "Usuario")}</span>
      <a href="perfil.html">Mi perfil</a>
      <a href="#" id="logoutLink">Cerrar sesión</a>
    `;
    const link = document.getElementById("logoutLink");
    if (link) link.addEventListener("click", (e) => { e.preventDefault(); logout(); });
  } else {
    menu.innerHTML = `
      <a href="login.html">Iniciar Sesión</a>
      <a href="register.html">Registrarse</a>
    `;
  }
}

/* =========
   HTTP helper (con parseo de errores)
   ========= */
async function parseBody(res) {
  const ct = (res.headers.get("content-type") || "").toLowerCase();
  if (res.status === 204) return null;

  if (ct.includes("application/json")) {
    try { return await res.json(); } catch { return null; }
  }
  try { return await res.text(); } catch { return null; }
}

function extractMessage(body, fallback) {
  if (!body) return fallback;
  if (typeof body === "string") return body;
  if (body.message) return body.message;

  if (body.errors && typeof body.errors === "object") {
    const firstKey = Object.keys(body.errors)[0];
    if (firstKey && Array.isArray(body.errors[firstKey]) && body.errors[firstKey][0]) {
      return body.errors[firstKey][0];
    }
  }
  return fallback;
}

function buildError(res, body, endpoint) {
  const fallback = `${res.status} ${res.statusText} -> ${endpoint}`;
  const err = new Error(extractMessage(body, fallback));
  err.status = res.status;
  err.body = body;
  err.endpoint = endpoint;
  return err;
}

async function apiRequest(endpoint, { method = "GET", body = null, headers = {} } = {}) {
  checkSessionInBackground();

  const url = window.API_URL + endpoint;

  const finalHeaders = {
    Accept: "application/json",
    ...authHeaders(),
    ...headers,
  };

  const fetchOptions = { method, headers: finalHeaders };

  if (body !== null && body !== undefined) {
    if (body instanceof FormData) {
      fetchOptions.body = body;
    } else if (typeof body === "object") {
      fetchOptions.headers = {
        "Content-Type": "application/json",
        ...fetchOptions.headers,
      };
      fetchOptions.body = JSON.stringify(body);
    } else {
      fetchOptions.body = body;
    }
  }

  const res = await fetch(url, fetchOptions);
  const parsed = await parseBody(res);

  if (res.status === 401 && getToken()) {
    logout();
    return null;
  }

  if (!res.ok) throw buildError(res, parsed, `${method} ${endpoint}`);
  return parsed;
}

async function apiGet(endpoint) { return apiRequest(endpoint, { method: "GET" }); }
async function apiPost(endpoint, data) { return apiRequest(endpoint, { method: "POST", body: data ?? {} }); }
async function apiPut(endpoint, data) { return apiRequest(endpoint, { method: "PUT", body: data ?? {} }); }
async function apiDelete(endpoint) { return apiRequest(endpoint, { method: "DELETE" }); }

/* =========
   Helpers para recursos 
   ========= */
async function fetchHoteles() { return apiGet("hoteles"); }
async function fetchHabitaciones() { return apiGet("habitaciones"); }
async function fetchTransportes() { return apiGet("transportes"); }
async function fetchDescuentos() { return apiGet("descuentos"); }
async function fetchReservaciones() { return apiGet("reservaciones"); }
async function fetchReservacionUsuarios(idReservacion) {
  return apiGet(`reservaciones/${idReservacion}/usuarios`);
}

async function createReservacion(body) {
  return apiPost("reservaciones", body);
}

async function attachUsuarioToReservacion(reservacionId, usuarioId) {
  return apiPost(`reservaciones/${reservacionId}/usuarios/attach`, { IdUsuario: usuarioId });
}

async function detachUsuarioFromReservacion(idReservacion, idUsuario) {
  return apiPost(`reservaciones/${idReservacion}/usuarios/detach`, { IdUsuario: idUsuario });
}


/* =========
   Encriptación de URL (Base64 + XOR)
   ========= */
const SECRET_KEY = "proyectoAgenciaJEJRJO"; 

function encryptUrlData(obj) {
  const text = JSON.stringify(obj);
  let result = "";
  for (let i = 0; i < text.length; i++) {
    // Operación XOR para ofuscar el texto
    result += String.fromCharCode(text.charCodeAt(i) ^ SECRET_KEY.charCodeAt(i % SECRET_KEY.length));
  }
  // Convertir a Base64 y reemplazar caracteres conflictivos en URLs (+, /, =)
  return btoa(result).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
}

function decryptUrlData(encodedText) {
  if (!encodedText) return null;
  try {
    // Revertir el formato URL-safe a Base64 normal
    let text = encodedText.replace(/-/g, '+').replace(/_/g, '/');
    while (text.length % 4) text += '=';
    let decoded = atob(text);
    
    let result = "";
    for (let i = 0; i < decoded.length; i++) {
      result += String.fromCharCode(decoded.charCodeAt(i) ^ SECRET_KEY.charCodeAt(i % SECRET_KEY.length));
    }
    return JSON.parse(result); // devuelve el original
  } catch (e) {
    console.warn("URL manipulada o inválida");
    return null; 
  }
}

/* =========
   Exports globales
   ========= */
window.apiRequest = apiRequest;
window.apiGet = apiGet;
window.apiPost = apiPost;
window.apiPut = apiPut;
window.apiDelete = apiDelete;

window.fetchHoteles = fetchHoteles;
window.fetchHabitaciones = fetchHabitaciones;
window.fetchTransportes = fetchTransportes;
window.fetchDescuentos = fetchDescuentos;
window.fetchReservaciones = fetchReservaciones;
window.fetchReservacionUsuarios = fetchReservacionUsuarios;

window.createReservacion = createReservacion;
window.attachUsuarioToReservacion = attachUsuarioToReservacion;
window.detachUsuarioFromReservacion = detachUsuarioFromReservacion;

window.getCurrentUser = getCurrentUser;
window.renderMenu = renderMenu;
window.logout = logout;
window.startSessionTimer = startSessionTimer;
window.getSessionElapsed = getSessionElapsed;
window.formatSessionTime = formatSessionTime;
window.checkSessionInBackground = checkSessionInBackground;
window.initBackgroundSessionCheck = initBackgroundSessionCheck;

window.encryptUrlData = encryptUrlData;
window.decryptUrlData = decryptUrlData;

document.addEventListener("DOMContentLoaded", () => {
  if (getToken()) initBackgroundSessionCheck();
});

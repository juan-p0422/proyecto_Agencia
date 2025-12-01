const API_URL = "http://127.0.0.1:8000/api/";

/* básicos */
async function apiGet(endpoint) {
  const res = await fetch(API_URL + endpoint);
  if (!res.ok) throw new Error(`GET ${endpoint} -> ${res.status}`);
  return res.json();
}

async function apiPost(endpoint, data) {
  const res = await fetch(API_URL + endpoint, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(data)
  });
  if (!res.ok) throw new Error(`POST ${endpoint} -> ${res.status}`);
  return res.json();
}

async function apiPut(endpoint, data) {
  const res = await fetch(API_URL + endpoint, {
    method: "PUT",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(data)
  });
  if (!res.ok) throw new Error(`PUT ${endpoint} -> ${res.status}`);
  return res.json();
}

async function apiDelete(endpoint) {
  const res = await fetch(API_URL + endpoint, { method: "DELETE" });
  if (!res.ok) throw new Error(`DELETE ${endpoint} -> ${res.status}`);
  return res.json();
}

/* helpers para recursos específicos */
async function fetchHoteles() {
  return apiGet("hoteles");
}

async function fetchHabitaciones() {
  return apiGet("habitaciones");
}

async function fetchTransportes() {
  return apiGet("transportes");
}

async function fetchDescuentos() {
  return apiGet("descuentos");
}

async function fetchReservaciones() {
  return apiGet("reservaciones");
}

async function fetchReservacionUsuarios(idReservacion) {
  return apiGet(`reservaciones/${idReservacion}/usuarios`);
}

/* acciones: crear reservación y asociar usuario */
async function createReservacion(body) {
  // body debe tener: FechaInicio, FechaFin, PrecioTotal, NumHuespedes, NumHabitaciones, IdHotel, IdTransporte, Estatus
  return apiPost("reservaciones", body);
}

async function attachUsuarioToReservacion(reservacionId, usuarioId) {
    const res = await fetch(`${API_URL}reservaciones/${reservacionId}/usuarios/attach`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            IdUsuario: usuarioId   // <-- ahora se envía en formato correcto
        })
    });

    // Intentar parsear JSON
    try {
        return await res.json();
    } catch (e) {
        console.error("La API respondió con algo que no es JSON:", e);
        return null;
    }
}

async function detachUsuarioFromReservacion(idReservacion, idUsuario) {
  return apiPost(`reservaciones/${idReservacion}/usuarios/detach`, { IdUsuario: idUsuario });
}

/* exportamos en ambiente no-module (global) */
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

// === AUTENTICACIÓN Y MENÚ DE USUARIO ===

let loginModalOpen = false;
let registerModalOpen = false;
let profileModalOpen = false;
let isLoggedIn = false;

const menuBtn = document.getElementById('user-menu-btn');
const menuDropdown = document.getElementById('user-menu-dropdown');

menuBtn.addEventListener('click', function() {
  menuDropdown.style.display = menuDropdown.style.display === 'none' ? 'block' : 'none';
});

// Cerrar menú al hacer clic fuera
document.addEventListener('click', function(e) {
  if (e.target !== menuBtn && !menuDropdown.contains(e.target)) {
    menuDropdown.style.display = 'none';
  }
});

// Aplicar estado UI cuando hay sesión iniciada
function applyLoggedInState(user) {
  isLoggedIn = true;
  document.getElementById('user-name').textContent = user.name || 'Usuario';
  document.getElementById('user-email').textContent = user.email || '';
  const logoutBtn = document.getElementById('logout-btn');
  if (logoutBtn) logoutBtn.style.display = 'block';
  document.querySelectorAll('[onclick="openLoginModal()"]').forEach(btn => btn.style.display = 'none');
}

// Cargar datos del usuario (inicial)
async function loadUser() {
  try {
    const res = await fetch('/api/user');
    if (res.ok) {
      const data = await res.json();
      applyLoggedInState(data);
    }
  } catch (err) {
    console.error('Error loading user:', err);
  }
}

function logout() {
  fetch('/logout', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' } })
    .then(() => { window.location.reload(); })
    .catch(err => console.error('Logout error:', err));
}

// Inicializar
loadUser();

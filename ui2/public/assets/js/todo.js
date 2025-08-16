
// Settings toggle card
function toggleSetting() {
  const card = document.getElementById('settings-card');
  card.classList.toggle('show');
}

// Close settings if clicked outside
window.addEventListener('click', function (e) {
  const card = document.getElementById('settings-card');
  const btn = document.querySelector('.setting-btn');
  if (!card.contains(e.target) && !btn.contains(e.target)) {
    card.classList.remove('show');
  }
});

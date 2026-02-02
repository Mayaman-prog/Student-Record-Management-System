(function () {
  document.addEventListener("click", (e) => {
    const btn = e.target.closest("[data-toggle-password]");
    if (!btn) return;
    const id = btn.getAttribute("data-toggle-password");
    const input = document.getElementById(id);
    if (!input) return;
    input.type = (input.type === "password") ? "text" : "password";
    btn.textContent = (input.type === "password") ? "Show" : "Hide";
  });

  document.addEventListener("click", (e) => {
    const a = e.target.closest("[data-confirm]");
    if (!a) return;
    const msg = a.getAttribute("data-confirm") || "Are you sure?";
    if (!confirm(msg)) e.preventDefault();
  });

  const notice = document.querySelector(".notice[data-autohide='1']");
  if (notice) setTimeout(() => notice.remove(), 2500);
})();
document.getElementById("cpwp-test-storage")?.addEventListener("click", async (event) => {
  const button = event.currentTarget;
  const result = document.getElementById("cpwp-storage-result");
  button.disabled = true;
  result.textContent = "Testing...";
  const body = new URLSearchParams({ action: "cpwp_test_storage", nonce: cpwpStorage.nonce });
  try {
    const response = await fetch(cpwpStorage.ajaxUrl, { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body });
    const data = await response.json();
    result.textContent = data.data?.message || "Connection test failed.";
    result.className = data.success ? "cpwp-storage-success" : "cpwp-storage-error";
  } catch {
    result.textContent = "Connection test failed.";
    result.className = "cpwp-storage-error";
  }
  button.disabled = false;
});

const openTab = id => {
  document.querySelectorAll(".cpwp-tab").forEach(tab => tab.classList.toggle("is-active", tab.dataset.tab === id));
  document.querySelectorAll(".cpwp-tab-panel").forEach(panel => panel.classList.toggle("is-active", panel.dataset.panel === id));
  window.localStorage.setItem("cpwpSettingsTab", id);
};
document.querySelectorAll(".cpwp-tab").forEach(tab => tab.addEventListener("click", () => openTab(tab.dataset.tab)));
openTab(window.localStorage.getItem("cpwpSettingsTab") || "branding");

const ajaxAction = async (action, nonce, extra = {}) => {
  const body = new URLSearchParams({ action, nonce, ...extra });
  const response = await fetch(cpwpStorage.ajaxUrl, { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body });
  return response.json();
};

document.getElementById("cpwp-list-storage")?.addEventListener("click", async () => {
  const target = document.getElementById("cpwp-storage-files");
  target.textContent = "Loading...";
  const data = await ajaxAction("cpwp_list_storage", cpwpStorage.manageNonce);
  if (!data.success) return target.textContent = data.data?.message || "Could not load files.";
  target.innerHTML = `<table class="widefat striped"><thead><tr><th>File</th><th>Size</th><th>Actions</th></tr></thead><tbody>${data.data.files.map(file => `<tr><td>${escapeHtml(file.key)}</td><td>${escapeHtml(file.size)}</td><td><button type="button" class="button cpwp-copy" data-url="${escapeHtml(file.url)}">Copy URL</button> <button type="button" class="button-link-delete cpwp-delete" data-key="${escapeHtml(file.key)}">Delete</button></td></tr>`).join("")}</tbody></table>`;
});
document.addEventListener("click", async event => {
  if (event.target.matches(".cpwp-copy")) await navigator.clipboard.writeText(event.target.dataset.url);
  if (event.target.matches(".cpwp-delete") && confirm("Delete this storage file?")) {
    const data = await ajaxAction("cpwp_delete_storage", cpwpStorage.manageNonce, { key: event.target.dataset.key });
    if (data.success) event.target.closest("tr").remove(); else alert(data.data?.message || "Delete failed.");
  }
});
document.getElementById("cpwp-export-settings")?.addEventListener("click", async () => {
  const data = await ajaxAction("cpwp_export_settings", cpwpStorage.settingsNonce);
  if (data.success) { const blob = new Blob([JSON.stringify(data.data.settings, null, 2)], { type: "application/json" }); const link = Object.assign(document.createElement("a"), { href: URL.createObjectURL(blob), download: "cpwp-settings.json" }); link.click(); }
});
document.getElementById("cpwp-import-settings")?.addEventListener("click", () => {
  const input = Object.assign(document.createElement("input"), { type: "file", accept: "application/json" }); input.click();
  input.onchange = async () => { const data = await ajaxAction("cpwp_import_settings", cpwpStorage.settingsNonce, { settings: await input.files[0].text() }); if (data.success) location.reload(); else alert(data.data?.message || "Import failed."); };
});
document.getElementById("cpwp-reset-settings")?.addEventListener("click", async () => { if (confirm("Reset all CP settings?")) { const data = await ajaxAction("cpwp_reset_settings", cpwpStorage.settingsNonce); if (data.success) location.reload(); } });

let dragged;
document.querySelectorAll(".cpwp-sortable li").forEach(item => {
  item.addEventListener("dragstart", () => { dragged = item; });
  item.addEventListener("dragover", event => { event.preventDefault(); if (dragged !== item) item.parentNode.insertBefore(dragged, item); });
  item.addEventListener("drop", () => { document.querySelector('[name="cpwp_settings[home_section_order]"]').value = [...document.querySelectorAll(".cpwp-sortable li")].map(item => item.dataset.value).join(","); });
});
const escapeHtml = value => String(value).replace(/[&<>"']/g, char => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;" })[char]);

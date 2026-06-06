document.addEventListener("click", (event) => {
  const storageButton = event.target.closest(".cpwp-storage-upload");
  if (storageButton) {
    uploadToStorage(storageButton);
  }
  const mediaButton = event.target.closest(".cpwp-media-select");
  if (mediaButton) {
    const input = document.querySelector(`[name="${mediaButton.dataset.target}"]`);
    const frame = wp.media({ title: "Select media", button: { text: "Use this file" }, multiple: false });
    frame.on("select", () => {
      input.value = frame.state().get("selection").first().toJSON().url;
      input.dispatchEvent(new Event("input", { bubbles: true }));
    });
    frame.open();
  }

  const addButton = event.target.closest(".cpwp-add");
  if (addButton) {
    const target = document.getElementById(addButton.dataset.target);
    const template = document.getElementById(target.dataset.template);
    const index = `${Date.now()}-${target.children.length}`;
    target.insertAdjacentHTML("beforeend", template.innerHTML.replaceAll("__INDEX__", index));
  }

  const removeButton = event.target.closest(".cpwp-remove");
  if (removeButton) {
    removeButton.closest(".cpwp-repeater-row").remove();
  }
});

const preview = document.getElementById("cpwp-video-preview");
const previewEmpty = document.getElementById("cpwp-preview-empty");
const videoInputs = ["_cpwp_mp4", "_cpwp_webm", "_cpwp_ogg"].map((name) => document.querySelector(`[name="${name}"]`));

function updatePreview() {
  const source = videoInputs.find((input) => input?.value)?.value;
  preview.hidden = !source;
  previewEmpty.hidden = Boolean(source);
  if (source && preview.src !== source) preview.src = source;
}

videoInputs.forEach((input) => input?.addEventListener("input", updatePreview));
updatePreview();

async function uploadToStorage(button) {
  const picker = document.createElement("input");
  picker.type = "file";
  picker.accept = button.dataset.target === "subtitle-new" ? ".vtt,.srt,text/vtt" : "video/*,image/*";
  picker.click();
  picker.addEventListener("change", async () => {
    const file = picker.files[0];
    if (!file) return;
    const original = button.textContent;
    button.disabled = true;
    button.textContent = "Preparing...";
    try {
      const body = new URLSearchParams({ action: "cpwp_presign_upload", nonce: cpwpVideoStorage.nonce, filename: file.name, content_type: file.type || "application/octet-stream" });
      const signedResponse = await fetch(cpwpVideoStorage.ajaxUrl, { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body });
      const signed = await signedResponse.json();
      if (!signed.success) throw new Error(signed.data?.message || "Could not prepare upload.");
      button.textContent = "Uploading...";
      await uploadFileWithProgress(signed.data.upload_url, signed.data.content_type, file, percent => { button.textContent = `Uploading ${percent}%`; });
      if (button.dataset.target === "subtitle-new") {
        document.querySelector('[data-target="cpwp-subtitles"]').click();
        const rows = document.querySelectorAll("#cpwp-subtitles .cpwp-repeater-row");
        rows[rows.length - 1].querySelector('input[type="url"]').value = signed.data.public_url;
      } else {
        const input = document.querySelector(`[name="${button.dataset.target}"]`);
        input.value = signed.data.public_url;
        input.dispatchEvent(new Event("input", { bubbles: true }));
      }
      button.textContent = "Uploaded";
      window.setTimeout(() => { button.textContent = original; }, 1800);
    } catch (error) {
      window.alert(error.message);
      button.textContent = original;
    }
    button.disabled = false;
  }, { once: true });
}

function uploadFileWithProgress(url, contentType, file, onProgress) {
  return new Promise((resolve, reject) => {
    const request = new XMLHttpRequest();
    request.open("PUT", url);
    request.setRequestHeader("Content-Type", contentType);
    request.upload.addEventListener("progress", event => { if (event.lengthComputable) onProgress(Math.round((event.loaded / event.total) * 100)); });
    request.addEventListener("load", () => request.status >= 200 && request.status < 300 ? resolve() : reject(new Error(`Storage upload failed: HTTP ${request.status}`)));
    request.addEventListener("error", () => reject(new Error("Storage upload failed due to a network error.")));
    request.send(file);
  });
}

document.getElementById("cpwp-channel-upload")?.addEventListener("click", () => {
  const picker = Object.assign(document.createElement("input"), { type: "file", accept: "video/*" });
  picker.click();
  picker.addEventListener("change", async () => {
    const file = picker.files[0];
    if (!file) return;
    const button = document.getElementById("cpwp-channel-upload");
    button.disabled = true;
    button.textContent = "Preparing upload...";
    try {
      const body = new URLSearchParams({ action: "cpwp_channel_presign_upload", nonce: cpwpChannel.nonce, filename: file.name, content_type: file.type || "video/mp4" });
      const response = await fetch(cpwpChannel.ajaxUrl, { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body });
      const signed = await response.json();
      if (!signed.success) throw new Error(signed.data?.message || "Could not prepare upload.");
      button.textContent = "Uploading...";
      const upload = await fetch(signed.data.upload_url, { method: "PUT", headers: { "Content-Type": signed.data.content_type }, body: file });
      if (!upload.ok) throw new Error(`Upload failed: HTTP ${upload.status}`);
      document.getElementById("cpwp-channel-video-url").value = signed.data.public_url;
      button.textContent = "Uploaded";
    } catch (error) {
      window.alert(error.message);
      button.textContent = "Upload video to my bucket";
    }
    button.disabled = false;
  }, { once: true });
});

document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("cp-upload-wizard-form");
  if (!form) return;

  const dropzone = document.getElementById("cp-upload-dropzone");
  const fileInput = document.getElementById("cp-upload-file-input");
  const selectFileBtn = document.getElementById("cp-select-file-btn");
  const progressContainer = document.getElementById("cp-progress-container");
  const progressBar = document.getElementById("cp-progress-bar");
  const progressPct = document.getElementById("cp-progress-pct");
  const progressFilename = document.getElementById("cp-progress-filename");
  const progressSpeed = document.getElementById("cp-progress-speed");
  const progressEta = document.getElementById("cp-progress-eta");

  const stepIndicators = document.querySelectorAll(".cp-upload-step");
  const stepPanels = document.querySelectorAll(".cp-upload-panel");
  let currentStep = 1;

  // Drag and drop handlers
  dropzone.addEventListener("dragover", (e) => {
    e.preventDefault();
    dropzone.classList.add("is-dragover");
  });

  dropzone.addEventListener("dragleave", () => {
    dropzone.classList.remove("is-dragover");
  });

  dropzone.addEventListener("drop", (e) => {
    e.preventDefault();
    dropzone.classList.remove("is-dragover");
    const file = e.dataTransfer.files[0];
    if (file) handleVideoUpload(file);
  });

  selectFileBtn.addEventListener("click", () => fileInput.click());
  fileInput.addEventListener("change", () => {
    const file = fileInput.files[0];
    if (file) handleVideoUpload(file);
  });

  // Navigation handlers
  document.querySelectorAll(".cp-next-step").forEach(btn => {
    btn.addEventListener("click", () => {
      if (currentStep === 2) {
        const titleVal = document.getElementById("upload-title").value.trim();
        const urlVal = document.getElementById("upload-video-url").value.trim();
        if (!titleVal || !urlVal) {
          alert("Please fill in the title and wait for the video upload to complete.");
          return;
        }
      }
      goToStep(currentStep + 1);
    });
  });

  document.querySelectorAll(".cp-prev-step").forEach(btn => {
    btn.addEventListener("click", () => {
      goToStep(currentStep - 1);
    });
  });

  function goToStep(step) {
    if (step < 1 || step > 4) return;
    currentStep = step;

    stepIndicators.forEach(ind => {
      const s = parseInt(ind.dataset.step);
      ind.classList.remove("is-active");
      if (s === currentStep) ind.classList.add("is-active");
    });

    stepPanels.forEach(panel => {
      const p = parseInt(panel.dataset.stepPanel);
      panel.classList.remove("is-active");
      if (p === currentStep) panel.classList.add("is-active");
    });
  }

  // Upload Thumbnail Handler
  const uploadThumbBtn = document.getElementById("cp-upload-thumbnail-btn");
  const thumbUrlInput = document.getElementById("upload-poster-url");
  if (uploadThumbBtn) {
    uploadThumbBtn.addEventListener("click", () => handleBucketFileUpload(thumbUrlInput, "image/*", uploadThumbBtn));
  }

  // Chapter Row Handlers
  const chaptersBody = document.querySelector("#upload-chapters-table tbody");
  document.getElementById("upload-add-chapter").addEventListener("click", () => {
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td><input type="number" class="ch-time" value="0" min="0" step="any" required /></td>
      <td><input type="text" class="ch-title" placeholder="Introduction" required /></td>
      <td><button type="button" class="cp-button cp-button-secondary cp-row-del">Remove</button></td>
    `;
    tr.querySelector(".cp-row-del").addEventListener("click", () => tr.remove());
    chaptersBody.appendChild(tr);
  });

  // Subtitle Row Handlers
  const subtitlesBody = document.querySelector("#upload-subtitles-table tbody");
  document.getElementById("upload-add-subtitle").addEventListener("click", () => {
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td><input type="text" class="sub-lang" placeholder="en" required /></td>
      <td><input type="text" class="sub-label" placeholder="English" /></td>
      <td>
        <div class="cp-sub-upload-cell">
          <input type="url" class="sub-src" placeholder="https://..." required />
          <button type="button" class="cp-button cp-btn-sub-upload">Upload VTT</button>
        </div>
      </td>
      <td><input type="radio" class="sub-default" name="upload_sub_default" /></td>
      <td><button type="button" class="cp-button cp-button-secondary cp-row-del">Remove</button></td>
    `;
    tr.querySelector(".cp-row-del").addEventListener("click", () => tr.remove());
    tr.querySelector(".cp-btn-sub-upload").addEventListener("click", (e) => {
      const input = tr.querySelector(".sub-src");
      handleBucketFileUpload(input, "text/vtt", e.target);
    });
    subtitlesBody.appendChild(tr);
  });

  // Publish Form submit handler
  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const publishBtn = document.getElementById("cp-publish-btn");
    publishBtn.disabled = true;
    publishBtn.textContent = "Publishing...";

    // Serialize chapters
    const chapters = [];
    chaptersBody.querySelectorAll("tr").forEach(row => {
      const time = parseFloat(row.querySelector(".ch-time").value) || 0;
      const title = row.querySelector(".ch-title").value.trim();
      if (title) chapters.push({ time, title });
    });

    // Serialize subtitles
    const subtitles = [];
    subtitlesBody.querySelectorAll("tr").forEach(row => {
      const srclang = row.querySelector(".sub-lang").value.trim();
      const label = row.querySelector(".sub-label").value.trim();
      const src = row.querySelector(".sub-src").value.trim();
      const isDefault = row.querySelector(".sub-default").checked;
      if (srclang && src) subtitles.push({ srclang, label: label || srclang, src, default: isDefault });
    });

    const formData = new FormData();
    formData.append("action", "cpwp_publish_channel_video");
    formData.append("cpwp_auth_nonce", form.querySelector('input[name="cpwp_auth_nonce"]').value);
    formData.append("cpwp_channel_video_nonce", form.querySelector('input[name="cpwp_channel_video_nonce"]').value);
    formData.append("channel_video_title", document.getElementById("upload-title").value);
    formData.append("channel_video_description", document.getElementById("upload-description").value);
    formData.append("channel_video_url", document.getElementById("upload-video-url").value);
    formData.append("poster_url", thumbUrlInput.value);
    formData.append("post_status", document.getElementById("upload-status").value);
    formData.append("accent_color", form.querySelector('input[name="accent_color"]').value);
    formData.append("preload", form.querySelector('select[name="preload"]').value);
    formData.append("autoplay", form.querySelector('input[name="autoplay"]').checked ? "1" : "");
    formData.append("loop", form.querySelector('input[name="loop"]').checked ? "1" : "");
    formData.append("muted", form.querySelector('input[name="muted"]').checked ? "1" : "");
    formData.append("allow_comments", document.getElementById("upload-allow-comments").checked ? "1" : "");
    formData.append("chapters", JSON.stringify(chapters));
    formData.append("subtitles", JSON.stringify(subtitles));

    try {
      const response = await fetch(cpwpUpload.ajaxUrl, {
        method: "POST",
        body: formData
      });
      const txt = await response.text();
      // Since it redirects or loads wp template redirects, let's parse result. 
      // The core publish_channel_video logic redirects, or prints a message. 
      // Because we submitted via AJAX, let's check response
      if (txt.includes("Your channel video has been published")) {
        alert("Video published successfully!");
        window.location.href = cpwpUpload.ajaxUrl.replace("wp-admin/admin-ajax.php", "discover/studio/");
      } else {
        // If there's an error message outputted, display it.
        const parser = new DOMParser();
        const doc = parser.parseFromString(txt, "text/html");
        const errorDiv = doc.querySelector(".cp-auth-error");
        throw new Error(errorDiv ? errorDiv.textContent : "Could not publish video. Make sure bucket permissions are open.");
      }
    } catch (error) {
      alert(error.message);
      publishBtn.disabled = false;
      publishBtn.textContent = "Publish Video";
    }
  });

  // Video File Uploader
  let uploadXhr = null;
  function handleVideoUpload(file) {
    if (!file) return;
    if (uploadXhr) uploadXhr.abort();

    dropzone.style.display = "none";
    progressContainer.style.display = "block";
    progressContainer.classList.add("is-uploading");
    progressFilename.textContent = file.name;
    progressBar.style.width = "0%";
    progressPct.textContent = "0%";

    const startTime = Date.now();

    const body = new URLSearchParams({
      action: "cpwp_channel_presign_upload",
      nonce: cpwpUpload.nonce,
      filename: file.name,
      content_type: file.type || "video/mp4"
    });

    fetch(cpwpUpload.ajaxUrl, {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body
    })
      .then(res => res.json())
      .then(signed => {
        if (!signed.success) throw new Error(signed.data?.message || "Could not prepare S3 upload.");

        let smoothedSpeed = 0;
        let lastLoaded = 0;
        let lastTime = Date.now();

        uploadXhr = new XMLHttpRequest();
        uploadXhr.open("PUT", signed.data.upload_url, true);
        uploadXhr.setRequestHeader("Content-Type", signed.data.content_type);

        uploadXhr.upload.addEventListener("progress", (e) => {
          if (!e.lengthComputable) return;
          const pct = Math.round((e.loaded / e.total) * 100);
          progressBar.style.width = `${pct}%`;
          progressPct.textContent = `${pct}%`;

          const now = Date.now();
          const elapsed = (now - lastTime) / 1000;
          if (elapsed > 0.5 || smoothedSpeed === 0) {
            const currentSpeed = (e.loaded - lastLoaded) / Math.max(elapsed, 0.1);
            smoothedSpeed = smoothedSpeed === 0 ? currentSpeed : (smoothedSpeed * 0.8 + currentSpeed * 0.2);
            lastTime = now;
            lastLoaded = e.loaded;
          }

          const remainingBytes = e.total - e.loaded;
          const eta = smoothedSpeed > 0 ? Math.round(remainingBytes / smoothedSpeed) : 0;

          progressSpeed.textContent = formatSpeed(smoothedSpeed);
          progressEta.textContent = `ETA: ${formatTime(eta)}`;
        });

        uploadXhr.addEventListener("load", () => {
          if (uploadXhr.status >= 200 && uploadXhr.status < 300) {
            document.getElementById("upload-video-url").value = signed.data.public_url;
            // Pre-populate title with filename (without extension)
            const cleanName = file.name.substring(0, file.name.lastIndexOf('.')) || file.name;
            document.getElementById("upload-title").value = cleanName;
            
            goToStep(2);
          } else {
            alert(`S3 upload failed: HTTP ${uploadXhr.status}`);
            resetUploader();
          }
        });

        uploadXhr.addEventListener("error", () => {
          alert("S3 upload encountered a network error.");
          resetUploader();
        });

        uploadXhr.send(file);
      })
      .catch(error => {
        alert(error.message);
        resetUploader();
      });
  }

  function resetUploader() {
    dropzone.style.display = "block";
    progressContainer.style.display = "none";
    fileInput.value = "";
    uploadXhr = null;
  }

  // S3 Presigned Upload helper for thumbnails and subtitles
  function handleBucketFileUpload(urlInput, accept, button) {
    const picker = Object.assign(document.createElement("input"), { type: "file", accept });
    picker.click();
    picker.addEventListener("change", async () => {
      const file = picker.files[0];
      if (!file) return;

      const originalText = button.textContent;
      button.disabled = true;
      button.textContent = "Uploading...";

      try {
        const body = new URLSearchParams({
          action: "cpwp_channel_presign_upload",
          nonce: cpwpUpload.nonce,
          filename: file.name,
          content_type: file.type || "application/octet-stream"
        });
        const response = await fetch(cpwpUpload.ajaxUrl, {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body
        });
        const signed = await response.json();
        if (!signed.success) throw new Error(signed.data?.message || "Could not prepare S3 upload.");

        const upload = await fetch(signed.data.upload_url, {
          method: "PUT",
          headers: { "Content-Type": signed.data.content_type },
          body: file
        });
        if (!upload.ok) throw new Error(`Upload failed: HTTP ${upload.status}`);

        urlInput.value = signed.data.public_url;
        button.textContent = "Uploaded";
        setTimeout(() => { button.textContent = originalText; button.disabled = false; }, 2000);
      } catch (error) {
        alert(error.message);
        button.textContent = originalText;
        button.disabled = false;
      }
    }, { once: true });
  }

  function formatSpeed(bytesPerSec) {
    if (bytesPerSec > 1024 * 1024) return `${(bytesPerSec / (1024 * 1024)).toFixed(1)} MB/s`;
    if (bytesPerSec > 1024) return `${(bytesPerSec / 1024).toFixed(1)} KB/s`;
    return `${bytesPerSec.toFixed(0)} B/s`;
  }

  function formatTime(seconds) {
    if (seconds > 3600) {
      const hrs = Math.floor(seconds / 3600);
      const mins = Math.floor((seconds % 3600) / 60);
      return `${hrs}h ${mins}m`;
    }
    if (seconds > 60) {
      const mins = Math.floor(seconds / 60);
      const secs = seconds % 60;
      return `${mins}m ${secs}s`;
    }
    return `${seconds}s`;
  }
});

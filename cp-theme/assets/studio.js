document.addEventListener("DOMContentLoaded", () => {
  // Tab Switching
  const tabs = document.querySelectorAll(".cp-studio-tab");
  const panels = document.querySelectorAll(".cp-studio-panel");

  tabs.forEach(tab => {
    tab.addEventListener("click", () => {
      const target = tab.dataset.tab;
      tabs.forEach(t => t.classList.remove("is-active"));
      panels.forEach(p => p.classList.remove("is-active"));

      tab.classList.add("is-active");
      const targetPanel = document.querySelector(`.cp-studio-panel[data-panel="${target}"]`);
      if (targetPanel) targetPanel.classList.add("is-active");
    });
  });

  // Video Management (Toggle & Delete)
  const contentPanel = document.querySelector('.cp-studio-panel[data-panel="content"]');
  if (contentPanel) {
    contentPanel.addEventListener("click", async (e) => {
      const toggleBtn = e.target.closest(".cp-btn-toggle");
      const deleteBtn = e.target.closest(".cp-btn-delete");
      if (!toggleBtn && !deleteBtn) return;

      const row = e.target.closest("tr");
      const videoId = row.dataset.videoId;
      const action = toggleBtn ? "toggle_status" : "delete";

      if (action === "delete" && !confirm("Are you sure you want to permanently delete this video?")) return;

      e.target.disabled = true;
      try {
        const response = await fetch(`${cpwpStudio.restUrl}/creator/video/${videoId}`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": cpwpStudio.nonce
          },
          body: JSON.stringify({ action })
        });
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.message || "Action failed.");

        if (action === "delete") {
          row.remove();
        } else {
          const statusCell = row.querySelector(".cp-video-status");
          const currentStatus = statusCell.textContent.trim().toLowerCase();
          const nextStatus = currentStatus === "publish" ? "Draft" : "Publish";
          statusCell.textContent = nextStatus;
          toggleBtn.textContent = nextStatus === "Publish" ? "Draft" : "Publish";
        }
      } catch (error) {
        alert(error.message);
      } finally {
        e.target.disabled = false;
      }
    });
  }

  // Comment Moderation
  const commentsPanel = document.querySelector('.cp-studio-panel[data-panel="comments"]');
  if (commentsPanel) {
    commentsPanel.addEventListener("click", async (e) => {
      const toggleBtn = e.target.closest(".cp-btn-comment-toggle");
      const deleteBtn = e.target.closest(".cp-btn-comment-delete");
      if (!toggleBtn && !deleteBtn) return;

      const row = e.target.closest("tr");
      const commentId = row.dataset.commentId;
      const action = deleteBtn ? "delete" : toggleBtn.dataset.action;

      if (action === "delete" && !confirm("Are you sure you want to delete this comment?")) return;

      e.target.disabled = true;
      try {
        const response = await fetch(`${cpwpStudio.restUrl}/creator/comments/${commentId}`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": cpwpStudio.nonce
          },
          body: JSON.stringify({ action })
        });
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.message || "Comment action failed.");

        if (action === "delete") {
          row.remove();
        } else {
          const statusCell = row.querySelector(".cp-comment-status");
          if (action === "approve") {
            statusCell.textContent = "Approved";
            toggleBtn.textContent = "Hold";
            toggleBtn.dataset.action = "unapprove";
          } else {
            statusCell.textContent = "Pending";
            toggleBtn.textContent = "Approve";
            toggleBtn.dataset.action = "approve";
          }
        }
      } catch (error) {
        alert(error.message);
      } finally {
        e.target.disabled = false;
      }
    });
  }

  // Edit Video Modal
  const editModal = document.getElementById("cp-edit-modal");
  const editForm = document.getElementById("cp-edit-video-form");
  const chaptersBody = document.querySelector("#edit-chapters-table tbody");
  const subtitlesBody = document.querySelector("#edit-subtitles-table tbody");

  if (contentPanel && editModal) {
    contentPanel.addEventListener("click", (e) => {
      const editBtn = e.target.closest(".cp-btn-edit");
      if (!editBtn) return;

      // Reset Tables
      chaptersBody.innerHTML = "";
      subtitlesBody.innerHTML = "";

      // Populate basic details
      document.getElementById("edit-video-id").value = editBtn.dataset.id;
      document.getElementById("edit-video-title").value = editBtn.dataset.title;
      document.getElementById("edit-video-description").value = editBtn.dataset.description;
      document.getElementById("edit-video-url").value = editBtn.dataset.videoUrl;
      document.getElementById("edit-video-poster").value = editBtn.dataset.poster;
      document.getElementById("edit-video-autoplay").checked = editBtn.dataset.autoplay === "1";
      document.getElementById("edit-video-loop").checked = editBtn.dataset.loop === "1";
      document.getElementById("edit-video-muted").checked = editBtn.dataset.muted === "1";
      document.getElementById("edit-video-allow-comments").checked = editBtn.dataset.allowComments === "1";
      document.getElementById("edit-video-preload").value = editBtn.dataset.preload || "metadata";
      document.getElementById("edit-video-accent").value = editBtn.dataset.accent || "#6d5dfc";

      const genreSelect = document.getElementById("edit-video-genre");
      if (genreSelect) genreSelect.value = editBtn.dataset.genre || "";

      const topicSelect = document.getElementById("edit-video-topic");
      if (topicSelect) topicSelect.value = editBtn.dataset.topic || "";

      const gameSelect = document.getElementById("edit-video-game");
      if (gameSelect) gameSelect.value = editBtn.dataset.game || "";

      const tagsInput = document.getElementById("edit-video-tags");
      if (tagsInput) tagsInput.value = editBtn.dataset.tags || "";


      // Populate Chapters
      try {
        const chapters = JSON.parse(editBtn.dataset.chapters || "[]");
        chapters.forEach(ch => addChapterRow(ch.time, ch.title, chaptersBody));
      } catch (err) {}

      // Populate Subtitles
      try {
        const subtitles = JSON.parse(editBtn.dataset.subtitles || "[]");
        subtitles.forEach(sub => addSubtitleRow(sub.srclang, sub.label, sub.src, sub.default, subtitlesBody));
      } catch (err) {}

      editModal.style.display = "flex";
      editModal.setAttribute("aria-modal", "true");
      editModal.setAttribute("role", "dialog");
      const firstInput = editModal.querySelector("input, textarea, select");
      if (firstInput) firstInput.focus();
    });

    document.getElementById("cp-close-modal").addEventListener("click", () => editModal.style.display = "none");
    document.getElementById("cp-cancel-modal").addEventListener("click", () => editModal.style.display = "none");

    // Add row handlers
    document.getElementById("edit-add-chapter").addEventListener("click", () => addChapterRow(0, "", chaptersBody));
    document.getElementById("edit-add-subtitle").addEventListener("click", () => addSubtitleRow("", "", "", false, subtitlesBody));

    // Submit form handler
    editForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const videoId = document.getElementById("edit-video-id").value;
      const submitBtn = editForm.querySelector('button[type="submit"]');
      submitBtn.disabled = true;

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

      const payload = {
        action: "update",
        title: document.getElementById("edit-video-title").value,
        description: document.getElementById("edit-video-description").value,
        video_url: document.getElementById("edit-video-url").value,
        poster_url: document.getElementById("edit-video-poster").value,
        autoplay: document.getElementById("edit-video-autoplay").checked ? 1 : 0,
        loop: document.getElementById("edit-video-loop").checked ? 1 : 0,
        muted: document.getElementById("edit-video-muted").checked ? 1 : 0,
        allow_comments: document.getElementById("edit-video-allow-comments").checked ? 1 : 0,
        preload: document.getElementById("edit-video-preload").value,
        accent_color: document.getElementById("edit-video-accent").value,
        chapters: JSON.stringify(chapters),
        subtitles: JSON.stringify(subtitles)
      };

      const genreSelectSave = document.getElementById("edit-video-genre");
      if (genreSelectSave) payload.video_genre = genreSelectSave.value;

      const topicSelectSave = document.getElementById("edit-video-topic");
      if (topicSelectSave) payload.video_topic = topicSelectSave.value;

      const gameSelectSave = document.getElementById("edit-video-game");
      if (gameSelectSave) payload.video_game = gameSelectSave.value;

      const tagsInputSave = document.getElementById("edit-video-tags");
      if (tagsInputSave) payload.video_tags = tagsInputSave.value;


      try {
        const response = await fetch(`${cpwpStudio.restUrl}/creator/video/${videoId}`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": cpwpStudio.nonce
          },
          body: JSON.stringify(payload)
        });
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.message || "Failed to update video.");

        alert("Video updated successfully!");
        location.reload();
      } catch (error) {
        alert(error.message);
      } finally {
        submitBtn.disabled = false;
      }
    });
  }

  // Row Helpers
  function addChapterRow(time, title, body) {
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td><input type="number" class="ch-time" value="${time}" min="0" step="any" required /></td>
      <td><input type="text" class="ch-title" value="${escapeHtml(title)}" required /></td>
      <td><button type="button" class="cp-button cp-button-secondary cp-row-del">Remove</button></td>
    `;
    tr.querySelector(".cp-row-del").addEventListener("click", () => tr.remove());
    body.appendChild(tr);
  }

  function addSubtitleRow(lang, label, src, isDefault, body) {
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td><input type="text" class="sub-lang" value="${escapeHtml(lang)}" placeholder="en" required /></td>
      <td><input type="text" class="sub-label" value="${escapeHtml(label)}" placeholder="English" /></td>
      <td>
        <div class="cp-sub-upload-cell">
          <input type="url" class="sub-src" value="${escapeHtml(src)}" required />
          <button type="button" class="cp-button cp-btn-sub-upload">Upload VTT</button>
        </div>
      </td>
      <td><input type="radio" class="sub-default" name="edit_sub_default" ${isDefault ? "checked" : ""} /></td>
      <td><button type="button" class="cp-button cp-button-secondary cp-row-del">Remove</button></td>
    `;
    tr.querySelector(".cp-row-del").addEventListener("click", () => tr.remove());
    tr.querySelector(".cp-btn-sub-upload").addEventListener("click", () => handleS3FileUpload(tr.querySelector(".sub-src"), "text/vtt"));
    body.appendChild(tr);
  }

  function escapeHtml(str) {
    if (!str) return "";
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
  }

  // Inject logo & banner uploaders dynamically in customization panel
  const customPanel = document.querySelector('.cp-studio-panel[data-panel="customization"]');
  if (customPanel) {
    const logoInput = customPanel.querySelector('input[name="channel_logo_url"]');
    const bannerInput = customPanel.querySelector('input[name="channel_banner_url"]');

    if (logoInput) addInlineUploader(logoInput, "image/*");
    if (bannerInput) addInlineUploader(bannerInput, "image/*");
  }

  function addInlineUploader(input, accept) {
    const wrapper = document.createElement("div");
    wrapper.className = "cp-thumbnail-selector-container";
    input.parentNode.insertBefore(wrapper, input);
    wrapper.appendChild(input);

    const btn = document.createElement("button");
    btn.type = "button";
    btn.className = "cp-button";
    btn.textContent = "Upload Image";
    btn.addEventListener("click", () => handleS3FileUpload(input, accept));
    wrapper.appendChild(btn);
  }

  // S3 Presigned Upload handler
  function handleS3FileUpload(urlInput, accept) {
    const picker = Object.assign(document.createElement("input"), { type: "file", accept });
    picker.click();
    picker.addEventListener("change", async () => {
      const file = picker.files[0];
      if (!file) return;

      const button = urlInput.nextElementSibling;
      const originalText = button.textContent;
      button.disabled = true;
      button.textContent = "Uploading...";

      try {
        const body = new URLSearchParams({
          action: "cpwp_channel_presign_upload",
          nonce: cpwpStudio.channelNonce,
          filename: file.name,
          content_type: file.type || "application/octet-stream"
        });
        const response = await fetch(cpwpStudio.ajaxUrl, {
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
});

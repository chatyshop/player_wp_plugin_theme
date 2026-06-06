document.addEventListener("DOMContentLoaded", () => {
  const ChatyPlayer = window.ChatyPlayer?.default || window.ChatyPlayer;
  if (!ChatyPlayer || typeof ChatyPlayer.create !== "function") return;

  document.querySelectorAll(".cpwp-player").forEach((element) => {
    if (!element.hasAttribute("data-chaty-initialized")) {
      ChatyPlayer.create(element);
    }
    setupAnalytics(element);
    setupProgress(element);
  });
});

const engagementRequest = async (path, body) => {
  const response = await fetch(`${cpwpEngagement.base}${path}`, { method: body ? "POST" : "GET", headers: { "Content-Type": "application/json", "X-WP-Nonce": cpwpEngagement.nonce }, body: body ? JSON.stringify(body) : undefined });
  if (!response.ok) throw new Error("Request failed");
  return response.json();
};

function setupProgress(element) {
  if (!window.cpwpEngagement?.features?.progress || !cpwpEngagement.loggedIn || !element.dataset.cpwpVideoId) return;
  const video = element.querySelector("video");
  if (!video) return;
  const id = element.dataset.cpwpVideoId;
  engagementRequest(`/engagement/${id}`).then(state => {
    if (state.progress?.time > 5 && state.progress?.percent < 95) video.addEventListener("loadedmetadata", () => { video.currentTime = Math.min(state.progress.time, Math.max(0, video.duration - 2)); }, { once: true });
  }).catch(() => {});
  let lastSent = 0;
  const save = () => {
    if (!video.duration || video.currentTime < 3) return;
    engagementRequest(`/engagement/${id}`, { action: "progress", time: video.currentTime, duration: video.duration, percent: (video.currentTime / video.duration) * 100 }).catch(() => {});
    lastSent = Date.now();
  };
  video.addEventListener("timeupdate", () => { if (Date.now() - lastSent > 15000) save(); });
  window.addEventListener("pagehide", save, { once: true });
}

const renderEngagement = (container, state) => {
  const like = container.querySelector('[data-action="like"]');
  const dislike = container.querySelector('[data-action="dislike"]');
  if (like) { like.querySelector("span").textContent = state.likes; like.classList.toggle("is-active", state.reaction === "like"); }
  if (dislike) { dislike.querySelector("span").textContent = state.dislikes; dislike.classList.toggle("is-active", state.reaction === "dislike"); }
  container.querySelector('[data-action="favorite"]')?.classList.toggle("is-active", state.favorite);
  container.querySelector('[data-action="watch_later"]')?.classList.toggle("is-active", state.watchLater);
  const menu = container.querySelector(".cpwp-playlist-menu");
  if (menu) menu.innerHTML = `${state.playlists.map(list => `<button type="button" data-playlist-id="${list.id}" class="${list.contains ? "is-active" : ""}">${list.contains ? "Remove from" : "Add to"} ${escapeCpwp(list.name)}</button>`).join("")}<button type="button" data-new-playlist>Create new playlist</button>`;
};

document.querySelectorAll(".cpwp-engagement").forEach(container => {
  if (!cpwpEngagement.features.reactions) container.querySelectorAll('[data-action="like"],[data-action="dislike"]').forEach(button => button.remove());
  if (!cpwpEngagement.features.favorites) container.querySelectorAll('[data-action="favorite"],[data-action="watch_later"]').forEach(button => button.remove());
  if (!cpwpEngagement.features.playlists) container.querySelectorAll('[data-action="playlist"],.cpwp-playlist-menu').forEach(item => item.remove());
  engagementRequest(`/engagement/${container.dataset.videoId}`).then(state => renderEngagement(container, state)).catch(() => {});
});

document.addEventListener("click", async event => {
  const container = event.target.closest(".cpwp-engagement");
  if (!container) return;
  const button = event.target.closest("button");
  if (!button) return;
  if (!cpwpEngagement.loggedIn) return window.location.href = cpwpEngagement.loginUrl;
  const id = container.dataset.videoId;
  const action = button.dataset.action;
  if (action === "playlist") return container.querySelector(".cpwp-playlist-menu").hidden = !container.querySelector(".cpwp-playlist-menu").hidden;
  let body;
  if (action === "like" || action === "dislike") body = { action: "reaction", value: button.classList.contains("is-active") ? "" : action };
  else if (action === "favorite" || action === "watch_later") body = { action };
  else if (button.dataset.playlistId) body = { action: "playlist", playlist_id: button.dataset.playlistId };
  else if (button.dataset.newPlaylist !== undefined) { const name = window.prompt("Playlist name"); if (!name) return; body = { action: "playlist", name }; }
  if (body) renderEngagement(container, await engagementRequest(`/engagement/${id}`, body));
});

const escapeCpwp = value => String(value).replace(/[&<>"']/g, char => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;" })[char]);
const libraryCards = videos => `<div class="cpwp-library-grid">${videos.map(video => `<article class="cpwp-library-card"><a href="${video.url}">${video.thumbnail ? `<img src="${video.thumbnail}" alt="">` : ""}${video.progress ? `<div class="cpwp-progress-bar"><span style="width:${video.progress.percent}%"></span></div>` : ""}<span>${escapeCpwp(video.title)}</span></a></article>`).join("")}</div>`;
document.querySelectorAll("[data-cpwp-library]").forEach(async container => {
  try {
    const data = await engagementRequest("/library");
    const sections = [["Continue watching", data.progress], ["Favorites", data.favorites], ["Watch later", data.watchLater], ...data.playlists.map(list => [list.name, list.videos])];
    container.innerHTML = `<h2>My Library</h2>${sections.filter(([, videos]) => videos.length).map(([title, videos]) => `<section class="cpwp-library-section"><h3>${escapeCpwp(title)}</h3>${libraryCards(videos)}</section>`).join("") || "<p>Your library is empty.</p>"}`;
  } catch { container.innerHTML = "<h2>My Library</h2><p>Could not load your library.</p>"; }
});

function setupAnalytics(element) {
  if (!window.cpwpAnalytics || !element.dataset.cpwpVideoId || element.dataset.cpwpTracking) return;
  element.dataset.cpwpTracking = "true";

  const video = element.querySelector("video");
  if (!video) return;

  const sessionKey = `cpwp_session_${element.dataset.cpwpVideoId}`;
  let session = sessionStorage.getItem(sessionKey);
  if (!session) {
    session = `${Date.now()}-${crypto.randomUUID?.() || Math.random().toString(36).slice(2)}`;
    sessionStorage.setItem(sessionKey, session);
  }

  let lastTick = performance.now();
  let pendingWatch = 0;
  let lastSentAt = 0;
  let completed = false;

  const send = (event, watchTime = 0, percent = 0) => {
    fetch(cpwpAnalytics.endpoint, {
      method: "POST",
      keepalive: true,
      headers: { "Content-Type": "application/json", "X-WP-Nonce": cpwpAnalytics.nonce },
      body: JSON.stringify({ post_id: Number(element.dataset.cpwpVideoId), token: element.dataset.cpwpToken, event, watch_time: Math.round(watchTime), percent: Math.round(percent), session })
    }).catch(() => {});
  };

  video.addEventListener("play", () => send("play"));
  video.addEventListener("timeupdate", () => {
    const now = performance.now();
    if (!video.paused) pendingWatch += Math.min(2, Math.max(0, (now - lastTick) / 1000));
    lastTick = now;
    const percent = video.duration ? (video.currentTime / video.duration) * 100 : 0;
    if (pendingWatch >= 15 && Date.now() - lastSentAt > 10000) {
      send("progress", pendingWatch, percent);
      pendingWatch = 0;
      lastSentAt = Date.now();
    }
    if (!completed && percent >= 90) {
      completed = true;
      send("complete", pendingWatch, percent);
      pendingWatch = 0;
    }
  });
  video.addEventListener("play", () => { lastTick = performance.now(); });
  window.addEventListener("pagehide", () => {
    if (pendingWatch > 1) send("progress", pendingWatch, video.duration ? (video.currentTime / video.duration) * 100 : 0);
  }, { once: true });
}

document.addEventListener("click", async (event) => {
  const shareButton = event.target.closest(".cpwp-native-share");
  const copyButton = event.target.closest(".cpwp-copy-link");
  const container = event.target.closest(".cpwp-share");
  if (!container) return;

  if (shareButton && navigator.share) {
    await navigator.share({ title: container.dataset.title, url: container.dataset.url }).catch(() => {});
  }

  if (copyButton) {
    if (navigator.clipboard) {
      await navigator.clipboard.writeText(container.dataset.url);
    } else {
      window.prompt("Copy this link:", container.dataset.url);
    }
    const original = copyButton.textContent;
    copyButton.textContent = "Copied";
    window.setTimeout(() => { copyButton.textContent = original; }, 1600);
  }
});

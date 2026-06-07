# CP WP Plugin + CP Theme

**Version:** 0.25.0 · **Author:** Chaty Technologies · **License:** GPL-2.0-or-later

Turn WordPress into a self-hosted video platform powered by **ChatyPlayer** — a self-hosted, feature-rich HTML5 video player.

> **Live demo:** https://devland.chatyshop.com/player

---

## ChatyPlayer

ChatyPlayer is a custom HTML5 video player library served from jsDelivr. The plugin automatically loads it; no manual setup is needed.

### CDN Assets (version `1.0.7`)

```html
<!-- CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/chatyshop/chatyplayer@1.0.7/dist/index.css">

<!-- JS -->
<script defer src="https://cdn.jsdelivr.net/gh/chatyshop/chatyplayer@1.0.7/dist/chatyplayer.umd.min.js"></script>
```

The plugin reads the `player_version` setting and optionally a `custom_cdn` base URL to build these paths dynamically via `CPWP_Assets`.

### How it Initialises

ChatyPlayer scans the page for elements with the class `chaty-player` and calls `ChatyPlayer.autoInit()`. Each matching element must carry the following `data-*` attributes:

| Attribute | Type | Description |
|---|---|---|
| `data-mp4` | URL | MP4 source |
| `data-webm` | URL | WebM source |
| `data-ogg` | URL | OGG source |
| `data-poster` | URL | Poster / thumbnail image |
| `data-autoplay` | `"true"` / `"false"` | Autoplay (forces muted) |
| `data-loop` | `"true"` / `"false"` | Loop playback |
| `data-muted` | `"true"` / `"false"` | Start muted |
| `data-preload` | `none` / `metadata` / `auto` | Preload strategy |
| `data-color` | hex / rgb | Accent colour (e.g. `#6d5dfc`) |
| `data-subtitles` | JSON array | Subtitle track objects (see below) |
| `data-chapters` | JSON array | Chapter marker objects (see below) |
| `data-thumbnails` | URL | Sprite sheet URL for scrub preview |
| `data-thumb-width` | number | Sprite frame width (px) |
| `data-thumb-height` | number | Sprite frame height (px) |
| `data-thumb-columns` | number | Sprite columns |
| `data-thumb-rows` | number | Sprite rows |
| `data-thumb-interval` | number | Seconds between frames |
| `data-cpwp-video-id` | number | WordPress post ID (for analytics) |
| `data-cpwp-token` | string | HMAC token for analytics API |

**Subtitle JSON format:**
```json
[
  { "src": "https://…/en.vtt", "label": "English", "srclang": "en", "default": true },
  { "src": "https://…/fr.vtt", "label": "Français", "srclang": "fr", "default": false }
]
```

**Chapter JSON format** (sorted by time automatically):
```json
[
  { "time": 0,   "title": "Introduction" },
  { "time": 120, "title": "Main Content" },
  { "time": 480, "title": "Conclusion" }
]
```

### Player Features (from source)

| Feature | Notes |
|---|---|
| Multi-source playback | MP4, WebM, OGG — best supported format is chosen |
| Adaptive quality | `sources` JSON array with labelled streams; auto-switches based on buffer health |
| Subtitle / CC | Fetches VTT files at runtime; "Off" + per-track selection; default track supported |
| Chapter markers | Visual segments on timeline, click-to-seek, active segment highlighted |
| Thumbnail scrub preview | Sprite sheet hover/touch preview with responsive scaling |
| Fullscreen | Native Fullscreen API + CSS fallback; keyboard `F` |
| Theatre mode | Full-width layout (desktop only — disabled on pointer:coarse) |
| Mini player | Fixed 260×146 px floating player; auto-activates on scroll out of view via IntersectionObserver; draggable |
| Picture-in-Picture | Native browser PiP API; auto-enters on tab hide |
| Playback speed | 0.5×, 0.75×, 1×, 1.25×, 1.5×, 2× |
| Volume control | Slider + mute toggle; touch: swipe up/down to adjust volume |
| Resume playback | Saves position to `localStorage` (key prefix `chatyplayer:`); restores on reload; clears on completion |
| Timestamp links | Reads `?t=` / `#t=` URL param on load; `getTimestampLink()` generates current-time URL |
| Keyboard shortcuts | See table below |
| Auto-hide UI | Hides controls after 2 s idle (desktop) / 3.5 s (touch); always visible when paused |
| Reduced motion | All transitions/animations disabled when `prefers-reduced-motion: reduce` |

### Keyboard Shortcuts

| Key | Action |
|---|---|
| `Space` / `K` | Play / Pause |
| `←` / `J` | Seek back 5 seconds |
| `→` / `L` | Seek forward 5 seconds |
| `↑` | Volume +5% |
| `↓` | Volume −5% |
| `M` | Toggle mute |
| `F` | Toggle fullscreen |
| `T` | Toggle theatre mode |

### JavaScript API

After `ChatyPlayer.autoInit()` each player element exposes `element.__chatyPlayerInstance__`.

```js
// Programmatic creation
const player = ChatyPlayer.create(element);

// Playback
player.play();
player.pause();
player.seek(120);           // seek to 120 s
player.setVolume(0.8);      // 0.0 – 1.0
player.setSpeed(1.5);       // 0.25 – 4.0

// Modes
player.toggleFullscreen();
player.toggleTheatre();
player.toggleMini();
player.togglePiP();

// Subtitles
player.enableSubtitle('en');
player.disableSubtitles();
player.getAvailableSubtitles(); // returns string[]
player.getCurrentSubtitle();    // returns string | null

// Quality
player.setQuality('1080p');   // or 'auto'
player.quality.getAvailableQualities(); // returns string[]
player.quality.getCurrentQuality();     // returns string

// Timestamp
const url = player.getTimestampLink(); // current position URL

// Lifecycle
player.destroy();
```

### Events (`player.api.on(event, handler)`)

| Event | Payload | Description |
|---|---|---|
| `ready` | — | Player initialised |
| `play` | — | Playback started |
| `pause` | — | Playback paused |
| `ended` | — | Video ended |
| `timeupdate` | `number` (seconds) | Playback position changed |
| `loadedmetadata` | `number` (duration) | Duration available |
| `error` | `MediaError` | Native video error |
| `speedchange` | `number` | Playback rate changed |
| `qualitychange` | `string` | Active quality label changed |
| `subtitlechange` | `string \| null` | Active subtitle language changed |
| `fullscreenchange` | `boolean` | Fullscreen state changed |
| `pipchange` | `boolean` | PiP state changed |
| `theatre` | `boolean` | Theatre mode state changed |
| `modechange` | `{ prev, next }` | Player mode changed |
| `scrubstart` | `number` | Scrubbing started |
| `scrubmove` | `number` | Scrub position moved |
| `scrubend` | `number` | Scrubbing ended |
| `destroy` | — | Player destroyed |

### State Object

`player.api` returns reactive state via `subscribe(listener)`.

| Key | Type | Description |
|---|---|---|
| `ready` | boolean | Player is initialised |
| `playing` | boolean | Currently playing |
| `scrubbing` | boolean | User is scrubbing |
| `muted` | boolean | Muted state |
| `volume` | number | Current volume (0–1) |
| `speed` | number | Playback rate |
| `quality` | string | Current quality label |
| `currentTime` | number | Current position (s) |
| `duration` | number | Total duration (s) |
| `fullscreen` | boolean | Fullscreen active |
| `pip` | boolean | PiP active |
| `theater` | boolean | Theatre mode active |
| `mini` | boolean | Mini player active |
| `subtitle` | string \| null | Active subtitle language |
| `destroyed` | boolean | Player destroyed |

### CSS Custom Properties

```css
.chatyplayer-root {
  --cp-bg:           #000000;
  --cp-surface:      #121212;
  --cp-surface-light:#1e1e1e;
  --cp-text:         #ffffff;
  --cp-text-muted:   #9ca3af;
  --cp-primary:      #3b82f6;   /* accent — overridden by data-color */
  --cp-primary-hover:#2563eb;
  --cp-border:       rgba(255,255,255,.12);
  --cp-buffer:       rgba(255,255,255,.4);
  --cp-progress:     #3b82f6;
}
```

---

## Requirements

| Requirement | Minimum |
|---|---|
| WordPress | 6.0 |
| PHP | 7.4 |
| PHP Extension | `SimpleXML` (for Storage Manager) |

---

## Repository Layout

```
cpwpplugin/
├── cp-wp-plugin/          # WordPress plugin
│   ├── cp-wp-plugin.php   # Entry point — defines constants, bootstraps singleton
│   ├── uninstall.php      # Cleanup on uninstall
│   ├── readme.txt         # WordPress.org readme
│   ├── post-types/
│   │   └── class-cpwp-video-post-type.php
│   ├── includes/
│   │   ├── class-cpwp-plugin.php          # Core singleton, hook registration
│   │   ├── class-cpwp-analytics.php       # REST analytics — views/watch-time/completions
│   │   ├── class-cpwp-assets.php          # Script & style enqueueing
│   │   ├── class-cpwp-engagement.php      # Likes, favorites, playlists, progress
│   │   ├── class-cpwp-player-renderer.php # ChatyPlayer HTML builder
│   │   ├── class-cpwp-seo.php             # VideoObject schema + Open Graph
│   │   ├── class-cpwp-shortcode.php       # [cp_player] shortcode
│   │   ├── class-cpwp-storage.php         # R2 / S3 / S3-compatible signed uploads
│   │   ├── class-cpwp-transcript.php      # Transcript full-text search
│   │   ├── class-cpwp-users.php           # Auth system (login/register/verify/…)
│   │   ├── class-cpwp-video-archive.php   # Video grid, cards, related videos
│   │   ├── class-cpwp-channels.php        # Creator channels
│   │   ├── class-cpwp-site-modules.php    # Site modules post types & gating
│   │   ├── class-cpwp-security.php        # Download security
│   │   ├── class-cpwp-moderation.php      # Moderation tools & logs
│   │   ├── class-cpwp-page-suites.php     # Virtual page suites
│   │   ├── class-cpwp-learning.php        # Courses & Quizzes modules
│   │   ├── class-cpwp-streaming.php       # Streaming configuration
│   │   ├── class-cpwp-affiliate.php       # Affiliate redirect & comparisons
│   │   ├── class-cpwp-community.php       # Community groups & voting
│   │   └── class-cpwp-creator-platform.php # Creator REST routes
│   ├── admin/
│   │   ├── class-cpwp-dashboard.php       # Admin dashboard + 7-day chart
│   │   ├── class-cpwp-settings.php        # Polish tabbed settings page
│   │   ├── class-cpwp-video-fields.php    # Video meta fields
│   │   ├── class-cpwp-bulk-videos.php     # Bulk upload manager
│   │   ├── css/
│   │   └── js/
│   └── public/
│       ├── css/
│       └── js/
└── cp-theme/              # Companion WordPress theme
    ├── style.css
    ├── functions.php
    ├── header.php
    ├── footer.php
    ├── front-page.php
    ├── single-cp_video.php
    ├── archive-cp_video.php
    ├── category.php
    ├── search.php
    ├── page.php
    ├── page-favorites.php
    ├── page-watch-later.php
    ├── sidebar-logged-in.php
    ├── comments.php
    ├── index.php
    ├── cpwp-unavailable.php   # Locked/gated content overlay template
    ├── page-suite.php         # Virtual page suite router
    ├── templates/             # Site-type template layouts
    │   ├── affiliate/
    │   ├── business_training/
    │   ├── courses/
    │   ├── creator_platform/
    │   ├── default/
    │   ├── gaming/
    │   ├── membership/
    │   ├── news/
    │   ├── podcast/
    │   ├── streaming/
    │   └── video_library/
    └── assets/
        ├── watch.js
        ├── upload.js
        ├── studio.js
        └── channel.js
```

---

## Installation

1. Upload `cp-wp-plugin/` to `/wp-content/plugins/`.
2. Upload `cp-theme/` to `/wp-content/themes/`.
3. **Activate CP WP Plugin** in *Plugins*.
4. **Activate CP Theme** in *Appearance → Themes*.
5. Go to **CP Videos → Dashboard** to verify setup.
6. Go to **CP Videos → Settings** to configure branding, storage, and features.

---

## Plugin Constants

Defined in `cp-wp-plugin.php` on load:

| Constant | Value |
|---|---|
| `CPWP_VERSION` | `0.9.2` |
| `CPWP_FILE` | Absolute path to plugin entry file |
| `CPWP_DIR` | Absolute path to plugin directory (trailing slash) |
| `CPWP_URL` | Public URL to plugin directory (trailing slash) |

---

## Custom Post Type: `cp_video`

Registered by `CPWP_Video_Post_Type::register()` on `init`.

| Property | Value |
|---|---|
| Slug | `/videos/` |
| Archive | `true` |
| REST API | `show_in_rest = true` |
| Menu icon | `dashicons-video-alt3` |
| Supports | title, editor, excerpt, thumbnail, comments |
| Taxonomies | category, post_tag |

---

## Video Meta Fields

Stored as post meta by `CPWP_Video_Fields`:

| Meta Key | Type | Description |
|---|---|---|
| `_cpwp_mp4` | string | MP4 source URL |
| `_cpwp_webm` | string | WebM source URL |
| `_cpwp_ogg` | string | OGG source URL |
| `_cpwp_subtitles` | JSON array | Subtitle track objects |
| `_cpwp_chapters` | JSON array | Chapter marker objects |
| `_cpwp_thumbnail_sprite` | string | Sprite sheet URL for scrubbing preview |
| `_cpwp_thumb_width` | int | Sprite thumbnail width (px) |
| `_cpwp_thumb_height` | int | Sprite thumbnail height (px) |
| `_cpwp_thumb_columns` | int | Sprite columns |
| `_cpwp_thumb_rows` | int | Sprite rows |
| `_cpwp_thumb_interval` | int | Sprite interval (seconds) |
| `_cpwp_autoplay` | bool | Per-video autoplay override |
| `_cpwp_loop` | bool | Per-video loop override |
| `_cpwp_muted` | bool | Per-video muted override |
| `_cpwp_preload` | string | `none` / `metadata` / `auto` |
| `_cpwp_accent_color` | hex | Per-video accent color |
| `_cpwp_transcript` | string | Full plain-text transcript |
| `_cpwp_views` | int | Total unique play sessions |
| `_cpwp_watch_time` | int | Total accumulated watch seconds |
| `_cpwp_completions` | int | Total completions (≥ 90 % watched) |
| `_cpwp_daily_analytics` | array | Rolling 90-day `{ views, watch_time, completions }` |
| `_cpwp_likes` | int | Total likes |
| `_cpwp_dislikes` | int | Total dislikes |

---

## Shortcodes

### `[cp_player video="ID"]`

Embeds ChatyPlayer for the given `cp_video` post ID.

```php
[cp_player video="42"]
```

Rendered output is a `<div>` with `data-*` attributes that ChatyPlayer reads:
`data-mp4`, `data-webm`, `data-ogg`, `data-poster`, `data-subtitles`, `data-chapters`,
`data-thumbnails`, `data-autoplay`, `data-loop`, `data-muted`, `data-preload`, `data-color`,
`data-cpwp-video-id`, `data-cpwp-token`.

### `[cp_video_grid limit="12" filters="true"]`

Renders a responsive, filterable, sortable, paginated video grid.

| Attribute | Default | Description |
|---|---|---|
| `limit` | `12` | Videos per page (max 50) |
| `filters` | `true` | Show search / category / sort bar |

URL parameters consumed: `cp_search`, `cp_category`, `cp_sort` (`newest` / `oldest` / `views`), `cp_page`.

---

## REST API Endpoints

Base: `/wp-json/cpwp/v1/`

### `POST /analytics`

Records a player event. No authentication required; validated by HMAC token.

**Request body:**

| Field | Required | Description |
|---|---|---|
| `post_id` | yes | `cp_video` post ID |
| `event` | yes | `play` \| `progress` \| `complete` |
| `watch_time` | no | Seconds watched this interval (capped at 60) |
| `percent` | no | Playback percent (0–100) |
| `session` | yes | Client session UUID |
| `token` | yes | `wp_hash('cpwp-analytics-' . post_id)` |

**Behaviour:**
- `play` — increments `_cpwp_views` once per session (24 h transient).
- `progress` — accumulates `_cpwp_watch_time`.
- `complete` (percent ≥ 90) — increments `_cpwp_completions`.
- Rate-limited to one event per session per 5 seconds (429 on violation).
- Daily analytics stored for 90 rolling days.

**Response:** `{ "recorded": true, "new_view": bool }`

---

### `GET /engagement/{post_id}`

Returns public + user engagement state for a video. No auth required.

**Response:**
```json
{
  "loggedIn": true,
  "loginUrl": "https://…",
  "reaction": "like",
  "likes": 42,
  "dislikes": 3,
  "favorite": true,
  "watchLater": false,
  "progress": { "time": 120.5, "duration": 600, "percent": 20.1, "updated": 1717000000 },
  "playlists": [{ "id": "list-uuid", "name": "My list", "contains": true }]
}
```

---

### `POST /engagement/{post_id}` *(login required)*

Updates engagement state.

| `action` | Required setting | Extra fields | Description |
|---|---|---|---|
| `reaction` | `enable_reactions` | `value`: `like`\|`dislike`\|`` | Toggle like or dislike |
| `favorite` | `enable_favorites_watch_later` | — | Toggle favorite (max 500) |
| `watch_later` | `enable_favorites_watch_later` | — | Toggle watch later (max 500) |
| `progress` | `enable_continue_watching` | `time`, `duration`, `percent` | Save playback position (max 100; cleared ≥ 95 %) |
| `playlist` | `enable_playlists` | `playlist_id`, `name` | Create playlist or toggle video membership |

Returns the updated engagement state identical to `GET`.

---

### `GET /library` *(login required)*

Returns the current user's full library:
```json
{
  "favorites": [ { "id": 1, "title": "…", "url": "…", "thumbnail": "…" } ],
  "watchLater": [ … ],
  "progress":   [ { …video, "progress": { "time": …, "percent": … } } ],
  "playlists":  [ { "id": "list-uuid", "name": "My list", "videos": [ … ] } ]
}
```

---

## Storage

`CPWP_Storage` provides S3-compatible direct upload support. Configured in **Settings → Storage**.

| Provider | Notes |
|---|---|
| Direct URL | Supply a public base URL; no signing |
| Cloudflare R2 | AWS Sig V4, region `auto` |
| Amazon S3 | AWS Sig V4, standard regions |
| S3-compatible | Any endpoint with AWS Sig V4 |

All uploaded files are placed under the `cp-videos/YYYY/MM/<uuid>-<filename>` key prefix.
Endpoints must use **HTTPS** and cannot resolve to private/reserved IP ranges.

**AJAX actions (admin only):**

| Action | Handler | Description |
|---|---|---|
| `cpwp_test_storage` | `CPWP_Storage::ajax_test` | Verify connection |
| `cpwp_presign_upload` | `CPWP_Storage::ajax_presign_upload` | Get 15-min presigned PUT URL |
| `cpwp_list_storage` | `CPWP_Storage::ajax_list` | List up to 100 files in `cp-videos/` |
| `cpwp_delete_storage` | `CPWP_Storage::ajax_delete` | Delete a file (key must start with `cp-videos/`) |

---

## User Authentication System

`CPWP_Users` intercepts requests with `?cpwp_auth=<action>` on `template_redirect`.

| Action | Route | Description |
|---|---|---|
| `login` | `?cpwp_auth=login` | Email/username + password sign-in |
| `register` | `?cpwp_auth=register` | Create account (min 8-char password) |
| `forgot` | `?cpwp_auth=forgot` | Send password-reset email |
| `reset` | `?cpwp_auth=reset&login=…&key=…` | Choose new password via reset link |
| `verify` | `?cpwp_auth=verify&uid=…&token=…` | Confirm email address (24 h token) |
| `resend-verification` | `?cpwp_auth=resend-verification` | Re-send verification email |
| `profile` | `?cpwp_auth=profile` | Edit display name, email, password |
| `delete-account` | `?cpwp_auth=delete-account` | Permanent deletion (password + "DELETE" required) |

**Security measures:**
- Login rate limit: 5 failures → 15-minute lockout per username + IP.
- Math CAPTCHA on login, register, and forgot-password forms (`enable_auth_captcha`).
- Email verification token: 40-char random string, bcrypt-hashed, 24 h expiry.
- Admins/editors are blocked from using `delete-account`.
- Non-editor users are redirected from `/wp-admin/` to their profile page.
- Admin bar hidden for non-editor users.

---

## SEO (`CPWP_SEO`)

Injected into `wp_head` (priority 5) for singular `cp_video` pages:

- `og:type` → `video.other`
- `og:title`, `og:url`, `og:description`, `og:image`, `og:video`
- `application/ld+json` — `VideoObject` schema with `name`, `description`, `thumbnailUrl`, `uploadDate`, `contentUrl`, `embedUrl`, `interactionStatistic` (WatchAction count)

---

## Admin Dashboard

**CP Videos → Dashboard** (`CPWP_Dashboard`):

**Summary stats:**
- Published videos / Draft videos
- Total views (sum of `_cpwp_views` across all videos)
- Average watch time (total watch time ÷ total views)
- Completion rate (total completions ÷ total views × 100 %)

**7-day bar chart:** Daily view counts from `_cpwp_daily_analytics` meta, normalised to bar heights.

**Per-video analytics table** (top 5 by views):
Views · Total watch time · Average watch time per view · Completion rate %

---

## Settings Reference

**CP Videos → Settings** — 8 tabs, stored in `cpwp_settings` option.

### Branding
`platform_name`, `tagline`, `logo_url`, `accent_color` (default `#6d5dfc`), `footer_text`

### Player
`player_version` (default `1.0.7`), `custom_cdn`, `default_preload` (`metadata`/`auto`/`none`), `default_muted`

### Video Features
`show_sharing`, `show_transcript`, `show_related`, `enable_comments`, `enable_analytics`,
`enable_reactions`, `enable_favorites_watch_later`, `enable_playlists`, `enable_continue_watching`

### Users
`enable_login`, `enable_registration`, `comments_login_only`, `enable_password_recovery`,
`enable_email_verification`, `enable_password_confirmation`, `enable_login_rate_limit`,
`enable_auth_captcha`, `enable_account_deletion`

### Storage
`storage_provider` (`direct`/`r2`/`s3`/`s3_compatible`), `storage_endpoint`,
`storage_bucket`, `storage_region` (default `auto`), `storage_public_url`,
`storage_access_key`, `storage_secret_key` *(never exported)*

### Subscriptions & Pricing
`enable_subscriptions`, `subscription_plugin` (`pmpro`/`woocommerce`/`memberpress`), `subscription_checkout_url`,
`enable_pricing_page`, `pricing_free_price`, `pricing_free_features`, `pricing_free_url`, 
`pricing_pro_price`, `pricing_pro_features`, `pricing_pro_url`,
`pricing_premium_price`, `pricing_premium_features`, `pricing_premium_url`

### Homepage
`home_section_order` (drag-and-drop), `home_featured_video`, `home_videos_per_section` (1–24),
`home_show_categories`, `home_show_trending`, `home_show_latest`, `home_show_most_viewed`,
`home_show_category_rows`, `home_show_promo`,
`home_trending_title`, `home_latest_title`, `home_most_viewed_title`,
`home_category_ids`, `home_hero_title`, `home_hero_description`, `home_hero_button`,
`home_promo_title`, `home_promo_content`, `home_promo_button`, `home_promo_url`

### Social
`facebook_url`, `x_url`

### Tools
Export settings to JSON (secret key excluded) · Import JSON · Reset all settings to defaults.

---

## CP Theme

Companion theme (`cp-theme/`) designed exclusively for the plugin.

**Key files:**

| File | Purpose |
|---|---|
| `front-page.php` | Configurable homepage — hero, category pills, trending/latest/most-viewed/category row/promo sections |
| `header.php` | Site header — logo, sidebar toggle (logged-in), search bar, auth links |
| `footer.php` | Site footer with social links and footer text |
| `sidebar-logged-in.php` | Collapsible sidebar nav (state persisted in `localStorage`) |
| `single-cp_video.php` | Single video page wrapper |
| `archive-cp_video.php` | Video archive |
| `page-favorites.php` | User favourites page |
| `page-watch-later.php` | Watch later page |
| `search.php` | Search results for `cp_video` |
| `comments.php` | Custom comment list with like/dislike + collapsible reply threads |
| `assets/watch.js` | Player-page JS (engagement controls, progress saving) |

**Typography:** Inter (Google Fonts, weights 400–900).

**Theme helper functions (`functions.php`):**

| Function | Description |
|---|---|
| `cp_theme_cp_setting($key, $fallback)` | Read a value from `cpwp_settings` option |
| `cp_theme_video_card($post_id)` | Render a single video card |
| `cp_theme_video_section($title, $args, $link)` | Render a titled video row section |
| `cp_theme_get_template_page_url($template)` | Find URL of a page using a given template |

---

## Registered WordPress Hooks

### Actions

| Hook | Callback | Priority |
|---|---|---|
| `init` | `CPWP_Video_Post_Type::register` | default |
| `wp_enqueue_scripts` | `CPWP_Assets::register_public_assets` | default |
| `admin_enqueue_scripts` | `CPWP_Assets::enqueue_admin_assets` | default |
| `admin_menu` | `CPWP_Dashboard::register` | default |
| `admin_init` | `CPWP_Settings::register_settings` | default |
| `admin_init` | `CPWP_Analytics::repair_legacy_data` | default |
| `admin_init` | `CPWP_Users::block_user_admin` | default |
| `template_redirect` | `CPWP_Users::handle_authentication` | default |
| `wp_head` | `CPWP_SEO::render_meta` | 5 |
| `add_meta_boxes` | `CPWP_Video_Fields::add_meta_boxes` | default |
| `save_post_cp_video` | `CPWP_Video_Fields::save` | default |
| `rest_api_init` | `CPWP_Analytics::register_routes` | default |
| `rest_api_init` | `CPWP_Engagement::register_routes` | default |
| `wp_ajax_cpwp_test_storage` | `CPWP_Storage::ajax_test` | default |
| `wp_ajax_cpwp_presign_upload` | `CPWP_Storage::ajax_presign_upload` | default |
| `wp_ajax_cpwp_list_storage` | `CPWP_Storage::ajax_list` | default |
| `wp_ajax_cpwp_delete_storage` | `CPWP_Storage::ajax_delete` | default |
| `wp_ajax_cpwp_export_settings` | `CPWP_Settings::ajax_export` | default |
| `wp_ajax_cpwp_import_settings` | `CPWP_Settings::ajax_import` | default |
| `wp_ajax_cpwp_reset_settings` | `CPWP_Settings::ajax_reset` | default |

### Filters

| Hook | Callback | Priority |
|---|---|---|
| `the_content` | `CPWP_Player_Renderer::prepend_to_video_content` | default |
| `the_content` | `CPWP_Video_Archive::render_archive_card` | 20 |
| `posts_join` | `CPWP_Transcript::search_join` | 10 |
| `posts_search` | `CPWP_Transcript::search_content` | 10 |
| `posts_distinct` | `CPWP_Transcript::search_distinct` | 10 |
| `comments_open` | `CPWP_Plugin::comments_open` | 10 |
| `preprocess_comment` | `CPWP_Users::require_login_for_comment` | default |
| `show_admin_bar` | `CPWP_Users::show_admin_bar` | default |
| `manage_cp_video_posts_columns` | `CPWP_Analytics::add_views_column` | default |
| `manage_cp_video_posts_custom_column` | `CPWP_Analytics::render_views_column` | 10 |

---

## Changelog

### 0.25.0
- Added custom virtual Pricing & Plans page (`/discover/pricing/`) with customize feature list, prices, and links in WP Settings.
- Added subscription payment gating, visibility/role access control, and visual lock indicators to all custom post types and standard WordPress posts/pages.
- Added footer menu registration and fallback scanners to match Privacy Policy, TOS, Contact, and Support pages.
- Added dynamic taxonomy selection (Category, Genre, Game, Topic, Tag) on frontend uploads, dashboard editing, watch pages, and Schema.org VideoObject SEO properties.
- Added Creator Studio Dashboard, Content Manager, Comment Moderator, and Channel Customizer templates for creator platforms, gaming, and podcast channels.
- Added Courses site type templates: archive, front-page, sidebar, single-course, single-lesson, and single-video layouts.

### 0.9.2
- Redesigned CP Settings as a polished 8-tab interface.

### 0.9.1
- Added security hardening, settings tools, import/export/reset, selectors, storage manager, and upload progress.

### 0.9.0
- Added configurable homepage builder for CP Theme.

### 0.8.2
- Added direct browser uploads to configured R2/S3-compatible storage.

### 0.8.1
- Added direct URL, R2, S3, and S3-compatible storage connections and connection testing.

### 0.8.0
- Added platform branding, player defaults, CDN, and feature settings.

### 0.7.3
- Fixed corrupted watch-time display in the CP Videos list.

### 0.7.2
- Added detailed per-video watch-time and completion analytics.

### 0.7.1
- Fixed analytics warnings, watch-time calculation, and dashboard layout.

### 0.7.0
- Added playback-based views, watch time, completions, and daily analytics.

### 0.6.0
- Added VideoObject schema, Open Graph metadata, and sharing controls.

### 0.5.0
- Added CP dashboard and filterable, sortable video grids.

### 0.4.0
- Added transcripts, richer single-video pages, and related videos.

### 0.3.0
- Added video cards, video-grid shortcode, and basic view analytics.

### 0.2.0
- Added media-library selectors, editor video preview, and player settings.

### 0.1.0
- Initial publishing and playback MVP.

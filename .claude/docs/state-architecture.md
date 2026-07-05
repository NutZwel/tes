# Claude: Read this file when working with session state, client-side state, or designing state management for a new feature.

# State & Memory Architecture

## 10.1. State Taxonomy

### GLOBAL STATE

**Server-side CI3 sessions** for auth, play_count, and theme_preference.

| State Key | Purpose | Location | Initial Value |
|---|---|---|---|
| `user_id` | Identifies authenticated user; NULL for guests | CI3 session driver | `NULL` |
| `username` | Display name for authenticated user | CI3 session driver | `NULL` |
| `role` | RBAC role (`admin`, `user`, or NULL for guest) | CI3 session driver | `NULL` |
| `play_count` | Guest play counter (enforces 3-play/session limit) | CI3 session driver | `0` |
| `theme_preference` | UI theme (`light` or `dark`) — registered users only | CI3 session driver | `light` |

**Rules:**
- Initialize with `NULL` for guest session (`play_count = 0`).
- NEVER store derived data that can be computed from existing state.
- NEVER store server-side objects or database entities directly in session.

### LOCAL STATE

**Client-side JavaScript** (`player.js`) manages the audio player UI.

| State Key | Purpose | Location | Initial Value |
|---|---|---|---|
| `queue` | Ordered array of song objects for playback | `player.js` module scope | `[]` |
| `currentTrackIndex` | Index into `queue` for the currently playing song | `player.js` module scope | `0` |
| `isShuffle` | Whether shuffle mode is active | `player.js` module scope | `false`| `isLoop` | Whether loop mode is active | `player.js` module scope | `false` |
| `volume` | Player volume level (0.0–1.0) | `player.js` module scope | `1.0` |

**Rules:**
- Prefer local state over global state by default.
- Lift to global (server session) only when 2+ unrelated components need the same state. In this project, that applies only to `theme_preference` (shared between player UI and catalog UI via server session).
- Queue state is purely client-side; it is NOT persisted to the server.

### SERVER STATE

**N/A** — no React Query / SWR equivalent for academic scope. All server state is derived from the database on each request.

---

## 10.2. State Mutation Rules

### Server-Side Session Mutation

- State MUST only be mutated through CI3 session setters:
  ```php
  $this->session->set_userdata('play_count', $newCount);
  $this->session->set_userdata('user_id', $userId);
  $this->session->set_userdata('theme_preference', $theme);
  ```
- Direct mutation of session objects is FORBIDDEN. Do not manipulate `$_SESSION` directly.

### Async State Updates (AJAX)

AJAX endpoints handle theme toggle, favorites toggle, and playlist song toggle. Each async update MUST handle three states explicitly:

1. **Loading** — disable the toggle control, show a spinner or loading indicator.
2. **Success** — update the UI to reflect the new state (e.g., filled heart icon for favorited).
3. **Error** — revert the UI to the previous state, show an error message.

Example flow for favorites toggle:
```
User clicks heart icon
  -> Set loading state (disable icon, show spinner)
  -> POST /favorites/toggle {song_id}
  -> On 200: Update icon to filled/outline based on JSON {status: 'added'|'removed'}
  -> On 401/403/500: Revert icon, show error toast
  -> Always: Re-enable icon, remove spinner
```

### Component Unmount Cleanup

- N/A — no JavaScript framework. There is no component lifecycle to unmount.
- If a pending `XMLHttpRequest` is in flight when the user navigates away, the browser will abort it automatically on page unload.

---

## State Flow Diagrams

### Guest Play Count Flow

```
User clicks "Play" on catalog card
  -> POST /player/check_play_limit {song_id}
  -> Server reads session['play_count']
  -> If play_count < 3:
       session['play_count'] += 1
       return JSON {status: 'allow', stream_url: '/stream/{id}'}
     If play_count >= 3:
       return JSON {status: 'deny'}
  -> Client: if 'allow', load stream_url into HTML5 Audio
  -> Client: if 'deny', show registration modal
```

### User Login Flow

```
User submits login form
  -> POST /auth/login {username, password}
  -> Server validates credentials (password_verify)
  -> On success:
       session['user_id'] = user.id
       session['username'] = user.username
       session['role'] = user.role
       session['theme_preference'] = user.theme_preference
       redirect to /catalog
  -> On failure:
       return 401 with generic message
       session remains guest (user_id = NULL)
```

### Theme Toggle Flow

```
User clicks theme toggle
  -> POST /user/set_theme {theme: 'dark'|'light'}
  -> Server validates user is authenticated (session['user_id'] NOT NULL)
  -> Updates users.theme_preference in database
  -> Updates session['theme_preference']
  -> Returns JSON {status: 'ok', theme: 'dark'|'light'}
  -> Client swaps body class (light <-> dark) via theme-switcher.js
```

---

## Adding New State

When designing state management for a new feature, follow this decision tree:

1. **Does the state need to survive page reload?** YES -> Server session (GLOBAL). NO -> Continue.
2. **Does the state need to be shared across 2+ unrelated UI components?** YES -> Server session (GLOBAL). NO -> Continue.
3. **Is the state specific to a single UI component?** YES -> Client-side JavaScript (LOCAL).
4. **Does the state represent persistent data that must survive server restart?** YES -> Database (via model), not session.

Document any new state keys in the tables above.

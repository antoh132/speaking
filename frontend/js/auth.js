/**
 * SpeakOn! — Auth Module
 *
 * Handles login, logout, token management, and authentication state.
 *
 * Requirements covered:
 *   - 1.1 : Authenticate user and store tokens
 *   - 1.3 : Redirect to role-specific dashboard after login
 *   - 1.4 : Logout revokes refresh token and clears local state
 */

const auth = (() => {
  const USER_KEY = 'speakon_user';

  // Deteksi base path otomatis
  const BASE = window.location.pathname.startsWith('/speakon/') ? '/speakon' : '';

  // ── Login ────────────────────────────────────────────────────────────────────

  /**
   * Authenticate the user with email and password.
   *
   * On success: stores tokens and user object, then redirects to dashboard.
   * On failure: throws an error object with code and message.
   *
   * @param {string} email
   * @param {string} password
   * @returns {Promise<Object>} User object on success
   * @throws {Object} Error object with code and message
   */
  async function login(email, password) {
    const response = await api.post('/auth/login', { email, password });

    const { accessToken, refreshToken, user, redirectPath } = response.data;

    // Store tokens and user info
    api.storeTokens(accessToken, refreshToken);
    localStorage.setItem(USER_KEY, JSON.stringify(user));

    return { user, redirectPath };
  }

  // ── Logout ───────────────────────────────────────────────────────────────────

  /**
   * Log out the current user.
   *
   * Revokes the refresh token on the server, then clears local state.
   *
   * @returns {Promise<void>}
   */
  async function logout() {
    const refreshToken = api.getRefreshToken();

    if (refreshToken) {
      try {
        await api.post('/auth/logout', { refreshToken });
      } catch (_) {
        // Ignore server errors — always clear local state
      }
    }

    api.clearTokens();
    localStorage.removeItem(USER_KEY);

    window.location.href = BASE + '/index.html';
  }

  // ── Current user ─────────────────────────────────────────────────────────────

  /**
   * Get the currently authenticated user from localStorage.
   *
   * @returns {Object|null} User object or null if not authenticated
   */
  function getCurrentUser() {
    const stored = localStorage.getItem(USER_KEY);
    if (!stored) return null;

    try {
      return JSON.parse(stored);
    } catch (_) {
      return null;
    }
  }

  /**
   * Check whether the user is currently authenticated.
   *
   * @returns {boolean}
   */
  function isAuthenticated() {
    return !!api.getAccessToken() && !!getCurrentUser();
  }

  // ── Redirect logic ───────────────────────────────────────────────────────────

  /**
   * Get the dashboard path for a given role.
   *
   * @param {string} role - 'superadmin' | 'dosen' | 'siswa'
   * @returns {string}    - Relative path to the dashboard HTML page
   */
  function getRedirectPath(role) {
    const paths = {
      superadmin: BASE + '/dashboard-superadmin.html',
      dosen:      BASE + '/dashboard-dosen.html',
      siswa:      BASE + '/dashboard-siswa.html',
    };
    return paths[role] || BASE + '/login.html';
  }

  /**
   * Redirect to the appropriate dashboard for the current user's role.
   * If not authenticated, redirect to login.
   */
  function redirectToDashboard() {
    const user = getCurrentUser();
    if (!user) {
      window.location.href = BASE + '/login.html';
      return;
    }
    window.location.href = getRedirectPath(user.role);
  }

  function requireAuth(allowedRoles = []) {
    if (!isAuthenticated()) {
      window.location.href = BASE + '/login.html';
      return;
    }

    if (allowedRoles.length > 0) {
      const user = getCurrentUser();
      if (!allowedRoles.includes(user?.role)) {
        window.location.href = getRedirectPath(user?.role);
      }
    }
  }

  // Public API
  return { login, logout, getCurrentUser, isAuthenticated, getRedirectPath, redirectToDashboard, requireAuth };
})();

window.auth = auth;

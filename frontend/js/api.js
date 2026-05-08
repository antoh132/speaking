/**
 * SpeakOn! — API Client Module
 *
 * Provides a consistent interface for all HTTP requests to the PHP backend.
 * Automatically injects JWT access tokens and handles 401 token refresh.
 *
 * Requirements covered:
 *   - 1.1 : Inject JWT token into every authenticated request
 *   - 1.5 : Auto-refresh access token on 401 TOKEN_EXPIRED
 */

const api = (() => {
  /**
   * Base URL — otomatis deteksi environment:
   * - XAMPP lokal: /speakon/api
   * - InfinityFree/hosting root: /api
   */
  const BASE_URL = (() => {
    const path = window.location.pathname;
    // Jika diakses dari subfolder /speakon/
    if (path.startsWith('/speakon/')) return '/speakon/api';
    // Jika di root domain (hosting)
    return '/api';
  })();

  /** Whether a token refresh is currently in progress */
  let isRefreshing = false;

  /** Queue of requests waiting for token refresh to complete */
  let refreshQueue = [];

  // ── Token management ────────────────────────────────────────────────────────

  /**
   * Get the stored access token from localStorage.
   * @returns {string|null}
   */
  function getAccessToken() {
    return localStorage.getItem('speakon_access_token');
  }

  /**
   * Get the stored refresh token from localStorage.
   * @returns {string|null}
   */
  function getRefreshToken() {
    return localStorage.getItem('speakon_refresh_token');
  }

  /**
   * Store tokens in localStorage.
   * @param {string} accessToken
   * @param {string} [refreshToken]
   */
  function storeTokens(accessToken, refreshToken) {
    localStorage.setItem('speakon_access_token', accessToken);
    if (refreshToken) {
      localStorage.setItem('speakon_refresh_token', refreshToken);
    }
  }

  /**
   * Clear all stored tokens (called on logout or auth failure).
   */
  function clearTokens() {
    localStorage.removeItem('speakon_access_token');
    localStorage.removeItem('speakon_refresh_token');
    localStorage.removeItem('speakon_user');
  }

  // ── Token refresh ────────────────────────────────────────────────────────────

  /**
   * Attempt to refresh the access token using the stored refresh token.
   * Queues concurrent requests and resolves them all once refresh completes.
   *
   * @returns {Promise<string>} New access token
   * @throws {Error} If refresh fails
   */
  async function refreshAccessToken() {
    if (isRefreshing) {
      // Wait for the in-progress refresh to complete
      return new Promise((resolve, reject) => {
        refreshQueue.push({ resolve, reject });
      });
    }

    isRefreshing = true;
    const refreshToken = getRefreshToken();

    if (!refreshToken) {
      isRefreshing = false;
      clearTokens();
      redirectToLogin();
      throw new Error('No refresh token available');
    }

    try {
      const response = await fetch(`${BASE_URL}/auth/refresh`, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ refreshToken }),
      });

      const data = await response.json();

      if (!response.ok || !data.data?.accessToken) {
        throw new Error('Token refresh failed');
      }

      const newAccessToken = data.data.accessToken;
      storeTokens(newAccessToken);

      // Resolve all queued requests
      refreshQueue.forEach(({ resolve }) => resolve(newAccessToken));
      refreshQueue = [];

      return newAccessToken;

    } catch (error) {
      // Reject all queued requests
      refreshQueue.forEach(({ reject }) => reject(error));
      refreshQueue = [];
      clearTokens();
      redirectToLogin();
      throw error;

    } finally {
      isRefreshing = false;
    }
  }

  // ── Core request function ────────────────────────────────────────────────────

  /**
   * Make an authenticated HTTP request.
   *
   * Automatically:
   *   - Injects Authorization: Bearer <token> header
   *   - Retries once after refreshing token on 401 TOKEN_EXPIRED
   *   - Redirects to login on unrecoverable auth failure
   *
   * @param {string} endpoint   - API endpoint path (e.g. '/auth/login')
   * @param {Object} [options]  - Fetch options (method, body, headers, etc.)
   * @param {boolean} [retry]   - Internal flag to prevent infinite retry loops
   * @returns {Promise<Object>} - Parsed JSON response body
   * @throws {Object}           - Error object with code and message
   */
  async function request(endpoint, options = {}, retry = false) {
    const url     = `${BASE_URL}${endpoint}`;
    const token   = getAccessToken();
    const headers = { ...options.headers };

    // Inject auth token if available
    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
    }

    // Set Content-Type for JSON bodies
    if (options.body && typeof options.body === 'string') {
      headers['Content-Type'] = 'application/json';
    }

    const fetchOptions = { ...options, headers };

    let response;
    try {
      response = await fetch(url, fetchOptions);
    } catch (networkError) {
      throw { code: 'NETWORK_ERROR', message: 'Gagal terhubung ke server.' };
    }

    // Handle 401 — attempt token refresh (once)
    if (response.status === 401 && !retry) {
      let data;
      try {
        data = await response.json();
      } catch (_) {
        data = {};
      }

      if (data?.error?.code === 'TOKEN_EXPIRED') {
        try {
          await refreshAccessToken();
          return request(endpoint, options, true); // Retry with new token
        } catch (_) {
          throw { code: 'SESSION_EXPIRED', message: 'Sesi Anda telah berakhir. Silakan masuk kembali.' };
        }
      }

      throw data?.error || { code: 'UNAUTHORIZED', message: 'Autentikasi diperlukan.' };
    }

    // Parse JSON response
    let data;
    try {
      data = await response.json();
    } catch (_) {
      throw { code: 'PARSE_ERROR', message: 'Respons server tidak valid.' };
    }

    // Throw error objects for non-2xx responses
    if (!response.ok) {
      throw data?.error || { code: 'API_ERROR', message: 'Terjadi kesalahan.' };
    }

    return data;
  }

  // ── HTTP method helpers ──────────────────────────────────────────────────────

  /**
   * GET request.
   * @param {string} endpoint
   * @param {Object} [params] - Query string parameters
   * @returns {Promise<Object>}
   */
  async function get(endpoint, params = {}) {
    const query = Object.keys(params).length
      ? '?' + new URLSearchParams(params).toString()
      : '';
    return request(endpoint + query, { method: 'GET' });
  }

  /**
   * POST request with JSON body.
   * @param {string} endpoint
   * @param {Object} body
   * @returns {Promise<Object>}
   */
  async function post(endpoint, body = {}) {
    return request(endpoint, {
      method: 'POST',
      body:   JSON.stringify(body),
    });
  }

  /**
   * PUT request with JSON body.
   * @param {string} endpoint
   * @param {Object} body
   * @returns {Promise<Object>}
   */
  async function put(endpoint, body = {}) {
    return request(endpoint, {
      method: 'PUT',
      body:   JSON.stringify(body),
    });
  }

  /**
   * DELETE request.
   * @param {string} endpoint
   * @returns {Promise<Object>}
   */
  async function del(endpoint) {
    return request(endpoint, { method: 'DELETE' });
  }

  /**
   * POST request with FormData (for file uploads).
   * @param {string}   endpoint
   * @param {FormData} formData
   * @returns {Promise<Object>}
   */
  async function upload(endpoint, formData) {
    // Do NOT set Content-Type — browser sets it with boundary for multipart
    return request(endpoint, {
      method: 'POST',
      body:   formData,
    });
  }

  // ── Utility ──────────────────────────────────────────────────────────────────

  /**
   * Redirect to the login page.
   */
  function redirectToLogin() {
    const base = window.location.pathname.startsWith('/speakon/') ? '/speakon' : '';
    if (!window.location.pathname.includes('login.html')) {
      window.location.href = base + '/login.html';
    }
  }

  // Public API
  return { get, post, put, delete: del, upload, storeTokens, clearTokens, getAccessToken, getRefreshToken };
})();

window.api = api;

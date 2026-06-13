/**
 * SpeakOn! — i18n Module
 *
 * Provides bilingual support (Bahasa Indonesia / English).
 * Loads translation JSON files and applies them to DOM elements
 * with the data-i18n attribute.
 *
 * Requirements covered:
 *   - 10.1 : Support Bahasa Indonesia and English
 *   - 10.2 : Language can be switched at runtime
 *   - 10.3 : Language preference is persisted
 */

const i18n = (() => {
  /** @type {Object} Currently loaded translations */
  let translations = {};

  /** @type {string} Current language code ('id' or 'en') */
  let currentLang = 'id';

  /** @type {string} Base URL for i18n JSON files */
  const I18N_BASE = (() => {
    const path = window.location.pathname;
    // Deteksi subfolder otomatis dari path (misal /speaking/, /speakon/, dll.)
    const match = path.match(/^(\/[^/]+\/)/);
    const subfolder = match ? match[1] : '/';
    return subfolder + 'frontend/i18n/';
  })();

  /**
   * Load translations for the given language and apply them to the DOM.
   *
   * @param {string} lang - Language code: 'id' or 'en'
   * @returns {Promise<void>}
   */
  async function setLanguage(lang) {
    if (!['id', 'en'].includes(lang)) {
      console.warn(`[i18n] Unsupported language: ${lang}. Falling back to 'id'.`);
      lang = 'id';
    }

    try {
      const response = await fetch(`${I18N_BASE}${lang}.json`);
      if (!response.ok) {
        throw new Error(`Failed to load ${lang}.json: ${response.status}`);
      }
      translations = await response.json();
      currentLang  = lang;

      // Persist preference to localStorage
      localStorage.setItem('speakon_lang', lang);

      // Apply translations to the DOM
      applyTranslations();

      // Dispatch event so other modules can react
      document.dispatchEvent(new CustomEvent('languageChanged', { detail: { lang } }));

    } catch (error) {
      console.error('[i18n] Failed to load translations:', error);
    }
  }

  /**
   * Get a translated string by dot-notation key.
   *
   * Supports simple variable interpolation: t('levels.progressPercent', { percent: 60 })
   * → "60% Selesai"
   *
   * @param {string} key       - Dot-notation key (e.g. 'auth.errors.invalidCredentials')
   * @param {Object} [vars={}] - Variables to interpolate into the string
   * @returns {string}         - Translated string, or the key itself if not found
   */
  function t(key, vars = {}) {
    const parts  = key.split('.');
    let   result = translations;

    for (const part of parts) {
      if (result === undefined || result === null) {
        return key; // Key not found — return the key as fallback
      }
      result = result[part];
    }

    if (typeof result !== 'string') {
      return key;
    }

    // Interpolate variables: replace {varName} with vars.varName
    return result.replace(/\{(\w+)\}/g, (_, varName) => {
      return vars[varName] !== undefined ? String(vars[varName]) : `{${varName}}`;
    });
  }

  /**
   * Get the current language code.
   *
   * @returns {string} 'id' or 'en'
   */
  function getCurrentLanguage() {
    return currentLang;
  }

  /**
   * Apply translations to all DOM elements with a data-i18n attribute.
   *
   * Supported attribute formats:
   *   - data-i18n="key"                → sets element.textContent
   *   - data-i18n-placeholder="key"    → sets element.placeholder
   *   - data-i18n-title="key"          → sets element.title
   *   - data-i18n-aria-label="key"     → sets element.ariaLabel
   *
   * @returns {void}
   */
  function applyTranslations() {
    // Text content
    document.querySelectorAll('[data-i18n]').forEach(el => {
      const key = el.getAttribute('data-i18n');
      el.textContent = t(key);
    });

    // Placeholder attribute
    document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
      const key = el.getAttribute('data-i18n-placeholder');
      el.placeholder = t(key);
    });

    // Title attribute
    document.querySelectorAll('[data-i18n-title]').forEach(el => {
      const key = el.getAttribute('data-i18n-title');
      el.title = t(key);
    });

    // aria-label attribute
    document.querySelectorAll('[data-i18n-aria-label]').forEach(el => {
      const key = el.getAttribute('data-i18n-aria-label');
      el.setAttribute('aria-label', t(key));
    });

    // Update the lang attribute on <html>
    document.documentElement.lang = currentLang;
  }

  /**
   * Initialise i18n by loading the user's preferred language.
   *
   * Priority: URL param → localStorage → user profile → default ('id')
   *
   * @param {string} [userPref] - Language preference from user profile (optional)
   * @returns {Promise<void>}
   */
  async function init(userPref) {
    const urlParam  = new URLSearchParams(window.location.search).get('lang');
    const stored    = localStorage.getItem('speakon_lang');
    const preferred = urlParam || userPref || stored || 'id';

    await setLanguage(preferred);
  }

  // Public API
  return { setLanguage, t, getCurrentLanguage, applyTranslations, init };
})();

// Make available globally
window.i18n = i18n;

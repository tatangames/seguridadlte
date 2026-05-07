(function () {
    if (!window.AppConfig) return;

    const SERVER_DEFAULT = window.AppConfig.themeDefault;
    const UPDATE_URL = window.AppConfig.updateThemeUrl;

    const iconEl = document.getElementById('theme-icon');

    // CSRF (axios ya lo suele tener, pero por seguridad)
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (token) axios.defaults.headers.common['X-CSRF-TOKEN'] = token;

    function applyTheme(mode) {
        const dark = mode === 'dark';

        document.body.classList.toggle('dark-mode', dark);
        document.documentElement.setAttribute(
            'data-bs-theme',
            dark ? 'dark' : 'light'
        );

        if (iconEl) {
            iconEl.classList.remove('fa-sun', 'fa-moon');
            iconEl.classList.add(dark ? 'fa-moon' : 'fa-sun');
        }
    }

    function themeToInt(mode) {
        return mode === 'dark' ? 1 : 0;
    }

    function intToTheme(v) {
        return v === 1 ? 'dark' : 'light';
    }

    // 🔹 Tema inicial desde servidor
    applyTheme(intToTheme(SERVER_DEFAULT));

    let saving = false;

    document.addEventListener('click', async (e) => {
        const a = e.target.closest('.dropdown-item[data-theme]');
        if (!a || saving) return;

        e.preventDefault();

        const selectedMode = a.dataset.theme;
        const newValue = themeToInt(selectedMode);

        const previousMode =
            document.documentElement.getAttribute('data-bs-theme') === 'dark'
                ? 'dark'
                : 'light';

        applyTheme(selectedMode);

        try {
            saving = true;
            await axios.post(UPDATE_URL, { tema: newValue });
            if (window.toastr) toastr.success('Tema actualizado');
        } catch (e) {
            applyTheme(previousMode);
            if (window.toastr) toastr.error('No se pudo actualizar el tema');
        } finally {
            saving = false;
        }
    });
})();

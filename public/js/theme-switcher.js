// Theme Switcher - ChallengeHub
document.addEventListener('DOMContentLoaded', function () {
    const themeToggle = document.getElementById('themeToggle');
    if (!themeToggle) return;

    themeToggle.addEventListener('click', function () {
        // id="theme-css" est injecté par PHP si on est en mode nuit
        const isNight = document.getElementById('theme-css') !== null;
        const newTheme = isNight ? 'day' : 'night';

        // Redirige vers ThemeController qui sauvegarde en session
        // puis revient sur la même page → plus de blocage
        window.location.href = 'index.php?action=switchTheme'
            + '&theme=' + newTheme
            + '&redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
    });
});
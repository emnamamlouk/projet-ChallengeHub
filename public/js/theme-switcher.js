// Theme Switcher - ChallengeHub
document.addEventListener('DOMContentLoaded', function () {
    const themeToggle = document.getElementById('themeToggle');

    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            // Vérifier si le CSS nuit est chargé (id="theme-css" mis par PHP)
            const isNight = document.getElementById('theme-css') !== null;
            const newTheme = isNight ? 'day' : 'night';

            // Redirection : PHP sauvegarde en session PUIS redirige vers la page actuelle
            window.location.href = 'index.php?action=switchTheme&theme=' + newTheme
                + '&redirect=' + encodeURIComponent(window.location.href);
        });
    }
});
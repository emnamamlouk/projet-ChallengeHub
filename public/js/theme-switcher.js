// Theme Switcher - ChallengeHub
document.addEventListener('DOMContentLoaded', function() {
    console.log('Theme switcher chargé !');
    
    const themeToggle = document.getElementById('themeToggle');
    
    if (themeToggle) {
        console.log('Bouton trouvé !');
        
        themeToggle.addEventListener('click', function() {
            const btn = this;
            const currentIcon = btn.querySelector('i').className;
            const isNight = currentIcon.includes('sun');
            
            // Animation
            btn.style.transform = 'scale(0.95)';
            setTimeout(() => btn.style.transform = 'scale(1)', 200);
            
            if (isNight) {
                // Mode jour
                document.getElementById('night-theme')?.remove();
                btn.innerHTML = '<i class="fas fa-moon"></i><span>Mode nuit</span>';
                fetch('index.php?action=switchTheme&theme=day');
            } else {
                // Mode nuit
                if (!document.getElementById('night-theme')) {
                    const link = document.createElement('link');
                    link.id = 'night-theme';
                    link.rel = 'stylesheet';
                    link.href = 'public/css/themes/night.css';
                    document.head.appendChild(link);
                }
                btn.innerHTML = '<i class="fas fa-sun"></i><span>Mode jour</span>';
                fetch('index.php?action=switchTheme&theme=night');
            }
        });
    } else {
        console.log('Bouton NON trouvé !');
    }
});
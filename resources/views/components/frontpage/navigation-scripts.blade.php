<script>
    (function () {
        const html = document.documentElement;
        const themeButton = document.getElementById('theme-toggle');
        const sun = document.getElementById('icon-sun');
        const moon = document.getElementById('icon-moon');
        const mobileMenuButton = document.getElementById('mobile-menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');
        const menuIcon = document.getElementById('icon-menu');
        const closeIcon = document.getElementById('icon-close');
        const mobileMenuLinks = document.querySelectorAll('.mobile-menu-link');

        if (!themeButton || !sun || !moon || !mobileMenuButton || !mobileMenu || !menuIcon || !closeIcon) {
            return;
        }

        function applyTheme(dark) {
            if (dark) {
                html.classList.add('dark');
                sun.classList.remove('hidden');
                moon.classList.add('hidden');
            } else {
                html.classList.remove('dark');
                sun.classList.add('hidden');
                moon.classList.remove('hidden');
            }
        }

        function setMobileMenuState(open) {
            mobileMenu.classList.toggle('hidden', !open);
            menuIcon.classList.toggle('hidden', open);
            closeIcon.classList.toggle('hidden', !open);
            mobileMenuButton.setAttribute('aria-expanded', open ? 'true' : 'false');
            mobileMenuButton.setAttribute('aria-label', open ? 'Luk menu' : 'Åbn menu');
        }

        applyTheme(html.classList.contains('dark'));
        setMobileMenuState(false);

        themeButton.addEventListener('click', function () {
            const isDark = html.classList.contains('dark');
            applyTheme(!isDark);
            localStorage.setItem('theme', isDark ? 'light' : 'dark');
        });

        mobileMenuButton.addEventListener('click', function () {
            const isOpen = mobileMenuButton.getAttribute('aria-expanded') === 'true';
            setMobileMenuState(!isOpen);
        });

        mobileMenuLinks.forEach(function (link) {
            link.addEventListener('click', function () {
                setMobileMenuState(false);
            });
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth >= 768) {
                setMobileMenuState(false);
            }
        });
    })();
</script>
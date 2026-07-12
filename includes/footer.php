<footer class="bg-white border-t border-slate-200 mt-12 py-6 text-center text-xs text-slate-400">
        &copy; 2026 Nova News. Bringing Light to Turth. All right reserved.
    </footer>

    <script>
        const themeToggle = document.getElementById('theme-toggle');
        const themeIcon = themeToggle.querySelector('i');
        const body = document.body;
        const storedTheme = localStorage.getItem('nova-news-theme');
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        const initialTheme = storedTheme || (prefersDark ? 'dark' : 'light');

        const applyTheme = (theme) => {
            const isDark = theme === 'dark';
            body.classList.toggle('theme-dark', isDark);
            body.classList.toggle('theme-light', !isDark);
            themeIcon.className = isDark ? 'fa-solid fa-sun text-lg' : 'fa-solid fa-moon text-lg';
            themeToggle.title = isDark ? 'Switch to day mode' : 'Switch to night mode';
            localStorage.setItem('nova-news-theme', theme);
        };

        applyTheme(initialTheme);

        themeToggle.addEventListener('click', () => {
            const nextTheme = body.classList.contains('theme-dark') ? 'light' : 'dark';
            applyTheme(nextTheme);
        });

        const filterButtons = document.querySelectorAll('.filter-btn');
        const newsGrid = document.getElementById('latest-news-grid');

        const setActiveButton = (activeFilter) => {
            filterButtons.forEach((button) => {
                const isActive = button.dataset.filter === activeFilter;
                button.classList.toggle('bg-[#5B41FF]', isActive);
                button.classList.toggle('text-white', isActive);
                button.classList.toggle('text-slate-600', !isActive);
            });
        };

        const fetchPosts = (filter) => {
            if (!newsGrid) return;
            newsGrid.style.opacity = '0.4';
            newsGrid.style.transition = 'opacity 0.2s';

            const baseUrl = window.location.pathname.includes('/user/') ? 'fetch-posts.php' : '../user/fetch-posts.php';
            fetch(baseUrl + '?type=' + encodeURIComponent(filter))
                .then((res) => res.json())
                .then((data) => {
                    if (data.html) {
                        newsGrid.innerHTML = data.html;
                    } else {
                        newsGrid.innerHTML = '<div class="col-span-2 text-center text-slate-400 py-12 text-sm">No articles found.</div>';
                    }
                    newsGrid.style.opacity = '1';
                })
                .catch(() => {
                    newsGrid.style.opacity = '1';
                });

            setActiveButton(filter);
        };

        filterButtons.forEach((button) => {
            button.addEventListener('click', () => {
                fetchPosts(button.dataset.filter);
            });
        });

        const activeFilter = document.querySelector('.filter-btn.bg-\\[\\#5B41FF\\]');
        if (activeFilter) {
            setActiveButton(activeFilter.dataset.filter);
        }
    </script>
</body>
</html>

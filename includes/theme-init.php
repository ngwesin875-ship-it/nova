<style>
    body {
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .dark {
        background-color: #020617;
        color: #E2E8F0;
    }

    .dark header,
    .dark footer,
    .dark .bg-white,
    .dark .bg-slate-100,
    .dark .bg-slate-50 {
        background-color: #111827 !important;
    }

    .dark .border-slate-200,
    .dark .border-slate-100 {
        border-color: #334155 !important;
    }

    .dark .text-slate-900,
    .dark .text-slate-800,
    .dark .text-slate-700,
    .dark .text-slate-600,
    .dark .text-slate-500,
    .dark .text-slate-400,
    .dark .text-slate-300,
    .dark .text-slate-950 {
        color: #E2E8F0 !important;
    }

    .dark .text-slate-400 {
        color: #94A3B8 !important;
    }

    .text-theme-adaptive {
        color: #0F172A;
    }

    .dark .text-theme-adaptive {
        color: #FFFFFF;
    }

    .dark .bg-slate-900,
    .dark .bg-slate-950 {
        background-color: #0F172A !important;
    }

    .dark .hover\:bg-slate-50:hover {
        background-color: #1E293B !important;
    }

    .dark .hover\:bg-slate-100:hover {
        background-color: #1E293B !important;
    }

    .dark .divide-slate-200 > :not([hidden]) ~ :not([hidden]) {
        border-color: #334155 !important;
    }

    .dark .from-amber-50 {
        --tw-gradient-from: #422006 !important;
        --tw-gradient-to: #0F172A !important;
    }

    .dark .hover\:border-slate-300:hover {
        border-color: #475569 !important;
    }

    .dark .text-blue-500 {
        color: #60A5FA !important;
    }

    .dark .hover\:text-red-600:hover {
        color: #FCA5A5 !important;
    }

    .dark .text-amber-600 {
        color: #FCD34D !important;
    }

    .dark .hover\:text-amber-700:hover {
        color: #FCD34D !important;
    }

    .dark .hover\:text-slate-900:hover {
        color: #F1F5F9 !important;
    }

    .dark .text-green-600 {
        color: #4ADE80 !important;
    }

    .dark .text-yellow-600 {
        color: #FACC15 !important;
    }

    .dark .text-red-600 {
        color: #F87171 !important;
    }

    .dark .text-gray-900,
    .dark .text-gray-800 {
        color: #E2E8F0 !important;
    }

    .dark .text-gray-700,
    .dark .text-gray-600 {
        color: #CBD5E1 !important;
    }

    .dark .text-gray-500,
    .dark .text-gray-400 {
        color: #94A3B8 !important;
    }

    .dark .text-gray-300 {
        color: #64748B !important;
    }

    .dark .bg-gray-50 {
        background-color: #1E293B !important;
    }

    .dark .border-gray-200,
    .dark .border-gray-100 {
        border-color: #334155 !important;
    }

    .dark .border-gray-50 {
        border-color: #334155 !important;
    }

    .dark .bg-red-50 {
        background-color: #450A0A !important;
    }

    .dark .text-red-700 {
        color: #FCA5A5 !important;
    }

    .dark .bg-green-50 {
        background-color: #052E16 !important;
    }

    .dark .text-green-700 {
        color: #86EFAC !important;
    }

    .dark .text-green-500 {
        color: #4ADE80 !important;
    }

    .dark .text-amber-500 {
        color: #FCD34D !important;
    }

    .dark .text-amber-700 {
        color: #FDE68A !important;
    }

    .dark .bg-amber-50,
    .dark .bg-amber-50\/50 {
        background-color: #422006 !important;
    }

    .dark .bg-blue-50 {
        background-color: #1E3A5F !important;
    }

    .dark .text-purple-500 {
        color: #C4B5FD !important;
    }

    .dark .bg-purple-50 {
        background-color: #2E1065 !important;
    }

    .dark .text-emerald-500 {
        color: #6EE7B7 !important;
    }

    .dark .bg-emerald-50 {
        background-color: #064E3B !important;
    }

    .dark .bg-emerald-50\/50 {
        background-color: rgba(6, 78, 59, 0.5) !important;
    }

    .dark .text-emerald-700 {
        color: #A7F3D0 !important;
    }

    .dark .border-amber-200\/60 {
        border-color: rgba(217, 119, 6, 0.3) !important;
    }

    .dark .border-red-200 {
        border-color: rgba(252, 165, 165, 0.3) !important;
    }

    .dark .border-green-200 {
        border-color: rgba(134, 239, 172, 0.3) !important;
    }

    .dark .border-amber-100 {
        border-color: #422006 !important;
    }

    .dark .border-amber-200 {
        border-color: rgba(217, 119, 6, 0.3) !important;
    }

    .dark .text-red-500 {
        color: #FCA5A5 !important;
    }

    .dark .text-amber-400 {
        color: #FDE68A !important;
    }

    .dark .text-green-400 {
        color: #86EFAC !important;
    }

    .dark .from-orange-50 {
        --tw-gradient-from: #431407 !important;
    }

    .dark .to-orange-50 {
        --tw-gradient-to: #0F172A !important;
    }

    .dark .hover\:bg-amber-50:hover {
        background-color: #422006 !important;
    }

    .dark .hover\:bg-amber-50\/50:hover {
        background-color: rgba(66, 32, 6, 0.5) !important;
    }

    .dark .shadow-sm {
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.4) !important;
    }

    .dark .shadow-xl {
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.35) !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('theme-toggle');
    const html = document.documentElement;
    const icon = toggleBtn ? toggleBtn.querySelector('i') : null;

    // Initialize theme from localStorage
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        html.classList.add('dark');
        if (icon) {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        }
    } else {
        html.classList.remove('dark');
    }
    
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            if (html.classList.contains('dark')) {
                // Switch to Light Mode
                html.classList.remove('dark');
                localStorage.setItem('theme', 'light');
                if (icon) {
                    icon.classList.remove('fa-sun');
                    icon.classList.add('fa-moon');
                }
            } else {
                // Switch to Dark Mode
                html.classList.add('dark');
                localStorage.setItem('theme', 'dark');
                if (icon) {
                    icon.classList.remove('fa-moon');
                    icon.classList.add('fa-sun');
                }
            }
        });
    }
});
</script>

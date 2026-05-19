document.addEventListener('DOMContentLoaded', function() {
    const navToggle = document.getElementById('navToggle');
    const siteNav = document.getElementById('siteNav');

    if (navToggle && siteNav) {
        navToggle.addEventListener('click', function() {
            siteNav.classList.toggle('open');
        });
    }
});

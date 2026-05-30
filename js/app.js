document.addEventListener('DOMContentLoaded', function() {
    const navToggle = document.getElementById('navToggle');
    const siteNav = document.getElementById('siteNav');
    const suggestionList = document.getElementById('movieSearchSuggestions');
    const searchInputs = document.querySelectorAll('input[type="search"][name="q"]');
    let suggestionTimer = null;

    if (navToggle && siteNav) {
        navToggle.addEventListener('click', function() {
            const isOpen = siteNav.classList.toggle('open');
            navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    }

    if (suggestionList && searchInputs.length) {
        searchInputs.forEach((input) => {
            input.addEventListener('input', function() {
                const query = input.value.trim();
                clearTimeout(suggestionTimer);

                if (query.length < 1) {
                    suggestionList.innerHTML = '';
                    return;
                }

                suggestionTimer = setTimeout(function() {
                    fetch(`search_suggest.php?q=${encodeURIComponent(query)}`)
                        .then((response) => response.ok ? response.json() : [])
                        .then((movies) => {
                            suggestionList.innerHTML = '';

                            movies.forEach((movie) => {
                                const option = document.createElement('option');
                                option.value = movie.title;
                                option.label = movie.genre ? `${movie.title} - ${movie.genre}` : movie.title;
                                suggestionList.appendChild(option);
                            });
                        })
                        .catch(() => {
                            suggestionList.innerHTML = '';
                        });
                }, 180);
            });
        });
    }
});

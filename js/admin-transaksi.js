const searchInput = document.querySelector('.search-input');

if (searchInput) {
    let timer;
    searchInput.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () {
            const search = searchInput.value.trim();
            let url = window.location.pathname;
            if (search !== '') {
                url += '?search=' + encodeURIComponent(search) + '&page=1';
            }
            window.location.href = url;
        }, 400);
    });
}
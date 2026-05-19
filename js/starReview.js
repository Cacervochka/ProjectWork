document.querySelectorAll(".reviewElement form").forEach(form => {
    const stars = form.querySelectorAll(".starRating span");
    const ratingInput = form.querySelector(".ratingInput");

    let selectedRating = Number(ratingInput.value) || 0;

    function render(value) {
        stars.forEach(star => {
            const v = Number(star.dataset.value);
            star.classList.toggle("active", v <= value);
        });
    }

    stars.forEach(star => {
        star.addEventListener("click", () => {
            selectedRating = Number(star.dataset.value);
            ratingInput.value = selectedRating;
            render(selectedRating);
        });

        star.addEventListener("mouseover", () => {
            render(Number(star.dataset.value));
        });

        star.addEventListener("mouseout", () => {
            render(selectedRating);
        });
    });

    render(selectedRating);
});
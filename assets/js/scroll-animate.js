document.addEventListener("DOMContentLoaded", function () {

    const animatedElements = document.querySelectorAll(".scroll-animate");

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            const el = entry.target;

            if (entry.isIntersecting) {
                el.classList.add("animated");
            }
        });
    }, {
        threshold: 0.35
    });

    animatedElements.forEach(el => observer.observe(el));

});

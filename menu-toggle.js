document.addEventListener('DOMContentLoaded', function () {
    const menuToggle = document.querySelector('.fa-bars');
    const menu = document.querySelector('.menu');

    if (menuToggle && menu) {
        menuToggle.addEventListener('click', function () {
            menu.classList.toggle('active');
        });
    }

    // Dropdown submenu toggle
    const submenuParents = document.querySelectorAll('.menu-item.has-submenu > a');
    submenuParents.forEach(parent => {
        parent.addEventListener('click', function (e) {
            e.preventDefault();
            const submenu = this.nextElementSibling;
            if (submenu) {
                submenu.classList.toggle('open');
            }
        });
    });
});

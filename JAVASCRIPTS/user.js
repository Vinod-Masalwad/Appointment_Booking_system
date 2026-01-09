
const userSidebar = document.getElementById("userSidebar");
const toggleBtn = document.getElementById("userToggleBtn");

toggleBtn.addEventListener("click", () => {
    userSidebar.classList.toggle("collapsed");
});

// AJAX LOAD
document.addEventListener("DOMContentLoaded", () => {

    const items = document.querySelectorAll(".user-menu li, .user-bottom div");
    const contentArea = document.getElementById("user-content-area");

    loadPage("dashboard.php");

    items.forEach(item => {
        item.addEventListener("click", () => {

            if (item.classList.contains("locked")) return;

            document.querySelector(".user-sidebar .active")?.classList.remove("active");
            item.classList.add("active");

            const page = item.dataset.page;
            if (page) loadPage(page);
        });
    });

    function loadPage(page) {
        fetch(page)
            .then(res => res.text())
            .then(data => contentArea.innerHTML = data)
            .catch(() => contentArea.innerHTML = "<h3>Page not found</h3>");
    }
});

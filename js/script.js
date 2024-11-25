const sidebarToggle = document.querySelector("#sidebar-toggle");
const sidebar = document.querySelector("#sidebar");

sidebar.classList.remove("collapsed");

sidebarToggle.addEventListener("click", function() {
    sidebar.classList.toggle("collapsed");
});

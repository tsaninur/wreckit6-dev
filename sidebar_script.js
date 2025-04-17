function toggleSidebar() {
    let sidebar = document.getElementById("sidebar");
    let isOpen = sidebar.classList.contains("open");

    if (isOpen) {
        sidebar.style.boxShadow = "none";
        sidebar.classList.remove("open");
    } else {
        sidebar.style.boxShadow = "2px 0 20px rgba(255, 255, 0, 0.5)";
        sidebar.classList.add("open");
    }
}

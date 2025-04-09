function toggleDropdown(category) {
    const dropdown = document.getElementById(`${category}-dropdown`);
    const arrow = document.getElementById(`${category}-arrow`);

    if (dropdown.style.display === "block") {
        dropdown.style.display = "none";
        arrow.classList.remove("rotate");
    } else {
        dropdown.style.display = "block";
        arrow.classList.add("rotate");
    }
}
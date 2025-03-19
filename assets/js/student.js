document.addEventListener("DOMContentLoaded", function () {
    const profileButton = document.getElementById("profileButton");
    const dropdownMenu = document.getElementById("profileDropdown");
  
    profileButton.addEventListener("click", function (event) {
      event.stopPropagation();
      dropdownMenu.classList.toggle("active");
    });
  
    document.addEventListener("click", function (event) {
      if (!profileButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
        dropdownMenu.classList.remove("active");
      }
    });
  });
  
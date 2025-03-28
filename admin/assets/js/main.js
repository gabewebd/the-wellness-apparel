document.addEventListener("DOMContentLoaded", function () {
    const profileIcon = document.getElementById("profile-icon");
    const profileMenu = document.getElementById("profile-menu");

    if (profileIcon && profileMenu) {
        profileIcon.addEventListener("click", function (event) {
            event.stopPropagation();
            profileMenu.classList.toggle("show");
        });

        document.addEventListener("click", function (event) {
            if (!profileMenu.contains(event.target) && event.target !== profileIcon) {
                profileMenu.classList.remove("show");
            }
        });
    }

    // Notifications Dropdown
    const notificationIcon = document.getElementById("notification-icon");
    const notificationsDropdown = document.getElementById("notifications-dropdown");

    if (notificationIcon && notificationsDropdown) {
        notificationIcon.addEventListener("click", function (event) {
            event.stopPropagation();
            notificationsDropdown.classList.toggle("show");
        });

        document.addEventListener("click", function (event) {
            if (!notificationsDropdown.contains(event.target) && event.target !== notificationIcon) {
                notificationsDropdown.classList.remove("show");
            }
        });
    }
});

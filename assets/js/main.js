
// josh dave

// document.addEventListener("DOMContentLoaded", function () {
//     const hamburger = document.getElementById("hamburger");
//     const sidebarOverlay = document.getElementById("sidebar-overlay");
//     const sidebarClose = document.getElementById("sidebar-close");

//     // Open Sidebar
//     hamburger.addEventListener("click", function () {
//         sidebarOverlay.classList.add("active");
//     });

//     // Close Sidebar when clicking outside or on the X button
//     function closeSidebar() {
//         sidebarOverlay.classList.remove("active");
//     }

//     sidebarOverlay.addEventListener("click", function (event) {
//         if (event.target === sidebarOverlay) {
//             closeSidebar();
//         }
//     });

//     sidebarClose.addEventListener("click", closeSidebar);
// });

document.addEventListener("DOMContentLoaded", function () {
    const profileIcon = document.getElementById("profile-icon");
    const profileMenu = document.getElementById("profile-menu");

    profileIcon.addEventListener("click", function (event) {
        event.stopPropagation();
        profileMenu.classList.toggle("show");
    });

    document.addEventListener("click", function (event) {
        if (!profileMenu.contains(event.target) && event.target !== profileIcon) {
            profileMenu.classList.remove("show");
        }
    });

    // Notifications Dropdown
    const notificationIcon = document.getElementById("notification-icon");
    const notificationsDropdown = document.getElementById("notifications-dropdown");

    notificationIcon.addEventListener("click", function (event) {
        event.stopPropagation();
        notificationsDropdown.classList.toggle("show");
    });

    document.addEventListener("click", function (event) {
        if (!notificationsDropdown.contains(event.target) && event.target !== notificationIcon) {
            notificationsDropdown.classList.remove("show");
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const notificationIcon = document.getElementById("notification-icon");
    const notificationsDropdown = document.getElementById("notifications-dropdown");

    if (notificationIcon && notificationsDropdown) {
        notificationIcon.addEventListener("click", function (event) {
            event.stopPropagation(); // Prevents click event from bubbling up
            notificationsDropdown.classList.toggle("show");
        });

        // Hide dropdown when clicking outside
        document.addEventListener("click", function (event) {
            if (!notificationsDropdown.contains(event.target) && event.target !== notificationIcon) {
                notificationsDropdown.classList.remove("show");
            }
        });
    }
});




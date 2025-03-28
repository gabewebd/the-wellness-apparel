document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("signup-form");

    form.addEventListener("submit", function (event) {
        event.preventDefault(); // Prevent form submission

        // Clear previous errors
        document.querySelectorAll(".error-message").forEach(el => el.innerText = "");

        // Get form values
        const displayName = document.getElementById("display-name").value.trim();
        const email = document.getElementById("email").value.trim();
        const username = document.getElementById("username").value.trim();
        const password = document.getElementById("password").value.trim();
        const terms = document.getElementById("terms").checked;

        let isValid = true;

        // Display Name validation
        if (displayName === "") {
            document.getElementById("display-name-error").innerText = "Display name is required.";
            isValid = false;
        }

        // Email validation
        if (!email.match(/^\S+@\S+\.\S+$/)) {
            document.getElementById("email-error").innerText = "Invalid email format.";
            isValid = false;
        }

        // Username validation (min 4 characters, only letters, numbers, underscores)
        if (!username.match(/^[a-zA-Z0-9_]{4,}$/)) {
            document.getElementById("username-error").innerText = "Username must be at least 4 characters and contain only letters, numbers, and underscores.";
            isValid = false;
        }

        // Password validation (min 8 characters, at least 1 number & special character)
        if (!password.match(/^(?=.*[0-9])(?=.*[!@#$%^&*])[a-zA-Z0-9!@#$%^&*]{8,}$/)) {
            document.getElementById("password-error").innerText = "Password must be at least 8 characters long and include at least one number and one special character.";
            isValid = false;
        }

        // Terms & Conditions checkbox validation
        if (!terms) {
            document.getElementById("terms-error").innerText = "You must agree to the Terms & Conditions.";
            isValid = false;
        }

        // If all validations pass, submit the form using AJAX
        if (isValid) {
            const formData = new FormData(form);
            fetch("sign-up_process.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "error") {
                    if (data.message.includes("Username")) {
                        document.getElementById("username-error").innerText = data.message;
                    } else if (data.message.includes("email")) {
                        document.getElementById("email-error").innerText = data.message;
                    } else {
                        alert(data.message); // Other errors
                    }
                } else if (data.status === "success") {
                    alert(data.message);
                    window.location.href = "login.php"; // Redirect on success
                }
            })
            .catch(error => console.error("Error:", error));
        }
    });
});

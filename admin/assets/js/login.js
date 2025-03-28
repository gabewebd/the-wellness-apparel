document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector("#login-form");
    const errorMessage = document.querySelector("#error-message");

    form.addEventListener("submit", function (e) {
        errorMessage.innerText = "";
        let errors = [];

        const email = document.querySelector("#email").value.trim();
        const password = document.querySelector("#password").value.trim();

        if (!email.includes("@") || !email.includes(".")) {
            errors.push("Invalid email format. Please enter a valid email address.");
        }

        if (password === "") {
            errors.push("Password cannot be empty. Please enter your password.");
        }

        if (errors.length > 0) {
            e.preventDefault();
            errorMessage.innerHTML = errors.join("<br>");
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const signupForm = document.getElementById("signup-form");
    
    signupForm.addEventListener("submit", function (event) {
        event.preventDefault();
        
        let formData = new FormData(signupForm);
        let errorMessages = document.querySelectorAll(".error-message");
        errorMessages.forEach(msg => msg.textContent = "");
        
        fetch("sign-up_process.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);  // Add this to debug
            if (data.status === "error") {
                handleErrors(data.message);
            } else {
                alert("Registration successful! Redirecting to login...");
                window.location.href = "login.php";
            }
        })
        .catch(error => console.error("Error:", error));
        
    });

    function handleErrors(message) {
        if (message.includes("Display Name")) {
            document.getElementById("display-name-error").textContent = message;
        } else if (message.includes("email")) {
            document.getElementById("email-error").textContent = message;
        } else if (message.includes("Username")) {
            document.getElementById("username-error").textContent = message;
        } else if (message.includes("Password")) {
            document.getElementById("password-error").textContent = message;
        } else if (message.includes("Terms")) {
            document.getElementById("terms-error").textContent = message;
        }
    }
});
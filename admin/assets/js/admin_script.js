function validateAdminRegister() {
    let name = document.getElementById("name").value.trim();
    let email = document.getElementById("email").value.trim();
    let password = document.getElementById("password").value.trim();
    let confirmPassword = document.getElementById("confirm_password").value.trim();

    if (name === "") {
        alert("Please enter admin name");
        return false;
    }

    if (email === "") {
        alert("Please enter admin email");
        return false;
    }

    if (password === "") {
        alert("Please enter password");
        return false;
    }

    if (password.length < 6) {
        alert("Password must be at least 6 characters");
        return false;
    }

    if (password !== confirmPassword) {
        alert("Password and confirm password do not match");
        return false;
    }

    return true;
}

function validateAdminLogin() {
    let email = document.getElementById("email").value.trim();
    let password = document.getElementById("password").value.trim();

    if (email === "") {
        alert("Please enter admin email");
        return false;
    }

    if (password === "") {
        alert("Please enter password");
        return false;
    }

    return true;
}
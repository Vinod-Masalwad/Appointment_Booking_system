/* ============================================================
   PASSWORD TOGGLE
============================================================ */
document.getElementById("togglePass1").addEventListener("click", () => {
    const pass = document.getElementById("password");
    const icon = document.getElementById("togglePass1");

    if (pass.type === "password") {
        pass.type = "text";
        icon.textContent = "visibility";
    } else {
        pass.type = "password";
        icon.textContent = "visibility_off";
    }
});

/* ============================================================
   SHOW ERROR TOAST
============================================================ */
function showErrorToast(message) {
    const toast = document.getElementById("errorToast");
    toast.innerText = message;
    toast.classList.add("show");
    setTimeout(() => toast.classList.remove("show"), 3000);
}

/* ============================================================
   LOGIN SUBMIT
============================================================ */
document.getElementById("btn1").addEventListener("click", function (e) {
    e.preventDefault();

    const email = document.getElementById("email").value.trim();
    const pass = document.getElementById("password").value.trim();

    if (!email || !pass) {
        showErrorToast("Please fill all fields!");
        return;
    }

    let formData = new FormData();
    formData.append("email", email);
    formData.append("password", pass);
    formData.append("login", true);

    fetch("login.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(data => {

        if (data === "USER_OK") {
            window.location.href = "./USER/user.php";
        }
        else if (data === "ADMIN_OK") {
            window.location.href = "./ADMIN/admin.php";
        }
        else if (data === "WRONG_PASS") {
            showErrorToast("Wrong password!");
        }
        else if (data === "NO_ACCOUNT") {
            alert("No account found! Please register.");
            showErrorToast("No account found! Please register. redirecting...");
            setTimeout(() => window.location.href = "register.php", 2000);
        }
        else {
            showErrorToast("Server error!");
        }
    });
});

/* ============================================================
   PASSWORD TOGGLE
============================================================ */
function togglePassword(passId, toggleId) {
    const pass = document.getElementById(passId);
    const toggle = document.getElementById(toggleId);

    toggle.addEventListener("click", () => {
        if (pass.type === "password") {
            pass.type = "text";
            toggle.textContent = "visibility";
        } else {
            pass.type = "password";
            toggle.textContent = "visibility_off";
        }
    });
}
togglePassword("password1", "togglePass1");
togglePassword("password2", "togglePass2");


/* ============================================================
   TOAST
============================================================ */
function showErrorToast(message) {
    const toast = document.getElementById("errorToast");
    toast.innerText = message;

    toast.classList.add("show");

    setTimeout(() => {
        toast.classList.remove("show");
    }, 3000);
}


/* ============================================================
   CHECK EMAIL → OPEN OTP POPUP → SEND OTP SAFELY
============================================================ */

const emailInput = document.getElementById("email");
let otpSent = false;  

emailInput.addEventListener("input", validateEmail);
emailInput.addEventListener("blur", validateEmail);

function validateEmail() {
    const email = emailInput.value.trim();
    const emailRegex = /^[a-zA-Z0-9._%+-]+@gmail\.com$/;

    if (email === "") return;
    if (!emailRegex.test(email)) return;

    if (otpSent) return;  

    otpSent = true;

    checkEmailExists(email);
}


/* ============================================================
   CHECK IF EMAIL EXISTS IN DATABASE
============================================================ */
function checkEmailExists(email) {

    let formData = new FormData();
    formData.append("check_email", true);
    formData.append("email", email);

    fetch("register.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(data => {

        if (data === "EXISTS") {
            showErrorToast("Email already registered! Redirecting...");
            setTimeout(() => {
                window.location.href = "login.php";
            }, 1500);
            return;
        }

        // Email available → Open OTP popup
        showOtpPopup();

        // NOW send OTP
        sendOtp(email);
    });
}

/* ============================================================
   SEND OTP (ONLY ONE FUNCTION)
============================================================ */
function sendOtp(email) {

    otpSent = true; 

    let formData = new FormData();
    formData.append("send_otp", true);
    formData.append("email", email);

    fetch("register.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(data => {

        if (data === "OTP_SENT") {

            // Show OTP box and verify button
            document.querySelector("#otpCard .inp").style.display = "flex";
            document.querySelector("#otpCard #btn-v").style.display = "block";

            // Lock email so user cannot change
            emailInput.readOnly = true;
            emailInput.style.background = "transparent"; 
            const emailBox = document.getElementById("email");
            emailBox.style.color = "#000";           
            emailBox.style.opacity = "1";            
            emailBox.style.cursor = "not-allowed";  

            // Remove event listeners
            emailInput.removeEventListener("input", validateEmail);
            emailInput.removeEventListener("blur", validateEmail);

        } else {
            showErrorToast("Couldn't send OTP!");
            otpSent = false; 
        }

    });
}


/* ============================================================
   FORM SUBMISSION (MAIN REGISTRATION)
============================================================ */
document.getElementById("btn1").addEventListener("click", function (e) {
    e.preventDefault();

    const name = document.querySelector("input[placeholder='Full Name']").value.trim();
    const email = document.getElementById("email").value.trim();
    const pass1 = document.getElementById("password1").value.trim();
    const pass2 = document.getElementById("password2").value.trim();
    const role = document.querySelector(".role-option.active")?.dataset.role;
    // Strong password validation
    const upper = /[A-Z]/;
    const lower = /[a-z]/;
    const digit = /[0-9]/;
    const special = /[!@#$%^&*(),.?":{}|<>]/;

    if (pass1.length < 6) {
        showErrorToast("Password must be at least 6 characters long!");
        return;
    }
    if (!upper.test(pass1)) {
        showErrorToast("Password must contain at least 1 uppercase letter");
        return;
    }
    if (!lower.test(pass1)) {
        showErrorToast("Password must contain at least 1 lowercase letter");
        return;
    }
    if (!digit.test(pass1)) {
        showErrorToast("Password must contain at least 1 number");
        return;
    }
    if (!special.test(pass1)) {
        showErrorToast("Password must contain at least 1 special character ");
    return;
    }


    if (!name || !email || !pass1 || !pass2 || !role) {
        showErrorToast("All fields are required!");
        return;
    }

    if (pass1 !== pass2) {
        showErrorToast("Passwords do not match!");
        return;
    }

    if (!otpVerified) {
        showErrorToast("Please verify OTP first!");
        return;
    }
    
    

    const formData = new FormData();
    formData.append("name", name);
    formData.append("email", email);
    formData.append("password", pass1);
    formData.append("role", role);
    formData.append("register", true);

    fetch("register.php", {
        method: "POST",
        body: formData
    })
        .then(res => res.text())
        .then(data => {
            if (data === "REGISTERED") {
                alert("Registration successful!");
                window.location.href = "login.php";
            } else {
                showErrorToast(data);
            }
        })
        .catch(err => {
            showErrorToast("Error connecting to server");
            console.log(err);
        });
});


/* ============================================================
   ROLE SELECTION
============================================================ */
document.querySelectorAll(".role-option").forEach(role => {
    role.addEventListener("click", () => {
        document.querySelectorAll(".role-option").forEach(x => x.classList.remove("active"));
        role.classList.add("active");
    });
});


/* ============================================================
   SHOW OTP POPUP
============================================================ */
function showOtpPopup() {
    document.getElementById("otpOverlay").style.display = "block";
    document.getElementById("otpCard").classList.add("show-otp");
    startOTP();
}


/* ============================================================
   OTP BOX AUTO-MOVE + VERIFY
============================================================ */
function startOTP() {
    const inputs = document.querySelectorAll(".input");
    inputs[0].focus();

    inputs.forEach((input, index) => {
        input.addEventListener("input", () => {
            if (input.value.length === 1 && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });

        input.addEventListener("keydown", (e) => {
            if (e.key === "Backspace" && input.value === "" && index > 0) {
                inputs[index - 1].focus();
            }
        });
    });
}


/* ============================================================
   VERIFY OTP CLICK
============================================================ */
document.getElementById("btn-v").addEventListener("click", () => {

    const inputs = document.querySelectorAll(".input");
    let otp =
        inputs[0].value +
        inputs[1].value +
        inputs[2].value +
        inputs[3].value;

    if (otp.length !== 4) {
        showErrorToast("Enter complete OTP!");
        return;
    }

    let formData = new FormData();
    formData.append("verify_otp", true);
    formData.append("otp", otp);

    fetch("register.php", {
        method: "POST",
        body: formData
    })
        .then(res => res.text())
        .then(data => {
            if (data === "OTP_CORRECT") {
    otpVerified = true;

    alert("OTP Verified Successfully!");

    const emailBox = document.getElementById("email");
    const verifiedText = document.getElementById("verifiedText");

    // Lock email input
    emailBox.readOnly = true;
    emailBox.style.cursor = "not-allowed";

    // Add verified class
    emailBox.classList.add("email-verified");

    // Show verified text
    verifiedText.style.display = "block";

    // Close popup
    document.getElementById("otpOverlay").style.display = "none";
    document.getElementById("otpCard").classList.remove("show-otp");

    showSuccessToast("Email Verified Successfully!");
    alert("OTP Verified Successfully!");
}

 else {
                alert("Invalid OTP")
                showErrorToast("Incorrect OTP!");
            }
        });
});

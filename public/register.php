<?php
session_start();
$title = "Register";
$style = "register_styles.css";
include '../includes/header.php';
?>
<?php if (!empty($_SESSION['error'])): ?>
    <div class="session-alert error"><?= htmlspecialchars($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); ?>
<?php elseif (!empty($_SESSION['success'])): ?>
    <div class="session-alert success"><?= htmlspecialchars($_SESSION['success']) ?></div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<main class="register-container">
    <!-- Left Side - Signup Form -->
    <div class="left-container">
        <div class="signup-form">
            <h1 class="title">SIGN UP</h1>
            <p class="subtitle">Already have an account? <a href="login.php">Log in</a></p>

            <form action="../api/register.php" method="POST">
                <div class="name-fields-container">
                    <div class="form-group name-field">
                        <label for="studentLastName">Last Name:</label>
                        <input type="text" id="studentLastName" name="studentLastName" required>
                    </div>
                    <div class="form-group name-field">
                        <label for="studentFirstName">First Name:</label>
                        <input type="text" id="studentFirstName" name="studentFirstName" required>
                    </div>
                    <div class="form-group name-field">
                        <label for="studentMiddleName">Middle Name:</label>
                        <input type="text" id="studentMiddleName" name="studentMiddleName">
                    </div>
                </div>
                <div class="form-group">
                    <label for="studentNumber">Student Number: (Format: yyyy-nnnnn-MN-0)</label>
                    <input type="text" id="studentNumber" name="studentNumber" pattern="\d{4}-\d{5}-MN-0" title="Format: yyyy-nnnnn-MN-0 (e.g., 2023-12345-MN-0)" required>
                    <small id="studentNumberWarning" style="color: red; display: none;">Please follow the format: yyyy-nnnnn-MN-0</small>
                </div>
                <div class="form-group">
                    <label for="studentCourse">Course:</label>
                    <select id="studentCourse" name="studentCourse" required>
                        <option value="">-- Select Course --</option>
                        <option value="DCvET">Diploma in Civil Engineering Technology</option>
                        <option value="DCET">Diploma in Computer Engineering Technology</option>
                        <option value="DEET">Diploma in Electrical Engineering Technology</option>
                        <option value="DECET">Diploma in Electronics Engineering Technology</option>
                        <option value="DIT">Diploma in Information Technology</option>
                        <option value="DMET">Diploma in Mechanical Engineering Technology</option>
                        <option value="DOMT">Diploma in Office Management Technology</option>
                        <option value="DRET">Diploma in Railway Engineering Technology</option>
                    </select>
                </div>
                <div class="year-section-container">
                    <div class="form-group year-field">
                        <label for="studentYear">Year:</label>
                        <select id="studentYear" name="studentYear" required>
                            <option value="">-- Select Year --</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                        </select>
                    </div>
                    <div class="form-group section-field">
                        <label for="studentSection">Section: 1,2,3,4,5...?</label>
                        <input type="text" id="studentSection" name="studentSection" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="studentEmail">Email:</label>
                    <input type="email" id="studentEmail" name="studentEmail" required>
                </div>
                <div class="form-group">
                    <label for="studentPassword">Password:</label>
                    <div class="input-icon">
                        <input type="password" id="studentPassword" name="studentPassword" required>
                        <img src="../assets/pictures/eye-off.png" class="toggle-icon" onclick="togglePassword('studentPassword', this)" alt="toggle password">
                    </div>
                    <small id="studentPasswordWarning" style="color: red; display: none;">Password must be 8–20 characters, include 2 numbers and 2 special characters.</small>
                </div>
                <div class="form-group">
                    <label for="studentConfirmPassword">Confirm Password:</label>
                    <div class="input-icon">
                        <input type="password" id="studentConfirmPassword" name="studentConfirmPassword" required>
                        <img src="../assets/pictures/eye-off.png" class="toggle-icon" onclick="togglePassword('studentConfirmPassword', this)" alt="toggle password">
                    </div>
                    <small id="studentPasswordMismatch" style="color: red; display: none;">Passwords do not match</small>
                </div>

                <button type="submit" class="btn">Sign Up</button>
            </form>

            <p class="terms">
                By using this service, you understand and agree to the PUP Online Services 
                <a href="https://www.pup.edu.ph/terms/" target="_blank">Terms of Use</a> and 
                <a href="https://www.pup.edu.ph/privacy/" target="_blank">Privacy Statement</a>.
            </p>
        </div>
    </div>

    <!-- Right Side - Background Image -->
    <div class="right-container"></div>
</main>

<style>
.session-alert {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    background-color: #4CAF50;
    color: white;
    padding: 14px 24px;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    z-index: 2000;
    font-weight: 500;
    max-width: 80%;
    text-align: center;
    animation: fadeInSlideDown 0.4s ease-in-out;
}
.session-alert.error {
    background-color: #f44336;
}
@keyframes fadeInSlideDown {
    from {
        opacity: 0;
        transform: translate(-50%, -20px);
    }
    to {
        opacity: 1;
        transform: translate(-50%, 0);
    }
}
</style>

<script>
function togglePassword(fieldId, icon) {
    const field = document.getElementById(fieldId);
    if (field.type === "password") {
        field.type = "text";
        icon.src = "../assets/pictures/eye.png";
    } else {
        field.type = "password";
        icon.src = "../assets/pictures/eye-off.png";
    }
}

const alertBox = document.querySelector('.session-alert');
if (alertBox) {
    setTimeout(() => {
        alertBox.style.transition = 'opacity 0.5s ease';
        alertBox.style.opacity = '0';
        setTimeout(() => alertBox.remove(), 500);
    }, 4000);
}

document.getElementById('studentPassword').addEventListener('input', validatePassword);
document.getElementById('studentConfirmPassword').addEventListener('input', checkPasswordMatch);
document.getElementById('studentNumber').addEventListener('input', validateStudentNumber);

function validatePassword() {
    const pwd = document.getElementById('studentPassword').value;
    const warning = document.getElementById('studentPasswordWarning');
    const regex = /^(?=(?:.*\d.*){2,})(?=(?:.*[!@#$%^&*()_+[\]{};':"\\|,.<>/?`~].*){2,}).{8,20}$/;
    warning.style.display = regex.test(pwd) ? 'none' : 'block';
}

function checkPasswordMatch() {
    const pwd = document.getElementById('studentPassword').value;
    const confirmPwd = document.getElementById('studentConfirmPassword').value;
    const mismatch = document.getElementById('studentPasswordMismatch');
    mismatch.style.display = (pwd && confirmPwd && pwd !== confirmPwd) ? 'block' : 'none';
}

function validateStudentNumber() {
    const studentNum = document.getElementById('studentNumber').value;
    const warning = document.getElementById('studentNumberWarning');
    const regex = /^\d{4}-\d{5}-MN-0$/;
    
    if (studentNum) {
        warning.style.display = regex.test(studentNum) ? 'none' : 'block';
        
        // Auto-format as user types
        if (studentNum.length > 0 && !studentNum.includes('-')) {
            let formatted = studentNum.replace(/\D/g, ''); // Remove non-digits
            if (formatted.length >= 4) {
                formatted = formatted.substr(0, 4) + '-' + formatted.substr(4);
            }
            if (formatted.length >= 11) {
                formatted = formatted.substr(0, 11) + '-mn-0';
                document.getElementById('studentNumber').value = formatted;
            }
        }
    } else {
        warning.style.display = 'none';
    }
}
</script>
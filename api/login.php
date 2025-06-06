<?php
require '../config/db_conn.php';
session_start(); // Start session

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Prepare statement to fetch user by email
    $stmt = $conn->prepare("SELECT id, firstName, middleName, lastName, studentNumber, course, year, section, password, role, status
                            FROM newusers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Check if account is disabled
        if ($user['status'] === 'disabled') {
            $_SESSION['error'] = "Account Disabled";
            header("Location: ../public/login.php?error=Your account has been disabled.");
            exit();
        }

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Build full name
            $fullName = ucwords(strtolower("{$user['firstName']} {$user['middleName']} {$user['lastName']}"));

            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullName'] = $fullName;
            $_SESSION['role'] = $user['role'];
            $_SESSION['org_id'] = $user['org_id'] ?? null;

            $_SESSION['success'] = "Log In Success";

            // Redirect based on role
            switch ($user['role']) {
                case 'admin':
                    header("Location: ../admin/dashboard.php");
                    break;
                case 'student':
                    header("Location: ../students/dashboard.php");
                    break;
                case 'org_admin':
                case 'orgAdmin': // Support both spellings
                    header("Location: ../admin_org/dashboard.php");
                    break;
                default:
                    $_SESSION['error'] = "Unknown role";
                    header("Location: ../public/login.php?error=Unknown role");
                    break;
            }
            exit();
        } else {
            $_SESSION['error'] = "Invalid Password";
            header("Location: ../public/login.php?error=Invalid password");
            exit();
        }
    } else {
        $_SESSION['error'] = "Invalid Email";
        header("Location: ../public/login.php?error=User not found");
        exit();
    }

    $stmt->close();
}

$conn->close();
?>

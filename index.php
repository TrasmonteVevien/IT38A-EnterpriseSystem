<?php
session_start();
require_once "config.php";

// Check if user is already logged in
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: jobseeker_dashboard.php");
    exit;
}

$username = $password = $email = "";
$username_err = $password_err = $email_err = $login_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['step']) && $_POST['step'] == '1') {
        // First step: Email and password verification
        if (empty(trim($_POST["email"]))) {
            $email_err = "Please enter your email.";
        } else {
            $email = trim($_POST["email"]);
        }

        if (empty(trim($_POST["password"]))) {
            $password_err = "Please enter your password.";
        } else {
            $password = trim($_POST["password"]);
        }

        if (empty($email_err) && empty($password_err)) {
            $sql = "SELECT id, username, password, role FROM users WHERE email = :email AND role = 'jobseeker'";
            
            if ($stmt = $pdo->prepare($sql)) {
                $stmt->bindParam(":email", $email, PDO::PARAM_STR);
                
                if ($stmt->execute()) {
                    if ($stmt->rowCount() == 1) {
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if (password_verify($password, $row["password"])) {
                            // Generate and send verification code
                            $verification_code = sprintf("%06d", mt_rand(0, 999999));
                            $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
                            
                            $update_sql = "UPDATE users SET verification_code = :code, verification_code_expires = :expires WHERE id = :id";
                            if ($update_stmt = $pdo->prepare($update_sql)) {
                                $update_stmt->bindParam(":code", $verification_code, PDO::PARAM_STR);
                                $update_stmt->bindParam(":expires", $expires, PDO::PARAM_STR);
                                $update_stmt->bindParam(":id", $row["id"], PDO::PARAM_INT);
                                $update_stmt->execute();
                            }
                            
                            if (sendVerificationEmail($email, $verification_code)) {
                                $_SESSION["temp_user_id"] = $row["id"];
                                header("location: verify_2fa.php");
                                exit;
                            } else {
                                $login_err = "Failed to send verification code. Please try again.";
                            }
                        } else {
                            $login_err = "Invalid email or password.";
                        }
                    } else {
                        $login_err = "Invalid email or password.";
                    }
                } else {
                    $login_err = "Oops! Something went wrong. Please try again later.";
                }
            }
            unset($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Seeker Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #000;
            color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }
        .form-control {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
        }
        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.15);
            border-color: #0dcaf0;
            color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(13, 202, 240, 0.25);
        }
        .btn-login {
            background-color: #0dcaf0;
            border: none;
            color: #000;
            font-weight: bold;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            background-color: #0bb6d9;
            transform: translateY(-2px);
        }
        .modal-content {
            background-color: #000;
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        .modal-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <h2 class="text-center mb-4">Job Seeker Login</h2>
            
            <?php 
            if (!empty($login_err)) {
                echo '<div class="alert alert-danger">' . $login_err . '</div>';
            }        
            ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="hidden" name="step" value="1">
                
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                    <span class="invalid-feedback"><?php echo $email_err; ?></span>
                </div>    

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="termsCheck" required>
                    <label class="form-check-label" for="termsCheck">I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a></label>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-login">Login</button>
                </div>

                <p class="text-center mt-3">
                    Don't have an account? <a href="register.php" class="text-info">Sign up now</a>
                </p>
            </form>
        </div>
    </div>

    <!-- Terms and Conditions Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Terms and Conditions</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>By using our job portal, you agree to:</p>
                    <ul>
                        <li>Provide accurate and truthful information</li>
                        <li>Maintain the confidentiality of your account</li>
                        <li>Not share your account credentials</li>
                        <li>Comply with all applicable laws and regulations</li>
                        <li>Respect the privacy of other users</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
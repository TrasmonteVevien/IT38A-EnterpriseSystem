<?php
session_start();
require_once "config.php";

// Check if user is in the verification process
if (!isset($_SESSION["temp_user_id"])) {
    header("location: index.php");
    exit;
}

$verification_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["verification_code"]))) {
        $verification_err = "Please enter the verification code.";
    } else {
        $verification_code = trim($_POST["verification_code"]);
        
        // Verify the code
        $sql = "SELECT id, username, role FROM users WHERE id = :id AND verification_code = :code AND verification_code_expires > NOW()";
        
        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(":id", $_SESSION["temp_user_id"], PDO::PARAM_INT);
            $stmt->bindParam(":code", $verification_code, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Start the session
                    $_SESSION["loggedin"] = true;
                    $_SESSION["id"] = $row["id"];
                    $_SESSION["username"] = $row["username"];
                    $_SESSION["role"] = $row["role"];
                    
                    // Clear the verification code
                    $update_sql = "UPDATE users SET verification_code = NULL, verification_code_expires = NULL WHERE id = :id";
                    if ($update_stmt = $pdo->prepare($update_sql)) {
                        $update_stmt->bindParam(":id", $_SESSION["temp_user_id"], PDO::PARAM_INT);
                        $update_stmt->execute();
                    }
                    
                    // Clear temporary session
                    unset($_SESSION["temp_user_id"]);
                    
                    // Redirect to dashboard
                    header("location: dashboard.php");
                    exit;
                } else {
                    $verification_err = "Invalid or expired verification code.";
                }
            } else {
                $verification_err = "Oops! Something went wrong. Please try again later.";
            }
        }
        unset($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #000;
            color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .verification-container {
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
            text-align: center;
            letter-spacing: 8px;
            font-size: 24px;
        }
        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.15);
            border-color: #0dcaf0;
            color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(13, 202, 240, 0.25);
        }
        .btn-verify {
            background-color: #0dcaf0;
            border: none;
            color: #000;
            font-weight: bold;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }
        .btn-verify:hover {
            background-color: #0bb6d9;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="verification-container">
            <h2 class="text-center mb-4">Two-Factor Authentication</h2>
            <p class="text-center mb-4">Please enter the verification code sent to your email.</p>
            
            <?php 
            if (!empty($verification_err)) {
                echo '<div class="alert alert-danger">' . $verification_err . '</div>';
            }        
            ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="mb-4">
                    <input type="text" name="verification_code" class="form-control form-control-lg <?php echo (!empty($verification_err)) ? 'is-invalid' : ''; ?>" maxlength="6" pattern="[0-9]*" inputmode="numeric" autocomplete="off" required>
                    <span class="invalid-feedback"><?php echo $verification_err; ?></span>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-verify">Verify</button>
                </div>

                <p class="text-center mt-3">
                    <a href="dashboard.php" class="text-info">Back to Login</a>
                </p>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-format the verification code input
        document.querySelector('input[name="verification_code"]').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html> 
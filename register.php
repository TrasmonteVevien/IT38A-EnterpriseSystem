<?php
// Include config file
require_once "config.php";

// Define variables and initialize with empty values
$username = $password = $confirm_password = $email = $role = $full_name = $address = $contact_number = $company_name = $employment_status = "";
$username_err = $password_err = $confirm_password_err = $email_err = $role_err = $full_name_err = $address_err = $contact_number_err = $company_name_err = $employment_status_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate username
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter a username.";
    } elseif(!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["username"]))){
        $username_err = "Username can only contain letters, numbers, and underscores.";
    } else{
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE username = :username";
        
        if($stmt = $pdo->prepare($sql)){
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            $param_username = trim($_POST["username"]);
            
            if($stmt->execute()){
                if($stmt->rowCount() == 1){
                    $username_err = "This username is already taken.";
                } else{
                    $username = trim($_POST["username"]);
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
            unset($stmt);
        }
    }

    // Validate email
    if(empty(trim($_POST["email"]))){
        $email_err = "Please enter an email.";
    } elseif(!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)){
        $email_err = "Please enter a valid email address.";
    } else{
        $email = trim($_POST["email"]);
    }

    // Validate role
    if(empty(trim($_POST["role"]))){
        $role_err = "Please select a role.";
    } else{
        $role = trim($_POST["role"]);
    }

    // Validate full name
    if(empty(trim($_POST["full_name"]))){
        $full_name_err = "Please enter your full name.";
    } else{
        $full_name = trim($_POST["full_name"]);
    }

    // Validate address
    if(empty(trim($_POST["address"]))){
        $address_err = "Please enter your address.";
    } else{
        $address = trim($_POST["address"]);
    }

    // Validate contact number
    if(empty(trim($_POST["contact_number"]))){
        $contact_number_err = "Please enter your contact number.";
    } else{
        $contact_number = trim($_POST["contact_number"]);
    }

    // Validate company name (for employers)
    if($role === "employer" && empty(trim($_POST["company_name"]))){
        $company_name_err = "Please enter your company name.";
    } else{
        $company_name = trim($_POST["company_name"]);
    }

    // Validate employment status (for jobseekers)
    if($role === "jobseeker" && empty(trim($_POST["employment_status"]))){
        $employment_status_err = "Please select your employment status.";
    } else{
        $employment_status = trim($_POST["employment_status"]);
    }

    // Validate password
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter a password.";     
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Password must have atleast 6 characters.";
    } else{
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm password.";     
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Password did not match.";
        }
    }

    // Check input errors before inserting in database
    if(empty($username_err) && empty($password_err) && empty($confirm_password_err) && 
       empty($email_err) && empty($role_err) && empty($full_name_err) && empty($address_err) && 
       empty($contact_number_err) && empty($company_name_err) && empty($employment_status_err)){
        
        try {
            $pdo->beginTransaction();

            // Insert into users table
            $sql = "INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
            $stmt->bindParam(":password", $param_password, PDO::PARAM_STR);
            $stmt->bindParam(":role", $param_role, PDO::PARAM_STR);
            
            $param_username = $username;
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            $param_role = $role;
            
            $stmt->execute();
            $user_id = $pdo->lastInsertId();

            // Insert into user_credentials table
            $sql = "INSERT INTO user_credentials (user_id, full_name, address, contact_number, company_name, employment_status) 
                    VALUES (:user_id, :full_name, :address, :contact_number, :company_name, :employment_status)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
            $stmt->bindParam(":full_name", $full_name, PDO::PARAM_STR);
            $stmt->bindParam(":address", $address, PDO::PARAM_STR);
            $stmt->bindParam(":contact_number", $contact_number, PDO::PARAM_STR);
            $stmt->bindParam(":company_name", $company_name, PDO::PARAM_STR);
            $stmt->bindParam(":employment_status", $employment_status, PDO::PARAM_STR);
            
            $stmt->execute();

            $pdo->commit();
            header("location: index.php");
        } catch(Exception $e) {
            $pdo->rollBack();
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f0f0;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .wrapper {
            width: 100%;
            max-width: 600px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            margin: 50px auto;
        }

        h2 {
            color: #000;
            text-align: center;
            margin-bottom: 20px;
        }

        p {
            text-align: center;
            font-size: 16px;
            color: #111;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-control {
            border-radius: 5px;
            border: 1px solid #666;
            padding: 10px;
            font-size: 14px;
            color: #000;
            background-color: #fff;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #000;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.3);
        }

        .btn-primary {
            background-color: #000;
            border-color: #000;
            color: #fff;
            padding: 10px 10px;
            font-size: 16px;
            width: 100%;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #333;
            border-color: #333;
        }

        .alert {
            margin-bottom: 20px;
            text-align: center;
            background-color: #eee;
            color: #000;
        }

        .invalid-feedback {
            font-size: 14px;
            color: #a00;
        }

        a {
            color: #000;
            text-decoration: underline;
        }

        a:hover {
            text-decoration: none;
        }

        .role-specific {
            display: none;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Sign Up</h2>
        <p>Please fill this form to create an account.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                <span class="invalid-feedback"><?php echo $email_err; ?></span>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" class="form-control <?php echo (!empty($role_err)) ? 'is-invalid' : ''; ?>" onchange="toggleRoleFields(this.value)">
                    <option value="">Select Role</option>
                    <option value="jobseeker" <?php echo ($role === "jobseeker") ? "selected" : ""; ?>>Job Seeker</option>
                    <option value="employer" <?php echo ($role === "employer") ? "selected" : ""; ?>>Employer</option>
                </select>
                <span class="invalid-feedback"><?php echo $role_err; ?></span>
            </div>
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" class="form-control <?php echo (!empty($full_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $full_name; ?>">
                <span class="invalid-feedback"><?php echo $full_name_err; ?></span>
            </div>
            <div class="form-group">
                <label>Address</label>
                <input type="text" name="address" class="form-control <?php echo (!empty($address_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $address; ?>">
                <span class="invalid-feedback"><?php echo $address_err; ?></span>
            </div>
            <div class="form-group">
                <label>Contact Number</label>
                <input type="tel" name="contact_number" class="form-control <?php echo (!empty($contact_number_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $contact_number; ?>">
                <span class="invalid-feedback"><?php echo $contact_number_err; ?></span>
            </div>
            <div class="form-group employer-fields role-specific">
                <label>Company Name</label>
                <input type="text" name="company_name" class="form-control <?php echo (!empty($company_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $company_name; ?>">
                <span class="invalid-feedback"><?php echo $company_name_err; ?></span>
            </div>
            <div class="form-group jobseeker-fields role-specific">
                <label>Employment Status</label>
                <select name="employment_status" class="form-control <?php echo (!empty($employment_status_err)) ? 'is-invalid' : ''; ?>">
                    <option value="">Select Status</option>
                    <option value="student" <?php echo ($employment_status === "student") ? "selected" : ""; ?>>Student</option>
                    <option value="graduate" <?php echo ($employment_status === "graduate") ? "selected" : ""; ?>>Graduate</option>
                    <option value="employed" <?php echo ($employment_status === "employed") ? "selected" : ""; ?>>Employed</option>
                </select>
                <span class="invalid-feedback"><?php echo $employment_status_err; ?></span>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit">
            </div>
            <p>Already have an account? <a href="index.php">Login here</a>.</p>
        </form>
    </div>

    <script>
        function toggleRoleFields(role) {
            const employerFields = document.querySelectorAll('.employer-fields');
            const jobseekerFields = document.querySelectorAll('.jobseeker-fields');
            
            employerFields.forEach(field => field.style.display = role === 'employer' ? 'block' : 'none');
            jobseekerFields.forEach(field => field.style.display = role === 'jobseeker' ? 'block' : 'none');
        }

        // Initialize fields based on current role
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.querySelector('select[name="role"]');
            if (roleSelect.value) {
                toggleRoleFields(roleSelect.value);
            }
        });
    </script>
</body>
</html>
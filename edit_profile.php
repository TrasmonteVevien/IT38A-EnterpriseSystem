<?php
session_start();

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}

// Include config file
require_once "config.php";

// Get user information
$sql = "SELECT u.*, uc.* FROM users u 
        LEFT JOIN user_credentials uc ON u.id = uc.user_id 
        WHERE u.id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(":id", $_SESSION["id"], PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Process form submission
if($_SERVER["REQUEST_METHOD"] == "POST"){
    try {
        // Start transaction
        $pdo->beginTransaction();

        // Update user credentials
        $sql = "UPDATE user_credentials SET 
                full_name = :full_name,
                email = :email,
                phone = :phone,
                address = :address,
                bio = :bio,
                employment_status = :employment_status,
                education = :education,
                skills = :skills,
                experience = :experience";

        // Add company fields if user is employer
        if($user["role"] === "employer") {
            $sql .= ", company_name = :company_name,
                     company_description = :company_description,
                     company_website = :company_website,
                     company_location = :company_location";
        }

        $sql .= " WHERE user_id = :user_id";

        $stmt = $pdo->prepare($sql);
        
        // Bind parameters
        $stmt->bindParam(":full_name", $_POST["full_name"]);
        $stmt->bindParam(":email", $_POST["email"]);
        $stmt->bindParam(":phone", $_POST["phone"]);
        $stmt->bindParam(":address", $_POST["address"]);
        $stmt->bindParam(":bio", $_POST["bio"]);
        $stmt->bindParam(":employment_status", $_POST["employment_status"]);
        $stmt->bindParam(":education", $_POST["education"]);
        $stmt->bindParam(":skills", $_POST["skills"]);
        $stmt->bindParam(":experience", $_POST["experience"]);
        $stmt->bindParam(":user_id", $_SESSION["id"]);

        // Bind company parameters if user is employer
        if($user["role"] === "employer") {
            $stmt->bindParam(":company_name", $_POST["company_name"]);
            $stmt->bindParam(":company_description", $_POST["company_description"]);
            $stmt->bindParam(":company_website", $_POST["company_website"]);
            $stmt->bindParam(":company_location", $_POST["company_location"]);
        }

        $stmt->execute();

        // Commit transaction
        $pdo->commit();
        
        // Redirect with success message
        redirect_with_message("dashboard.php", "Profile updated successfully!");
    } catch(PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $error = "Error updating profile: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
            max-width: 800px;
            padding: 20px;
            margin: 0 auto;
        }
        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .btn-primary {
            background-color: #000;
            border-color: #000;
        }
        .btn-primary:hover {
            background-color: #333;
            border-color: #333;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="form-container">
            <h2 class="mb-4">Edit Profile</h2>
            <?php echo display_message(); ?>
            <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user["full_name"]); ?>" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user["email"]); ?>" required>
                </div>

                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user["phone"]); ?>">
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user["address"]); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Bio</label>
                    <textarea name="bio" class="form-control" rows="3"><?php echo htmlspecialchars($user["bio"]); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Employment Status</label>
                    <select name="employment_status" class="form-control">
                        <option value="employed" <?php echo $user["employment_status"] == "employed" ? "selected" : ""; ?>>Employed</option>
                        <option value="unemployed" <?php echo $user["employment_status"] == "unemployed" ? "selected" : ""; ?>>Unemployed</option>
                        <option value="student" <?php echo $user["employment_status"] == "student" ? "selected" : ""; ?>>Student</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Education</label>
                    <textarea name="education" class="form-control" rows="3"><?php echo htmlspecialchars($user["education"]); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Skills</label>
                    <textarea name="skills" class="form-control" rows="3"><?php echo htmlspecialchars($user["skills"]); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Experience</label>
                    <textarea name="experience" class="form-control" rows="3"><?php echo htmlspecialchars($user["experience"]); ?></textarea>
                </div>

                <?php if($user["role"] === "employer"): ?>
                    <h4 class="mt-4 mb-3">Company Information</h4>
                    <div class="form-group">
                        <label>Company Name</label>
                        <input type="text" name="company_name" class="form-control" value="<?php echo htmlspecialchars($user["company_name"]); ?>">
                    </div>

                    <div class="form-group">
                        <label>Company Description</label>
                        <textarea name="company_description" class="form-control" rows="3"><?php echo htmlspecialchars($user["company_description"]); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Company Website</label>
                        <input type="url" name="company_website" class="form-control" value="<?php echo htmlspecialchars($user["company_website"]); ?>">
                    </div>

                    <div class="form-group">
                        <label>Company Location</label>
                        <input type="text" name="company_location" class="form-control" value="<?php echo htmlspecialchars($user["company_location"]); ?>">
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <input type="submit" class="btn btn-primary" value="Update Profile">
                    <a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 
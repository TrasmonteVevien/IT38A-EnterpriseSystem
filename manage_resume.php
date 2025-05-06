<?php
// Initialize the session
session_start();

// Check if the user is logged in and is a job seeker
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "jobseeker"){
    header("location: index.php");
    exit;
}

// Include config file
require_once "config.php";

// Define variables and initialize with empty values
$resume_err = "";
$success_msg = "";

// Get current resume if exists
$current_resume = null;
$sql = "SELECT * FROM resumes WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(":user_id", $_SESSION["id"], PDO::PARAM_INT);
$stmt->execute();
if($stmt->rowCount() > 0) {
    $current_resume = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Check if file was uploaded without errors
    if(isset($_FILES["resume"]) && $_FILES["resume"]["error"] == 0){
        $allowed = array("pdf", "doc", "docx");
        $filename = $_FILES["resume"]["name"];
        $filetype = $_FILES["resume"]["type"];
        $filesize = $_FILES["resume"]["size"];
    
        // Verify file extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if(!in_array(strtolower($ext), $allowed)){
            $resume_err = "Error: Please upload a valid PDF or Word document.";
        }
        // Verify file size - 5MB maximum
        else if($filesize > 5 * 1024 * 1024){
            $resume_err = "Error: File size is larger than the allowed limit of 5MB.";
        }
        else{
            // Create uploads directory if it doesn't exist
            $upload_dir = "uploads/resumes/";
            if(!file_exists($upload_dir)){
                mkdir($upload_dir, 0777, true);
            }

            // Generate unique filename
            $new_filename = uniqid() . "_" . $filename;
            $upload_path = $upload_dir . $new_filename;

            // Upload file
            if(move_uploaded_file($_FILES["resume"]["tmp_name"], $upload_path)){
                try {
                    $pdo->beginTransaction();

                    // Insert new resume record
                    $sql = "INSERT INTO resumes (user_id, file_path) VALUES (:user_id, :file_path)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(":user_id", $_SESSION["id"], PDO::PARAM_INT);
                    $stmt->bindParam(":file_path", $upload_path, PDO::PARAM_STR);
                    $stmt->execute();

                    $pdo->commit();
                    $success_msg = "Resume uploaded successfully!";
                    
                    // Refresh current resume
                    $sql = "SELECT * FROM resumes WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(":user_id", $_SESSION["id"], PDO::PARAM_INT);
                    $stmt->execute();
                    $current_resume = $stmt->fetch(PDO::FETCH_ASSOC);
                } catch(Exception $e) {
                    $pdo->rollBack();
                    $resume_err = "Error: Something went wrong. Please try again later.";
                }
            } else {
                $resume_err = "Error: There was a problem uploading your file.";
            }
        }
    } else {
        $resume_err = "Error: Please select a file to upload.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Resume</title>
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

        .page-header {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .resume-card {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .btn-primary {
            background-color: #000;
            border-color: #000;
        }

        .btn-primary:hover {
            background-color: #333;
            border-color: #333;
        }

        .custom-file-label::after {
            content: "Browse";
            background-color: #000;
            color: #fff;
        }

        .resume-preview {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }

        .resume-preview i {
            font-size: 2em;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="page-header">
            <div class="row">
                <div class="col-md-8">
                    <h2>Manage Resume</h2>
                    <p>Upload and manage your resume/CV</p>
                </div>
                <div class="col-md-4 text-right">
                    <a href="dashboard.php" class="btn btn-outline-primary">Back to Dashboard</a>
                </div>
            </div>
        </div>

        <?php if(!empty($success_msg)): ?>
            <div class="alert alert-success">
                <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>

        <?php if(!empty($resume_err)): ?>
            <div class="alert alert-danger">
                <?php echo $resume_err; ?>
            </div>
        <?php endif; ?>

        <div class="resume-card">
            <h4>Upload New Resume</h4>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="resume" name="resume" accept=".pdf,.doc,.docx">
                        <label class="custom-file-label" for="resume">Choose file</label>
                    </div>
                    <small class="form-text text-muted">
                        Accepted formats: PDF, DOC, DOCX (Max size: 5MB)
                    </small>
                </div>
                <div class="form-group">
                    <input type="submit" class="btn btn-primary" value="Upload Resume">
                </div>
            </form>
        </div>

        <?php if($current_resume): ?>
            <div class="resume-card">
                <h4>Current Resume</h4>
                <div class="resume-preview text-center">
                    <i class="fas fa-file-alt text-primary"></i>
                    <h5><?php echo basename($current_resume["file_path"]); ?></h5>
                    <p class="text-muted">
                        Uploaded on: <?php echo date("F d, Y", strtotime($current_resume["created_at"])); ?>
                    </p>
                    <div class="btn-group">
                        <a href="<?php echo htmlspecialchars($current_resume["file_path"]); ?>" class="btn btn-primary" target="_blank">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="<?php echo htmlspecialchars($current_resume["file_path"]); ?>" class="btn btn-outline-primary" download>
                            <i class="fas fa-download"></i> Download
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        // Update file input label with selected filename
        $(".custom-file-input").on("change", function() {
            var fileName = $(this).val().split("\\").pop();
            $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        });
    </script>
</body>
</html> 
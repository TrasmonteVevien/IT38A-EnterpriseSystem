<?php
// Initialize the session
session_start();

// Check if the user is logged in and is an employer
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "employer"){
    header("location: index.php");
    exit;
}

// Include config file
require_once "config.php";

// Check if application ID is provided
if(!isset($_GET["id"]) || empty($_GET["id"])){
    header("location: dashboard.php");
    exit;
}

// Get application details
$sql = "SELECT ja.*, jo.title as job_title, jo.description as job_description, 
               u.username as applicant_username, uc.full_name as applicant_name, 
               uc.email as applicant_email, uc.contact_number as applicant_contact,
               uc.employment_status, r.file_path as resume_path
        FROM job_applications ja 
        JOIN job_offers jo ON ja.job_offer_id = jo.id 
        JOIN users u ON ja.applicant_id = u.id 
        JOIN user_credentials uc ON u.id = uc.user_id 
        JOIN resumes r ON ja.resume_id = r.id 
        WHERE ja.id = :id AND jo.employer_id = :employer_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(":id", $_GET["id"], PDO::PARAM_INT);
$stmt->bindParam(":employer_id", $_SESSION["id"], PDO::PARAM_INT);
$stmt->execute();

if($stmt->rowCount() == 0){
    header("location: dashboard.php");
    exit;
}

$application = $stmt->fetch(PDO::FETCH_ASSOC);

// Process status update
$status_err = "";
$status_success = "";

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["status"])){
    $new_status = trim($_POST["status"]);
    if(in_array($new_status, ["pending", "reviewed", "accepted", "rejected"])){
        $sql = "UPDATE job_applications SET status = :status WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":status", $new_status, PDO::PARAM_STR);
        $stmt->bindParam(":id", $_GET["id"], PDO::PARAM_INT);
        
        if($stmt->execute()){
            $status_success = "Application status updated successfully!";
            $application["status"] = $new_status;
        } else {
            $status_err = "Something went wrong. Please try again later.";
        }
    } else {
        $status_err = "Invalid status selected.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Application - <?php echo htmlspecialchars($application["job_title"]); ?></title>
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
            max-width: 1000px;
            padding: 20px;
            margin: 0 auto;
        }

        .application-header {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .application-details {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .applicant-info {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background-color: #000;
            border-color: #000;
        }

        .btn-primary:hover {
            background-color: #333;
            border-color: #333;
        }

        .status-badge {
            font-size: 0.9em;
            padding: 5px 10px;
            border-radius: 15px;
        }

        .section-title {
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .resume-preview {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            text-align: center;
        }

        .resume-preview i {
            font-size: 2em;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="application-header">
            <div class="row">
                <div class="col-md-8">
                    <h2>Application for <?php echo htmlspecialchars($application["job_title"]); ?></h2>
                    <p>
                        <span class="badge badge-<?php 
                            echo $application["status"] === "pending" ? "warning" : 
                                ($application["status"] === "accepted" ? "success" : 
                                ($application["status"] === "rejected" ? "danger" : "info")); 
                        ?> status-badge">
                            <?php echo ucfirst(htmlspecialchars($application["status"])); ?>
                        </span>
                        Applied on: <?php echo date("F d, Y", strtotime($application["created_at"])); ?>
                    </p>
                </div>
                <div class="col-md-4 text-right">
                    <a href="dashboard.php" class="btn btn-outline-primary">Back to Dashboard</a>
                </div>
            </div>
        </div>

        <?php if(!empty($status_success)): ?>
            <div class="alert alert-success">
                <?php echo $status_success; ?>
            </div>
        <?php endif; ?>

        <?php if(!empty($status_err)): ?>
            <div class="alert alert-danger">
                <?php echo $status_err; ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="application-details">
                    <h4 class="section-title">Job Details</h4>
                    <p><?php echo nl2br(htmlspecialchars($application["job_description"])); ?></p>

                    <div class="resume-preview">
                        <i class="fas fa-file-alt text-primary"></i>
                        <h5>Applicant's Resume</h5>
                        <p class="text-muted">
                            <?php echo basename($application["resume_path"]); ?>
                        </p>
                        <div class="btn-group">
                            <a href="<?php echo htmlspecialchars($application["resume_path"]); ?>" class="btn btn-primary" target="_blank">
                                <i class="fas fa-eye"></i> View Resume
                            </a>
                            <a href="<?php echo htmlspecialchars($application["resume_path"]); ?>" class="btn btn-outline-primary" download>
                                <i class="fas fa-download"></i> Download
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="applicant-info">
                    <h4 class="section-title">Applicant Information</h4>
                    <p>
                        <strong>Name:</strong> <?php echo htmlspecialchars($application["applicant_name"]); ?><br>
                        <strong>Email:</strong> <?php echo htmlspecialchars($application["applicant_email"]); ?><br>
                        <strong>Contact:</strong> <?php echo htmlspecialchars($application["applicant_contact"]); ?><br>
                        <strong>Status:</strong> <?php echo ucfirst(htmlspecialchars($application["employment_status"])); ?>
                    </p>

                    <h4 class="section-title">Update Status</h4>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $_GET["id"]); ?>" method="post">
                        <div class="form-group">
                            <select name="status" class="form-control">
                                <option value="pending" <?php echo $application["status"] === "pending" ? "selected" : ""; ?>>Pending</option>
                                <option value="reviewed" <?php echo $application["status"] === "reviewed" ? "selected" : ""; ?>>Reviewed</option>
                                <option value="accepted" <?php echo $application["status"] === "accepted" ? "selected" : ""; ?>>Accepted</option>
                                <option value="rejected" <?php echo $application["status"] === "rejected" ? "selected" : ""; ?>>Rejected</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Update Status</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
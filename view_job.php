<?php
// Initialize the session
session_start();

// Check if the user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}

// Include config file
require_once "config.php";

// Check if job ID is provided
if(!isset($_GET["id"]) || empty($_GET["id"])){
    header("location: search_jobs.php");
    exit;
}

// Get job details
$sql = "SELECT jo.*, u.username as employer_name, uc.company_name, uc.full_name as employer_full_name 
        FROM job_offers jo 
        JOIN users u ON jo.employer_id = u.id 
        JOIN user_credentials uc ON u.id = uc.user_id 
        WHERE jo.id = :id AND jo.status = 'active'";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(":id", $_GET["id"], PDO::PARAM_INT);
$stmt->execute();

if($stmt->rowCount() == 0){
    header("location: search_jobs.php");
    exit;
}

$job = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if user has already applied
$has_applied = false;
if($_SESSION["role"] === "jobseeker"){
    $sql = "SELECT id FROM job_applications WHERE job_offer_id = :job_id AND applicant_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":job_id", $_GET["id"], PDO::PARAM_INT);
    $stmt->bindParam(":user_id", $_SESSION["id"], PDO::PARAM_INT);
    $stmt->execute();
    $has_applied = $stmt->rowCount() > 0;
}

// Process application
$application_err = "";
$application_success = "";

if($_SERVER["REQUEST_METHOD"] == "POST" && $_SESSION["role"] === "jobseeker" && !$has_applied"){
    // Check if user has a resume
    $sql = "SELECT id FROM resumes WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":user_id", $_SESSION["id"], PDO::PARAM_INT);
    $stmt->execute();
    
    if($stmt->rowCount() == 0){
        $application_err = "Please upload your resume before applying.";
    } else {
        $resume = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Submit application
        $sql = "INSERT INTO job_applications (job_offer_id, applicant_id, resume_id) VALUES (:job_id, :user_id, :resume_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":job_id", $_GET["id"], PDO::PARAM_INT);
        $stmt->bindParam(":user_id", $_SESSION["id"], PDO::PARAM_INT);
        $stmt->bindParam(":resume_id", $resume["id"], PDO::PARAM_INT);
        
        if($stmt->execute()){
            $application_success = "Application submitted successfully!";
            $has_applied = true;
        } else {
            $application_err = "Something went wrong. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($job["title"]); ?> - Job Details</title>
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

        .job-header {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .job-details {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .company-info {
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

        .job-meta {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 20px;
        }

        .job-meta i {
            margin-right: 5px;
        }

        .section-title {
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="job-header">
            <div class="row">
                <div class="col-md-8">
                    <h2><?php echo htmlspecialchars($job["title"]); ?></h2>
                    <p class="job-meta">
                        <i class="fas fa-building"></i> <?php echo htmlspecialchars($job["company_name"]); ?><br>
                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job["location"]); ?><br>
                        <i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($job["job_type"]); ?><br>
                        <?php if(!empty($job["salary_range"])): ?>
                            <i class="fas fa-money-bill-wave"></i> <?php echo htmlspecialchars($job["salary_range"]); ?>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-md-4 text-right">
                    <a href="search_jobs.php" class="btn btn-outline-primary">Back to Search</a>
                </div>
            </div>
        </div>

        <?php if(!empty($application_success)): ?>
            <div class="alert alert-success">
                <?php echo $application_success; ?>
            </div>
        <?php endif; ?>

        <?php if(!empty($application_err)): ?>
            <div class="alert alert-danger">
                <?php echo $application_err; ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="job-details">
                    <h4 class="section-title">Job Description</h4>
                    <p><?php echo nl2br(htmlspecialchars($job["description"])); ?></p>

                    <h4 class="section-title">Requirements</h4>
                    <p><?php echo nl2br(htmlspecialchars($job["requirements"])); ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="company-info">
                    <h4 class="section-title">Company Information</h4>
                    <p>
                        <strong>Company:</strong> <?php echo htmlspecialchars($job["company_name"]); ?><br>
                        <strong>Location:</strong> <?php echo htmlspecialchars($job["location"]); ?><br>
                        <strong>Posted by:</strong> <?php echo htmlspecialchars($job["employer_full_name"]); ?><br>
                        <strong>Posted on:</strong> <?php echo date("F d, Y", strtotime($job["created_at"])); ?>
                    </p>

                    <?php if($_SESSION["role"] === "jobseeker"): ?>
                        <?php if($has_applied): ?>
                            <div class="alert alert-info">
                                You have already applied for this position.
                            </div>
                        <?php else: ?>
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $_GET["id"]); ?>" method="post">
                                <button type="submit" class="btn btn-primary btn-block">Apply Now</button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
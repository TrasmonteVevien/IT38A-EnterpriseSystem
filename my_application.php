<?php
session_start();

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: dashboard.php");
    exit;
}

// Include config file
require_once "config.php";

// Get user's applications
$sql = "SELECT ja.*, jo.title, jo.company_name, jo.location, jo.salary, 
        u.username as employer_name, ja.status, ja.created_at, ja.updated_at
        FROM job_applications ja 
        JOIN job_offers jo ON ja.job_offer_id = jo.id 
        JOIN users u ON jo.employer_id = u.id 
        WHERE ja.applicant_id = :applicant_id 
        ORDER BY ja.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(":applicant_id", $_SESSION["id"], PDO::PARAM_INT);
$stmt->execute();
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Applications</title>
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
            max-width: 1200px;
            padding: 20px;
            margin: 0 auto;
        }
        .application-card {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
        }
        .status-pending { background-color: #ffc107; color: #000; }
        .status-reviewed { background-color: #17a2b8; color: #fff; }
        .status-accepted { background-color: #28a745; color: #fff; }
        .status-rejected { background-color: #dc3545; color: #fff; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>My Application</h2>
            <div>
                <a href="dashboard.php" class="btn btn-outline-primary">Back to Dashboard</a>
                <a href="search_jobs.php" class="btn btn-primary">Find More Jobs</a>
            </div>
        </div>

        <?php if(empty($applications)): ?>
            <div class="application-card text-center">
                <h3>No Applications Yet</h3>
                <p>Start applying for jobs to see them here!</p>
                <a href="search_jobs.php" class="btn btn-primary">Search Jobs</a>
            </div>
        <?php else: ?>
            <?php foreach($applications as $application): ?>
                <div class="application-card">
                    <div class="row">
                        <div class="col-md-8">
                            <h3><?php echo htmlspecialchars($application["title"]); ?></h3>
                            <p class="text-muted">
                                <?php echo htmlspecialchars($application["company_name"]); ?> - 
                                <?php echo htmlspecialchars($application["location"]); ?>
                            </p>
                            <p>
                                <strong>Applied:</strong> <?php echo date("F d, Y", strtotime($application["created_at"])); ?><br>
                                <strong>Last Updated:</strong> <?php echo date("F d, Y", strtotime($application["updated_at"])); ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-right">
                            <span class="status-badge status-<?php echo strtolower($application["status"]); ?>">
                                <?php echo ucfirst(htmlspecialchars($application["status"])); ?>
                            </span>
                            <div class="mt-2">
                                <a href="view_job.php?id=<?php echo $application["job_offer_id"]; ?>" class="btn btn-sm btn-outline-primary">View Job</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html> 
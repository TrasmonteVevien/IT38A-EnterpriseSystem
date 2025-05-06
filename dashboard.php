<?php
// Initialize the session
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

// Get job offers for job seekers
$job_offers = [];
if($user["role"] === "jobseeker") {
    $sql = "SELECT jo.*, u.username as employer_name, uc.company_name 
            FROM job_offers jo 
            JOIN users u ON jo.employer_id = u.id 
            JOIN user_credentials uc ON u.id = uc.user_id 
            WHERE jo.status = 'active' 
            ORDER BY jo.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $job_offers = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get applications for employers
$applications = [];
if($user["role"] === "employer") {
    $sql = "SELECT ja.*, jo.title, u.username as applicant_name, uc.full_name, uc.employment_status 
            FROM job_applications ja 
            JOIN job_offers jo ON ja.job_offer_id = jo.id 
            JOIN users u ON ja.applicant_id = u.id 
            JOIN user_credentials uc ON u.id = uc.user_id 
            WHERE jo.employer_id = :employer_id 
            ORDER BY ja.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":employer_id", $_SESSION["id"], PDO::PARAM_INT);
    $stmt->execute();
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get application statistics for employers
$stats = [];
if($user["role"] === "employer") {
    $sql = "SELECT 
                COUNT(*) as total_applications,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'reviewed' THEN 1 ELSE 0 END) as reviewed,
                SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
            FROM job_applications ja 
            JOIN job_offers jo ON ja.job_offer_id = jo.id 
            WHERE jo.employer_id = :employer_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":employer_id", $_SESSION["id"], PDO::PARAM_INT);
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .dashboard-header {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .dashboard-card {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .stats-card {
            text-align: center;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .stats-card i {
            font-size: 2em;
            margin-bottom: 10px;
        }

        .btn-primary {
            background-color: #000;
            border-color: #000;
        }

        .btn-primary:hover {
            background-color: #333;
            border-color: #333;
        }

        .nav-tabs {
            border-bottom: 2px solid #dee2e6;
        }

        .nav-tabs .nav-link {
            border: none;
            color: #000;
        }

        .nav-tabs .nav-link.active {
            border: none;
            border-bottom: 2px solid #000;
            color: #000;
        }

        .table th {
            border-top: none;
        }

        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="dashboard-header">
            <div class="row">
                <div class="col-md-8">
                    <h2>Welcome, <?php echo htmlspecialchars($user["full_name"]); ?>!</h2>
                    <p>Role: <?php echo ucfirst(htmlspecialchars($user["role"])); ?></p>
                </div>
                <div class="col-md-4 text-right">
                    <a href="edit_profile.php" class="btn btn-primary">Edit Profile</a>
                    <a href="logout.php" class="btn btn-outline-danger">Sign Out</a>
                </div>
            </div>
        </div>

        <?php if($user["role"] === "employer"): ?>
            <!-- Employer Dashboard -->
            <div class="row">
                <div class="col-md-4">
                    <div class="stats-card bg-light">
                        <i class="fas fa-file-alt text-primary"></i>
                        <h3><?php echo $stats["total_applications"]; ?></h3>
                        <p>Total Applications</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card bg-light">
                        <i class="fas fa-clock text-warning"></i>
                        <h3><?php echo $stats["pending"]; ?></h3>
                        <p>Pending Reviews</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card bg-light">
                        <i class="fas fa-check-circle text-success"></i>
                        <h3><?php echo $stats["accepted"]; ?></h3>
                        <p>Accepted Applications</p>
                    </div>
                </div>
            </div>

            <div class="dashboard-card">
                <div class="row">
                    <div class="col-md-8">
                        <h3>Application Statistics</h3>
                        <div class="chart-container">
                            <canvas id="applicationsChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h3>Quick Actions</h3>
                        <a href="post_job.php" class="btn btn-primary btn-block mb-2">Post New Job</a>
                        <a href="manage_jobs.php" class="btn btn-outline-primary btn-block">Manage Jobs</a>
                    </div>
                </div>
            </div>

            <div class="dashboard-card">
                <h3>Recent Applications</h3>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Applicant</th>
                                <th>Job Title</th>
                                <th>Status</th>
                                <th>Applied Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($applications as $application): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($application["full_name"]); ?></td>
                                    <td><?php echo htmlspecialchars($application["title"]); ?></td>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo $application["status"] === "pending" ? "warning" : 
                                                ($application["status"] === "accepted" ? "success" : 
                                                ($application["status"] === "rejected" ? "danger" : "info")); 
                                        ?>">
                                            <?php echo ucfirst(htmlspecialchars($application["status"])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date("M d, Y", strtotime($application["created_at"])); ?></td>
                                    <td>
                                        <a href="view_application.php?id=<?php echo $application["id"]; ?>" class="btn btn-sm btn-primary">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php else: ?>
            <!-- Job Seeker Dashboard -->
            <div class="row">
                <div class="col-md-4">
                    <div class="stats-card bg-light">
                        <i class="fas fa-file-alt text-primary"></i>
                        <h3>Resume</h3>
                        <p>Manage your resume</p>
                        <a href="manage_resume.php" class="btn btn-primary btn-sm">Update Resume</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card bg-light">
                        <i class="fas fa-briefcase text-success"></i>
                        <h3>Applications</h3>
                        <p>Track your applications</p>
                        <a href="my_applications.php" class="btn btn-primary btn-sm">View Applications</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card bg-light">
                        <i class="fas fa-search text-info"></i>
                        <h3>Search Jobs</h3>
                        <p>Find new opportunities</p>
                        <a href="search_jobs.php" class="btn btn-primary btn-sm">Search Jobs</a>
                    </div>
                </div>
            </div>

            <div class="dashboard-card">
                <h3>Latest Job Offers</h3>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Company</th>
                                <th>Position</th>
                                <th>Location</th>
                                <th>Posted Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($job_offers as $job): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($job["company_name"]); ?></td>
                                    <td><?php echo htmlspecialchars($job["title"]); ?></td>
                                    <td><?php echo htmlspecialchars($job["location"]); ?></td>
                                    <td><?php echo date("M d, Y", strtotime($job["created_at"])); ?></td>
                                    <td>
                                        <a href="view_job.php?id=<?php echo $job["id"]; ?>" class="btn btn-sm btn-primary">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if($user["role"] === "employer"): ?>
    <script>
        // Initialize applications chart
        const ctx = document.getElementById('applicationsChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Reviewed', 'Accepted', 'Rejected'],
                datasets: [{
                    data: [
                        <?php echo $stats["pending"]; ?>,
                        <?php echo $stats["reviewed"]; ?>,
                        <?php echo $stats["accepted"]; ?>,
                        <?php echo $stats["rejected"]; ?>
                    ],
                    backgroundColor: [
                        '#ffc107',
                        '#17a2b8',
                        '#28a745',
                        '#dc3545'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
    <?php endif; ?>
</body>
</html> 
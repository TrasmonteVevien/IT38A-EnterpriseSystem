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

// Initialize variables
$location = $job_type = $search = "";
$jobs = [];

// Process search form
if($_SERVER["REQUEST_METHOD"] == "POST" || isset($_GET["search"])) {
    $search = isset($_POST["search"]) ? trim($_POST["search"]) : (isset($_GET["search"]) ? trim($_GET["search"]) : "");
    $location = isset($_POST["location"]) ? trim($_POST["location"]) : (isset($_GET["location"]) ? trim($_GET["location"]) : "");
    $job_type = isset($_POST["job_type"]) ? trim($_POST["job_type"]) : (isset($_GET["job_type"]) ? trim($_GET["job_type"]) : "");

    // Build query
    $sql = "SELECT jo.*, u.username as employer_name, uc.company_name 
            FROM job_offers jo 
            JOIN users u ON jo.employer_id = u.id 
            JOIN user_credentials uc ON u.id = uc.user_id 
            WHERE jo.status = 'active'";
    $params = [];

    if(!empty($search)) {
        $sql .= " AND (jo.title LIKE :search OR jo.description LIKE :search OR uc.company_name LIKE :search)";
        $params[":search"] = "%$search%";
    }

    if(!empty($location)) {
        $sql .= " AND jo.location = :location";
        $params[":location"] = $location;
    }

    if(!empty($job_type)) {
        $sql .= " AND jo.job_type = :job_type";
        $params[":job_type"] = $job_type;
    }

    $sql .= " ORDER BY jo.created_at DESC";

    $stmt = $pdo->prepare($sql);
    foreach($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Jobs</title>
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

        .search-header {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .job-card {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: transform 0.2s;
        }

        .job-card:hover {
            transform: translateY(-5px);
        }

        .btn-primary {
            background-color: #000;
            border-color: #000;
        }

        .btn-primary:hover {
            background-color: #333;
            border-color: #333;
        }

        .form-control:focus {
            border-color: #000;
            box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.25);
        }

        .company-logo {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
            margin-right: 15px;
        }

        .job-meta {
            color: #666;
            font-size: 0.9em;
        }

        .job-meta i {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="search-header">
            <div class="row">
                <div class="col-md-8">
                    <h2>Search Jobs</h2>
                    <p>Find your next opportunity in Bukidnon</p>
                </div>
                <div class="col-md-4 text-right">
                    <a href="dashboard.php" class="btn btn-outline-primary">Back to Dashboard</a>
                </div>
            </div>
        </div>

        <div class="search-header">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <input type="text" name="search" class="form-control" placeholder="Search jobs or companies..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <select name="location" class="form-control">
                                <option value="">All Locations</option>
                                <option value="Bukidnon" <?php echo $location === "Bukidnon" ? "selected" : ""; ?>>Bukidnon</option>
                                <option value="Manolo Fortich" <?php echo $location === "Manolo Fortich" ? "selected" : ""; ?>>Manolo Fortich</option>
                                <option value="Damilag" <?php echo $location === "Damilag" ? "selected" : ""; ?>>Damilag</option>
                                <option value="Phillips" <?php echo $location === "Phillips" ? "selected" : ""; ?>>Phillips</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <select name="job_type" class="form-control">
                                <option value="">All Job Types</option>
                                <option value="Web Developer" <?php echo $job_type === "Web Developer" ? "selected" : ""; ?>>Web Developer</option>
                                <option value="Technician" <?php echo $job_type === "Technician" ? "selected" : ""; ?>>Technician</option>
                                <option value="Dishwasher" <?php echo $job_type === "Dishwasher" ? "selected" : ""; ?>>Dishwasher</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-block">Search</button>
                    </div>
                </div>
            </form>
        </div>

        <?php if(!empty($jobs)): ?>
            <?php foreach($jobs as $job): ?>
                <div class="job-card">
                    <div class="row">
                        <div class="col-md-8">
                            <h4><?php echo htmlspecialchars($job["title"]); ?></h4>
                            <p class="job-meta">
                                <i class="fas fa-building"></i> <?php echo htmlspecialchars($job["company_name"]); ?><br>
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job["location"]); ?><br>
                                <i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($job["job_type"]); ?><br>
                                <?php if(!empty($job["salary_range"])): ?>
                                    <i class="fas fa-money-bill-wave"></i> <?php echo htmlspecialchars($job["salary_range"]); ?>
                                <?php endif; ?>
                            </p>
                            <p><?php echo nl2br(htmlspecialchars(substr($job["description"], 0, 200) . "...")); ?></p>
                        </div>
                        <div class="col-md-4 text-right">
                            <a href="view_job.php?id=<?php echo $job["id"]; ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info">
                No jobs found matching your criteria. Try adjusting your search filters.
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 
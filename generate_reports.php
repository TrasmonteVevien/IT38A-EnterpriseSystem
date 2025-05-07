<?php
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: index.php");
    exit;
}

require_once "config.php";

// Handle report generation
if(isset($_POST["generate_report"])) {
    $report_type = $_POST["report_type"];
    $date_from = $_POST["date_from"];
    $date_to = $_POST["date_to"];
    $format = $_POST["format"];
    
    // Generate report based on type
    switch($report_type) {
        case "users":
            $sql = "SELECT u.*, uc.full_name, uc.email, uc.phone, uc.employment_status
                    FROM users u
                    LEFT JOIN user_credentials uc ON u.id = uc.user_id
                    WHERE u.created_at BETWEEN :date_from AND :date_to
                    ORDER BY u.created_at DESC";
            break;
            
        case "transactions":
            $sql = "SELECT t.*, u.username, uc.full_name
                    FROM transactions t
                    JOIN users u ON t.user_id = u.id
                    LEFT JOIN user_credentials uc ON u.id = uc.user_id
                    WHERE t.transaction_date BETWEEN :date_from AND :date_to
                    ORDER BY t.transaction_date DESC";
            break;
            
        case "feedback":
            $sql = "SELECT f.*, u.username, uc.full_name
                    FROM feedback f
                    JOIN users u ON f.user_id = u.id
                    LEFT JOIN user_credentials uc ON u.id = uc.user_id
                    WHERE f.created_at BETWEEN :date_from AND :date_to
                    ORDER BY f.created_at DESC";
            break;
            
        case "activity":
            $sql = "SELECT al.*, u.username
                    FROM activity_logs al
                    JOIN users u ON al.user_id = u.id
                    WHERE al.created_at BETWEEN :date_from AND :date_to
                    ORDER BY al.created_at DESC";
            break;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":date_from", $date_from);
    $stmt->bindParam(":date_to", $date_to);
    $stmt->execute();
    $report_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Save report to database
    $sql = "INSERT INTO reports (admin_id, report_type, date_from, date_to, format, created_at) 
            VALUES (:admin_id, :report_type, :date_from, :date_to, :format, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":admin_id", $_SESSION["id"]);
    $stmt->bindParam(":report_type", $report_type);
    $stmt->bindParam(":date_from", $date_from);
    $stmt->bindParam(":date_to", $date_to);
    $stmt->bindParam(":format", $format);
    $stmt->execute();
    $report_id = $pdo->lastInsertId();
    
    // Generate file based on format
    if($format === "csv") {
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=report_$report_id.csv");
        
        $output = fopen("php://output", "w");
        fputcsv($output, array_keys($report_data[0]));
        
        foreach($report_data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    } elseif($format === "pdf") {
        // PDF generation would go here
        // This would require a PDF library like TCPDF or FPDF
    }
}

// Get recent reports
$sql = "SELECT r.*, u.username as admin_username 
        FROM reports r 
        JOIN users u ON r.admin_id = u.id 
        ORDER BY r.created_at DESC 
        LIMIT 10";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$recent_reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Generation - Admin Dashboard</title>
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
        .report-form {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .recent-reports {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Report Generation</h2>
            <a href="dashboard.php" class="btn btn-outline-primary">Back to Dashboard</a>
        </div>

        <!-- Report Generation Form -->
        <div class="report-form">
            <form method="post" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Report Type</label>
                            <select name="report_type" class="form-control" required>
                                <option value="">Select Report Type</option>
                                <option value="users">User Report</option>
                                <option value="transactions">Transaction Report</option>
                                <option value="feedback">Feedback Report</option>
                                <option value="activity">Activity Log Report</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Date From</label>
                            <input type="date" name="date_from" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Date To</label>
                            <input type="date" name="date_to" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Format</label>
                            <select name="format" class="form-control" required>
                                <option value="csv">CSV</option>
                                <option value="pdf">PDF</option>
                            </select>
                        </div>
                    </div>
                </div>
                <button type="submit" name="generate_report" class="btn btn-primary">
                    <i class="fas fa-file-export"></i> Generate Report
                </button>
            </form>
        </div>

        <!-- Recent Reports -->
        <div class="recent-reports">
            <h4>Recent Reports</h4>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Date Range</th>
                            <th>Format</th>
                            <th>Generated By</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recent_reports as $report): ?>
                            <tr>
                                <td><?php echo $report["id"]; ?></td>
                                <td><?php echo ucfirst($report["report_type"]); ?></td>
                                <td>
                                    <?php echo date("M d, Y", strtotime($report["date_from"])); ?> -
                                    <?php echo date("M d, Y", strtotime($report["date_to"])); ?>
                                </td>
                                <td><?php echo strtoupper($report["format"]); ?></td>
                                <td><?php echo htmlspecialchars($report["admin_username"]); ?></td>
                                <td><?php echo date("M d, Y H:i", strtotime($report["created_at"])); ?></td>
                                <td>
                                    <a href="download_report.php?id=<?php echo $report["id"]; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
    </script>
</body>
</html> 
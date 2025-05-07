<?php
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: index.php");
    exit;
}

require_once "config.php";

// Handle feedback status updates
if(isset($_POST["update_status"])) {
    $feedback_id = $_POST["feedback_id"];
    $new_status = $_POST["status"];
    $admin_response = $_POST["admin_response"];
    
    $sql = "UPDATE feedback SET status = :status, admin_response = :admin_response, resolved_at = NOW() WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":status", $new_status);
    $stmt->bindParam(":admin_response", $admin_response);
    $stmt->bindParam(":id", $feedback_id);
    $stmt->execute();
    
    // Log the activity
    $sql = "INSERT INTO activity_logs (user_id, action, description) VALUES (:admin_id, 'update_feedback_status', :description)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":admin_id", $_SESSION["id"]);
    $description = "Updated feedback ID $feedback_id status to $new_status";
    $stmt->bindParam(":description", $description);
    $stmt->execute();
}

// Get feedback statistics
$sql = "SELECT 
            COUNT(*) as total_feedback,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_feedback,
            SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_feedback,
            SUM(CASE WHEN type = 'bug' THEN 1 ELSE 0 END) as bug_reports,
            SUM(CASE WHEN type = 'feature' THEN 1 ELSE 0 END) as feature_requests,
            SUM(CASE WHEN type = 'complaint' THEN 1 ELSE 0 END) as complaints
        FROM feedback";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Get all feedback with user information
$sql = "SELECT f.*, u.username, uc.full_name 
        FROM feedback f 
        JOIN users u ON f.user_id = u.id 
        LEFT JOIN user_credentials uc ON u.id = uc.user_id 
        ORDER BY f.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$feedback_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Feedback Management - Admin Dashboard</title>
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
        .stats-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
        }
        .stats-card i {
            font-size: 2em;
            margin-bottom: 10px;
        }
        .feedback-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .feedback-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .feedback-content {
            margin-bottom: 15px;
        }
        .feedback-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9em;
            color: #666;
        }
        .type-bug { color: #dc3545; }
        .type-feature { color: #28a745; }
        .type-complaint { color: #ffc107; }
        .status-pending { color: #ffc107; }
        .status-resolved { color: #28a745; }
        .status-rejected { color: #dc3545; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Feedback Management</h2>
            <div>
                <a href="dashboard.php" class="btn btn-outline-primary">Back to Dashboard</a>
                <a href="generate_reports.php?type=feedback" class="btn btn-outline-success">Generate Report</a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <i class="fas fa-comments text-primary"></i>
                    <h3><?php echo $stats["total_feedback"]; ?></h3>
                    <p>Total Feedback</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <i class="fas fa-clock text-warning"></i>
                    <h3><?php echo $stats["pending_feedback"]; ?></h3>
                    <p>Pending Feedback</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <i class="fas fa-check-circle text-success"></i>
                    <h3><?php echo $stats["resolved_feedback"]; ?></h3>
                    <p>Resolved Feedback</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <i class="fas fa-bug text-danger"></i>
                    <h3><?php echo $stats["bug_reports"]; ?></h3>
                    <p>Bug Reports</p>
                </div>
            </div>
        </div>

        <!-- Feedback List -->
        <div class="feedback-list">
            <?php foreach($feedback_list as $feedback): ?>
                <div class="feedback-card">
                    <div class="feedback-header">
                        <div>
                            <h5 class="mb-0">
                                <span class="type-<?php echo $feedback["type"]; ?>">
                                    <i class="fas fa-<?php echo $feedback["type"] === "bug" ? "bug" : ($feedback["type"] === "feature" ? "lightbulb" : "exclamation-circle"); ?>"></i>
                                    <?php echo ucfirst($feedback["type"]); ?>
                                </span>
                            </h5>
                            <small class="text-muted">
                                From: <?php echo htmlspecialchars($feedback["full_name"]); ?> 
                                (<?php echo htmlspecialchars($feedback["username"]); ?>)
                            </small>
                        </div>
                        <div>
                            <span class="status-<?php echo $feedback["status"]; ?>">
                                <?php echo ucfirst($feedback["status"]); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="feedback-content">
                        <p><?php echo nl2br(htmlspecialchars($feedback["message"])); ?></p>
                    </div>
                    
                    <div class="feedback-footer">
                        <div>
                            <small>Submitted: <?php echo date("M d, Y H:i", strtotime($feedback["created_at"])); ?></small>
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#responseModal<?php echo $feedback["id"]; ?>">
                                Respond
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Response Modal -->
                <div class="modal fade" id="responseModal<?php echo $feedback["id"]; ?>" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Respond to Feedback</h5>
                                <button type="button" class="close" data-dismiss="modal">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <form method="post">
                                <div class="modal-body">
                                    <input type="hidden" name="feedback_id" value="<?php echo $feedback["id"]; ?>">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="status" class="form-control">
                                            <option value="pending" <?php echo $feedback["status"] === "pending" ? "selected" : ""; ?>>Pending</option>
                                            <option value="resolved" <?php echo $feedback["status"] === "resolved" ? "selected" : ""; ?>>Resolved</option>
                                            <option value="rejected" <?php echo $feedback["status"] === "rejected" ? "selected" : ""; ?>>Rejected</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Admin Response</label>
                                        <textarea name="admin_response" class="form-control" rows="4"><?php echo htmlspecialchars($feedback["admin_response"]); ?></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" name="update_status" class="btn btn-primary">Save Response</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 
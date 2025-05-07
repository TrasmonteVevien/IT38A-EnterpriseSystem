<?php
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: index.php");
    exit;
}

require_once "config.php";

// Handle security update submission
if(isset($_POST["submit_update"])) {
    $update_type = $_POST["update_type"];
    $description = $_POST["description"];
    $severity = $_POST["severity"];
    $status = $_POST["status"];
    
    $sql = "INSERT INTO security_updates (admin_id, update_type, description, severity, status, created_at) 
            VALUES (:admin_id, :update_type, :description, :severity, :status, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":admin_id", $_SESSION["id"]);
    $stmt->bindParam(":update_type", $update_type);
    $stmt->bindParam(":description", $description);
    $stmt->bindParam(":severity", $severity);
    $stmt->bindParam(":status", $status);
    $stmt->execute();
    
    // Log the activity
    $sql = "INSERT INTO activity_logs (user_id, action, description) VALUES (:admin_id, 'security_update', :description)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":admin_id", $_SESSION["id"]);
    $description = "Added new security update: $update_type";
    $stmt->bindParam(":description", $description);
    $stmt->execute();
}

// Handle settings update
if(isset($_POST["update_settings"])) {
    $setting_name = $_POST["setting_name"];
    $setting_value = $_POST["setting_value"];
    
    $sql = "UPDATE admin_settings SET setting_value = :value WHERE setting_name = :name";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":value", $setting_value);
    $stmt->bindParam(":name", $setting_name);
    $stmt->execute();
    
    // Log the activity
    $sql = "INSERT INTO activity_logs (user_id, action, description) VALUES (:admin_id, 'update_settings', :description)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":admin_id", $_SESSION["id"]);
    $description = "Updated setting: $setting_name";
    $stmt->bindParam(":description", $description);
    $stmt->execute();
}

// Get recent security updates
$sql = "SELECT su.*, u.username as admin_username 
        FROM security_updates su 
        JOIN users u ON su.admin_id = u.id 
        ORDER BY su.created_at DESC 
        LIMIT 10";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$security_updates = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get current settings
$sql = "SELECT * FROM admin_settings";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Security Settings - Admin Dashboard</title>
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
        .security-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .severity-high { color: #dc3545; }
        .severity-medium { color: #ffc107; }
        .severity-low { color: #28a745; }
        .status-pending { color: #ffc107; }
        .status-resolved { color: #28a745; }
        .status-in-progress { color: #17a2b8; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Security Settings</h2>
            <a href="dashboard.php" class="btn btn-outline-primary">Back to Dashboard</a>
        </div>

        <div class="row">
            <!-- Security Updates -->
            <div class="col-md-8">
                <div class="security-card">
                    <h4>Security Updates</h4>
                    <form method="post" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Update Type</label>
                                    <select name="update_type" class="form-control" required>
                                        <option value="patch">Security Patch</option>
                                        <option value="vulnerability">Vulnerability Fix</option>
                                        <option value="update">System Update</option>
                                        <option value="maintenance">Maintenance</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Severity</label>
                                    <select name="severity" class="form-control" required>
                                        <option value="high">High</option>
                                        <option value="medium">Medium</option>
                                        <option value="low">Low</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control" required>
                                        <option value="pending">Pending</option>
                                        <option value="in-progress">In Progress</option>
                                        <option value="resolved">Resolved</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                        </div>
                        <button type="submit" name="submit_update" class="btn btn-primary">
                            <i class="fas fa-shield-alt"></i> Submit Update
                        </button>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Severity</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($security_updates as $update): ?>
                                    <tr>
                                        <td><?php echo ucfirst($update["update_type"]); ?></td>
                                        <td><?php echo htmlspecialchars($update["description"]); ?></td>
                                        <td>
                                            <span class="severity-<?php echo $update["severity"]; ?>">
                                                <?php echo ucfirst($update["severity"]); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-<?php echo $update["status"]; ?>">
                                                <?php echo ucfirst($update["status"]); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date("M d, Y H:i", strtotime($update["created_at"])); ?></td>
                                        <td><?php echo htmlspecialchars($update["admin_username"]); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- System Settings -->
            <div class="col-md-4">
                <div class="security-card">
                    <h4>System Settings</h4>
                    <form method="post">
                        <?php foreach($settings as $setting): ?>
                            <div class="form-group">
                                <label><?php echo ucwords(str_replace("_", " ", $setting["setting_name"])); ?></label>
                                <input type="text" name="setting_value" class="form-control" 
                                       value="<?php echo htmlspecialchars($setting["setting_value"]); ?>" required>
                                <input type="hidden" name="setting_name" value="<?php echo $setting["setting_name"]; ?>">
                            </div>
                        <?php endforeach; ?>
                        <button type="submit" name="update_settings" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 
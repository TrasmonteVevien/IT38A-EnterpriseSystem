<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}

// Example company data (replace with database data later)
$companies = [
    ["name" => "Del Monte Philippines Inc.", "position" => "Quality Assurance Analyst", "location" => "Manolo Fortich, Bukidnon", "openings" => 3],
    ["name" => "Sumifru Philippines Corp.", "position" => "Production Supervisor", "location" => "Quezon, Bukidnon", "openings" => 2],
    ["name" => "Lantapan Agri Ventures Inc.", "position" => "Agriculturist", "location" => "Lantapan, Bukidnon", "openings" => 1],
    ["name" => "Kasilak Development Foundation", "position" => "Community Development Officer", "location" => "Valencia City, Bukidnon", "openings" => 2],
    ["name" => "Monde Nissin Corporation", "position" => "Machine Operator", "location" => "Malaybalay City, Bukidnon", "openings" => 5],
    ["name" => "San Miguel Foods Inc.", "position" => "Warehouse Staff", "location" => "Maramag, Bukidnon", "openings" => 4],
    ["name" => "Dole Philippines Inc.", "position" => "HR Assistant", "location" => "Quezon, Bukidnon", "openings" => 2],
    ["name" => "Universal Robina Corporation", "position" => "Electrical Technician", "location" => "Valencia City, Bukidnon", "openings" => 2],
    ["name" => "La Fortuna Farms", "position" => "Farm Supervisor", "location" => "Don Carlos, Bukidnon", "openings" => 1],
    ["name" => "Northern Mindanao Medical Center", "position" => "Medical Technologist", "location" => "Malaybalay City, Bukidnon", "openings" => 3],
    ["name" => "Mindanao State University", "position" => "IT Support Specialist", "location" => "Maramag, Bukidnon", "openings" => 2],
    ["name" => "Bukidnon State University", "position" => "Guidance Counselor", "location" => "Malaybalay City, Bukidnon", "openings" => 1],
    ["name" => "Mount Kitanglad Agri Dev Corp.", "position" => "Environmental Officer", "location" => "Lantapan, Bukidnon", "openings" => 1],
    ["name" => "Taipan Agri Ventures", "position" => "Finance Associate", "location" => "Kibawe, Bukidnon", "openings" => 2],
    ["name" => "Philippine Carabao Center", "position" => "Research Assistant", "location" => "Central Mindanao University, Maramag", "openings" => 1],
    ["name" => "Nestle Philippines Inc.", "position" => "Logistics Coordinator", "location" => "Cagayan de Oro (near Bukidnon)", "openings" => 2],
    ["name" => "Green Mindanao Energy Corp.", "position" => "Mechanical Engineer", "location" => "Quezon, Bukidnon", "openings" => 3],
    ["name" => "AgriFarm Solutions", "position" => "Agri Technician", "location" => "Valencia City, Bukidnon", "openings" => 2],
    ["name" => "Bukidnon Hydro Energy Corp.", "position" => "Civil Engineer", "location" => "Impasug-ong, Bukidnon", "openings" => 2],
    ["name" => "NorMinCorp", "position" => "Marketing Associate", "location" => "Malaybalay City, Bukidnon", "openings" => 1],
];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | JPost</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #fff;
            color: #000;
        }
        .navbar {
            background-color: #000;
            color: #fff;
        }
        .navbar .form-control {
            background-color: #333;
            border: none;
            color: #fff;
        }
        .navbar .form-control::placeholder {
            color: #ccc;
        }
        .navbar .btn, .navbar a {
            color: #fff;
        }
        .sidebar {
            background-color: #f1f1f1;
            min-height: 100vh;
            padding: 20px;
        }
        .sidebar a {
            display: block;
            padding: 10px 15px;
            margin: 10px 0;
            color: #000;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
        }
        .sidebar a:hover {
            background-color: #ddd;
        }
        .content {
            padding: 30px;
        }
        table th {
            background-color: #000;
            color: #fff;
        }
    </style>
</head>
<body>

<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark px-4">
    <a class="navbar-brand font-weight-bold" href="#">JPost</a>
    <form class="form-inline ml-auto">
        <input class="form-control mr-sm-2" type="search" placeholder="Search jobs..." aria-label="Search">
        <a href="settings.php" class="btn btn-link"><i class="bi bi-gear-fill"></i></a>
        <a href="logout.php" class="btn btn-link"><i class="bi bi-box-arrow-right"></i></a>
    </form>
</nav>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 sidebar">
            <h5 class="text-center mb-4">Hello, <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b>!</h5>
            <a href="profile.php"><i class="bi bi-person-circle"></i> Profile</a>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 content">
            <h2>Available Job Openings</h2>
            <p>Below are companies currently looking for applicants:</p>

            <table class="table table-bordered table-hover mt-4">
                <thead>
                    <tr>
                        <th>Company Name</th>
                        <th>Position</th>
                        <th>Location</th>
                        <th>Openings</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($companies as $company): ?>
                        <tr>
                            <td><?php echo $company['name']; ?></td>
                            <td><?php echo $company['position']; ?></td>
                            <td><?php echo $company['location']; ?></td>
                            <td><?php echo $company['openings']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
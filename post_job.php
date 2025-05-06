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

// Define variables and initialize with empty values
$title = $description = $location = $job_type = $salary_range = $requirements = "";
$title_err = $description_err = $location_err = $job_type_err = $salary_range_err = $requirements_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate title
    if(empty(trim($_POST["title"]))){
        $title_err = "Please enter a job title.";
    } else{
        $title = trim($_POST["title"]);
    }

    // Validate description
    if(empty(trim($_POST["description"]))){
        $description_err = "Please enter a job description.";
    } else{
        $description = trim($_POST["description"]);
    }

    // Validate location
    if(empty(trim($_POST["location"]))){
        $location_err = "Please select a location.";
    } else{
        $location = trim($_POST["location"]);
    }

    // Validate job type
    if(empty(trim($_POST["job_type"]))){
        $job_type_err = "Please select a job type.";
    } else{
        $job_type = trim($_POST["job_type"]);
    }

    // Validate salary range
    if(empty(trim($_POST["salary_range"]))){
        $salary_range_err = "Please enter a salary range.";
    } else{
        $salary_range = trim($_POST["salary_range"]);
    }

    // Validate requirements
    if(empty(trim($_POST["requirements"]))){
        $requirements_err = "Please enter job requirements.";
    } else{
        $requirements = trim($_POST["requirements"]);
    }

    // Check input errors before inserting in database
    if(empty($title_err) && empty($description_err) && empty($location_err) && 
       empty($job_type_err) && empty($salary_range_err) && empty($requirements_err)){
        
        // Prepare an insert statement
        $sql = "INSERT INTO job_offers (employer_id, title, description, location, job_type, salary_range, requirements) 
                VALUES (:employer_id, :title, :description, :location, :job_type, :salary_range, :requirements)";
         
        if($stmt = $pdo->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":employer_id", $param_employer_id, PDO::PARAM_INT);
            $stmt->bindParam(":title", $param_title, PDO::PARAM_STR);
            $stmt->bindParam(":description", $param_description, PDO::PARAM_STR);
            $stmt->bindParam(":location", $param_location, PDO::PARAM_STR);
            $stmt->bindParam(":job_type", $param_job_type, PDO::PARAM_STR);
            $stmt->bindParam(":salary_range", $param_salary_range, PDO::PARAM_STR);
            $stmt->bindParam(":requirements", $param_requirements, PDO::PARAM_STR);
            
            // Set parameters
            $param_employer_id = $_SESSION["id"];
            $param_title = $title;
            $param_description = $description;
            $param_location = $location;
            $param_job_type = $job_type;
            $param_salary_range = $salary_range;
            $param_requirements = $requirements;
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Redirect to dashboard
                header("location: dashboard.php");
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            unset($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Post a Job</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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

        .form-header {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .form-card {
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

        .form-control:focus {
            border-color: #000;
            box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.25);
        }

        .invalid-feedback {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="form-header">
            <div class="row">
                <div class="col-md-8">
                    <h2>Post a New Job</h2>
                    <p>Fill in the details to create a new job listing</p>
                </div>
                <div class="col-md-4 text-right">
                    <a href="dashboard.php" class="btn btn-outline-primary">Back to Dashboard</a>
                </div>
            </div>
        </div>

        <div class="form-card">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label>Job Title</label>
                    <input type="text" name="title" class="form-control <?php echo (!empty($title_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $title; ?>">
                    <span class="invalid-feedback"><?php echo $title_err; ?></span>
                </div>

                <div class="form-group">
                    <label>Job Description</label>
                    <textarea name="description" class="form-control <?php echo (!empty($description_err)) ? 'is-invalid' : ''; ?>" rows="5"><?php echo $description; ?></textarea>
                    <span class="invalid-feedback"><?php echo $description_err; ?></span>
                </div>

                <div class="form-group">
                    <label>Location</label>
                    <select name="location" class="form-control <?php echo (!empty($location_err)) ? 'is-invalid' : ''; ?>">
                        <option value="">Select Location</option>
                        <option value="Bukidnon" <?php echo $location === "Bukidnon" ? "selected" : ""; ?>>Bukidnon</option>
                        <option value="Manolo Fortich" <?php echo $location === "Manolo Fortich" ? "selected" : ""; ?>>Manolo Fortich</option>
                        <option value="Damilag" <?php echo $location === "Damilag" ? "selected" : ""; ?>>Damilag</option>
                        <option value="Phillips" <?php echo $location === "Phillips" ? "selected" : ""; ?>>Phillips</option>
                    </select>
                    <span class="invalid-feedback"><?php echo $location_err; ?></span>
                </div>

                <div class="form-group">
                    <label>Job Type</label>
                    <select name="job_type" class="form-control <?php echo (!empty($job_type_err)) ? 'is-invalid' : ''; ?>">
                        <option value="">Select Job Type</option>
                        <option value="Web Developer" <?php echo $job_type === "Web Developer" ? "selected" : ""; ?>>Web Developer</option>
                        <option value="Technician" <?php echo $job_type === "Technician" ? "selected" : ""; ?>>Technician</option>
                        <option value="Dishwasher" <?php echo $job_type === "Dishwasher" ? "selected" : ""; ?>>Dishwasher</option>
                    </select>
                    <span class="invalid-feedback"><?php echo $job_type_err; ?></span>
                </div>

                <div class="form-group">
                    <label>Salary Range</label>
                    <input type="text" name="salary_range" class="form-control <?php echo (!empty($salary_range_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $salary_range; ?>" placeholder="e.g., ₱15,000 - ₱20,000">
                    <span class="invalid-feedback"><?php echo $salary_range_err; ?></span>
                </div>

                <div class="form-group">
                    <label>Requirements</label>
                    <textarea name="requirements" class="form-control <?php echo (!empty($requirements_err)) ? 'is-invalid' : ''; ?>" rows="5"><?php echo $requirements; ?></textarea>
                    <span class="invalid-feedback"><?php echo $requirements_err; ?></span>
                </div>

                <div class="form-group">
                    <input type="submit" class="btn btn-primary" value="Post Job">
                </div>
            </form>
        </div>
    </div>
</body>
</html> 
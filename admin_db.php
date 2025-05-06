<?php
class AdminDB {
    private $pdo;
    private static $instance = null;

    private function __construct() {
        try {
            $this->pdo = new PDO(
                "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME,
                DB_USERNAME,
                DB_PASSWORD,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    // User Management Methods
    public function getUsers($limit = 10, $offset = 0) {
        $stmt = $this->pdo->prepare("
            SELECT u.*, uc.full_name, uc.employment_status 
            FROM users u 
            LEFT JOIN user_credentials uc ON u.id = uc.user_id 
            ORDER BY u.created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    public function getUserStats() {
        $stmt = $this->pdo->query("SELECT 
            COUNT(*) as total_users,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_users,
            SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_users,
            SUM(CASE WHEN role = 'employer' THEN 1 ELSE 0 END) as total_employers,
            SUM(CASE WHEN role = 'jobseeker' THEN 1 ELSE 0 END) as total_jobseekers,
            SUM(CASE WHEN employment_status = 'student' THEN 1 ELSE 0 END) as total_students,
            SUM(CASE WHEN employment_status = 'graduate' THEN 1 ELSE 0 END) as total_graduates,
            SUM(CASE WHEN employment_status = 'employed' THEN 1 ELSE 0 END) as total_employed
            FROM users u
            LEFT JOIN user_credentials uc ON u.id = uc.user_id");
        return $stmt->fetch();
    }

    // Job Management Methods
    public function getJobs($limit = 10, $offset = 0) {
        $stmt = $this->pdo->prepare("
            SELECT jo.*, u.username as employer_name, uc.company_name
            FROM job_offers jo
            JOIN users u ON jo.employer_id = u.id
            LEFT JOIN user_credentials uc ON u.id = uc.user_id
            ORDER BY jo.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    public function getJobStats() {
        $stmt = $this->pdo->query("SELECT 
            COUNT(*) as total_jobs,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_jobs,
            SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_jobs,
            COUNT(DISTINCT employer_id) as total_employers,
            COUNT(DISTINCT location) as total_locations
            FROM job_offers");
        return $stmt->fetch();
    }

    // Application Management Methods
    public function getApplications($limit = 10, $offset = 0) {
        $stmt = $this->pdo->prepare("
            SELECT ja.*, jo.title as job_title, 
                   u1.username as applicant_name, u2.username as employer_name,
                   r.file_path as resume_path
            FROM job_applications ja
            JOIN job_offers jo ON ja.job_offer_id = jo.id
            JOIN users u1 ON ja.applicant_id = u1.id
            JOIN users u2 ON jo.employer_id = u2.id
            JOIN resumes r ON ja.resume_id = r.id
            ORDER BY ja.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    // Security Logs Methods
    public function getSecurityLogs($limit = 10, $offset = 0) {
        $stmt = $this->pdo->prepare("
            SELECT sl.*, u.username
            FROM security_logs sl
            LEFT JOIN users u ON sl.user_id = u.id
            ORDER BY sl.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    // Activity Logs Methods
    public function getActivityLogs($limit = 10, $offset = 0) {
        $stmt = $this->pdo->prepare("
            SELECT al.*, u.username
            FROM activity_logs al
            JOIN users u ON al.user_id = u.id
            ORDER BY al.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    // Settings Methods
    public function getSettings() {
        $stmt = $this->pdo->query("SELECT * FROM settings");
        return $stmt->fetchAll();
    }

    public function updateSetting($key, $value) {
        $stmt = $this->pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        return $stmt->execute([$value, $key]);
    }

    // Feedback Methods
    public function getFeedback($limit = 10, $offset = 0) {
        $stmt = $this->pdo->prepare("
            SELECT f.*, u.username, uc.full_name
            FROM feedback f
            JOIN users u ON f.user_id = u.id
            LEFT JOIN user_credentials uc ON u.id = uc.user_id
            ORDER BY f.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    // Logging Methods
    public function logActivity($userId, $action, $details = '') {
        $stmt = $this->pdo->prepare("
            INSERT INTO activity_logs (user_id, action, details)
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([$userId, $action, $details]);
    }

    public function logSecurityEvent($userId, $action, $status, $ipAddress = null, $userAgent = null) {
        $stmt = $this->pdo->prepare("
            INSERT INTO security_logs (user_id, action, status, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$userId, $action, $status, $ipAddress, $userAgent]);
    }

    // Additional Methods for Job Portal
    public function getLocationStats() {
        $stmt = $this->pdo->query("
            SELECT location, COUNT(*) as job_count
            FROM job_offers
            GROUP BY location
            ORDER BY job_count DESC
        ");
        return $stmt->fetchAll();
    }

    public function getEmploymentStatusStats() {
        $stmt = $this->pdo->query("
            SELECT employment_status, COUNT(*) as user_count
            FROM user_credentials
            GROUP BY employment_status
        ");
        return $stmt->fetchAll();
    }

    public function getApplicationStatusStats() {
        $stmt = $this->pdo->query("
            SELECT status, COUNT(*) as application_count
            FROM job_applications
            GROUP BY status
        ");
        return $stmt->fetchAll();
    }
}
?> 
<?php
    /** @noinspection ALL */
    require __DIR__ . '/vendor/autoload.php';

// Create necessary directories
    $directories = [
        'uploads/student_photos',
        'views',
        'assets/images',
        'vendor'
    ];
    
    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }

// Database configuration
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'school_manager');
    define('APP_NAME', 'Streams of Wisdom Secondary School');
    define('APP_URL', 'http://localhost/schoolmanagementsystem');
    define('ADMIN_EMAIL', 'admin@streamsofwisdom.com');
    
    class Database {
        private $pdo;
        
        public function __construct() {
            $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8';
            try {
                $this->pdo = new PDO($dsn, DB_USER, DB_PASS);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
        
        public function query($sql, $params = []) {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        }
        
        public function getLastInsertId() {
            return $this->pdo->lastInsertId();
        }
        
        public function getLastError() {
            return $this->pdo->errorInfo()[2];
        }
    }
    
    session_start();
    
    
    /*
     * User Authentication Class
     * Handles login, logout, and session management
     */
class Auth {
    private $db;
    private $userTable = 'users';
    public function __construct(Database $db) {
        $this->db = $db;
    }
    public function login($username, $password) {
        if (empty($username) || empty($password)) {
            return false;
        }
        
        $sql = "SELECT * FROM {$this->userTable} WHERE username = ?";
        $stmt = $this->db->query($sql, [$username]);
        
        if ($stmt && $user = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                session_regenerate_id(true); // Security measure
                return true;
            }
        }
        return false;
    }
    public function logout() {
        // Unset all session variables
        $_SESSION = array();
        // Destroy the session
        session_destroy();
        return true;
    }
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    public function getLoggedInUser() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'role' => $_SESSION['role']
            ];
        }
        return null;
    }
    public function hasRole($role) {
        if ($this->isLoggedIn()) {
            return $_SESSION['role'] === $role;
        }
        return false;
    }
}
/*
 * Student Management Class
 * Handles student registration, profiles, and academic records
 */
class StudentManager {
    private $db;
    private $studentTable = 'students';
    private $classTable = 'classes';
    private $attendanceTable = 'attendance';
    public function __construct(Database $db) {
        $this->db = $db;
    }
    public function registerStudent($data) {
        // Handle photo upload
        $photoPath = '';
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $targetDir = "uploads/student_photos/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9\.\-_]/', '', basename($_FILES['photo']['name']));
            $targetFile = $targetDir . $fileName;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
                $photoPath = $targetFile;
            }
        }
        // Add validation:
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['photo']['type'], $allowedTypes)) {
            // Handle invalid file type
        }
        $sql = "INSERT INTO {$this->studentTable}
        (admission_no, first_name, last_name, gender, dob, address,
        guardian_name, guardian_phone, guardian_email, class_name, photo, registration_date)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $params = [
            $data['admission_no'],
            $data['first_name'],
            $data['last_name'],
            $data['gender'],
            $data['dob'],
            $data['address'],
            $data['guardian_name'],
            $data['guardian_phone'],
            $data['guardian_email'],
            $data['class_name'],
            $photoPath,
        ];
        if ($this->db->query($sql, $params)) {
            return $this->db->getLastInsertId();
        }
        return false;
    }
    public function updateStudent($admission_no, $data) {
        // Handle photo upload
        $photoSql = "";
        $photoParam = [];
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
                // Handle specific error codes
                throw new Exception("File upload error: " . $_FILES['photo']['error']);
            }
            $targetDir = "uploads/student_photos/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            $fileName = time() . '_' . basename($_FILES['photo']['name']);
            $targetFile = $targetDir . $fileName;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
                $photoSql = ", photo = ?";
                $photoParam = [$targetFile];
                // Delete old photo if exists
                $student = $this->getStudentByAdmission_no($admission_no);
                if ($student && !empty($student['photo']) && file_exists($student['photo'])) {
                    unlink($student['photo']);
                }
            }
        }
                    $sql = "UPDATE {$this->studentTable} SET
        first_name = ?, last_name = ?, gender = ?, dob = ?, address = ?,
         guardian_name = ?, guardian_phone = ?, guardian_email = ?, class_name = ?
             {$photoSql}";
        $params = [
            $data['first_name'],
            $data['last_name'],
            $data['gender'],
            $data['dob'],
            $data['address'],
            $data['guardian_name'],
            $data['guardian_phone'],
            $data['guardian_email'],
            $data['class_name'],
        ];
        if (!empty($photoParam)) {
            $params = array_merge($params, $photoParam);
        }
        $params[] = $admission_no;
        return $this->db->query($sql, $params) ? true : false;
    }
    public function getStudentByAdmission_no($admission_no) {
        $sql = "SELECT s.*, c.name as class_name
        FROM {$this->studentTable} s
        LEFT JOIN {$this->classTable} c ON s.class_name = c.admission_no
        WHERE s.admission_no = ?";
        $stmt = $this->db->query($sql, [$admission_no]);
        return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
    }
    public function getAllStudents($limit = null, $offset = 0) {
// Initialize base query
        $sql = "SELECT s.*, c.name as class_name
        FROM {$this->studentTable} s
LEFT JOIN {$this->classTable} c ON s.class_name = c.admission_no
ORDER BY s.first_name, s.last_name";
        $params = []; // Initialize params array
// Add LIMIT clause if provided
        if ($limit) {
            $sql .= " LIMIT :offset, :limit";
            $params['offset'] = (int)$offset;
            $params['limit'] = (int)$limit;
        }
        // Prepare and execute with parameters if they exist
        $stmt = $this->db->prepare($sql);
        if ($limit) {
            foreach ($params as $key => $value) {
                $stmt->bindValue(':'.$key, $value, PDO::PARAM_INT);
            }
        }
        $stmt->execute();
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }
            public function getStudentsByClass($class_name) {
        $sql = "SELECT * FROM {$this->studentTable}  ORDER BY first_name,
        last_name";
        $stmt = $this->db->query($sql, [$class_name]);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }
    public function recordAttendance($student_admission_no, $date, $status) {
        // First check if attendance record already exists
        $sql = "SELECT admission_no FROM {$this->attendanceTable} WHERE student_sdmission_no = ? AND date = ?";
        $stmt = $this->db->query($sql, [$student_admission_no, $date]);
        if ($stmt && $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Update existing record
            $sql = "UPDATE {$this->attendanceTable} SET status = ? WHERE student_admission_no = ? AND date
            = ?";
            return $this->db->query($sql, [$status, $student_admission_no, $date]) ? true : false;
        } else {
            // Insert new record
            $sql = "INSERT INTO {$this->attendanceTable} (student_admission_no, date, status) VALUES (?, ?,
            ?)";
            return $this->db->query($sql, [$student_admission_no, $date, $status]) ? true : false;
        }
    }
    public function getAttendance($student_admission_no, $startDate, $endDate) {
        $sql = "SELECT * FROM {$this->attendanceTable} WHERE student_admission_no = ? AND date
        BETWEEN ? AND ? ORDER BY date";
        $stmt = $this->db->query($sql, [$student_admission_no, $startDate, $endDate]);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }
    public function deleteStudent($admission_no) {
        $sql = "DELETE FROM {$this->studentTable} WHERE admission_no = ?";
        return $this->db->query($sql, [$admission_no]) ? true : false;
    }
}
/*Finance Management Class
 *Handles fees, payments, expenses and financial reports
 */
class FinanceManager
{
    private $db;
    private $feesTable = 'fees';
    private $paymentsTable = 'payments';
    private $expensesTable = 'expenses';
    private string $lastError;
    private $feeStructureTable;
    private $studentTable;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function createFeeStructure($class_name, $term, $year, $amount, $description)
    {
        $sql = "INSERT INTO {$this->feesTable} (class_name, term, year, amount, description,
        created_at)
VALUES (?, ?, ?, ?, ?, NOW())";
        $params = [$class_name, $term, $year, $amount, $description];
        return $this->db->query($sql, $params) ? $this->db->getLastInsertId() : false;
    }

    public function getFeeStructure($class_name, $term, $year)
    {
        $sql = "SELECT * FROM {$this->feesTable} WHERE class_name = ? AND term = ? AND year = ?";
        $stmt = $this->db->query($sql, [$class_name, $term, $year]);
        return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
    }

    public function recordPayment($student_admission_no, $amount, $paymentDate, $paymentMethod,
                                  $reference, $description, $receivedBy)
    {
        try {
            // Start transaction FIRST
            $this->db->query("START TRANSACTION");
            $sql = "INSERT INTO {$this->paymentsTable}
        (student_admission_no, amount, payment_date, payment_method, reference, description,
        received_by, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            $params = [$student_admission_no, $amount, $paymentDate, $paymentMethod, $reference,
                $description, $receivedBy];
            $result = $this->db->query($sql, $params);
            if (!$result) {
                throw new Exception("Payment recording failed");
            }
            // Only commit if everything succeeded
            $this->db->query("COMMIT");
            return $this->db->getLastInsertId();
        } catch (Exception $e) {
            // Rollback if anything fails
            $this->db->query("ROLLBACK");
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function getStudentPayments($student_admission_no)
    {
        $sql = "SELECT * FROM {$this->paymentsTable} WHERE student_admission_no = ? ORDER BY
        payment_date DESC";
        $stmt = $this->db->query($sql, [$student_admission_no]);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function getPaymentByAdmission_no($paymentadmission_no)
    {
        $sql = "SELECT * FROM {$this->paymentsTable} WHERE admission_no = ?";
        $stmt = $this->db->query($sql, [$paymentadmission_no]);
        return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
    }

    public function recordExpense($category, $amount, $expenseDate, $paymentMethod,
                                  $reference, $description, $authorizedBy)
    {
        $sql = "INSERT INTO {$this->expensesTable}
        (category, amount, expense_date, payment_method, reference, description,
        authorized_by, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $params = [$category, $amount, $expenseDate, $paymentMethod, $reference, $description,
            $authorizedBy];
        return $this->db->query($sql, $params) ? $this->db->getLastInsertId() : false;
    }

    public function getExpenses($startDate, $endDate)
    {
        $sql = "SELECT * FROM {$this->expensesTable} WHERE expense_date BETWEEN ? AND ?
        ORDER BY expense_date DESC";
        $stmt = $this->db->query($sql, [$startDate, $endDate]);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function getTotalPaymentsByDate($startDate, $endDate)
    {
        $sql = "SELECT SUM(amount) as total FROM {$this->paymentsTable} WHERE
        payment_date BETWEEN ? AND ?";
        $stmt = $this->db->query($sql, [$startDate, $endDate]);
        $result = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : ['total' => 0];
        return $result['total'] ?: 0; // Return 0 if total is NULL
    }

    public function getTotalExpensesByDate($startDate, $endDate)
    {
        $sql = "SELECT SUM(amount) as total FROM {$this->expensesTable} WHERE expense_date
        BETWEEN ? AND ?";
        $stmt = $this->db->query($sql, [$startDate, $endDate]);
        $result = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : ['total' => 0];
        return $result['total'] ?: 0; // Return 0 if total is NULL
    }

    public function generateFinancialReport($startDate, $endDate)
    {
        $totalIncome = $this->getTotalPaymentsByDate($startDate, $endDate);
        $totalExpenses = $this->getTotalExpensesByDate($startDate, $endDate);
        $balance = $totalIncome - $totalExpenses;
        $payments = $this->getPaymentsByDate($startDate, $endDate);
        $expenses = $this->getExpenses($startDate, $endDate);
        return [
            'period' => ['start' => $startDate, 'end' => $endDate],
            'summary' => [
                'total_income' => $totalIncome,
                'total_expenses' => $totalExpenses,
                'balance' => $balance
            ],
            'payments' => $payments,
            'expenses' => $expenses
        ];
    }

    /**
     * Retrieves payments that occurred on the specified date.
     * @param string|DateTime $date The date to filter payments by
     * @return array List of payments made on the given date
     */
    public function getPaymentsByDate($startDate, $endDate)
    {
        $sql = "SELECT p.*, s.first_name, s.last_name, s.admission_no
        FROM {$this->paymentsTable} p
        JOIN students s ON p.student_admission_no = s.admission_no
        WHERE p.payment_date BETWEEN ? AND ?
        ORDER BY p.payment_date DESC";
        $stmt = $this->db->query($sql, [$startDate, $endDate]);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function getStudentBalance($student_admission_no, $termId)
    {
        // Validate input parameters
        if (!is_numeric($student_amission_no) || $student_admission_no <= 0 ||
            !is_numeric($termId) || $termId <= 0) {
            error_log("Invalid parameters studentId: $student_admission_no, termId: $termId");
            return false;
        }
        try {
            // 1. Get student's class information
            $sql = "SELECT class_name FROM {$this->studentTable} WHERE admission_no = :student_admission_no";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':student_admission_no', $student_admission_no, PDO::PARAM_VARCHAR);
            $stmt->execute();
            if (!$stmt) {
                error_log("Failed to prepare student query");
                return false;
            }
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$student || !isset($student['class_name'])) {
                error_log("Student not found or missing class NAME");
                return false;
            }
            // 2. Get fee structure for the student's class and term
            $feeSql = "SELECT amount, due_date FROM {$this->feeStructureTable}
WHERE class_name = :classname AND term_id = :termId";
            $feeStmt = $this->db->prepare($feeSql);
            $feeStmt->bindValue(':className', $student['class_name'], PDO::PARAM_VARCHAR);
            $feeStmt->bindValue(':termId', $termId, PDO::PARAM_INT);
            $feeStmt->execute();
            $feeStructure = $feeStmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($feeStructure)) {
                error_log("No fee structure found for class {$student['class_name']} and term $termId");
                return 0; // No fees defined means balance is 0
            }
            // 3. Calculate total payments made
            $paymentSql = "SELECT SUM(amount) as total_paid FROM {$this->paymentsTable}
WHERE student_admission_no = :studentadmissionno AND term_id = :termId";
            $paymentStmt = $this->db->prepare($paymentSql);
            $paymentStmt->bindValue(':studentAdmissionno', $studentAdmissionno, PDO::PARAM_VARCHAR);
            $paymentStmt->bindValue(':termId', $termId, PDO::PARAM_INT);
            $paymentStmt->execute();
            $paymentData = $paymentStmt->fetch(PDO::FETCH_ASSOC);
            $totalPaid = $paymentData['total_paid'] ?? 0;
            // 4. Calculate total fees expected
            $totalFees = array_sum(array_column($feeStructure, 'amount'));
// 5. Return balance (fees - payments)
            return max(0, $totalFees - $totalPaid); // Ensure balance doesn't go negative
        } catch (PDOException $e) {
            error_log("Database error in getStudentBalance(): " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("General error in getStudentBalance(): " . $e->getMessage());
            return false;
        }
    }
}
/*
Academic Management Class
Handles subjects, exams, grades, and report cards
*/
class AcademicManager {
    private $db;
    private $subjectsTable = 'subjects';
    private $examsTable = 'exams';
    private $examResultsTable = 'exam_results';
    private $gradesTable = 'grades';
    private $termsTable = 'terms';
    public function __construct(Database $db) {
        $this->db = $db;
    }
    public function addSubject($name, $code, $description) {
        $sql = "INSERT INTO {$this->subjectsTable} (name, code, description) VALUES (?, ?, ?)";
        return $this->db->query($sql, [$name, $code, $description]) ? $this->db->getLastInsertId() :
            false;
    }
    public function getSubjects() {
        $sql = "SELECT * FROM {$this->subjectsTable} ORDER BY name";
        $stmt = $this->db->query($sql);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }
    public function createExam($name, $term, $year, $startDate, $endDate) {
        $sql = "INSERT INTO {$this->examsTable}
        (name, term, year, start_date, end_date, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())";
        $params = [$name, $term, $year, $startDate, $endDate];
        return $this->db->query($sql, $params) ? $this->db->getLastInsertId() : false;
    }
    public function getExams($term = null, $year = null) {
        $params = [];
        $sql = "SELECT * FROM {$this->examsTable}";
        if ($term && $year) {
            $sql .= " WHERE term = ? AND year = ?";
            $params = [$term, $year];
        } elseif ($term) {
            $sql .= " WHERE term = ?";
            $params = [$term];
        } elseif ($year) {
            $sql .= " WHERE year = ?";
            $params = [$year];
        }
        $sql .= " ORDER BY start_date DESC";
        $stmt = $this->db->query($sql, $params);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }
    public function recordExamResult($student_admission_no, $exam_name, $subject_name, $marks, $comments = '')
    {
        // First check if result already exists
        $sql = "SELECT id FROM {$this->examResultsTable}
        WHERE student_admission_no = ? AND exam_name = ? AND subject_name = ?";
        $stmt = $this->db->query($sql, [$student_admission_no, $exam_name, $subject_name]);
        if ($stmt && $stmt->fetch(PDO::FETCH_ASSOC)) {
// Update existing record
            $sql = "UPDATE {$this->examResultsTable}
            SET marks = ?, comments = ?, updated_at = NOW()
            WHERE student_admission_no = ? AND exam_name = ? AND subject_name = ?";
            return $this->db->query($sql, [$marks, $comments, $student_admision_no, $exam_name, $subject_name]) ?
                true : false;
        } else {
            // Insert new record
            $sql = "INSERT INTO {$this->examResultsTable}
            (student_admission_no, exam_name, subject_name, marks, comments, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())";
            return $this->db->query($sql, [$student_admission_no, $exam_name, $subject_name, $marks, $comments]) ?
                $this->db->getLastInsertId() : false;
        }
    }
    public function getExamResults($exam_name, $student_admission_no = null, $subject_name = null) {
        $params = [$examId];
        $sql = "SELECT r.*, s.name as subject_name, s.code as subject_code
FROM {$this->examResultsTable} r
JOIN {$this->subjectsTable} s ON r.subject_name = s.name
WHERE r.exam_name = ?";
        if ($student_admission_no) {
            $sql .= " AND r.student_admission_no = ?";
            $params[] = $student_admission_no;
        }
        if ($subject_name) {
            $sql .= " AND r.subject_name = ?";
            $params[] = $subject_name;
        }
        $sql .= " ORDER BY s.name";
        $stmt = $this->db->query($sql, $params);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }
    public function getStudentExamResults($student_admission_no, $term, $year) {
        $sql = "SELECT r.*, e.name as exam_name, s.name as subject_name, s.code as subject_code
        FROM {$this->examResultsTable} r
        JOIN {$this->examsTable} e ON r.exam_name = e.name
        JOIN {$this->subjectsTable} s ON r.subject_name = s.name
        WHERE r.student_admission_no = ? AND e.term = ? AND e.year = ?
        ORDER BY s.name";
        $params = [$student_admission_no, $term, $year];
        $stmt = $this->db->query($sql, $params);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }
    public function getGradeByMarks($marks) {
        $sql = "SELECT * FROM {$this->gradesTable}
        WHERE min_marks <= ? AND max_marks >= ?
        ORDER BY min_marks DESC LIMIT 1";
        $stmt = $this->db->query($sql, [$marks, $marks]);
        return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
    }
    public function getTerms() {
        $sql = "SELECT * FROM {$this->termsTable} ORDER BY start_date";
        $stmt = $this->db->query($sql);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }
    public function generateReportCard($student_admission_no, $term, $year) {
        // Get student details
        $studentManager = new StudentManager($this->db);
        $student = $studentManager->getStudentByAdmission_no($student_admission_no);
        if (!$student) {
            return false;
        }
        // Get current term details
        $currentTerm = $this->getCurrentTerm($term, $year);
        if (!$currentTerm) {
            return false;
        }
            // Get exam results
        $results = $this->getStudentExamResults($student_admission_no, $term, $year);
        // Get subjects categorized by learning area (for Ugandan curriculum)
        $learningAreas = $this->getLearningAreas();
        $categorizedResults = [];
        foreach ($learningAreas as $area_name => $area) {
            $categorizedResults[$area_name] = [
                'name' => $area['name'],
                'subjects' => []
            ];
        }
                // Calculate averages and assign grades
        $totalMarks = 0;
        $subjectCount = 0;
        foreach ($results as &$result) {
            $grade = $this->getGradeByMarks($result['marks']);
            $result['grade'] = $grade ? $grade['grade'] : '-';
            $result['remarks'] = $grade ? $grade['remarks'] : '-';
            // Assign to learning area
            $subject_name = $result['subject_name'];
            $learningArea_name = $this->getSubjectLearningArea($subject_name);
            if ($learningArea_name && isset($categorizedResults[$learningArea_name])) {
                $categorizedResults[$learningArea_name]['subjects'][] = $result;
            } else {
                // Default to "Other" if no specific area found
                $categorizedResults['other']['subjects'][] = $result;
            }
            $totalMarks += $result['marks'];
            $subjectCount++;
        }
        $averageMarks = $subjectCount > 0 ? round($totalMarks / $subjectCount, 2) : 0;
        $overallGrade = $this->getGradeByMarks($averageMarks);
        // Get attendance
        $attendance = [];
        if ($currentTerm) {
            $attendance = $studentManager->getAttendance(
                $student_admission_no,
                $currentTerm['start_date'],
                $currentTerm['end_date']
            );
        }
        // Get finance info
        $financeManager = new FinanceManager($this->db);
        $balance = $financeManager->getStudentBalance($student_admission_no, $currentTerm ?
            $currentTerm['id'] : null);
        // Get competence assessments
        $competences = $this->getStudentCompetences();
        // Get term dates and school info
        $schoolInfo = $this->getSchoolInfo();
        return [
            'student' => $student,
            'term' => $term,
            'year' => $year,
            'term_dates' => [
                'start' => $currentTerm['start_date'],
                'end' => $currentTerm['end_date'],
                'next_term' => $this->getNextTermStartDate($term, $year)
            ],
            'school_info' => $schoolInfo,
            'results' => $results,
            'categorized_results' => $categorizedResults,
            'summary' => [
                'total_marks' => $totalMarks,
                'subject_count' => $subjectCount,
                'average_marks' => $averageMarks,
                'overall_grade' => $overallGrade ? $overallGrade['grade'] : '-',
                'remarks' => $overallGrade ? $overallGrade['remarks'] : '-'
            ],
            'attendance' => [
                'total_days' => count($attendance),
                'present' => count(array_filter($attendance, function($a) { return $a['status'] ===
                    'present'; })),
                'absent' => count(array_filter($attendance, function($a) { return $a['status'] ===
                    'absent'; }))
            ],
            'competences' => $competences,
            'finance' => $balance,
            'generated_date' => date('Y-m-d H:i:s')
        ];
    }
    // Helper methods for the Ugandan curriculum
    private function getLearningAreas() {
        return [
            1 => ['name' => 'Languages'],
            2 => ['name' => 'Sciences'],
            3 => ['name' => 'Mathematics'],
            4 => ['name' => 'Social Studies'],
            5 => ['name' => 'Religious Education'],
            6 => ['name' => 'Technology and Enterprise'],
            7 => ['name' => 'Creative Arts'],
            8 => ['name' => 'Life Education'],
            'other' => ['name' => 'Other Subjects']
        ];
    }
    private function getSubjectLearningArea($subject_name) {
        $sql = "SELECT learning_area_name FROM {$this->subjectsTable} WHERE name = ?";
        $stmt = $this->db->query($sql, [$subject_name]);
        $result = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
        return $result ? $result['learning_area_name'] : null;
    }
    private function getNextTermStartDate($term, $year) {
        // Logic to calculate next term's start date
        if ($term == 3) {
            $nextYear = $year + 1;
            $sql = "SELECT start_date FROM {$this->termsTable} WHERE term = 1 AND year = ?
LIMIT 1";
            $stmt = $this->db->query($sql, [$nextYear]);
        } else {
            $nextTerm = $term + 1;
            $sql = "SELECT start_date FROM {$this->termsTable} WHERE term = ? AND year = ? LIMIT
    1";
            $stmt = $this->db->query($sql, [$nextTerm, $year]);
        }
        $result = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
        return $result ? $result['start_date'] : null;
    }
    private function getSchoolInfo() {
        return [
            'name' => APP_NAME,
            'address' => '123 School Road, Kampala, Uganda',
            'phone' => '+256 123 456789',
            'email' => ADMIN_EMAIL,
            'website' => 'www.streamsofwisdom.com',
            'motto' => 'Yes We Can',
            'logo' => 'assets/images/school_logo.png'
        ];
    }
    public function getStudentCompetences() {
        // In a real implementation, this would fetch from a competences table
// For now, we'll return sample data
        return [
            'generic_competences' => [
                'critical_thinking' => ['rating' => 4, 'remarks' => 'Very good'],
                'creativity_innovation' => ['rating' => 3, 'remarks' => 'Good'],
                'communication' => ['rating' => 4, 'remarks' => 'Very good'],
                'cooperation' => ['rating' => 5, 'remarks' => 'Excellent'],
                'self_directed_learning' => ['rating' => 3, 'remarks' => 'Good'],
                'socio_cultural' => ['rating' => 4, 'remarks' => 'Very good']
            ],
            'values' => [
                'respect' => ['rating' => 4, 'remarks' => 'Very good'],
                'integrity' => ['rating' => 5, 'remarks' => 'Excellent'],
                'discipline' => ['rating' => 3, 'remarks' => 'Good'],
                'responsibility' => ['rating' => 4, 'remarks' => 'Very good']
            ]
        ];
    }
    // Let's add the function to view and generate PDF report cards
    public function viewReportCard($student_admission_no, $term, $year) {
        $this->requireLogin();
        $academicManager = new AcademicManager($this->db);
        $reportCard = $academicManager->generateReportCard($student_admission_no, $term, $year);
        if (!$reportCard) {
            $_SESSION['error'] = 'Failed to generate report card: ' . $this->db->getLastError();
            header('Location: /academics/report-cards');
            exit;
        }
        // Check if PDF generation is requested
        if (isset($_GET['format']) && $_GET['format'] === 'pdf') {
            $this->generateReportCardPDF($reportCard);
            exit;
        }
        include 'views/academics/view_report_card.php';
    }
    // Function to generate PDF report card
    private function generateReportCardPDF($reportCard) {
        // Use mPDF to generate the report card
        require_once 'vendor/autoload.php';
        // Create new mPDF instance
        $mpdf = new \Mpdf\Mpdf([
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 15,
            'margin_bottom' => 15,
        ]);
        // Set document information
        $mpdf->SetTitle(APP_NAME . ' - Report Card');
        $mpdf->SetAuthor(APP_NAME);
        // Build HTML for the report card
        $html = $this->buildReportCardHTML($reportCard);
// Write HTML to the PDF
        $mpdf->WriteHTML($html);
        // Output the PDF
        $filename = 'report_card_' . $reportCard['student']['admission_no'] . '_' .
            $reportCard['term'] . '_' . $reportCard['year'] . '.pdf';
        $mpdf->Output($filename, 'D');
    }
    // Function to build the HTML for the report card
    private function buildReportCardHTML($reportCard)
    {
        $student = $reportCard['student'];
        $school = $reportCard['school_info'];
    }
        private function getCurrentTerm($term, $year) {
        $sql = "SELECT * FROM {$this->termsTable} WHERE term = ? AND year = ?";
        $stmt = $this->db->query($sql, [$term, $year]);
        return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
    }
}
    /*
     * Communication Class
     * Handles notices, messages, and notifications
     */
class CommunicationManager {
    private $db;
    private $noticesTable = 'notices';
    private $messagesTable = 'messages';
    private $recipientsTable = 'message_recipients';
    public function __construct(Database $db) {
        $this->db = $db;
    }
    public function createNotice($title, $content, $publishDate, $expiryDate, $audience) {
        $sql = "INSERT INTO {$this->noticesTable}
(title, content, publish_date, expiry_date, audience, created_at)
VALUES (?, ?, ?, ?, ?, NOW())";
        $params = [$title, $content, $publishDate, $expiryDate, $audience];
        return $this->db->query($sql, $params) ? $this->db->getLastInsertId() : false;
    }
    public function getNotices($audience = null, $active = true) {
        $params = [];
        $sql = "SELECT * FROM {$this->noticesTable}";
        $conditions = [];
        if ($audience) {
            $conditions[] = "audience = ?";
            $params[] = $audience;
        }
        if ($active) {
            $now = date('Y-m-d');
            $conditions[] = "publish_date <= ?";
            $conditions[] = "expiry_date >= ?";
            $params[] = $now;
            $params[] = $now;
        }
        if (count($conditions) > 0) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        $sql .= " ORDER BY publish_date DESC";
        $stmt = $this->db->query($sql, $params);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }
    public function sendMessage($sender_name, $subject, $content, $recipients) {
        // Insert message
        $sql = "INSERT INTO {$this->messagesTable}
        (sender_name, subject, content, sent_at)
        VALUES (?, ?, ?, NOW())";
        $params = [$sender_name, $subject, $content];
        if ($this->db->query($sql, $params)) {
            $message_name = $this->db->getLastInsertId();
            // Insert recipients
            foreach ($recipients as $recipient_name) {
                $sql = "INSERT INTO {$this->recipientsTable}
                (message_name, recipient_name, read_status, created_at)
                VALUES (?, ?, 'unread', NOW())";
                $this->db->query($sql, [$message_name, $recipient_name]);
            }
            return $message_name;
        }
        return false;
    }
    public function getMessages($user_name, $folder = 'inbox') {
        if ($folder === 'inbox') {
            $sql = "SELECT m.*, u.username as sender_name, r.read_status
            FROM {$this->messagesTable} m
            JOIN {$this->recipientsTable} r ON m.name = r.message_name
            JOIN users u ON m.sender_name = u.name
            WHERE r.recipient_name = ?
            ORDER BY m.sent_at DESC";
            $params = [$user_name];
        } else { // sent
            $sql = "SELECT m.*, COUNT(r.name) as recipient_count
            FROM {$this->messagesTable} m
            JOIN {$this->recipientsTable} r ON m.name = r.message_name
            WHERE m.sender_name = ?
            GROUP BY m.name
            ORDER BY m.sent_at DESC";
            $params = [$user_name];
        }
        $stmt = $this->db->query($sql, $params);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }
    public function getMessage($message_name, $user_name) {
        $sql = "SELECT m.*, u.username as sender_name, r.read_status
        FROM {$this->messagesTable} m
        LEFT JOIN {$this->recipientsTable} r ON m.name = r.message_name AND r.recipient_name = ?
        JOIN users u ON m.sender_name = u.name
        WHERE m.name = ?";
        $params = [$user_name, $message_name];
        $stmt = $this->db->query($sql, $params);
        if ($stmt && $message = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Mark as read if recipient
            if ($message['read_status'] === 'unread') {
                $this->markAsRead($message_name, $user_name);
            }
// Get recipients
            $sql = "SELECT r.*, u.username as recipient_name
            FROM {$this->recipientsTable} r
            JOIN users u ON r.recipient_name = u.name
            WHERE r.message_name = ?";
            $stmt = $this->db->query($sql, [$message_name]);
            $message['recipients'] = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            return $message;
        }
        return false;
    }
    public function markAsRead($message_name, $user_name) {
        $sql = "UPDATE {$this->recipientsTable}
        SET read_status = 'read', read_at = NOW()
        WHERE message_name = ? AND recipient_name = ?";
        return $this->db->query($sql, [$message_name, $user_name]) ? true : false;
    }
    public function deleteMessage($message_name, $user_name) {
// Check if user is sender or recipient
        $sql = "SELECT * FROM {$this->messagesTable} WHERE name = ? AND sender_name = ?";
        $stmt = $this->db->query($sql, [$message_name, $user_name]);
        if ($stmt && $stmt->fetch(PDO::FETCH_ASSOC)) {
            // User is sender, delete message and all recipients
            $sql = "DELETE FROM {$this->recipientsTable} WHERE message_name = ?";
            $this->db->query($sql, [$message_name]);
            $sql = "DELETE FROM {$this->messagesTable} WHERE name = ?";
            return $this->db->query($sql, [$message_name]) ? true : false;
        } else {
// Check if user is recipient
            $sql = "SELECT * FROM {$this->recipientsTable}
            WHERE message_name = ? AND recipient_name = ?";
            $stmt = $this->db->query($sql, [$message_name, $user_name]);
            if ($stmt && $stmt->fetch(PDO::FETCH_ASSOC)) {
                // User is recipient, just delete recipient record
                $sql = "DELETE FROM {$this->recipientsTable}
                WHERE message_name = ? AND recipient_name = ?";
                return $this->db->query($sql, [$message_name, $user_name]) ? true : false;
            }
        }
        return false;
    }
}
/*
 * Utility Class
*Provides helper functions
 */
class Utils {
    // Generate random string
    public static function generateRandomString($length = 10) {
        $characters =
            '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
    // Format currency
    public static function formatCurrency($amount) {
        return number_format($amount, 2);
    }
    // Format date
    public static function formatDate($date, $format = 'd-M-Y') {
        return date($format, strtotime($date));
    }
    // Generate PDF from HTML
    public static function generatePDF() {
        // This is a placeholder for PDF generation
        // In a real application, you would use a library like FPDF, TCPDF, or mPDF
// Example using mPDF
/*
 * require_once 'vendor/autoload.php';
 * $mpdf = new \Mpdf\Mpdf();
$mpdf->WriteHTML($html);
$mpdf->Output($filename, 'D');
 */
        return true;
    }
    // Send email
    public static function sendEmail($to, $subject, $message, $from = null) {
        // This is a placeholder for email sending
// In a real application, you would use a library like PHPMailer
        $from = $from ?: ADMIN_EMAIL;
        $headers = "From: $from\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        return mail($to, $subject, $message, $headers);
    }
}
/*
 * Router Class
 * Handles URL routing
 */
class Router {
    private $routes = [];
    public function add($method, $path, $callback) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'callback' => $callback
        ];
    }
    public function get($path, $callback) {
        $this->add('GET', $path, $callback);
    }
    public function post($path, $callback) {
        $this->add('POST', $path, $callback);
    }
    public function run() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];
        foreach ($this->routes as $route) {
            if ($route['method'] != $method) {
                continue;
            }
            $pattern = $this->convertPatternToRegex($route['path']);
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                return call_user_func_array($route['callback'], $matches);
            }
        }
        // No matching route found
        header("HTTP/1.0 404 Not Found");
        echo "404 Not Found";
    }
    private function convertPatternToRegex($pattern) {
        // Convert parameters like :id to regex capture groups
        $pattern = preg_replace('/:([a-zA-Z0-9_]+)/', '(?<$1>[^/]+)', $pattern);
        return '#^' . $pattern . '$#';
    }
}
/*
 * App Class
 * Main application class
 */
class App
{
    private $db;
    private $auth;
    private $router;

    public function __construct()
    {
        $this->db = new Database();
        $this->auth = new Auth($this->db);
        $this->router = new Router();
        $this->setupRoutes();
    }

    private function setupRoutes()
    {
        // Auth routes
        $this->router->get('/login', [$this, 'showLoginPage']);
        $this->router->post('/login', [$this, 'handleLogin']);
        $this->router->get('/logout', [$this, 'handleLogout']);
        // Dashboard
        $this->router->get('/', [$this, 'showDashboard']);
        $this->router->get('/dashboard', [$this, 'showDashboard']);
        // Student routes
        $this->router->get('/students', [$this, 'showStudentsList']);
        $this->router->get('/students/new', [$this, 'showStudentForm']);
        $this->router->post('/students/new', [$this, 'handleStudentCreate']);
        $this->router->get('/students/:admission_no', [$this, 'showStudentDetails']);
        $this->router->get('/students/:admission_no/edit', [$this, 'showStudentEditForm']);
        $this->router->post('/students/:admission_no/edit', [$this, 'handleStudentUpdate']);
        $this->router->get('/students/:admission_no/delete', [$this, 'handleStudentDelete']);
        // Finance routes
        $this->router->get('/finance', [$this, 'showFinanceDashboard']);
        $this->router->get('/finance/payments', [$this, 'showPaymentsList']);
        $this->router->get('/finance/payments/new', [$this, 'showPaymentForm']);
        $this->router->post('/finance/payments/new', [$this, 'handlePaymentCreate']);
        $this->router->get('/finance/expenses', [$this, 'showExpensesList']);
        $this->router->get('/finance/expenses/new', [$this, 'showExpenseForm']);
        $this->router->post('/finance/expenses/new', [$this, 'handleExpenseCreate']);
        $this->router->get('/finance/reports', [$this, 'showFinanceReports']);
        $this->router->post('/finance/reports', [$this, 'generateFinanceReport']);
        // Academic routes
        $this->router->get('/academics', [$this, 'showAcademicDashboard']);
        $this->router->get('/academics/subjects', [$this, 'showSubjectsList']);
        $this->router->get('/academics/exams', [$this, 'showExamsList']);
        $this->router->get('/academics/exams/new', [$this, 'showExamForm']);
        $this->router->post('/academics/exams/new', [$this, 'handleExamCreate']);
        $this->router->get('/academics/results', [$this, 'showResultsPage']);
        $this->router->post('/academics/results', [$this, 'handleResultsEntry']);
        $this->router->get('/academics/report-cards', [$this, 'showReportCardGeneration']);
        $this->router->post('/academics/report-cards', [$this, 'generateReportCards']);
        $this->router->get('/academics/report-cards/:student/:term/:year', [$this,
            'viewReportCard']);
// Communication routes
        $this->router->get('/communication', [$this, 'showCommunicationDashboard']);
        $this->router->get('/communication/notices', [$this, 'showNoticesList']);
        $this->router->get('/communication/notices/new', [$this, 'showNoticeForm']);
        $this->router->post('/communication/notices/new', [$this, 'handleNoticeCreate']);
        $this->router->get('/communication/messages', [$this, 'showMessagesList']);
        $this->router->get('/communication/messages/new', [$this, 'showMessageForm']);
        $this->router->post('/communication/messages/new', [$this, 'handleMessageCreate']);
        $this->router->get('/communication/messages/:name', [$this, 'viewMessage']);
    }

    public function run()
    {
        $this->router->run();
    }

    // Auth Controllers
    public function showLoginPage()
    {
        if ($this->auth->isLoggedIn()) {
            header('Location: /dashboard');
            exit;
        }
        include 'views/login.php';
    }

    public function handleLogin()
    {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        if ($this->auth->login($username, $password)) {
            header('Location: /dashboard');
        } else {
            $_SESSION['error'] = 'Invalid username or password';
            header('Location: /login');
        }
        exit;
    }

    public function handleLogout()
    {
        $this->auth->logout();
        header('Location: /login');
        exit;
    }

    // Dashboard Controller
    public function showDashboard()
    {
        $this->requireLogin();
        $studentManager = new StudentManager($this->db);
        $financeManager = new FinanceManager($this->db);
        $academicManager = new AcademicManager($this->db);
        $communicationManager = new CommunicationManager($this->db);
        $today = date('Y-m-d');
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');
        $data = [
            'totalStudents' => count($studentManager->getAllStudents()),
            'monthlyIncome' => $financeManager->getTotalPaymentsByDate($monthStart,
                $monthEnd),
            'monthlyExpenses' => $financeManager->getTotalExpensesByDate($monthStart,
                $monthEnd),
            'activeNotices' => $communicationManager->getNotices(),
            'upcomingExams' => $academicManager->getExams()
        ];
        include 'views/dashboard.php';
    }

    // Student Controllers
    public function showStudentsList()
    {
        $this->requireLogin();
        $studentManager = new StudentManager($this->db);
        $students = $studentManager->getAllStudents();
        include 'views/students/index.php';
    }

    public function showStudentForm()
    {
        $this->requireLogin();
        $classManager = new ClassManager($this->db);
        $classes = $classManager->getAll();
        include 'views/students/form.php';
    }

    public function handleStudentCreate()
    {
        $this->requireLogin();
        $studentManager = new StudentManager($this->db);
        $result = $studentManager->registerStudent($_POST);
        if ($result) {
            $_SESSION['success'] = 'Student registered successfully';
            header('Location: /students');
        } else {
            $_SESSION['error'] = 'Failed to register student: ' . $this->db->getLastError();
            header('Location: /students/new');
        }
        exit;
    }

    public function showStudentDetails($admission_no)
    {
        $this->requireLogin();
        $studentManager = new StudentManager($this->db);
        $student = $studentManager->getStudentByAdmission_no($admission_no);
        if (!$student) {
            $_SESSION['error'] = 'Student not found';
            header('Location: /students');
            exit;
        }
        // Get financial information
        $financeManager = new FinanceManager($this->db);
        $payments = $financeManager->getStudentPayments($admission_no);
        // Get academic information
        $academicManager = new AcademicManager($this->db);
        $terms = $academicManager->getTerms();
        $currentTerm = current($terms); // Just get the first term for demo
        $results = [];
        if ($currentTerm) {
            $results = $academicManager->getStudentExamResults(
                $id,
                $currentTerm['term'],
                $currentTerm['year']
            );
        }
        include 'views/students/view.php';
    }

    public function showStudentEditForm($admission_no)
    {
        $this->requireLogin();
        $studentManager = new StudentManager($this->db);
        $classManager = new ClassManager($this->db);
        $student = $studentManager->getStudentByAdmission_no($admission_no);
        $classes = $classManager->getAll();
        if (!$student) {
            $_SESSION['error'] = 'Student not found';
            header('Location: /students');
            exit;
        }
        include 'views/students/form.php';
    }
    public function handleStudentUpdate($admission_no)
    {
        $this->requireLogin();
        $studentManager = new StudentManager($this->db);
        $result = $studentManager->updateStudent($admission_no, $_POST);
        if ($result) {
            $_SESSION['success'] = 'Student updated successfully';
            header('Location: /students/' . $admission_no);
        } else {
            $_SESSION['error'] = 'Failed to update student: ' . $this->db->getLastError();
            header('Location: /students/' . $admission_no . '/edit');
        }
        exit;
    }

    public function handleStudentDelete($admission_no)
    {
        $this->requireLogin();
        $this->requireAdmin();
        $studentManager = new StudentManager($this->db);
        $result = $studentManager->deleteStudent($admission_no);
        if ($result) {
            $_SESSION['success'] = 'Student deleted successfully';
        } else {
            $_SESSION['error'] = 'Failed to delete student: ' . $this->db->getLastError();
        }
        header('Location: /students');
        exit;
    }

    // Finance Controllers
    public function showFinanceDashboard()
    {
        $this->requireLogin();
        $financeManager = new FinanceManager($this->db);
        $today = date('Y-m-d');
        $weekAgo = date('Y-m-d', strtotime('-7 days'));
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');
        $data = [
            'todayPayments' => $financeManager->getTotalPaymentsByDate($today, $today),
            'weeklyPayments' => $financeManager->getTotalPaymentsByDate($weekAgo, $today),
            'monthlyPayments' => $financeManager->getTotalPaymentsByDate($monthStart,
                $monthEnd),
            'todayExpenses' => $financeManager->getTotalExpensesByDate($today, $today),
            'weeklyExpenses' => $financeManager->getTotalExpensesByDate($weekAgo, $today),
            'monthlyExpenses' => $financeManager->getTotalExpensesByDate($monthStart,
                $monthEnd)
        ];
        include 'views/finance/dashboard.php';
    }

    public function showPaymentsList()
    {
        $this->requireLogin();
        $financeManager = new FinanceManager($this->db);
        $today = date('Y-m-d');
        $monthAgo = date('Y-m-d', strtotime('-30 days'));
        $payments = $financeManager->getPaymentsByDate($monthAgo, $today);
        include 'views/finance/payments.php';
    }

    public function showPaymentForm()
    {
        $this->requireLogin();
        // Get students for dropdown
        $studentManager = new StudentManager($this->db);
        $students = $studentManager->getAllStudents();
        include 'views/finance/payment_form.php';
    }

    public function handlePaymentCreate()
    {
        $this->requireLogin();
        $financeManager = new FinanceManager($this->db);
        $result = $financeManager->recordPayment(
            $_POST['student_admission_no'],
            $_POST['amount'],
            $_POST['payment_date'],
            $_POST['payment_method'],
            $_POST['reference'],
            $_POST['description'],
            $this->auth->getLoggedInUser()['id']
        );
        if ($result) {
            $_SESSION['success'] = 'Payment recorded successfully';
            header('Location: /finance/payments');
        } else {
            $_SESSION['error'] = 'Failed to record payment: ' . $this->db->getLastError();
            header('Location: /finance/payments/new');
        }
        exit;
    }

    public function showExpensesList()
    {
        $this->requireLogin();
        $financeManager = new FinanceManager($this->db);
        $today = date('Y-m-d');
        $monthAgo = date('Y-m-d', strtotime('-30 days'));
        $expenses = $financeManager->getExpenses($monthAgo, $today);
        include 'views/finance/expenses.php';
    }

    public function showExpenseForm()
    {
        $this->requireLogin();
        include 'views/finance/expense_form.php';
    }

    public function handleExpenseCreate()
    {
        $this->requireLogin();
        $financeManager = new FinanceManager($this->db);
        $result = $financeManager->recordExpense(
            $_POST['category'],
            $_POST['amount'],
            $_POST['expense_date'],
            $_POST['payment_method'],
            $_POST['reference'],
            $_POST['description'],
            $this->auth->getLoggedInUser()['id']
        );
        if ($result) {
            $_SESSION['success'] = 'Expense recorded successfully';
            header('Location: /finance/expenses');
        } else {
            $_SESSION['error'] = 'Failed to record expense: ' . $this->db->getLastError();
            header('Location: /finance/expenses/new');
        }
        exit;
    }

    public function showFinanceReports()
    {
        $this->requireLogin();
        include 'views/finance/reports.php';
    }

    public function generateFinanceReport()
    {
        $this->requireLogin();
        $startDate = $_POST['start_date'];
        $endDate = $_POST['end_date'];
        $financeManager = new FinanceManager($this->db);
        $report = $financeManager->generateFinancialReport($startDate, $endDate);
        $_SESSION['report'] = $report;
        header('Location: /finance/reports');
        exit;
    }

    // Academic Controllers
    public function showAcademicDashboard()
    {
        $this->requireLogin();
        $academicManager = new AcademicManager($this->db);
        $subjects = $academicManager->getSubjects();
        $terms = $academicManager->getTerms();
        $exams = $academicManager->getExams();
        include 'views/academics/dashboard.php';
    }

    public function showSubjectsList()
    {
        $this->requireLogin();
        $academicManager = new AcademicManager($this->db);
        $subjects = $academicManager->getSubjects();
        include 'views/academics/subjects.php';
    }

    public function showExamsList()
    {
        $this->requireLogin();
        $academicManager = new AcademicManager($this->db);
        $exams = $academicManager->getExams();
        include 'views/academics/exams.php';
    }

    public function showExamForm()
    {
        $this->requireLogin();
        $academicManager = new AcademicManager($this->db);
        $terms = $academicManager->getTerms();
        include 'views/academics/exam_form.php';
    }

    public function handleExamCreate()
    {
        $this->requireLogin();
        $academicManager = new AcademicManager($this->db);
        $result = $academicManager->createExam(
            $_POST['name'],
            $_POST['term'],
            $_POST['year'],
            $_POST['start_date'],
            $_POST['end_date']
        );
        if ($result) {
            $_SESSION['success'] = 'Exam created successfully';
            header('Location: /academics/exams');
        } else {
            $_SESSION['error'] = 'Failed to create exam: ' . $this->db->getLastError();
            header('Location: /academics/exams/new');
        }
        exit;
    }

    public function showResultsPage()
    {
        $this->requireLogin();
        $academicManager = new AcademicManager($this->db);
        $exams = $academicManager->getExams();
        $subjects = $academicManager->getSubjects();
        $studentManager = new StudentManager($this->db);
        $students = [];
        if (isset($_GET['exam_name']) && isset($_GET['class_name'])) {
            $students = $studentManager->getStudentsByClass($_GET['class_name']);
        }
        include 'views/academics/results.php';
    }

    public function handleResultsEntry()
    {
        $this->requireLogin();
        $academicManager = new AcademicManager($this->db);
        $success = true;
        foreach ($_POST['marks'] as $student_admission_no => $subjects) {
            foreach ($subjects as $subject_name => $marks) {
                $comments = $_POST['comments'][$student_admission_no][$subject_name] ?? '';
                $result = $academicManager->recordExamResult(
                    $student_admission_no,
                    $_POST['exam_name'],
                    $subject_name,
                    $marks,
                    $comments
                );
                if (!$result) {
                    $success = false;
                }
            }
        }
        if ($success) {
            $_SESSION['success'] = 'Exam results recorded successfully';
        } else {
            $_SESSION['error'] = 'Some errors occurred while recording results: ' . $this->db->getLastError
                ();
        }
        header('Location: /academics/results');
        exit;
    }

    public function showReportCardGeneration()
    {
        $this->requireLogin();
        $academicManager = new AcademicManager($this->db);
        $terms = $academicManager->getTerms();
        $studentManager = new StudentManager($this->db);
        $students = [];
        // Get classes for dropdown
        $sql = "SELECT * FROM classes ORDER BY name";
        $stmt = $this->db->query($sql);
        $classes = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        if (isset($_GET['class_name'])) {
            $students = $studentManager->getStudentsByClass($_GET['class_name']);
        }
        include 'views/academics/report_cards.php';
    }

    public function generateReportCards()
    {
        $this->requireLogin();
        $term = $_POST['term'];
        $year = $_POST['year'];
        $studen_admission_nos = $_POST['student_admission_nos'] ?? [];
        if (empty($student_admission_nos)) {
            $_SESSION['error'] = 'No students selected';
            header('Location: /academics/report-cards');
            exit;
        }
        // Generate report cards for all selected students
        $academicManager = new AcademicManager($this->db);
        $generatedReports = [];
        foreach ($student_admission_nos as $student_admission_no) {
            $reportCard = $academicManager->generateReportCard($student_admission_no, $term, $year);
            if ($reportCard) {
                $generatedReports[] = $student_admission_no;
            }
        }
        if (count($generatedReports) > 0) {
            $_SESSION['success'] = count($generatedReports) . ' report card(s) generated successfully';
            // Redirect to the first student's report card
            header('Location: /academics/report-cards/' . $generatedReports[0] . '/' . $term . '/' . $year);
        } else {
            $_SESSION['error'] = 'Failed to generate report cards';
            header('Location: /academics/report-cards');
        }
        exit;
    }

    public function viewReportCard($student_admssion_no, $term, $year)
    {
        $this->requireLogin();
        $academicManager = new AcademicManager($this->db);
        $reportCard = $academicManager->generateReportCard($student_admission_no, $term, $year);
        if (!$reportCard) {
            $_SESSION['error'] = 'Failed to generate report card: ' . $this->db->getLastError();
            header('Location: /academics/report-cards');
            exit;
        }
        include 'views/academics/view_report_card.php';
    }

    // Communication Controllers
    public function showCommunicationDashboard()
    {
        $this->requireLogin();
        $communicationManager = new CommunicationManager($this->db);
        $notices = $communicationManager->getNotices();
        $user_name = $this->auth->getLoggedInUser()['name'];
        $messages = $communicationManager->getMessages($user_name);
        include 'views/communication/dashboard.php';
    }

    public function showNoticesList()
    {
        $this->requireLogin();
        $communicationManager = new CommunicationManager($this->db);
        $notices = $communicationManager->getNotices(null, false);
        include 'views/communication/notices.php';
    }

    public function showNoticeForm()
    {
        $this->requireLogin();
        include 'views/communication/notice_form.php';
    }

    public function handleNoticeCreate()
    {
        $this->requireLogin();
        $communicationManager = new CommunicationManager($this->db);
        $result = $communicationManager->createNotice(
            $_POST['title'],
            $_POST['content'],
            $_POST['publish_date'],
            $_POST['expiry_date'],
            $_POST['audience']
        );
        if ($result) {
            $_SESSION['success'] = 'Notice created successfully';
            header('Location: /communication/notices');
        } else {
            $_SESSION['error'] = 'Failed to create notice: ' . $this->db->getLastError();
            header('Location: /communication/notices/new');
        }
        exit;
    }

    public function showMessagesList()
    {
        $this->requireLogin();
        $user_name = $this->auth->getLoggedInUser()['name'];
        $folder = $_GET['folder'] ?? 'inbox';
        $communicationManager = new CommunicationManager($this->db);
        $messages = $communicationManager->getMessages($user_name, $folder);
        include 'views/communication/messages.php';
    }

    public function showMessageForm()
    {
        $this->requireLogin();
        // Get users for recipient dropdown
        $sql = "SELECT id, username, role FROM users WHERE id != ?";
        $stmt = $this->db->query($sql, [$this->auth->getLoggedInUser()['id']]);
        $users = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        include 'views/communication/message_form.php';
    }

    public function handleMessageCreate()
    {
        $this->requireLogin();
        $sender_name = $this->auth->getLoggedInUser()['name'];
        $subject = $_POST['subject'];
        $content = $_POST['content'];
        $recipients = $_POST['recipients'] ?? [];
        $communicationManager = new CommunicationManager($this->db);
        $result = $communicationManager->sendMessage($sender_name, $subject, $content,
            $recipients);
        if ($result) {
            $_SESSION['success'] = 'Message sent successfully';
            header('Location: /communication/messages');
        } else {
            $_SESSION['error'] = 'Failed to send message: ' . $this->db->getLastError();
            header('Location: /communication/messages/new');
        }
        exit;
    }

    public function viewMessage($id)
    {
        $this->requireLogin();
        $userId = $this->auth->getLoggedInUser()['id'];
        $communicationManager = new CommunicationManager($this->db);
        $message = $communicationManager->getMessage($id, $userId);
        if (!$message) {
            $_SESSION['error'] = 'Message not found';
            header('Location: /communication/messages');
            exit;
        }
        include 'views/communication/view_message.php';
    }

    // Helper methods
    private function requireLogin()
    {
        if (!$this->auth->isLoggedIn()) {
            header('Location: /login');
            exit;
        }
    }

    private function requireAdmin()
    {
        if (!$this->auth->hasRole('admin')) {
            $_SESSION['error'] = 'You do not have permission to perform this action';
            header('Location: /dashboard');
            exit;
        }
    }
}
?>
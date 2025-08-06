<?php
class User {
    private $conn;
    public $id;
    public $name;
    public $email;
    public $role;
    public $status;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Find user by email
    public function findByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        if ($user) {
            $this->id = $user['id'];
            $this->name = $user['name'];
            $this->email = $user['email'];
            $this->role = $user['role'];
            $this->status = $user['status'];
            $this->password = $user['password'];
            return true;
        }
        return false;
    }

    // Verify user password
    public function verifyPassword($password) {
        return password_verify($password, $this->password);
    }

    // Register a new user
    public function register($name, $email, $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $role = 'student';
        $stmt = $this->conn->prepare("INSERT INTO users(name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $hash, $role);
        return $stmt->execute();
    }

    // Get all student users
    public function getAllStudents() {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE role='student' ORDER BY id ASC");
        $stmt->execute();
        $result = $stmt->get_result();
        $students = [];
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
        return $students;
    }

    // Update user status
    public function updateStatus($user_id, $status) {
        $stmt = $this->conn->prepare("UPDATE users SET status=? WHERE id=?");
        $stmt->bind_param("si", $status, $user_id);
        return $stmt->execute();
    }
}
?>

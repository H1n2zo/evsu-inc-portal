<?php
// controllers/RegisterController.php
// Single Responsibility: Handle the account registration page
// OOP Concepts:
//   - Inheritance: extends Controller
//   - Encapsulation: private validate() and handlePost() hide business logic

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/AuthGuard.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/AuditLogModel.php';
require_once __DIR__ . '/../models/SettingsModel.php';

class RegisterController extends Controller
{
    private AuthGuard     $guard;
    private UserModel     $users;
    private AuditLogModel $logs;

    public function __construct()
    {
        parent::__construct();
        $this->guard = new AuthGuard();
        $this->users = new UserModel();
        $this->logs  = new AuditLogModel();
    }

    public function run(): void
    {
        if (!empty($_SESSION['user_id'])) { $this->redirect('index.php'); }

        $type    = ($_GET['type'] ?? 'student') === 'employee' ? 'employee' : 'student';
        $error   = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            [$error, $success] = $this->handlePost($type);
        }

        $this->render('register', [
            'pageTitle' => 'Register',
            'csrf'      => $this->guard->csrfToken(),
            'type'      => $type,
            'error'     => $error,
            'success'   => $success,
        ]);
    }

    private function handlePost(string $type): array
    {
        $this->guard->verifyCsrf();

        $full_name  = trim($_POST['full_name'] ?? '');
        $email      = trim($_POST['email'] ?? '');
        $username   = trim($_POST['username'] ?? '');
        $password   = $_POST['password'] ?? '';
        $confirm    = $_POST['confirm_password'] ?? '';
        $acct_type  = $_POST['account_type'] === 'employee' ? 'employee' : 'student';
        $student_id = trim($_POST['student_id'] ?? '');
        $dept       = trim($_POST['department'] ?? '');

        $error = $this->validate($full_name, $email, $username, $password, $confirm);
        if ($error) return [$error, ''];

        if ($this->users->usernameOrEmailExists($username, $email)) {
            return ['Username or email already exists.', ''];
        }

        $status = $acct_type === 'student' ? 'active' : 'pending';
        $this->users->create([
            'full_name'     => $full_name,
            'username'      => $username,
            'email'         => $email,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
            'account_type'  => $acct_type,
            'status'        => $status,
            'student_id'    => $student_id ?: null,
            'department'    => $dept ?: null,
        ]);

        $this->logs->write(null, $username, null, 'Registration', "New $acct_type account created", $_SERVER['REMOTE_ADDR'] ?? '');

        $msg = $acct_type === 'student'
            ? 'Account created! You can now sign in.'
            : 'Account request submitted. An administrator will review and activate it.';
        return ['', $msg];
    }

    private function validate(string $name, string $email, string $user, string $pass, string $confirm): string
    {
        if (!$name || !$email || !$user || !$pass) return 'All required fields must be filled.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))  return 'Please enter a valid email address.';
        if (strlen($pass) < 8)                           return 'Password must be at least 8 characters.';
        if ($pass !== $confirm)                          return 'Passwords do not match.';
        return '';
    }
}

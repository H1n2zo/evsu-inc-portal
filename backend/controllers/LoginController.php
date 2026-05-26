<?php
// controllers/LoginController.php
// Single Responsibility: Handle the login page request/response cycle
// OOP Concepts:
//   - Inheritance: extends Controller (gets session, View renderer, helpers)
//   - Encapsulation: private handlePost() hides POST logic

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/AuthService.php';
require_once __DIR__ . '/../core/AuthGuard.php';

class LoginController extends Controller
{
    private AuthService $auth;
    private AuthGuard   $guard;

    public function __construct()
    {
        parent::__construct();
        $this->auth  = new AuthService();
        $this->guard = new AuthGuard();
    }

    public function run(): void
    {
        // Redirect already-logged-in users
        if (!empty($_SESSION['user_id'])) {
            $this->redirectByRole();
        }

        $type  = ($_GET['type'] ?? 'employee') === 'student' ? 'student' : 'employee';
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $error = $this->handlePost();
        }

        // Pass all data the view needs — view does ZERO logic
        $this->render('login', [
            'pageTitle' => $type === 'student' ? 'Student Login' : 'Employee Login',
            'csrf'      => $this->guard->csrfToken(),
            'type'      => $type,
            'error'     => $error,
        ]);
    }

    // Private: encapsulate POST handling
    private function handlePost(): string
    {
        $this->guard->verifyCsrf();
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$username || !$password) {
            return 'Please enter your username and password.';
        }

        $result = $this->auth->login($username, $password);
        if ($result['success']) {
            $this->redirectByRole();
        }
        return $result['error'];
    }

    private function redirectByRole(): never
    {
        $type = $_SESSION['account_type'];
        if ($type === 'admin')        $this->redirect('admin/dashboard.php');
        elseif ($type === 'employee') $this->redirect('employee/dashboard.php');
        else                          $this->redirect('student/dashboard.php');
    }
}

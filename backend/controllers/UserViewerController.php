<?php
// controllers/UserViewerController.php
// Single Responsibility: Admin view of a single user profile + their applications (READ)
// OOP Concepts:
//   - Inheritance: extends Controller
//   - Composition: UserModel + ApplicationModel injected

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/AuthGuard.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/ApplicationModel.php';

class UserViewerController extends Controller
{
    private AuthGuard        $guard;
    private UserModel        $users;
    private ApplicationModel $apps;

    public function __construct()
    {
        parent::__construct();
        $this->guard = new AuthGuard();
        $this->users = new UserModel();
        $this->apps  = new ApplicationModel();
    }

    public function run(): void
    {
        $this->guard->requireAdmin();
        $uid  = (int)($_GET['id'] ?? 0);
        $user = $this->users->findById($uid);
        if (!$user) { $this->redirect('admin/users.php'); }

        $this->render('admin/user_view', [
            'pageTitle'  => 'User — ' . $user['full_name'],
            'activePage' => 'users',
            'csrf'       => $this->guard->csrfToken(),
            'user'       => $user,
            'apps'   => $user['account_type'] === 'student'
                            ? $this->apps->getForStudent($uid)
                            : [],
        ]);
    }
}

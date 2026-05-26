<?php
// models/ApplicationModel.php
// Data Layer — all database operations for INC applications
// OOP Concepts:
//   - Encapsulation: private baseQuery() hides the join SQL; public methods expose clean API
//   - Inheritance: extends Model (gets DB connection via getDb())

require_once __DIR__ . '/../core/Model.php';

class ApplicationModel extends Model
{
    // Private: reusable SELECT with all joins so every fetch includes related names
    private function baseQuery(): string
    {
        return "SELECT a.*,
            u.full_name,      u.username,    u.email,
            u.student_id      AS stu_id,
            inst.full_name    AS instructor_name,
            dh.full_name      AS depthead_name,
            reg.full_name     AS registrar_name
            FROM inc_applications a
            JOIN  users u    ON a.student_id    = u.id
            LEFT JOIN users inst ON a.instructor_id = inst.id
            LEFT JOIN users dh   ON a.dept_head_id  = dh.id
            LEFT JOIN users reg  ON a.registrar_id  = reg.id";
    }

    // ── READ ─────────────────────────────────────────────────────

    public function findById(int $id): array|false
    {
        $stmt = $this->getDb()->prepare($this->baseQuery() . " WHERE a.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByIdForStudent(int $id, int $studentId): array|false
    {
        $stmt = $this->getDb()->prepare($this->baseQuery() . " WHERE a.id = ? AND a.student_id = ?");
        $stmt->execute([$id, $studentId]);
        return $stmt->fetch();
    }

    public function getForStudent(int $studentId, string $status = ''): array
    {
        $where  = ['a.student_id = ?'];
        $params = [$studentId];
        if ($status) { $where[] = 'a.status = ?'; $params[] = $status; }

        $sql  = $this->baseQuery() . " WHERE " . implode(' AND ', $where);
        $sql .= " ORDER BY a.updated_at DESC";

        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * READ applications scoped to a given employee role.
     *
     * Supported filters:
     *   'status'  string  — exact status match
     *   'step'    int     — exact current_step match
     *   'search'  string  — full_name / app_code / subject_name LIKE
     *   'limit'   int
     *   'offset'  int
     *
     * For registrar (uid=0 allowed) we broaden the scope to all step-5/6 apps.
     */
    public function getForEmployee(string $role, int $uid, array $filters = []): array
    {
        [$where, $params] = $this->buildEmployeeWhere($role, $uid, $filters);

        $sql  = $this->baseQuery() . " WHERE " . implode(' AND ', $where);
        $sql .= " ORDER BY a.updated_at DESC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
            if (!empty($filters['offset'])) {
                $sql .= " OFFSET " . (int)$filters['offset'];
            }
        }

        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countForEmployee(string $role, int $uid, array $filters = []): int
    {
        [$where, $params] = $this->buildEmployeeWhere($role, $uid, $filters);

        $stmt = $this->getDb()->prepare(
            "SELECT COUNT(*) FROM inc_applications a
             JOIN users u ON a.student_id = u.id
             WHERE " . implode(' AND ', $where)
        );
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Private helper — builds WHERE clauses + params for employee-scoped queries.
     * Handles 'status', 'step', 'search' filters consistently.
     */
    private function buildEmployeeWhere(string $role, int $uid, array $filters): array
    {
        $where  = ['1=1'];
        $params = [];

        // Role-based scope
        if ($role === 'instructor') {
            $where[]  = "a.instructor_id = ?";
            $params[] = $uid;
        } elseif ($role === 'dept_head') {
            $where[]  = "a.dept_head_id = ?";
            $params[] = $uid;
        } elseif ($role === 'registrar') {
            // Registrar sees all apps at step 5 or 6 — uid optional
            $where[] = "a.current_step IN (5,6)";
        }

        // Explicit step filter (used by grade_input, dept_review, or_verify, grade_posting pages)
        if (!empty($filters['step'])) {
            $where[]  = "a.current_step = ?";
            $params[] = (int)$filters['step'];
        }

        // Status filter
        if (!empty($filters['status'])) {
            $where[]  = "a.status = ?";
            $params[] = $filters['status'];
        }

        // Full-text search
        if (!empty($filters['search'])) {
            $where[] = "(u.full_name LIKE ? OR a.app_code LIKE ? OR a.subject_name LIKE ? OR a.subject_code LIKE ?)";
            $s       = "%{$filters['search']}%";
            array_push($params, $s, $s, $s, $s);
        }

        return [$where, $params];
    }

    public function getAll(array $filters = []): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) { $where[] = "a.status = ?"; $params[] = $filters['status']; }
        if (!empty($filters['search'])) {
            $where[] = "(u.full_name LIKE ? OR a.app_code LIKE ? OR a.subject_name LIKE ? OR a.subject_code LIKE ?)";
            $s       = "%{$filters['search']}%";
            array_push($params, $s, $s, $s, $s);
        }

        $sql  = $this->baseQuery() . " WHERE " . implode(' AND ', $where);
        $sql .= " ORDER BY a.created_at DESC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
            if (!empty($filters['offset'])) $sql .= " OFFSET " . (int)$filters['offset'];
        }

        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countAll(array $filters = []): int
    {
        $where  = ['1=1'];
        $params = [];
        if (!empty($filters['status'])) { $where[] = "a.status = ?"; $params[] = $filters['status']; }
        if (!empty($filters['search'])) {
            $where[] = "(u.full_name LIKE ? OR a.app_code LIKE ? OR a.subject_name LIKE ? OR a.subject_code LIKE ?)";
            $s       = "%{$filters['search']}%";
            array_push($params, $s, $s, $s, $s);
        }
        $stmt = $this->getDb()->prepare(
            "SELECT COUNT(*) FROM inc_applications a
             JOIN users u ON a.student_id = u.id
             WHERE " . implode(' AND ', $where)
        );
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function countForStudent(int $uid, string $status = ''): int
    {
        $where  = ['student_id = ?'];
        $params = [$uid];
        if ($status) { $where[] = "status = ?"; $params[] = $status; }
        $stmt = $this->getDb()->prepare(
            "SELECT COUNT(*) FROM inc_applications WHERE " . implode(' AND ', $where)
        );
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function pendingPaymentForStudent(int $uid): array
    {
        $stmt = $this->getDb()->prepare(
            "SELECT * FROM inc_applications
             WHERE student_id = ? AND current_step = 4 AND status = 'pending_payment'"
        );
        $stmt->execute([$uid]);
        return $stmt->fetchAll();
    }

    public function getStatusCounts(): array
    {
        return $this->getDb()
            ->query("SELECT status, COUNT(*) as cnt FROM inc_applications GROUP BY status")
            ->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function globalStats(): array
    {
        $db = $this->getDb();
        return [
            'total'    => (int)$db->query("SELECT COUNT(*) FROM inc_applications")->fetchColumn(),
            'pending'  => (int)$db->query("SELECT COUNT(*) FROM inc_applications WHERE status IN ('in_progress','pending_payment','verification')")->fetchColumn(),
            'resolved' => (int)$db->query("SELECT COUNT(*) FROM inc_applications WHERE status = 'resolved'")->fetchColumn(),
        ];
    }

    // ── READ — check duplicate ────────────────────────────────────

    public function isDuplicate(int $uid, string $code, string $semester, string $year): bool
    {
        $stmt = $this->getDb()->prepare(
            "SELECT id FROM inc_applications
             WHERE student_id = ? AND subject_code = ? AND semester = ?
               AND school_year = ? AND status != 'rejected'
             LIMIT 1"
        );
        $stmt->execute([$uid, $code, $semester, $year]);
        return (bool)$stmt->fetch();
    }

    // ── CREATE ───────────────────────────────────────────────────

    public function create(array $data): int
    {
        // Generate a unique padded code e.g. INC-0042
        $count = (int)$this->getDb()
            ->query("SELECT COUNT(*) FROM inc_applications")
            ->fetchColumn();
        $code  = 'INC-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        $stmt  = $this->getDb()->prepare(
            "INSERT INTO inc_applications
                (app_code, student_id, subject_name, subject_code, units,
                 semester, school_year, instructor_id, dept_head_id,
                 current_step, status, created_at, updated_at)
             VALUES (?,?,?,?,?,?,?,?,?, 2, 'in_progress', NOW(), NOW())"
        );
        $stmt->execute([
            $code,
            $data['student_id'],
            $data['subject_name'],
            $data['subject_code'],
            $data['units'],
            $data['semester'],
            $data['school_year'],
            $data['instructor_id'] ?: null,
            $data['dept_head_id']  ?: null,
        ]);
        return (int)$this->getDb()->lastInsertId();
    }

    // ── UPDATE ───────────────────────────────────────────────────

    public function updateStep(int $id, array $data): void
    {
        $sets   = [];
        $params = [];
        foreach ($data as $col => $val) {
            $sets[]   = "`{$col}` = ?";
            $params[] = $val;
        }
        $sets[]   = "updated_at = NOW()";
        $params[] = $id;

        $this->getDb()
            ->prepare("UPDATE inc_applications SET " . implode(', ', $sets) . " WHERE id = ?")
            ->execute($params);
    }
}

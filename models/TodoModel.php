<?php
// ============================================================
// AntiGravity Todo App – Todo Model
// ============================================================
require_once __DIR__ . '/../config/database.php';

class TodoModel {

    /**
     * Fetch paginated todos for a user with optional filters.
     *
     * @param int    $userId
     * @param array  $filters  ['status','priority','group_id','search']
     * @param int    $page     1-based page number
     * @param int    $perPage  Items per page
     */
    public static function getAll(int $userId, array $filters = [], int $page = 1, int $perPage = ITEMS_PER_PAGE): array {
        $where  = ['t.user_id = :uid', 't.deleted_at IS NULL'];
        $params = [':uid' => $userId];

        if (!empty($filters['status'])) {
            $where[] = 't.status = :status';
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['priority'])) {
            $where[] = 't.priority = :priority';
            $params[':priority'] = $filters['priority'];
        }
        if (!empty($filters['group_id'])) {
            $where[] = 't.group_id = :group_id';
            $params[':group_id'] = (int)$filters['group_id'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(t.title LIKE :search OR t.description LIKE :search)';
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $whereClause = implode(' AND ', $where);
        $offset      = ($page - 1) * $perPage;

        // Total count for pagination
        $countSql   = "SELECT COUNT(*) FROM todos t WHERE {$whereClause}";
        $countStmt  = db()->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Actual data
        $sql  = "SELECT t.*, g.name AS group_name, g.color AS group_color
                 FROM todos t
                 LEFT JOIN todo_groups g ON g.id = t.group_id
                 WHERE {$whereClause}
                 ORDER BY FIELD(t.priority,'high','medium','low'), t.due_date ASC, t.created_at DESC
                 LIMIT :limit OFFSET :offset";
        $stmt = db()->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        return ['data' => $rows, 'total' => $total, 'pages' => (int)ceil($total / $perPage)];
    }

    /**
     * Fetch a single todo that belongs to a user (non-deleted).
     */
    public static function getById(int $id, int $userId): array|false {
        $stmt = db()->prepare(
            'SELECT t.*, g.name AS group_name FROM todos t
             LEFT JOIN todo_groups g ON g.id = t.group_id
             WHERE t.id = ? AND t.user_id = ? AND t.deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute([$id, $userId]);
        return $stmt->fetch();
    }

    /**
     * Create a new todo. Returns new ID or false.
     */
    public static function create(int $userId, array $data): int|false {
        $stmt = db()->prepare(
            'INSERT INTO todos (user_id, group_id, title, description, priority, status, due_date)
             VALUES (:uid, :gid, :title, :desc, :priority, :status, :due)'
        );
        $stmt->execute([
            ':uid'      => $userId,
            ':gid'      => $data['group_id'] ?: null,
            ':title'    => $data['title'],
            ':desc'     => $data['description'] ?? null,
            ':priority' => $data['priority'] ?? 'medium',
            ':status'   => $data['status']   ?? 'pending',
            ':due'      => $data['due_date']  ?: null,
        ]);
        $newId = (int)db()->lastInsertId();
        return $newId > 0 ? $newId : false;
    }

    /**
     * Update an existing todo. Returns affected rows count.
     */
    public static function update(int $id, int $userId, array $data): int {
        $stmt = db()->prepare(
            'UPDATE todos
             SET group_id=:gid, title=:title, description=:desc,
                 priority=:priority, status=:status, due_date=:due
             WHERE id=:id AND user_id=:uid AND deleted_at IS NULL'
        );
        $stmt->execute([
            ':gid'      => $data['group_id'] ?: null,
            ':title'    => $data['title'],
            ':desc'     => $data['description'] ?? null,
            ':priority' => $data['priority'] ?? 'medium',
            ':status'   => $data['status']   ?? 'pending',
            ':due'      => $data['due_date']  ?: null,
            ':id'       => $id,
            ':uid'      => $userId,
        ]);
        return $stmt->rowCount();
    }

    /**
     * Update only the status of a todo (used by AJAX toggle).
     */
    public static function updateStatus(int $id, int $userId, string $status): int {
        $allowed = ['pending', 'in_progress', 'completed'];
        if (!in_array($status, $allowed, true)) {
            return 0;
        }
        $stmt = db()->prepare(
            'UPDATE todos SET status=? WHERE id=? AND user_id=? AND deleted_at IS NULL'
        );
        $stmt->execute([$status, $id, $userId]);
        return $stmt->rowCount();
    }

    /**
     * Soft-delete a todo (sets deleted_at timestamp).
     */
    public static function softDelete(int $id, int $userId): int {
        $stmt = db()->prepare(
            'UPDATE todos SET deleted_at=NOW() WHERE id=? AND user_id=? AND deleted_at IS NULL'
        );
        $stmt->execute([$id, $userId]);
        return $stmt->rowCount();
    }

    /**
     * Dashboard counts for a user.
     * Returns [total, completed, pending, in_progress, high_priority]
     */
    public static function getDashboardStats(int $userId): array {
        $stmt = db()->prepare(
            "SELECT
                COUNT(*) AS total,
                SUM(status = 'completed')  AS completed,
                SUM(status = 'pending')    AS pending,
                SUM(status = 'in_progress') AS in_progress,
                SUM(priority = 'high')     AS `high_priority`
             FROM todos
             WHERE user_id = ? AND deleted_at IS NULL"
        );
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: [];
    }

    /**
     * Aggregate stats for a single group (all pages).
     * Returns [total, completed, pending, in_progress]
     */
    public static function getGroupStats(int $userId, int $groupId): array {
        $stmt = db()->prepare(
            "SELECT
                COUNT(*) AS total,
                SUM(status = 'completed')   AS completed,
                SUM(status = 'pending')     AS pending,
                SUM(status = 'in_progress') AS in_progress
             FROM todos
             WHERE user_id = ? AND group_id = ? AND deleted_at IS NULL"
        );
        $stmt->execute([$userId, $groupId]);
        return $stmt->fetch() ?: ['total' => 0, 'completed' => 0, 'pending' => 0, 'in_progress' => 0];
    }

    /**
     * Fetch todos due today for a user.
     */
    public static function getDueToday(int $userId): array {
        $stmt = db()->prepare(
            "SELECT t.*, g.name AS group_name FROM todos t
             LEFT JOIN todo_groups g ON g.id = t.group_id
             WHERE t.user_id=? AND t.deleted_at IS NULL
               AND t.due_date=CURDATE() AND t.status != 'completed'
             ORDER BY t.priority DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}

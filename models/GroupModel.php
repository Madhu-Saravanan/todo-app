<?php
// ============================================================
// AntiGravity Todo App – Todo Group Model
// ============================================================
require_once __DIR__ . '/../config/database.php';

class GroupModel {

    /**
     * Get all groups for a user (with todo counts).
     */
    public static function getAllByUser(int $userId): array {
        $stmt = db()->prepare(
            "SELECT g.*,
                    COUNT(t.id) AS todo_count
             FROM todo_groups g
             LEFT JOIN todos t ON t.group_id = g.id AND t.deleted_at IS NULL
             WHERE g.user_id = ?
             GROUP BY g.id
             ORDER BY g.name ASC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Get a single group by ID and user.
     */
    public static function getById(int $id, int $userId): array|false {
        $stmt = db()->prepare(
            'SELECT * FROM todo_groups WHERE id = ? AND user_id = ? LIMIT 1'
        );
        $stmt->execute([$id, $userId]);
        return $stmt->fetch();
    }

    /**
     * Create a new group. Returns new ID or false.
     */
    public static function create(int $userId, array $data): int|false {
        $stmt = db()->prepare(
            'INSERT INTO todo_groups (user_id, name, description, color)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $data['name'],
            $data['description'] ?? null,
            $data['color'] ?? '#6366f1',
        ]);
        $newId = (int)db()->lastInsertId();
        return $newId > 0 ? $newId : false;
    }

    /**
     * Update an existing group. Returns affected rows.
     */
    public static function update(int $id, int $userId, array $data): int {
        $stmt = db()->prepare(
            'UPDATE todo_groups
             SET name=?, description=?, color=?
             WHERE id=? AND user_id=?'
        );
        $stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            $data['color'] ?? '#6366f1',
            $id,
            $userId,
        ]);
        return $stmt->rowCount();
    }

    /**
     * Delete a group (and its todos will have group_id set to NULL via FK).
     */
    public static function delete(int $id, int $userId): int {
        $stmt = db()->prepare(
            'DELETE FROM todo_groups WHERE id = ? AND user_id = ?'
        );
        $stmt->execute([$id, $userId]);
        return $stmt->rowCount();
    }
}

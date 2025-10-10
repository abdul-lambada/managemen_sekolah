<?php

declare(strict_types=1);

abstract class Model
{
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = db();
    }

    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }

    public function find(int|string $id, ?string $key = null): ?array
    {
        $key ??= $this->primaryKey;
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$key} = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $keys = array_keys($data);
        $columns = implode(', ', $keys);
        $placeholders = ':' . implode(', :', $keys);

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }

    public function update(int|string $id, array $data, ?string $key = null): bool
    {
        $sets = [];
        foreach ($data as $column => $value) {
            $sets[] = "{$column} = :{$column}";
        }

        $key ??= $this->primaryKey;
        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets) . " WHERE {$key} = :id";
        $data['id'] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete(int|string $id, ?string $key = null): bool
    {
        $key ??= $this->primaryKey;
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$key} = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function count(array $conditions = []): int
    {
        if (empty($conditions)) {
            return (int) $this->db->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();
        }

        $clauses = [];
        foreach ($conditions as $column => $value) {
            $clauses[] = "{$column} = :{$column}";
        }

        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE " . implode(' AND ', $clauses);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($conditions);

        return (int) $stmt->fetchColumn();
    }

    public function latest(string $orderBy = 'created_at', int $limit = 5): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy} DESC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function paginate(int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $stmt = $this->db->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM {$this->table} LIMIT :offset, :limit");
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll();

        $total = (int) $this->db->query('SELECT FOUND_ROWS()')->fetchColumn();
        $pages = (int) ceil($total / $perPage);

        return [
            'items' => $items,
            'total' => $total,
            'pages' => $pages,
            'current' => $page,
        ];
    }
}

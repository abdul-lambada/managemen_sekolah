<?php

abstract class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct()
    {
        $this->db = db();
    }

    public function all()
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }

    public function find($id, $key = null)
    {
        if ($key === null) {
            $key = $this->primaryKey;
        }
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$key} = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create($data)
    {
        $keys = array_keys($data);
        $columns = implode(', ', $keys);
        $placeholders = ':' . implode(', :', $keys);

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }

    public function update($id, $data, $key = null)
    {
        $sets = [];
        foreach ($data as $column => $value) {
            $sets[] = "{$column} = :{$column}";
        }

        if ($key === null) {
            $key = $this->primaryKey;
        }
        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets) . " WHERE {$key} = :id";
        $data['id'] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete($id, $key = null)
    {
        if ($key === null) {
            $key = $this->primaryKey;
        }
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$key} = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function count($conditions = [])
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

    public function latest($orderBy = 'created_at', $limit = 5)
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy} DESC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function paginate($page = 1, $perPage = 10)
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

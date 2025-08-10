<?php
/**
 * Classe base para todos os modelos
 */
abstract class BaseModel {
    protected static $table;
    protected static $primaryKey = 'id';
    protected static $fillable = [];
    protected static $hidden = [];
    
    /**
     * Busca um registro por ID
     */
    public static function find($id) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Lista todos os registros
     */
    public static function all($orderBy = null, $limit = null) {
        global $pdo;
        $sql = "SELECT * FROM " . static::$table;
        
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Busca registros com condições
     */
    public static function where($conditions, $params = [], $orderBy = null, $limit = null) {
        global $pdo;
        $sql = "SELECT * FROM " . static::$table . " WHERE $conditions";
        
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Conta registros
     */
    public static function count($conditions = null, $params = []) {
        global $pdo;
        $sql = "SELECT COUNT(*) as total FROM " . static::$table;
        
        if ($conditions) {
            $sql .= " WHERE $conditions";
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    /**
     * Insere um novo registro
     */
    public static function create($data) {
        global $pdo;
        
        // Filtrar apenas campos permitidos
        $data = array_intersect_key($data, array_flip(static::$fillable));
        
        $fields = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO " . static::$table . " ($fields) VALUES ($placeholders)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute($data)) {
            return $pdo->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Atualiza um registro
     */
    public static function update($id, $data) {
        global $pdo;
        
        // Filtrar apenas campos permitidos
        $data = array_intersect_key($data, array_flip(static::$fillable));
        
        $setClause = [];
        foreach (array_keys($data) as $field) {
            $setClause[] = "$field = :$field";
        }
        
        $sql = "UPDATE " . static::$table . " SET " . implode(', ', $setClause) . " WHERE " . static::$primaryKey . " = :id";
        $data['id'] = $id;
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($data);
    }
    
    /**
     * Remove um registro
     */
    public static function delete($id) {
        global $pdo;
        $stmt = $pdo->prepare("DELETE FROM " . static::$table . " WHERE " . static::$primaryKey . " = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Busca com paginação
     */
    public static function paginate($page = 1, $perPage = 20, $conditions = null, $params = []) {
        $offset = ($page - 1) * $perPage;
        
        // Contar total
        $total = self::count($conditions, $params);
        
        // Buscar registros
        $sql = "SELECT * FROM " . static::$table;
        if ($conditions) {
            $sql .= " WHERE $conditions";
        }
        $sql .= " LIMIT $perPage OFFSET $offset";
        
        global $pdo;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        
        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];
    }
    
    /**
     * Executa uma query personalizada
     */
    public static function query($sql, $params = []) {
        global $pdo;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Executa uma query que retorna apenas um registro
     */
    public static function queryOne($sql, $params = []) {
        global $pdo;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    /**
     * Executa uma query que não retorna dados (INSERT, UPDATE, DELETE)
     */
    public static function execute($sql, $params = []) {
        global $pdo;
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Inicia uma transação
     */
    public static function beginTransaction() {
        global $pdo;
        return $pdo->beginTransaction();
    }
    
    /**
     * Confirma uma transação
     */
    public static function commit() {
        global $pdo;
        return $pdo->commit();
    }
    
    /**
     * Reverte uma transação
     */
    public static function rollback() {
        global $pdo;
        return $pdo->rollback();
    }
    
    /**
     * Sanitiza dados antes de salvar
     */
    protected static function sanitizeData($data) {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (in_array($key, static::$fillable)) {
                $sanitized[$key] = is_string($value) ? htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8') : $value;
            }
        }
        return $sanitized;
    }
    
    /**
     * Remove campos sensíveis da resposta
     */
    protected static function hideSensitiveFields($data) {
        if (is_array($data)) {
            foreach (static::$hidden as $field) {
                unset($data[$field]);
            }
        }
        return $data;
    }
} 
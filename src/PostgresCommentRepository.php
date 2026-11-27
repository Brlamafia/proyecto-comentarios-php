<?php

declare(strict_types=1);

final class PostgresCommentRepository
{
    private PgSql\Connection $connection;

    public function __construct(string $databaseUrl, CommentRepository $legacy)
    {
        if (!function_exists('pg_connect') || !preg_match('~^postgres(?:ql)?://~', $databaseUrl)) {
            throw new RuntimeException('La configuración de PostgreSQL no es válida.');
        }

        $connection = @pg_connect($databaseUrl);
        if ($connection === false) {
            throw new RuntimeException('No fue posible conectar con PostgreSQL.');
        }

        $this->connection = $connection;
        if (!$this->tableExists()) {
            $this->initialize($legacy);
        }
    }

    /** @return list<array<string, string>> */
    public function all(): array
    {
        return pg_fetch_all($this->query(
            'SELECT id, name, role, message, created_at::text AS created_at
             FROM voces_comments ORDER BY created_at DESC, sequence DESC'
        ));
    }

    /** @param array{name: string, role: string, message: string} $data */
    public function add(array $data): void
    {
        $this->insert(bin2hex(random_bytes(16)), $data, date(DATE_ATOM));
    }

    private function initialize(CommentRepository $legacy): void
    {
        $this->query('BEGIN');
        try {
            // Bootstrap once and commit the table and legacy comments together.
            $this->query('SELECT pg_advisory_xact_lock(1381324357, 1)');
            if (!$this->tableExists()) {
                $this->query(
                    'CREATE TABLE voces_comments (
                        sequence bigint GENERATED ALWAYS AS IDENTITY,
                        id text PRIMARY KEY,
                        name text NOT NULL,
                        role text NOT NULL,
                        message text NOT NULL,
                        created_at timestamptz NOT NULL
                    )'
                );

                foreach (array_reverse($legacy->all()) as $comment) {
                    $this->insert($comment['id'], $comment, $comment['created_at']);
                }
            }
            $this->query('COMMIT');
        } catch (Throwable $error) {
            @pg_query($this->connection, 'ROLLBACK');
            throw $error;
        }
    }

    private function tableExists(): bool
    {
        return pg_fetch_result($this->query("SELECT to_regclass('voces_comments') IS NOT NULL"), 0, 0) === 't';
    }

    /** @param array{name: string, role: string, message: string} $data */
    private function insert(string $id, array $data, string $createdAt): void
    {
        $this->query(
            'INSERT INTO voces_comments (id, name, role, message, created_at) VALUES ($1, $2, $3, $4, $5)',
            [$id, $data['name'], $data['role'], $data['message'], $createdAt]
        );
    }

    /** @param list<string> $params */
    private function query(string $sql, array $params = []): PgSql\Result
    {
        $result = @pg_query_params($this->connection, $sql, $params);
        if ($result === false) {
            throw new RuntimeException('No fue posible completar la operación de comentarios en PostgreSQL.');
        }

        return $result;
    }
}

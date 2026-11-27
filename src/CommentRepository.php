<?php

declare(strict_types=1);

final class CommentRepository
{
    public function __construct(private readonly string $filePath)
    {
    }

    /** @return list<array<string, string>> */
    public function all(): array
    {
        $comments = array_reverse($this->readForDisplay());
        usort($comments, static fn (array $a, array $b): int => strtotime($b['created_at']) <=> strtotime($a['created_at']));

        return $comments;
    }

    /** @return list<array<string, string>> */
    private function readForDisplay(): array
    {
        // Windows cannot replace a snapshot while another PHP process has it open.
        if (PHP_OS_FAMILY !== 'Windows' || !file_exists($this->filePath)) {
            return $this->readSnapshot();
        }

        $lock = $this->openLock();
        try {
            if (!flock($lock, LOCK_SH)) {
                throw new RuntimeException('No fue posible bloquear los comentarios para leerlos.');
            }
            return $this->readSnapshot();
        } finally {
            fclose($lock);
        }
    }

    /** @return list<array<string, string>> */
    private function readSnapshot(): array
    {
        // Writers publish a complete snapshot with rename(). Open readers retain their snapshot.
        if (!file_exists($this->filePath)) {
            return [];
        }

        $contents = @file_get_contents($this->filePath);
        if ($contents === false) {
            throw new RuntimeException('No fue posible leer los comentarios.');
        }

        $comments = json_decode($contents, false, 512, JSON_THROW_ON_ERROR);
        if (!is_array($comments)) {
            throw new RuntimeException('Formato de comentarios inválido.');
        }

        foreach ($comments as &$comment) {
            foreach (['id', 'name', 'role', 'message', 'created_at'] as $field) {
                if (!is_string($comment->$field ?? null)) {
                    throw new RuntimeException('Registro de comentario inválido.');
                }
            }
            $comment = (array) $comment;
        }
        unset($comment);

        return $comments;
    }

    /** @return resource */
    private function openLock()
    {
        $lock = @fopen($this->filePath . '.lock', 'c');
        if ($lock === false) {
            throw new RuntimeException('No fue posible abrir el bloqueo de comentarios.');
        }

        return $lock;
    }

    /** @param array{name: string, role: string, message: string} $data */
    public function add(array $data): void
    {
        $directory = dirname($this->filePath);
        if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('No fue posible crear el directorio de comentarios.');
        }

        // The lock belongs to a separate file so replacing the snapshot cannot replace the lock.
        $lock = $this->openLock();
        $temporaryPath = null;
        try {
            if (!flock($lock, LOCK_EX)) {
                throw new RuntimeException('No fue posible bloquear los comentarios.');
            }

            $comments = $this->readSnapshot();
            $comments[] = [
                'id' => bin2hex(random_bytes(16)),
                ...$data,
                'created_at' => date(DATE_ATOM),
            ];
            $json = json_encode($comments, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . PHP_EOL;

            $temporaryPath = @tempnam($directory, '.comments-');
            if ($temporaryPath === false || dirname($temporaryPath) !== realpath($directory)
                || @file_put_contents($temporaryPath, $json) !== strlen($json)
                || !@rename($temporaryPath, $this->filePath)) {
                throw new RuntimeException('No fue posible guardar los comentarios.');
            }
            $temporaryPath = null;
        } finally {
            if (is_string($temporaryPath)) {
                @unlink($temporaryPath);
            }
            fclose($lock);
        }
    }
}


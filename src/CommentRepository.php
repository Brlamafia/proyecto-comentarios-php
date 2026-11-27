<?php

declare(strict_types=1);

final class CommentRepository
{
    public function __construct(private readonly string $filePath)
    {
        $directory = dirname($this->filePath);

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        if (!file_exists($this->filePath)) {
            file_put_contents($this->filePath, "[]\n", LOCK_EX);
        }
    }

    /** @return array<int, array<string, string>> */
    public function all(): array
    {
        $handle = fopen($this->filePath, 'c+');

        if ($handle === false) {
            throw new RuntimeException('No fue posible abrir el archivo de comentarios.');
        }

        try {
            flock($handle, LOCK_SH);
            rewind($handle);
            $contents = stream_get_contents($handle) ?: '[]';
            $comments = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }

        if (!is_array($comments)) {
            return [];
        }

        usort($comments, static fn (array $a, array $b): int => strcmp($b['created_at'], $a['created_at']));

        return $comments;
    }

    /** @param array{name: string, role: string, message: string} $data */
    public function add(array $data): void
    {
        $handle = fopen($this->filePath, 'c+');

        if ($handle === false) {
            throw new RuntimeException('No fue posible guardar el comentario.');
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                throw new RuntimeException('El archivo está ocupado. Intenta nuevamente.');
            }

            rewind($handle);
            $contents = stream_get_contents($handle) ?: '[]';
            $comments = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
            $comments = is_array($comments) ? $comments : [];

            $comments[] = [
                'id' => bin2hex(random_bytes(8)),
                'name' => $data['name'],
                'role' => $data['role'],
                'message' => $data['message'],
                'created_at' => date(DATE_ATOM),
            ];

            rewind($handle);
            ftruncate($handle, 0);
            fwrite($handle, json_encode($comments, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . PHP_EOL);
            fflush($handle);
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }
}


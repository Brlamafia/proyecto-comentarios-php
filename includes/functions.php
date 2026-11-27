<?php

declare(strict_types=1);

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function old(string $key): string
{
    return escape((string) ($_SESSION['form_data'][$key] ?? ''));
}

function initials(string $name): string
{
    $words = preg_split('/\s+/u', trim($name)) ?: [];
    $letters = '';

    foreach (array_slice($words, 0, 2) as $word) {
        $firstLetter = function_exists('mb_substr') ? mb_substr($word, 0, 1) : substr($word, 0, 1);
        $letters .= function_exists('mb_strtoupper') ? mb_strtoupper($firstLetter) : strtoupper($firstLetter);
    }

    return $letters !== '' ? $letters : 'V';
}

function commentField(mixed $value): string
{
    if (!is_string($value)) {
        return '';
    }

    return preg_replace('/^\s+|\s+$/u', '', $value) ?? $value;
}

function commentLength(string $value): int
{
    if (str_contains($value, "\0")) {
        return 0;
    }

    $length = preg_match_all('/./us', $value);
    return $length === false ? 0 : $length;
}

function formatDate(string $date): string
{
    $timestamp = strtotime($date);

    if ($timestamp === false) {
        return 'Fecha no disponible';
    }

    return date('d/m/Y · h:i A', $timestamp);
}

/** @param array<string, string> $input
 *  @return array<string, string>
 */
function validateComment(array $input): array
{
    $errors = [];
    $nameLength = commentLength($input['name']);
    $messageLength = commentLength($input['message']);

    if ($nameLength < 2 || $nameLength > 60) {
        $errors['name'] = 'Escribe un nombre de 2 a 60 caracteres.';
    }

    if (!in_array($input['role'], ['Estudiante', 'Docente', 'Visitante'], true)) {
        $errors['role'] = 'Selecciona un tipo de participante válido.';
    }

    if ($messageLength < 10 || $messageLength > 500) {
        $errors['message'] = 'El comentario debe contener entre 10 y 500 caracteres.';
    }

    return $errors;
}


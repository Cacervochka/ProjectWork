<?php
function scheduleDayLabel(string $date): string
{
    $today = new DateTimeImmutable('today');
    $target = new DateTimeImmutable($date);
    $diff = (int) $today->diff($target)->format('%r%a');

    if ($diff === 0) {
        return 'TODAY';
    }

    if ($diff === 1) {
        return 'TOMORROW';
    }

    return strtoupper($target->format('l'));
}

function scheduleDayKey(string $date): string
{
    $today = new DateTimeImmutable('today');
    $target = new DateTimeImmutable($date);
    $diff = (int) $today->diff($target)->format('%r%a');

    if ($diff === 0) {
        return 'date.today';
    }

    if ($diff === 1) {
        return 'date.tomorrow';
    }

    return 'date.' . strtolower($target->format('l'));
}

function fetchScheduleDateTabs(PDO $pdo): array
{
    $stmt = $pdo->prepare(
        'SELECT DATE(show_time) AS schedule_date
         FROM schedules
         WHERE show_time >= NOW()
         GROUP BY DATE(show_time)
         ORDER BY schedule_date ASC'
    );
    $stmt->execute();

    $tabs = [];
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $date) {
        $dateTime = new DateTimeImmutable($date);
        $tabs[] = [
            'label' => scheduleDayLabel($date),
            'key' => scheduleDayKey($date),
            'date' => $date,
            'display' => $dateTime->format('d. m.'),
        ];
    }

    return $tabs;
}

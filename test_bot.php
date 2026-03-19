<?php
$token = '8692416300:AAH03ArJ3vdhbZMSh3CDr9ew6yYfozSS1IE';
$offset = 0;

echo "Начинаю long polling...\n";

while (true) {
    $url = "https://api.telegram.org/bot$token/getUpdates?offset=$offset&timeout=30";
    $response = file_get_contents($url);
    if ($response === false) {
        echo "Ошибка запроса\n";
        sleep(5);
        continue;
    }

    $data = json_decode($response, true);
    if (isset($data['result'])) {
        foreach ($data['result'] as $update) {
            $offset = $update['update_id'] + 1;
            $message = $update['message']['text'] ?? '[не текст]';
            $chat = $update['message']['chat']['type'] ?? 'unknown';
            $from = $update['message']['from']['first_name'] ?? 'unknown';
            echo "[$chat] $from: $message\n";
        }
    }

    // Небольшая пауза перед следующим запросом (необязательно)
    // sleep(1);
}

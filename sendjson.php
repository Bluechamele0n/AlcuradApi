<?php
$url = "http://localhost:8080/tor%20api/php/alcuradapi.php";

$data = [
    "request" => "listKeys",
    "userId" => "admin",
    "key" => "admin",
    "password" => "admin"
];

$ch = curl_init($url);
$payload = json_encode($data);

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

echo "Response from API:\n$response\n";

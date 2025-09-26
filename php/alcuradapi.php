<?php
include 'requests.php';

header("Content-Type: application/json");

// 1. Get JSON input
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Handle invalid JSON
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid JSON"]);
    exit;
}

// 2. Extract params with defaults
$request       = $data["request"]       ?? null;
$requestedPage = $data["requestedPage"] ?? null;
$userId        = $data["userId"]        ?? null;
$lang          = $data["lang"]          ?? "all";
$password      = $data["password"]      ?? null;
$key           = $data["key"]           ?? null;

// 3. Call your main handler
$result = getcontent($request, $requestedPage, $userId, $lang, $password, $key);

// 4. Output the result (always JSON)
echo json_encode($result, JSON_PRETTY_PRINT);

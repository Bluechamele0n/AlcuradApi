<?php

include 'util.php';
include 'dokumenthandler.php';
include 'pagehandler.php';
include 'languagehandler.php';
include 'profilehandler.php';


header("Content-Type: application/json");

echo json_encode([
    "status" => "success",
    "message" => "This is a placeholder response from requests.php"
]);



function getcontent($request = null,$requestedPage = null, $userId = null, $lang = "all", $password = null, $key = null) {
    $content = parse_ini_file(__DIR__ . "/../content.ini", true, INI_SCANNER_RAW);
    


    // get all keys in content
    if ($key === null or $key === '') {
        http_response_code(400);
        return ["error" => "Missing 'key' parameter."];
    } elseif ($request === "listKeys" && $key === "admin") {
        foreach ($content as $section => $data) {
            if (isset($data['key']) and $data['key'] === $key) {
                // Found the key, return the section name
                return ["key" => $section];
            }
        }
    } else {
        // if password for userid exists in content.ini and matches set $correctPassword = true
        if ($password === null or $password === '' and $userId === null or $userId === '') {
            http_response_code(400);
            return ["error" => "wrong 'password' or userid parameter."];
        } else {
            if (isset($content[$userId]) and isset($content[$userId]['password']) and $content[$userId]['password'] === $password) {
                $correctPassword = true;
            } else {
                $correctPassword = false;
                http_response_code(403);
                return ["error" => "Invalid 'password' or 'userId'."];
            }
        }
        
        
        if ($request === null or $request === '' or $correctPassword === false) {
            http_response_code(400);
            return ["error" => "Missing 'request' parameter."];
        } elseif ($correctPassword === true) {
            if ($request === "getDocument") { getPage($requestedPage, $userId, $content, $lang);
            } elseif ($request === "listLanguages") {
                hasLang($userId, true);
            } elseif ($request === "addLanguage" && $lang !== null and $lang !== '') {
                if ($lang === "all") {
                    http_response_code(400);
                    return ["error" => "Cannot add 'all' as a language."];
                } elseif (strlen($lang) !== 3) {
                    http_response_code(400);
                    return ["error" => "Language code must be 3 characters."];
                } elseif (!preg_match('/^[a-zA-Z]{3}$/', $lang)) {
                    http_response_code(400);
                    return ["error" => "Language code must be 3 alphabetic characters."];
                } elseif ($lang === "" or $lang === null) {
                    http_response_code(400);
                    return ["error" => "Language code cannot be empty."];
                } else {addlang($userId, $lang);}
            } elseif ($request === "removeLanguage") {
                removeLang($userId, $lang, true);
            } elseif ($request === "listDocuments") {
                listDocuments($userId, $password);
            } elseif ($request === "updateDocument") {
                updateDocument($requestedPage, $userId);
            } elseif ($request === "addDocument") {
                addDocument($requestedPage, $userId, true);
            } elseif ($request === "removeDocument") {
                removeDocument($requestedPage, $userId, true);
            } elseif ($request === "removeUser") { 
                removeUser($userId);
            } else {
                http_response_code(400);
                return ["error" => "Unknown request: $request"];
            } 
        
        } elseif ($request === "listUsers") {listUsers();
        } elseif ($request === "addUser") {
            if ($userId === null or $userId === '' or $password === null or $password === '') {
                http_response_code(400);
                return ["error" => "Missing 'userId' or 'password' parameter."];
            } else { addUserWithPasswordAndOrglanguages($userId, $password);}
        } else {
            http_response_code(400);
            return ["error" => "Unknown request: $request"];
        } 
    }
}

function listUsers() {
    global $content;
    $users = array_keys($content);
    return ["users" => $users];
}


function getPage($requestedPage, $userId, $content, $lang) {
    if ($requestedPage === null or $requestedPage === '') {
        http_response_code(400);
        return ["error" => "Missing 'requestedPage' parameter."];
    }
    if (!isset($content[$userId][$requestedPage])) {
        http_response_code(404);
        return ["error" => "Document not found for user."];
    }
    $docContent = $content[$userId][$requestedPage];
    if (is_string($docContent)) $docContent = json_decode($docContent, true);
    if (!is_array($docContent)) {
        http_response_code(500);
        return ["error" => "Invalid document format."];
    }

    // Build map of lang => blocks preserving order of languages
    $langOrder = [];
    $langToBlocks = [];
    foreach ($docContent as $langObj) {
        if (!is_array($langObj)) continue;
        foreach ($langObj as $k => $v) {
            $langOrder[] = $k;
            $langToBlocks[$k] = is_array($v) ? $v : [];
        }
    }

    if ($lang === "all") {
        return ["document" => $docContent];
    } else {
        if (!isset($langToBlocks[$lang])) {
            http_response_code(404);
            return ["error" => "Language not found in document."];
        }
        return ["document" => [$lang => $langToBlocks[$lang]]];
    }
}


// functions left are listDocuments, updateDocument, addDocument, removeDocument, addUserWithPasswordAndOrglanguages, removeUser
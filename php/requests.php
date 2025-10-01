<?php

include __DIR__.'/pagehandler.php';
// include __DIR__.'/languagehandler.php';


header("Content-Type: application/json");





function getcontent($request = null,$requestedPage = null, $userId = null, $lang = "all", $password = null, $key = null) {
    $content = parse_ini_file(__DIR__ . "/../content.ini", true, INI_SCANNER_RAW);
    
    

    // get all keys in content
    if ($key === null or $key === '') {
        http_response_code(400);
        return ["error" => "Missing 'key' field."];
    } elseif ($request === "listKeys" && $key === "admin") {
        foreach ($content as $section => $data) {
            if (isset($data['key']) and $data['key'] === $key) {
                // Found the key, return the section name
                return ["key" => $section];
            }
        }
    } 
 
    if ($request === null or $request === '') {
        http_response_code(400);
        return ["error" => "Missing 'request' field."];
    } else {
        if ($request === "getDocument") { 
            $profileLogin = passwordAndKeyController($key, $userId, $password);
            $userId = $profileLogin[0];
            $password = $profileLogin[1];
            getPage($requestedPage, $userId, $content, $lang);
        } elseif ($request === "updateDocument") {
            $profileLogin = passwordAndKeyController($key, $userId, $password);
            $userId = $profileLogin[0];
            $password = $profileLogin[1];

            updateDocument($requestedPage, $lang ,$userId, $password);
        } elseif ($request === "addDocument") {
            $profileLogin = passwordAndKeyController($key, $userId, $password);
            $userId = $profileLogin[0];
            $password = $profileLogin[1];
            addNewDocument($userId, $requestedPage, true);
        } elseif ($request === "removeDocument") {
            $profileLogin = passwordAndKeyController($key, $userId, $password);
            $userId = $profileLogin[0];
            $password = $profileLogin[1];
            removeDocument($requestedPage, $userId, true, $lang);
        } elseif ($request === "listLanguages") {
            hasLang($userId, true);
        } elseif ($request === "addLanguage" && $lang !== null and $lang !== '') {
            $profileLogin = passwordAndKeyController($key, $userId, $password);
            $userId = $profileLogin[0];
            $password = $profileLogin[1];
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
            $profileLogin = passwordAndKeyController($key, $userId, $password);
            $userId = $profileLogin[0];
            $password = $profileLogin[1];
            removeLang($userId, $lang, true);
        } elseif ($request === "listDocuments") {
            listDocuments($userId, $lang);
        } elseif ($request === "listUsers") {
            listUsers();
        } elseif ($request === "addUser") {
            if ($userId === null or $userId === '' or $password === null or $password === '') {
                http_response_code(400);
                return ["error" => "Missing 'userId' or 'password' field."];
            } else { addUserWithPasswordAndOrglanguages($userId, $password);}
        } elseif ($request === "removeUser") { 
            removeUser($userId);
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
        return ["error" => "Missing 'requestedPage' field."];
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

function listDocuments($userId = null, $lang = "all") {
    global $content;
    // if userid == null list all documents for all users else only for that user
    if ($userId === null or $userId === '') {
        $allDocs = [];
        foreach ($content as $section => $data) {
            if (isset($data['key']) or isset($data['password'])) continue; // skip keys and passwords
            $allDocs[$section] = array_keys($data);
        }
        return ["documents" => $allDocs];
    } else {
        if (!isset($content[$userId])) {
            http_response_code(404);
            return ["error" => "User ID not found."];
        }
        $userDocs = array_keys($content[$userId]);
        // remove 'password' and 'languages' from list
        $userDocs = array_filter($userDocs, function($doc) {
            return $doc !== 'password' && $doc !== 'languages';
        });
        return ["documents" => [$userId => array_values($userDocs)]];
    }
}



function passwordAndKeyController($key = null, $userId = null, $password = null) {
    // check if key is valid and converts it to a userId and password if not key provided check if userId and password are valid
    global $content;
    if ($key !== null and $key !== '') {
        foreach ($content as $section => $data) {
            if (isset($data['key']) and $data['key'] === $key) {
                // Found the key, return the section name
                return [$section, $data['password']];
            }
        }
        http_response_code(403);
        return ["error" => "Invalid 'key'."];
    } else {
        if ($userId === null or $userId === '' or $password === null or $password === '') {
            http_response_code(400);
            return ["error" => "Missing 'userId' or 'password' field."];
        } else {
            if (isset($content[$userId]) and isset($content[$userId]['password']) and $content[$userId]['password'] === $password) {
                return [$userId, $password];
            } else {
                http_response_code(403);
                return ["error" => "Invalid 'userId' or 'password'."];
            }
        }
    }
}
// functions left are listDocuments, updateDocument, addDocument, removeDocument, addUserWithPasswordAndOrglanguages, removeUser
<?php

include __DIR__.'/pagehandler.php';
// include __DIR__.'/languagehandler.php';


header("Content-Type: application/json");





function getcontent($request = null,$requestedPage = null, $userId = null, $lang = "all", $password = null, $key = null, $newContent = null) {
    // Load the INI file with sections and raw values
    $content = parse_ini_file(__DIR__ . "/../content.ini", true, INI_SCANNER_RAW);
 
    if ($request === null or $request === '') {
        http_response_code(400);
        return ["error" => "Missing 'request' field."];
    } else {
        if ($request === "getDocument") { 
            $profileLogin = passwordAndKeyController($key, $userId, $password);
            $userId = $profileLogin[0];
            $password = $profileLogin[1];
            $response = getPage($requestedPage, $userId, $content, $lang);
            //var_dump($response);
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        } elseif ($request === "updateDocument") {
            if ($newContent === null or $newContent === '') {
                http_response_code(400);
                return ["error" => "Missing 'newContent' field."];
            }

            $profileLogin = passwordAndKeyController($key, $userId, $password);
            $userId = $profileLogin[0];
            $password = $profileLogin[1];

            updateDocument($requestedPage, $lang ,$userId, $newContent);
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
            return listLanguages($userId, $lang, true);
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
            return listUsers($userId);
        } elseif ($request === "addUser") {
            if ($userId === null or $userId === '' or $password === null or $password === '') {
                http_response_code(400);
                return ["error" => "Missing 'userId' or 'password' field."];
            } else {$key = addUserWithPasswordAndOrglanguages($userId, $password);
                return $key;
            }
        } elseif ($request === "removeUser") { 

            $profileLogin = passwordAndKeyController($key, $userId, $password);
            $userId = $profileLogin[0];
            $password = $profileLogin[1];
            // return they key 
            //return ["key" => $key, "userId" => $userId, "password" => $password];
            if (ifKeyExists($userId, $key) === false) {
                http_response_code(403);
                return ["error" => "Only User with thier key can remove users."];
            }
            removeUser($userId);
        } else {
            http_response_code(400);
            return ["error" => "Unknown request: $request"];
        }
    } 
    
}

function ifKeyExists($userId = null, $key = null) {
    global $content;
    if ($userId === null or $userId === '' or $key === null or $key === '') {
        return false;
    }

    if (!isset($content[$userId]) || !isset($content[$userId]['key'])) {
        return false;
    }

    // Normalize and trim both sides
    $stored = trim(mb_convert_encoding($content[$userId]['key'], 'UTF-8', 'UTF-8'));
    $given  = trim(mb_convert_encoding($key, 'UTF-8', 'UTF-8'));

    return $stored === $given;
}




function listUsers($userId = null) {
    global $content;
    $users = [];
    if ($userId === null or $userId === '') {
        foreach ($content as $section => $data) {
            if (isset($data['password']) and isset($data['key'])) {
                $users[] = ["userId" => $section];
            }
        }
    } else {
        if (isset($content[$userId]) and isset($content[$userId]['password']) and isset($content[$userId]['key'])) {
            return ["exists" => true];
        } else {
            return ["exists" => false];
        }
    }
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
    $documents = [];
    if ($userId === null or $userId === '') {
        foreach ($content as $section => $data) {
            foreach ($data as $key => $value) {
                if ($key !== 'password' and $key !== 'key') {
                    if ($lang === "all") {
                        $documents[] = ["userId" => $section, "document" => $key];
                    } else {
                        $docContent = json_decode($value, true);
                        if (is_array($docContent)) {
                            foreach ($docContent as $langObj) {
                                if (isset($langObj[$lang])) {
                                    $documents[] = ["userId" => $section, "document" => $key];
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
    } else {
        if (!isset($content[$userId])) {
            http_response_code(404);
            return ["error" => "User not found."];
        }
        foreach ($content[$userId] as $key => $value) {
            if ($key !== 'password' and $key !== 'key') {
                if ($lang === "all") {
                    $documents[] = ["userId" => $userId, "document" => $key];
                } else {
                    $docContent = json_decode($value, true);
                    if (is_array($docContent)) {
                        foreach ($docContent as $langObj) {
                            if (isset($langObj[$lang])) {
                                $documents[] = ["userId" => $userId, "document" => $key];
                                break;
                            }
                        }
                    }
                }
            }
        }
    }
    return ["documents" => $documents];
    
}



function passwordAndKeyController($key = null, $userId = null, $password = null) {
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


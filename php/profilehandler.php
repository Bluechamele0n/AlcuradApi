<?php
$content = parse_ini_file(__DIR__ . "/../content.ini", true, INI_SCANNER_RAW);
if ($content === false) exit("Error: Unable to load content.ini file.");


function existingUsers() {
    global $content;
    return array_keys($content);
}


function addUser($newUserId) {
    global $content;

    // Check if the user already exists
    if (isset($content[$newUserId])) {
        echo "Error: User ID already exists.";
        return;
    }

    // Add the new user section
    $newSection = "[$newUserId]\n";

    // Append the new section to the content.ini file
    file_put_contents(__DIR__ . "/../content.ini", $newSection, FILE_APPEND);

    // Reload the content array
    $content = parse_ini_file(__DIR__ . "/../content.ini", true, INI_SCANNER_RAW);

    //echo "User ID " . htmlspecialchars($newUserId) . " added successfully.";
}


// Append-only add: create new section with password without touching other entries
function addUserWithPasswordAndOrglanguages($newUserId, $newPassword) {
    global $content;

    if (isset($content[$newUserId])) {
        echo "Error: User ID already exists.";
        return false;
    }
    // do random japanese characters as key that not already exists and is 32 chars long and turn into a string

    $key = generateUniqueKey($content);
    if ($key === null) {
        echo "Error: Unable to generate a unique key.";
        return false;
    }

    // Build append text: section + password only
    $append = "\n[{$newUserId}]\n"
    . "key = \"" . str_replace(["\n", "\r"], '', (string)$key) . "\"\n"
    . "password = \"" . str_replace(["\n", "\r"], '', (string)$newPassword) . "\"\n"
    . "languages = " . json_encode(['eng', 'swe'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";

    file_put_contents(__DIR__ . "/../content.ini", $append, FILE_APPEND);

    // Reload RAW
    $content = parse_ini_file(__DIR__ . "/../content.ini", true, INI_SCANNER_RAW);
   // echo "User ID " . htmlspecialchars($newUserId) . " added successfully.";
    // return the key in json format
    return ["key" => $key];
}

function generateUniqueKey($content) {
    // Allowed character ranges (Hiragana, Katakana, Kanji) – precomposed only
    $chars = [];

    // Hiragana (U+3041 – U+3096, skipping combining marks U+3099–U+309C)
    foreach (range(0x3041, 0x3096) as $code) {
        $chars[] = mb_chr($code, 'UTF-8');
    }

    // Katakana (U+30A1 – U+30FA)
    foreach (range(0x30A1, 0x30FA) as $code) {
        $chars[] = mb_chr($code, 'UTF-8');
    }
    // Add Katakana small KE (ヵ) and small KA (ヶ)
    $chars[] = mb_chr(0x30F5, 'UTF-8'); // ヵ
    $chars[] = mb_chr(0x30F6, 'UTF-8'); // ヶ

    // Full-width prolonged sound mark (ー), iteration mark (々), middle dot (・)
    $chars[] = 'ー';
    $chars[] = '々';
    $chars[] = '・';

    // Kanji (basic CJK unified ideographs U+4E00 – U+9FFF)
    foreach (range(0x4E00, 0x9FFF) as $code) {
        $chars[] = mb_chr($code, 'UTF-8');
    }

    // Collect all existing keys
    $existingKeys = [];
    foreach ($content as $userId => $userData) {
        if (isset($userData['key'])) {
            $existingKeys[] = (string)$userData['key'];
        }
    }

    // Generate until unique
    do {
        $newKey = '';
        for ($i = 0; $i < 32; $i++) {
            $newKey .= $chars[random_int(0, count($chars) - 1)];
        }
    } while (in_array($newKey, $existingKeys, true));

    return $newKey;
}



function removeUser($userId) {
    $content = parse_ini_file(__DIR__ . "/../content.ini", true, INI_SCANNER_RAW);
    if ($content === false) {
        echo "Error: Unable to load content.ini file.";
        return;
    }
    if (!isset($content[$userId])) {
        echo "Error: User ID does not exist.";
        return;
    }

    // Convert JSON-looking strings back to arrays
    foreach ($content as $section => &$docs) {
        foreach ($docs as $key => &$value) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            }
        }
    }

    // Remove the user section
    unset($content[$userId]);
    //var_dump($content);
    writeIni($content);
    //var_dump($content);
    echo "User ID " . htmlspecialchars($userId) . " removed successfully.";
    
}



function updateUserPassword($userId, $newPassword) {
    global $content;

    if (!isset($content[$userId])) {
        echo "Error: User ID does not exist.";
        return false;
    }

    // Update the password in the content array
    $content[$userId]['password'] = str_replace(["\n", "\r"], '', (string)$newPassword);

    writeIni($content);

    echo "Password for User ID " . htmlspecialchars($userId) . " updated successfully.";
    return true;
}
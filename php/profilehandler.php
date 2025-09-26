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

    // Build append text: section + password only
    $append = "\n[{$newUserId}]\npassword = \"" . str_replace(["\n", "\r"], '', (string)$newPassword) . "\"\n"  . "languages = " . json_encode(['eng', 'sve'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
    file_put_contents(__DIR__ . "/../content.ini", $append, FILE_APPEND);

    // Reload RAW
    $content = parse_ini_file(__DIR__ . "/../content.ini", true, INI_SCANNER_RAW);
   // echo "User ID " . htmlspecialchars($newUserId) . " added successfully.";
    return true;
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

    writeIni($content);

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
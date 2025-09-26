<?php

$content = parse_ini_file(__DIR__ . "/../content.ini", true, INI_SCANNER_RAW);
if ($content === false) exit("Error: Unable to load content.ini file.");




function languageVersions($language) {
    global $content;

    // look for current user languages in content.ini
    $userId = isset($_POST['userId']) ? $_POST['userId'] : null;
    if ($userId === null || !isset($content[$userId])) {
        echo "Error: No user ID provided or user not found.";
        return;
    }
    $userSection = $content[$userId];
    $availableLanguages = [];
    // look for key called "languages" under users section
    if (isset($userSection['languages'])) {
        $langs = json_decode($userSection['languages'], true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($langs)) {
            $availableLanguages = $langs;
        }
    }
    // Render language selection dropdown
    echo '<form method="POST" action="">';
    echo '<label for="language">Select Language:</label>';
    echo '<select name="language" id="language" onchange="this.form.submit()">';
    foreach ($availableLanguages as $langCode) {
        $selected = ($langCode === $language) ? 'selected' : '';
        echo '<option value="' . htmlspecialchars($langCode) . '" ' . $selected . '>' . htmlspecialchars(strtoupper($langCode)) . '</option>';
    }
    echo '</select>';
    echo '<input type="hidden" name="userId" value="' . htmlspecialchars($userId) . '">';
    echo '</form>';
    return $availableLanguages;   
}


function addLang($userId, $newLangCode) {
    global $content;

    if (!isset($content[$userId])) {
        echo "Error: User ID does not exist.";
        return false;
    }

    // Get current languages
    $currentLangs = [];
    if (isset($content[$userId]['languages'])) {
        $currentLangs = json_decode($content[$userId]['languages'], true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($currentLangs)) {
            $currentLangs = [];
        }
    }

    
    
    // decode json inside INI
    foreach ($content as $section => &$docs) {
        foreach ($docs as $key => &$value) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            }
        }
    }

    // Add new language
    $currentLangs[] = $newLangCode;
    $content[$userId]['languages'] = json_encode($currentLangs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    // Write back to INI file
    writeIni($content);

    echo "Language " . htmlspecialchars($newLangCode) . " added successfully for User ID " . htmlspecialchars($userId) . ".";
    return true;
}

function whatLanguage() {
    if (isset($_POST['language'])) {
        $language = $_POST['language'];
        return $language;
    } else {
        return "eng"; // Default language
    }
}


// re
function removeLang($userId, $lang, $api = false) {
    global $content;

    if (!isset($content[$userId])) {
        if ($api) {
            http_response_code(400);
            return ["error" => "User ID does not exist."];
        } else {
            echo "Error: User ID does not exist.";
            return false;
        }
    }

    // Get current languages
    $currentLangs = [];
    if (isset($content[$userId]['languages'])) {
        $currentLangs = json_decode($content[$userId]['languages'], true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($currentLangs)) {
            $currentLangs = [];
        }
    }

    // decode json inside INI
    foreach ($content as $section => &$docs) {
        foreach ($docs as $key => &$value) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            }
        }
    }

    // Remove the language if it exists
    if (($key = array_search($lang, $currentLangs)) !== false) {
        unset($currentLangs[$key]);
        // Reindex array
        $currentLangs = array_values($currentLangs);
        $content[$userId]['languages'] = json_encode($currentLangs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // Write back to INI file
        writeIni($content);

        if ($api) {
            return ["status" => "Language " . htmlspecialchars($lang) . " removed successfully for User ID " . htmlspecialchars($userId) . "."];
        } else {
            echo "Language " . htmlspecialchars($lang) . " removed successfully for User ID " . htmlspecialchars($userId) . ".";
            return true;
        }
    } else {
        if ($api) {
            http_response_code(400);
            return ["error" => "Language not found for this user."];
        } else {
            echo "Error: Language not found for this user.";
            return false;
        }
    }

}


function hasLang($userId, $api = false) {
    global $content;

    if (!isset($content[$userId])) {
        if ($api) {
            http_response_code(400);
            return ["error" => "User ID does not exist."];
        } else {
            echo "Error: User ID does not exist.";
            return false;
        }
    }

    // Get current languages
    $currentLangs = [];
    if (isset($content[$userId]['languages'])) {
        $currentLangs = json_decode($content[$userId]['languages'], true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($currentLangs)) {
            $currentLangs = [];
        }
    }

    if ($api) {
        return ["languages" => $currentLangs];
    } else {
        echo "Languages for User ID " . htmlspecialchars($userId) . ": " . implode(", ", $currentLangs);
        return true;
    }
}
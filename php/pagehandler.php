<?php

$content = parse_ini_file(__DIR__ . "/../content.ini", true, INI_SCANNER_RAW);
if ($content === false) {
    echo "Error: Unable to load content.ini file.";
}
foreach ($content as $section => $docs) {
    foreach ($docs as $key => $value) {
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) $content[$section][$key] = $decoded;
    }
}


function page($page, $yourId = null, $docName = null) {
    global $content;

    // Check if the content is loaded
    if ($content === false) {
        echo "Error: Unable to load content.ini file.";
        return;
    }

    // Check if the section for the given user ID exists
    if ($page === "doc" && isset($content[$yourId]) && $docName !== null) {
        echo "<script>document.body.innerHTML = '';" . "</script>";
        $langId = isset($_POST['langId']) ? $_POST['langId'] : null;
        openDocument($docName, $yourId, $langId);
    } elseif ($page === "user" && isset($content[$yourId]) && $docName === null) {
        openUserPage($yourId);
    } elseif ($page === "home" && $yourId === null or $docName === null) {
        $_POST = ['homepage']; // to prevent re-submission
        homepage();
    } elseif ($page === "lang" && isset($content[$yourId]) && $docName === null) {
        languangeChoser($yourId);
    } else {
        echo "Error: Page not found.";
    }
}

function openUserPage($userid) {
    global $content;
    

    // Check if the content is loaded
    if ($content === false) {
        echo "Error: Unable to load content.ini file.";
        return;
    }

    // Check if the section for the given $userid exists
    if (!isset($content[$userid])) {
        echo "Error: No section found for User ID: " . htmlspecialchars($userid);
        return;
    }

    // Get the keys and values from the section matching $userid
    $userSection = $content[$userid];

    // Stop rendering homepage content
    echo "<script>document.body.innerHTML = '';</script>";

    // Render user page content
    echo "<h1>User Page</h1>";
    echo "<p>Welcome, User ID: " . htmlspecialchars($userid) . "</p>";
    languangeChoser($userid);
    echo '<p>Add language: '. whatLanguage() .'</p>';
    echo "<form method='POST' action=''>";
    echo "<input type='text' name='newLangCode' placeholder='New Language Code'>";
    echo "<input type='hidden' name='userId' value='" . htmlspecialchars($userid) . "'>";
    echo "<button type='submit' name='addLang'>Add Language</button>";
    echo "</form>";
    echo "<p>Choose your document:</p>";
    echo "<div>";
    echo "<form method='POST' action=''>";
    // Generate buttons for each key in the user's section
    echo "<input type='hidden' name='userId' value='" . htmlspecialchars($userid) . "'>";
    if (isset($_POST['langId'])) echo "<input type='hidden' name='langId' value='" . htmlspecialchars($_POST['langId']) . "'>";
    // will not show first key in ini file as it is an password placeholder
    foreach ($userSection as $key => $value) {
        if ($key === "password") continue; // Skip the password key
        if ($key === "languages") continue; // Skip the languages key
        echo "<button type='submit' name='docButton' value='" . htmlspecialchars($key) . "'>" . htmlspecialchars($key) . "</button><br>";
    }
    echo "<br><button type='submit' name='logoutButton'>Log Out</button>";
    echo $docToAdd = "<br><input type='text' name='newDocName' placeholder='New Document Name'>";
    echo "<br><button type='submit' name='addDocButton', value='" . htmlspecialchars($docToAdd). "'>Add New Document</button>";
    echo $docToRemove = "<br><input type='text' name='removeDocName' placeholder='Document Name to Remove'>";
    echo "<br><button type='submit' name='removeDocButton' value='". htmlspecialchars($docToRemove) ."'>Remove Document</button>";
    echo "<br><button type='submit' name='accountRemoveButton' style='color:white background-color: crimson'>Remove account</button>";

    echo "</form>";
    echo "</div>";
}




function languangeChoser($userId) {
    global $content;
    // look for current user languages in content.ini
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
    // make a selection for language and make the selected one be in $_POST['langId']
    $language = isset($_POST['langId']) ? $_POST['langId'] : (isset($availableLanguages[0]) ? $availableLanguages[0] : null);
    // Render language selection dropdown
    echo '<form method="POST" action="">';
    echo '<label for="language">Select Language:</label>';
    echo '<select name="langId" id="language" onchange="this.form.submit()">';
    foreach ($availableLanguages as $langCode) {
        $selected = ($langCode === $language) ? 'selected' : '';
        echo '<option value="' . htmlspecialchars($langCode) . '" ' . $selected . '>' . htmlspecialchars(strtoupper($langCode)) . '</option>';
    }
    echo '<input type="hidden" name="userId" value="' . htmlspecialchars($userId) . '">';
    echo '</select>';
    echo '</form>';
    
    
}




function homepage() {
    global $content;
    //print_r($content);
    echo "Welcome to the homepage!";
    echo "<div>";
    echo "<form method='POST' action=''>";
    echo "<input type='text' name='userName' placeholder='Username'>";
    echo "<br>";
    echo "<input type='password' name='password' placeholder='Password'>";
    echo "<br>";
    echo "<button type='submit' name='loginButton'>Login</button>";
    echo "<button type='submit' name='registerButton'>Register</button>";
    echo "<button type='submit' name='changePasswordButton'>Change Password</button>";
    echo "</form>";
    echo "</div>";

    requests();
}


function requests() {
    global $content;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['docButton'])) {
        $selectedDoc = htmlspecialchars($_POST['docButton']);
        $userId = htmlspecialchars($_POST['userId']);
        $langId = isset($_POST['langId']) ? htmlspecialchars($_POST['langId']) : null;
        echo "<p>You selected document: " . $selectedDoc . "</p>";
        // Preserve selected language when opening document
        $_POST = $langId ? ['langId' => $langId] : [];
        page("doc", $userId, $selectedDoc);
        // Add code here to handle the selected document
    }elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addDocButton'])) {
        $newDocName = htmlspecialchars($_POST['newDocName']);
        $userId = htmlspecialchars($_POST['userId']);
        if (!empty($newDocName)) {
            addNewDocument($userId, $newDocName);
            $_POST = [];
        } else {
            echo "Error: Document name cannot be empty.";
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['removeDocButton'])) {
        $removeDocName = htmlspecialchars($_POST['removeDocName']);
        $userId = htmlspecialchars($_POST['userId']);
        if (!empty($removeDocName)) {
            removeDocument($userId, $removeDocName);
            $_POST = [];
        } else {
            echo "Error: Document name to remove cannot be empty.";
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['saveDoc'])) {
        $userId = htmlspecialchars($_POST['userId']);
        $docName = htmlspecialchars($_POST['docName']);
        $langId = isset($_POST['langId']) ? htmlspecialchars($_POST['langId']) : null;
        $_POST = ['langId' => $langId];
        openDocument($docName, $userId, $langId);
        $_POST = []; 
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['backDocButton'])) {
        $userId = htmlspecialchars($_POST['userId']);
        page("user", $userId);
        $_POST = [];
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accountRemoveButton'])) {
        $userId = htmlspecialchars($_POST['userId']);
        removeUser($userId);
        page("home");
        $_POST = [];
    
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logoutButton'])) {
        page("home");
        $_POST = [];


    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['homepage']) || empty($_POST)) {
        echo "Welcome to this page:<br>";
        $content = parse_ini_file(__DIR__ . "/../content.ini", true);
        foreach ($content as $section => $docs) {
            echo $section . "\n";
            echo "<br>";
        }
        $_POST = ['homepage']; // to prevent re-submission
        page("home");
    }


    if // add lang
    ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addLang'])) {
        $userId = htmlspecialchars($_POST['userId']);
        $newLangCode = htmlspecialchars($_POST['newLangCode']);
        if (!empty($newLangCode)) {
            if (function_exists('addLang')) {
                addLang($userId, $newLangCode);
            } else {
                echo "Error: addLang function not found.";
            }
        } else {
            echo "Error: Language code cannot be empty.";
        }
        page("user", $userId);
        $_POST = []; // to prevent re-submission
    }

    // when chosen lang go back to user page
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['langId'])) {
        $userId = htmlspecialchars($_POST['userId']);
        page("user", $userId);
    }


    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registerButton'])) {
        print_r($content);
        $newUserId = htmlspecialchars($_POST['userName']);
        $newPassword = htmlspecialchars($_POST['password']);
        if (!empty($newUserId) && !empty($newPassword)) {
            if (isset($content[$newUserId])) {
                echo "Error: User ID already exists.";
                return;
            }
            // Append-only create section with password to avoid mutating other keys
            if (function_exists('addUserWithPasswordAndOrglanguages')) {
                addUserWithPasswordAndOrglanguages($newUserId, $newPassword);
            } else {
                // Fallback: minimal mutation approach
                $append = "\n[{$newUserId}]\npassword = \"" . str_replace(["\n", "\r"], '', (string)$newPassword) . "\"\n";
                file_put_contents(__DIR__ . "/../content.ini", $append, FILE_APPEND);
                $content = parse_ini_file(__DIR__ . "/../content.ini", true, INI_SCANNER_RAW);
                echo "User ID " . htmlspecialchars($newUserId) . " added successfully.";
            }
        } else {
            echo "Error: Username and Password cannot be empty.";
        }
        page("user", $newUserId);
        $_POST = []; // to prevent re-submission
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['changePasswordButton'])) {
        changePassword();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['loginButton'])) {
        $userName = htmlspecialchars($_POST['userName']);
        $password = htmlspecialchars($_POST['password']);
        if (!empty($userName) or !empty($password)) {
            if ($content === false) {
                echo "Error: Unable to load content.ini file.";
                return;
            } else {
                // Check if the section for the entered ID exists
                if (isset($content[$userName]) && isset($content[$userName]['password']) && $content[$userName]['password'] === $password) {
                    echo "Match found! User ID: " . htmlspecialchars($userName);
                    page("user", $userName);
                } else {
                    page("home");
                    echo "No match found for the entered ID.";
                    
                }
            }
        } else {
            page("home");
            echo "Error: Search field is empty.";
        }
    }
}


function changePassword() {
    global $content;
    echo "Change Password";
    echo "<form method='POST' action=''>"; 
    echo "<input type='text' name='userId' placeholder='User ID'>";
    echo "<br>";
    echo "<input type='password' name='oldPassword' placeholder='Old Password'>";
    echo "<br>";
    echo "<input type='password' name='newPassword' placeholder='New Password'>";
    echo "<br>";
    echo "<button type='submit' name='changePasswordButton'>Change Password</button>";
    echo "</form>";

    updatePassword();
}

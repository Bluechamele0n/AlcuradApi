<?php

include 'dokumenthandler.php';
include 'profilehandler.php';
include 'languagehandler.php';


session_start();



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


requests();




// Persist langId across requests
if (isset($_POST['langId'])) {
    $_SESSION['langId'] = $_POST['langId'];
}

// Default fallback
if (!isset($_SESSION['langId'])) {
    $_SESSION['langId'] = 'eng'; // default
}

// if start dont exists, go to homepage
// if from php file request do not open homepage

function webbsite() {
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
    $includedFrom = $backtrace[1]['file'] ?? null;
    echo "<!-- Included from: " . htmlspecialchars($includedFrom) . " -->"; // Debug info
    if ($includedFrom !== __DIR__ . '/php/pagehandler.php') {
        if (!isset($_POST['homepage']) && !isset($_POST['loginButton']) && !isset($_POST['registerButton']) 
            && !isset($_POST['changePasswordButton']) && !isset($_POST['docButton']) 
            && !isset($_POST['addDocButton']) && !isset($_POST['removeDocButton']) 
            && !isset($_POST['saveDoc']) && !isset($_POST['backDocButton']) 
            && !isset($_POST['accountRemoveButton']) && !isset($_POST['logoutButton']) 
            && !isset($_POST['userPageLangChange']) && !isset($_POST['homepageLangChange']) 
            && !isset($_POST['addLang']) && !isset($_POST['removeLangbutton']) && !isset($_POST['apiVersion'])) {
                page("home", null, null, $_SESSION['langId']);
        }
    }

}
function page($page, $yourId = null, $docName = null, $langId = null) {
    global $content;

    
    // Always pull latest langId if not given
    if ($langId === null && isset($_SESSION['langId'])) {
        $langId = $_SESSION['langId'];
    }

    if ($content === false) {
        echo "Error: Unable to load content.ini file.";
        return;
    }

    switch($page) {
        case "doc":
            if (isset($content[$yourId]) && $docName !== null) {
                openDocument($docName, $yourId, $langId);
                unset($_POST['docButton']);
                $_POST['homepage'] = false;
            }
            break;
        case "user":
            if (isset($content[$yourId]) && $docName === null) {
                echo "<script>document.body.innerHTML = '';</script>";
                openUserPage($yourId);
            }
            break;
        case "home":
            if ($yourId === null && $docName === null) {
                homepage($langId);
            }
            break;
        case "lang":
            if (isset($content[$yourId]) && $docName === null) {
                languangeChoser($yourId,false);
            }
            break;
        default:
            echo "Error: Invalid page.";
            return;
    }
    
    //var_dump("Page rendered: $page for User ID: " . ($yourId ?? 'N/A') . " Document: " . ($docName ?? 'N/A') . " Language: " . ($langId ?? 'N/A'));
}


function openUserPage($userid) {
    global $content;
    
    // Check if the content is loaded
    if ($content === false) {
        echo '<div style="padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; color: #721c24;">';
        echo '<h3>Error: Unable to load content</h3>';
        echo '<p>Unable to load content.ini file.</p>';
        echo '</div>';
        return;
    }

    // Check if the section for the given $userid exists
    if (!isset($content[$userid])) {
        echo '<div style="padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; color: #721c24;">';
        echo '<h3>Error: User not found</h3>';
        echo '<p>No section found for User ID: ' . htmlspecialchars($userid) . '</p>';
        echo '</div>';
        return;
    }

    // Get the keys and values from the section matching $userid
    $userSection = $content[$userid];

    // Stop rendering homepage content
    echo "<script>document.body.innerHTML = '';</script>";

    // Main container with modern styling
    echo '<div style="min-height: 100vh; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); padding: 20px; font-family: Arial, sans-serif;">';
    
    // Header section
    echo '<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 8px 32px rgba(0,0,0,0.1);">';
    echo '<h1 style="margin: 0 0 10px 0; font-size: 2.5em; font-weight: 300;">User Dashboard</h1>';
    echo '<p style="margin: 0; font-size: 1.2em; opacity: 0.9;">Welcome back, <strong>' . htmlspecialchars($userid) . '</strong></p>';
    echo '</div>';

    // Language management section
    echo '<div style="background: #fff; border-radius: 12px; padding: 25px; margin-bottom: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 1px solid #e1e8ed;">';
    echo '<h3 style="margin: 0 0 20px 0; color: #2c3e50; font-size: 1.4em; border-bottom: 2px solid #3498db; padding-bottom: 10px;">Language Management</h3>';
    
    // Language chooser
    languangeChoser($userid, true);
    
    // Add language form
    echo '<div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #17a2b8;">';
    echo '<h4 style="margin: 0 0 10px 0; color: #495057;">Add New Language</h4>';
    echo '<form method="POST" action="" style="display: flex; gap: 10px; align-items: center;">';
    echo "<input type='hidden' name='langId' value='" . htmlspecialchars($_SESSION['langId']). "'>";
    echo '<input type="text" name="newLangCode" placeholder="Language Code (e.g., fra, deu)" style="padding: 8px 12px; border: 1px solid #ced4da; border-radius: 6px; font-size: 14px; flex: 1; max-width: 200px;">';
    echo '<input type="hidden" name="userId" value="' . htmlspecialchars($userid) . '">';
    echo '<button type="submit" name="addLang" style="padding: 8px 16px; background: #17a2b8; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">Add Language</button>';
    echo '<button type="submit" name="removeLangbutton" style="padding: 8px 16px; background: #dc3545; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">Remove Language</button>';
    echo '</form>';
    echo '<p style="margin: 5px 0 0 0; font-size: 12px; color: #6c757d;">Current language: ' . ($_POST['langId']) . '</p>';
    echo '</div>';
    echo '</div>';

    // Document management section
    echo '<div style="background: #fff; border-radius: 12px; padding: 25px; margin-bottom: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 1px solid #e1e8ed;">';
    echo '<h3 style="margin: 0 0 20px 0; color: #2c3e50; font-size: 1.4em; border-bottom: 2px solid #28a745; padding-bottom: 10px;">Document Management</h3>';
    
    // Document list
    echo '<div style="margin-bottom: 20px;">';
    echo '<h4 style="margin: 0 0 15px 0; color: #495057;">Your Documents</h4>';
    echo '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">';
    
    // Generate buttons for each document
    echo '<form method="POST" action="" style="display: contents;">';
    echo "<input type='hidden' name='langId' value='" . htmlspecialchars($_SESSION['langId']). "'>";
    echo '<input type="hidden" name="userId" value="' . htmlspecialchars($userid) . '">';
    if (isset($_POST['langId'])) echo '<input type="hidden" name="langId" value="' . htmlspecialchars($_POST['langId']) . '">';
    
    $docCount = 0;
    foreach ($userSection as $key => $value) {
        if ($key === "password" || $key === "languages" || $key === "key") continue; // Skip system keys
        $docCount++;
        echo '<input type="hidden" name="editDoc" value="1">'; // default to edit mode
        echo '<button type="submit" name="docButton" value="' . htmlspecialchars($key) . '" style="padding: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 500; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3); transition: all 0.3s ease; text-align: center;">';
        echo '<div style="font-size: 24px; margin-bottom: 5px;">📄</div>';
        echo '<div>' . htmlspecialchars($key) . '</div>';
        echo '</button>';
    }
    
    if ($docCount === 0) {
        echo '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #6c757d; background: #f8f9fa; border-radius: 8px; border: 2px dashed #dee2e6;">';
        echo '<div style="font-size: 48px; margin-bottom: 15px;">📝</div>';
        echo '<h4 style="margin: 0 0 10px 0; color: #495057;">No documents yet</h4>';
        echo '<p style="margin: 0; color: #6c757d;">Create your first document below</p>';
        echo '</div>';
    }
    
    echo '</form>';
    echo '</div>';
    echo '</div>';

    // Document actions
    echo '<div style="background: #f8f9fa; border-radius: 8px; padding: 20px; border: 1px solid #e9ecef;">';
    echo '<h4 style="margin: 0 0 15px 0; color: #495057;">Document Actions</h4>';
    
    // Add document form
    echo '<form method="POST" action="" style="margin-bottom: 15px;">';
    echo "<input type='hidden' name='langId' value='" . htmlspecialchars($_SESSION['langId']). "'>";
    echo '<input type="hidden" name="userId" value="' . htmlspecialchars($userid) . '">';
    echo '<div style="display: flex; gap: 10px; align-items: center; margin-bottom: 10px;">';
    echo '<input type="text" name="newDocName" placeholder="New Document Name" style="padding: 8px 12px; border: 1px solid #ced4da; border-radius: 6px; font-size: 14px; flex: 1; max-width: 250px;">';
    echo '<button type="submit" name="addDocButton" style="padding: 8px 16px; background: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">➕ Add Document</button>';
    echo '</div>';
    echo '</form>';
    
    // Remove document form
    echo '<form method="POST" action="">';
    echo "<input type='hidden' name='langId' value='" . htmlspecialchars($_SESSION['langId']). "'>";
    echo '<input type="hidden" name="userId" value="' . htmlspecialchars($userid) . '">';
    echo '<div style="display: flex; gap: 10px; align-items: center;">';
    echo '<input type="text" name="removeDocName" placeholder="Document Name to Remove" style="padding: 8px 12px; border: 1px solid #ced4da; border-radius: 6px; font-size: 14px; flex: 1; max-width: 250px;">';
    echo '<button type="submit" name="removeDocButton" style="padding: 8px 16px; background: #dc3545; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">🗑️ Remove Document</button>';
    echo '</div>';
    echo '</form>';
    echo '</div>';
    echo '</div>';

    // Account actions
    echo '<div style="background: #fff; border-radius: 12px; padding: 25px; margin-bottom: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 1px solid #e1e8ed;">';
    echo '<h3 style="margin: 0 0 20px 0; color: #2c3e50; font-size: 1.4em; border-bottom: 2px solid #dc3545; padding-bottom: 10px;">Account Actions</h3>';
    
    echo '<div style="display: flex; gap: 15px; flex-wrap: wrap;">';
    echo '<form method="POST" action="">';
    echo "<input type='hidden' name='langId' value='" . htmlspecialchars($_SESSION['langId']). "'>";
    echo '<button type="submit" name="logoutButton" style="padding: 12px 24px; background: #6c757d; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 500; display: flex; align-items: center; gap: 8px;">🚪 Logout</button>';
    echo '</form>';
    
    echo '<form method="POST" action="">';
    echo "<input type='hidden' name='langId' value='" . htmlspecialchars($_SESSION['langId']). "'>";
    echo '<input type="hidden" name="userId" value="' . htmlspecialchars($userid) . '">';
    echo '<button type="submit" name="accountRemoveButton" style="padding: 12px 24px; background: #dc3545; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 500; display: flex; align-items: center; gap: 8px;">⚠️ Remove Account</button>';
    echo '</form>';
    echo '</div>';
    echo '</div>';

    echo '</div>'; // Close main container
}




function languangeChoser($userId, $onUserPage = false) {
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
        $langs = $userSection['languages'];
        
        if (is_string($langs)) {
            $decoded = json_decode($langs, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $langs = $decoded;
            }
        }
    
        if (is_array($langs)) {
            $availableLanguages = $langs;
        }
    }
    // make a selection for language and make the selected one be in $_POST['langId']
    $language = isset($_POST['langId']) ? $_POST['langId'] : (isset($availableLanguages[0]) ? $availableLanguages[0] : null);
    // Render language selection dropdown
    echo '<form method="POST" action="">';
    echo '<label for="language">Select Language:</label>';
    echo '<select name="langId" id="language">';
    foreach ($availableLanguages as $langCode) {
        $selected = ($langCode === $language) ? 'selected' : '';
        echo '<option value="' . htmlspecialchars($langCode) . '" ' . $selected . '>' . htmlspecialchars(strtoupper($langCode)) . '</option>';
    }
    echo '</select>';

    echo '<input type="hidden" name="userPageLangChange" value="1">';
    echo '<input type="hidden" name="userId" value="' . htmlspecialchars($userId) . '">';
    echo '<button type="submit" style="margin-left: 10px; padding: 5px 15px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Update Language</button>';
    echo '</form>';
    
    
}




function homepage($langId = "sve") {
    global $content;
    //print_r($content);
    echo '<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 8px; margin-bottom: 20px;">';
    echo '<h1 style="margin: 0 0 10px 0; font-size: 2.5em;">Welcome to AlcuradApi</h1>';
    echo '<p style="margin: 0; font-size: 1.2em; opacity: 0.9;">Your multilingual document management system</p>';
    echo '</div>';
    
    echo '<div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
    echo '<h3 style="margin: 0 0 15px 0; color: #333;">User Authentication</h3>';
    echo "<form method='POST' action=''>";
    echo "<input type='hidden' name='langId' value='" . htmlspecialchars($_SESSION['langId']). "'>";
    echo "<div style='margin-bottom: 10px;'>";
    echo "<input type='text' name='userName' placeholder='Username' style='padding: 10px; width: 200px; border: 1px solid #ccc; border-radius: 4px;'>";
    echo "</div>";
    echo "<div style='margin-bottom: 15px;'>";
    echo "<input type='password' name='password' placeholder='Password' style='padding: 10px; width: 200px; border: 1px solid #ccc; border-radius: 4px;'>";
    echo "</div>";
    echo "<button type='submit' name='loginButton' style='padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px;'>Login</button>";
    echo "<button type='submit' name='registerButton' style='padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px;'>Register</button>";
    echo "<button type='submit' name='changePasswordButton' style='padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;'>Change Password</button>";
    echo "</form>";
    echo "</div>";
    
    echo '<div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
    echo '<h3 style="margin: 0 0 15px 0; color: #333;">Browse All Documents</h3>';
    loadAllPages(true, $langId); // load all pages but make them showable only if they have something in the selected language
    echo '</div>';
    
    
}


function requests() {
    global $content;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['docButton'])) {
        $selectedDoc = htmlspecialchars($_POST['docButton']);
        $userId = htmlspecialchars($_POST['userId']);
        $langId = isset($_POST['langId']) ? htmlspecialchars($_POST['langId']) : null;
        
        // Debug: Check what's being sent
        echo "<!-- Debug: viewDoc = " . (isset($_POST['viewDoc']) ? $_POST['viewDoc'] : 'not set') . " -->";
        
        // Check if this is a request to view the document (from homepage)
        if (isset($_POST['viewDoc']) && $_POST['viewDoc'] == '1') {
            // Show HTML version of the document directly
            showHtmlDocversion($selectedDoc, $userId, $langId);
            $_POST['viewDoc'] = '0'; // reset to prevent re-submission
        } elseif (isset($_POST['editDoc']) && $_POST['editDoc'] == '1') {
            page("doc", $userId, $selectedDoc);
            $_POST['editDoc'] = '0'; // reset to prevent re-submission
        }
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
        $_POST['homepage'] = false; // to prevent re-submission
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['backDocButton'])) {
        $userId = htmlspecialchars($_POST['userId']);
        page("user", $userId);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accountRemoveButton'])) {
        $userId = htmlspecialchars($_POST['userId']);
        removeUser($userId);
        page("home");
        $_POST = [];
        $_POST["homepage"] = false; // to prevent re-submission
    
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logoutButton'])) {
        page("home");
        $_POST = [];
        $_POST["homepage"] = false; // to prevent re-submission

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['homepage']) && $_POST['homepage'] === true) {
        // Preserve language selection when going back to homepage
        $preservedLang = isset($_POST['langId']) ? $_POST['langId'] : null;
        $_POST = $preservedLang ? ['langId' => $preservedLang] : [];
        page("home");
        $_POST["homepage"] = false; // to prevent re-submission

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['userPageLangChange'])) {
        // Handle language change on user page - stay on user page
        $userId = htmlspecialchars($_POST['userId']);
        $selectedLang = $_POST['langId'];
        $_POST = ['langId' => $selectedLang];
        page("user", $userId);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['homepageLangChange'])) {
        // Handle language change on homepage - stay on homepage
        $selectedLang = $_POST['langId'];
        $_POST = ['langId' => $selectedLang];
        page("home");}

    


    if // add lang
    ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addLang'])) {
        $userId = htmlspecialchars($_POST['userId']);
        $newLangCode = htmlspecialchars($_POST['newLangCode']);
        if (!empty($newLangCode)) {
            if (function_exists('addLang')) {
                echo "Adding language: " . htmlspecialchars($newLangCode) . " to user: " . htmlspecialchars($userId);
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
    if // remove lang
    ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['removeLangbutton'])) {
        $userId = htmlspecialchars($_POST['userId']);
        $langToRemove = isset($_POST['langId']) ? htmlspecialchars($_POST['langId']) : null;
        if (!empty($langToRemove)) {
            if (function_exists('removeLang')) {
                echo "Removing language: " . htmlspecialchars($langToRemove) . " from user: " . htmlspecialchars($userId);
                removeLang($userId, $langToRemove);
            } else {
                echo "Error: removeLang function not found.";
            }
        } else {
            echo "Error: Language code to remove cannot be empty.";
        }
        page("user", $userId);
        $_POST = []; // to prevent re-submission
    }

    // when chosen lang go back to user page
    // if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['langId'])) {
    //     $userId = htmlspecialchars($_POST['userId']);
    //     page("user", $userId);
    // }


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
                    $langId = isset($_POST['langId']) ? $_SESSION['langId']: $_SESSION['langId'];
                    page("home", null , null, $langId);
                    echo "No match found for the entered ID.";
                    
                }
            }
        } else {
            page("home", null, null, $langId);
            echo "Error: Search field is empty.";
        }
    }
}


function changePassword() {
    global $content;
    echo "Change Password";
    echo "<form method='POST' action=''>"; 
    echo "<input type='hidden' name='langId' value='" . htmlspecialchars($_SESSION['langId']). "'>";
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


function loadAllPages($active, $langId = "sve") {
    global $content;
    if ($content === false) {
        echo "Error: Unable to load content.ini file.";
        return;
    }
    if ($langId === null && isset($_SESSION['langId'])) {
        $langId = $_SESSION['langId'];
    }
    
    // Get all available languages from all users (no duplicates)
    $allLanguages = [];
    foreach ($content as $section => $docs) {
        if (isset($docs['languages'])) {
            $langs = $docs['languages'];
            if (is_string($langs)) {
                $decoded = json_decode($langs, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $langs = $decoded;
                }
            }
            if (is_array($langs)) {
                $allLanguages = array_merge($allLanguages, $langs);
            }
        }
    }    
    $allLanguages = array_unique($allLanguages);
    
    // Get selected language from langId or default to first available
    $language = in_array($langId, $allLanguages) ? $langId : (isset($allLanguages[0]) ? $allLanguages[0] : null);
    if ($language === null) {
        echo '<div style="margin: 20px 0; padding: 10px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px;">';
        echo '<p>No languages available. Please add languages to user accounts.</p>';
        echo '</div>';
        return;
    }
    
    // Render language selection dropdown
    echo '<div style="margin: 20px 0; padding: 10px; border: 1px solid #ccc; background: #f9f9f9;">';
    echo '<form method="POST" action="">';
    echo '<label for="language"><strong>Select Language:</strong></label><br>';
    echo '<select name="langId" id="language" style="margin: 5px 0; padding: 5px;">';
    foreach ($allLanguages as $langCode) {
        $selected = ($langCode === $language) ? 'selected' : '';
        echo '<option name="langId" value="' . htmlspecialchars($langCode) . '" ' . $selected . '>' . htmlspecialchars(strtoupper($langCode)) . '</option>';
    }
    echo '</select>';
    echo '<input type="hidden" name="homepageLangChange" value="1">';
    echo '<button type="submit" style="margin-left: 10px; padding: 5px 15px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Update Language</button>';
    echo '</form>';
    echo '</div>';

    // Get all documents from all users and filter by selected language
    $documentsByLanguage = [];
    foreach ($content as $section => $docs) {
        foreach ($docs as $key => $value) {
            if ($key === "password" || $key === "languages") continue; // Skip system keys
            
            $docContent = $value;
            if (is_string($docContent)) $docContent = json_decode($docContent, true);
            if (!is_array($docContent)) continue; // Skip invalid document formats
            
            // Check if document has content in the selected language
            $hasLangContent = false;
            $langBlocks = [];
            
            // Parse document structure to find language content
            foreach ($docContent as $langObj) {
                if (is_array($langObj)) {
                    foreach ($langObj as $langKey => $langValue) {
                        if ($langKey === $language && is_array($langValue) && !empty($langValue)) {
                            $hasLangContent = true;
                            $langBlocks = $langValue;
                            break;
                        }
                    }
                }
            }
            
            // Only show documents that have content in the selected language
            if ($hasLangContent) {
                $documentsByLanguage[] = [
                    'userId' => $section,
                    'docName' => $key,
                    'content' => $langBlocks
                ];
            }
        }
    }
    
    // Display documents grouped by user
    if (!empty($documentsByLanguage)) {
        echo '<div style="margin: 20px 0;">';
        echo '<h3>Documents in ' . htmlspecialchars(strtoupper($language)) . ':</h3>';
        
        $currentUser = null;
        foreach ($documentsByLanguage as $doc) {
            if ($currentUser !== $doc['userId']) {
                if ($currentUser !== null) echo '</div>'; // Close previous user group
                echo '<div style="margin: 10px 0; padding: 10px; border-left: 3px solid #007cba;">';
                echo '<h4 style="margin: 0 0 10px 0; color: #007cba;">User: ' . htmlspecialchars($doc['userId']) . '</h4>';
                $currentUser = $doc['userId'];
            }
            
            echo "<form method='POST' action='' style='display: inline-block; margin: 5px;'>";
            echo "<input type='hidden' name='userId' value='" . htmlspecialchars($doc['userId']) . "'>";
            echo "<input type='hidden' name='langId' value='" . htmlspecialchars($language) . "'>";
            echo "<input type='hidden' name='viewDoc' value='1'>";
            echo "<button type='submit' name='docButton' value='" . htmlspecialchars($doc['docName']) . "' style='padding: 8px 15px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;'>";
            echo "View " . htmlspecialchars($doc['docName']);
            echo "</button>";
            echo "</form>";
        }
        if ($currentUser !== null) echo '</div>'; // Close last user group
        echo '</div>';
    } else {
        echo '<div style="margin: 20px 0; padding: 10px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px;">';
        echo '<p>No documents found in ' . htmlspecialchars(strtoupper($language)) . ' language.</p>';
        echo '</div>';
    }
}
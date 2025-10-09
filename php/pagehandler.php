<?php

//include 'apidocumentation.php';
include 'dokumenthandler.php';
include 'profilehandler.php';
include 'languagehandler.php';
include 'fonts.php'; 


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
                if (!isset($content['AlcuradApi']) || !isset($content['AlcuradApi']['Editor'])) {
                    echo '<div class="alert-error"><h3>Error: No user dashboard content found</h3><p>Please contact the administrator.</p></div>';
                    return;
                }
                $editorContent = $content['AlcuradApi']['Editor'];
                $selectedLang = $_SESSION['langId'] ?? $langId;
                $langBlock = null;
                foreach ($editorContent as $langEntry) {
                    if (isset($langEntry[$selectedLang])) {$langBlock = $langEntry[$selectedLang]; break;}
                }
                // fallback to English if not found
                if ($langBlock === null) {
                    foreach ($editorContent as $langEntry) {if (isset($langEntry['eng'])) {$langBlock = $langEntry['eng']; break;}}
                }
                $editorContent = $langBlock;  
                openDocument($docName, $yourId, $langId, $editorContent);
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
        // case "apidocmd":
        //     displayDocumentation(__DIR__ . '/../documentation.md');
        //     break;
        default:
            echo "Error: Invalid page.";
            return;
    }
    
    //var_dump("Page rendered: $page for User ID: " . ($yourId ?? 'N/A') . " Document: " . ($docName ?? 'N/A') . " Language: " . ($langId ?? 'N/A'));
}


function openUserPage($userid) {
    global $content;

    if ($content === false) {
        echo '<div class="alert-error"><h3>Error: Unable to load content</h3><p>Unable to load content.ini file.</p></div>';
        return;
    }

    if (!isset($content[$userid])) {
        echo '<div class="alert-error"><h3>Error: User not found</h3><p>No section found for User ID: ' . htmlspecialchars($userid) . '</p></div>';
        return;
    }

    echo '<link rel="stylesheet" href="./css/alcurad.css">';

    if (!isset($content['AlcuradApi']) || !isset($content['AlcuradApi']['userdashboard'])) {
        echo '<div class="alert-error"><h3>Error: No user dashboard content found</h3><p>Please contact the administrator.</p></div>';
        return;
    }

    $userpageContent = $content['AlcuradApi']['userdashboard'];

    $selectedLang = $_SESSION['langId'] ?? $langId;
    $langBlock = null;

    foreach ($userpageContent as $langEntry) {
        if (isset($langEntry[$selectedLang])) {
            $langBlock = $langEntry[$selectedLang];
            break;
        }
    }

    // fallback to English if not found
    if ($langBlock === null) {
        foreach ($userpageContent as $langEntry) {
            if (isset($langEntry['eng'])) {
                $langBlock = $langEntry['eng'];
                break;
            }
        }
    }

    $userpageContent = $langBlock;  
    $tag1 = $userpageContent[0];
    $tag1 = $tag1['p'];
    $tag2 = $userpageContent[1];
    $tag2 = $tag2['p'];
    $tag3 = $userpageContent[2];
    $tag3 = $tag3['p'];
    $tag4 = $userpageContent[3];
    $tag4 = $tag4['p'];
    $tag5 = $userpageContent[4];
    $tag5 = $tag5['p'];
    $tag6 = $userpageContent[5];
    $tag6 = $tag6['p'];
    $tag7 = $userpageContent[6];
    $tag7 = $tag7['p'];
    $tag8 = $userpageContent[7];
    $tag8 = $tag8['p'];
    $tag9 = $userpageContent[8];
    $tag9 = $tag9['p'];
    $tag10 = $userpageContent[9];
    $tag10 = $tag10['p'];
    $tag11 = $userpageContent[10];
    $tag11 = $tag11['p'];
    $tag12 = $userpageContent[11];
    $tag12 = $tag12['p'];
    $tag13 = $userpageContent[12];
    $tag13 = $tag13['p'];
    $tag14 = $userpageContent[13];
    $tag14 = $tag14['p'];
    $tag15 = $userpageContent[14];
    $tag15 = $tag15['p'];
    $tag16 = $userpageContent[15];
    $tag16 = $tag16['p'];
    $tag17 = $userpageContent[16];
    $tag17 = $tag17['p'];
    $tag18 = $userpageContent[17];
    $tag18 = $tag18['p'];
    $tag19 = $userpageContent[18];
    $tag19 = $tag19['p'];
    $tag20 = $userpageContent[19];
    $tag20 = $tag20['p'];
    $tag21 = $userpageContent[20];
    $tag21 = $tag21['p'];


    $userSection = $content[$userid];

    echo '<div class="main-container">';

    // Header
    echo '<div class="header">';
    echo '<div class="left-header">';
    echo '<h1>'.$tag1.'</h1>';
    echo '<p>'.$tag2.'<strong>' . htmlspecialchars($userid) . '</strong></p>';
    echo '</div>';
    showUsersKey($userid, $tag21);
    echo '</div>';

    // Language Management
    echo '<div class="card language-header">';
    echo '<h3>'.$tag3.'</h3>';
    languangeChoser($userid, true, $tag4, $tag5);
    echo '<form method="POST" action="" class="flex-gap">';
    echo "<input type='hidden' name='langId' value='" . htmlspecialchars($_SESSION['langId']). "'>";
    echo '<input type="text" name="newLangCode" placeholder="'.$tag6.'">';
    echo '<input type="hidden" name="userId" value="' . htmlspecialchars($userid) . '">';
    echo '<button type="submit" name="addLang" class="btn-add">'.$tag7.'</button>';
    echo '<button type="submit" name="removeLangbutton" class="btn-remove">'.$tag8.'</button>';
    echo '</form>';
    echo '</div>';

    // Document Management
    echo '<div class="card document-header">';
    echo '<h3>'.$tag9.'</h3>';
    echo '<div><h4>'.$tag10.'</h4>';
    echo '<form method="POST" action="" class="doc-grid">';
    echo "<input type='hidden' name='langId' value='" . htmlspecialchars($_SESSION['langId']). "'>";
    echo '<input type="hidden" name="userId" value="' . htmlspecialchars($userid) . '">';

    $docCount = 0;
    foreach ($userSection as $key => $value) {
        if ($key === "password" || $key === "languages" || $key === "key") continue;
        $docCount++;
        echo '<input type="hidden" name="editDoc" value="1">';
        echo '<button type="submit" name="docButton" value="' . htmlspecialchars($key) . '">';
        echo '<div>📄</div>';
        echo '<div>' . htmlspecialchars($key) . '</div>';
        echo '</button>';
    }

    if ($docCount === 0) {
        echo '<div class="doc-empty"><div>📝</div><h4>'.$tag19.'</h4><p>'.$tag20.'</p></div>';
    }

    echo '</form></div>';

    // Document Actions
    echo '<h4>'.$tag11.'</h4>';
    echo '<form method="POST" action="" class="flex-gap">';
    echo "<input type='hidden' name='langId' value='" . htmlspecialchars($_SESSION['langId']). "'>";
    echo '<input type="hidden" name="userId" value="' . htmlspecialchars($userid) . '">';
    echo '<input type="text" name="newDocName" placeholder="'.$tag12.'">';
    echo '<button type="submit" name="addDocButton" class="btn-add">'.$tag13.'</button>';
    echo '</form>';

    echo '<form method="POST" action="" class="flex-gap">';
    echo "<input type='hidden' name='langId' value='" . htmlspecialchars($_SESSION['langId']). "'>";
    echo '<input type="hidden" name="userId" value="' . htmlspecialchars($userid) . '">';
    echo '<input type="text" name="removeDocName" placeholder="'.$tag14.'">';
    echo '<button type="submit" name="removeDocButton" class="btn-remove">'.$tag15.'</button>';
    echo '</form>';
    echo '</div>';

    // Account Actions
    echo '<div class="card account-header">';
    echo '<h3>'.$tag16.'</h3>';
    echo '<div class="btn-group-account">';
    echo '<form method="POST" action="" class="flex-gap">';
    echo "<input type='hidden' name='langId' value='" . htmlspecialchars($_SESSION['langId']). "'>";
    echo '<button type="submit" name="logoutButton" class="btn-logout">'.$tag17.'</button>';
    echo '</form>';

    echo '<form method="POST" action="" class="flex-gap">';
    echo "<input type='hidden' name='langId' value='" . htmlspecialchars($_SESSION['langId']). "'>";
    echo '<input type="hidden" name="userId" value="' . htmlspecialchars($userid) . '">';
    echo '<button type="submit" name="accountRemoveButton" class="btn-remove">'.$tag18.'</button>';
    echo '</form>';
    echo '</div>';
    echo '</div>';

    echo '</div>'; // End main-container
}




function languangeChoser($userId, $onUserPage = false, $tag4, $tag5) {
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
    echo '<label for="language">'.$tag4.'</label>';
    echo '<select name="langId" id="language">';
    foreach ($availableLanguages as $langCode) {
        $selected = ($langCode === $language) ? 'selected' : '';
        echo '<option value="' . htmlspecialchars($langCode) . '" ' . $selected . '>' . htmlspecialchars(strtoupper($langCode)) . '</option>';
    }
    echo '</select>';

    echo '<input type="hidden" name="userPageLangChange" value="1">';
    echo '<input type="hidden" name="userId" value="' . htmlspecialchars($userId) . '">';
    echo '<button type="submit" style="margin-left: 10px; padding: 5px 15px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">'.$tag5.'</button>';
    echo '</form>';
    
    
}




function homepage($langId = "swe") {
    global $content;
    // clear the body if homepage is loaded twice only
    echo "<script>document.body.innerHTML = '';</script>";
    //print_r($content);
    // from content take the homepage document from AlcuradApi user and put every p it into a list
    if ($content === false) {
        echo "Error: Unable to load content.ini file.";
        return;
    }
    if (!isset($content['AlcuradApi']) || !isset($content['AlcuradApi']['homepage'])) {
        echo "Error: No homepage content found.";
        return;
    }
    
    $homepageContent = $content['AlcuradApi']['homepage'];
    $selectedLang = $_SESSION['langId'] ?? $langId;
    $langBlock = null;

    foreach ($homepageContent as $langEntry) {
        if (isset($langEntry[$selectedLang])) {
            $langBlock = $langEntry[$selectedLang];
            break;
        }
    }

    // fallback to English if not found
    if ($langBlock === null) {
        foreach ($homepageContent as $langEntry) {
            if (isset($langEntry['eng'])) {
                $langBlock = $langEntry['eng'];
                break;
            }
        }
    }

    if (!isset($content['AlcuradApi']) || !isset($content['AlcuradApi']['InfoCard'])) {
        echo "Error: No Infocard content found.";
        return;
    }
    
    $infoCardContent = $content['AlcuradApi']['InfoCard'];
    $selectedLang = $_SESSION['langId'] ?? $langId;
    $langBlock2 = null;

    foreach ($infoCardContent as $langEntry) {
        if (isset($langEntry[$selectedLang])) {
            $langBlock2 = $langEntry[$selectedLang];
            break;
        }
    }

    // fallback to English if not found
    if ($langBlock2 === null) {
        foreach ($infoCardContent as $langEntry) {
            if (isset($langEntry['eng'])) {
                $langBlock2 = $langEntry['eng'];
                break;
            }
        }
    }
    
    $infoCardContent = $langBlock2;
    $homepageContent = $langBlock;  
    $tag1 = $homepageContent[0];
    $tag1 = $tag1['p'];
    $tag2 = $homepageContent[1];
    $tag2 = $tag2['p'];
    $tag3 = $homepageContent[2];
    $tag3 = $tag3['p'];
    $tag4 = $homepageContent[3];
    $tag4 = $tag4['p'];
    $tag10 = $homepageContent[9];
    $tag10 = $tag10['p'];
    $tag11 = $homepageContent[10];
    $tag11 = $tag11['p'];
    $tag12 = $homepageContent[11];
    $tag12 = $tag12['p'];
    $tag13 = $homepageContent[12];
    $tag13 = $tag13['p'];
    $tag14 = $homepageContent[13];
    $tag14 = $tag14['p'];
    


    echo '<link rel="stylesheet" href="./css/alcurad.css">';
    echo '<div class="main-container">';
    echo '<div class="header">';
    echo '<div class="left-header">';
    echo '<h1>'.$tag1.'</h1>';
    echo '<p>'.$tag2.'</p>';
    echo '</div>';
    echo '<div class="right-header">';
    echo '<form method="POST" action="">';
    echo "<input type='hidden' name='userId' value='AlcuradApi'>";
    echo "<input type='hidden' name='langId' value='all'>";
    echo "<input type='hidden' name='viewDoc' value='1'>";
    echo '<button type="submit" name="docButton" value="ApiDocumentation" id="apiDocButton" class="btn btn-docs">';
    echo '<img src="favicon.ico" alt="API Docs" class="btn-icon">';
    echo 'Documentation';
    echo '</button>';
    echo '</form>';
    echo '</div>';
    // echo '<form method="POST" action="">';
    // echo '<button type="submit" name="apiDocButton" formaction="" class="btn btn-docs" onclick="window.location.href=\'?page=apidocmd\'">API Documentation</button>';
    // echo '<form>';
    echo '</div>';

    echo '<div class="utility-card">';
    echo '<div class="card auth-card">';
    echo '<div class="input-group">';
    echo '<h3>'.$tag3.'</h3>';
    echo '<form method="POST" action="">';
    echo '<input type="hidden" name="langId" value="' . htmlspecialchars($_SESSION['langId']) . '">';

    echo '<div class="form-group">';
    echo '<input type="password" name="password" placeholder="'.$tag11.'">';
    echo '</div>';

    echo '<div class="form-group">';
    echo '<input type="text" name="userName" placeholder="'.$tag10.'">';
    echo '</div>';

    echo '</div>';
    echo '<div class="btn-group">';
    echo '<button type="submit" name="changePasswordButton" class="btn btn-change">'.$tag14.'</button>';
    echo '<button type="submit" name="loginButton" class="btn btn-login">'.$tag12.'</button>';
    echo '<button type="submit" name="registerButton" class="btn btn-register">'.$tag13.'</button>';
    echo '</div>';
    echo '</form>';
    echo '</div>';
    echo '<div class="card info-card">';
    echo displayMarkdownAsHtml($infoCardContent);
    echo '</div>';
    echo '</div>'; // end utility card

    echo '<div class="card doc-card">';
    echo '<h3>'.$tag4.'</h3>';
    loadAllPages(true, $langId, $homepageContent);
    echo '</div>';
    echo '</div>'; // end main container


    
    
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
            if (isset($content[$userId][$selectedDoc][0]['all']) && !empty($content[$userId][$selectedDoc][0]['all']) && !isset($content[$userId][$selectedDoc][0][$langId])) {
                $langId = 'all'; // force all if exists
            }
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
            // unset all posts that triggered this specificly
            unset($_POST['newDocName']);
            unset($_POST['addDocButton']);
            // if post is empty set apiversion to false to prevent re-submission
           
            $_POST["apiVersion"] = false; // to prevent re-submission
        } else {
            unset($_POST['removeDocName']);
            unset($_POST['removeDocButton']);
          
            $_POST["apiVersion"] = false; // to prevent re-submission
            echo "Error: Document name cannot be empty.";
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['removeDocButton'])) {
        $removeDocName = htmlspecialchars($_POST['removeDocName']);
        $userId = htmlspecialchars($_POST['userId']);
        if (!empty($removeDocName)) {
            removeDocument($userId, $removeDocName);
            unset($_POST['removeDocName']);
            unset($_POST['removeDocButton']);
            $_POST["apiVersion"] = false; // to prevent re-submission

        } else {
            unset($_POST['removeDocName']);
            unset($_POST['removeDocButton']);
           
            $_POST["apiVersion"] = false; // to prevent re-submission
            echo "Error: Document name to remove cannot be empty.";
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['saveDoc'] ) || isset($_POST['updatedocName']) )) {
        $userId = htmlspecialchars($_POST['userId']);
        $docName = htmlspecialchars($_POST['docName']);
        $langId = isset($_POST['langId']) ? htmlspecialchars($_POST['langId']) : null;
        $_POST = ['langId' => $langId];
            // new doc name contains anything other than current doc name
        if (!isset($content['AlcuradApi']) || !isset($content['AlcuradApi']['Editor'])) {
            echo '<div class="alert-error"><h3>Error: No user dashboard content found</h3><p>Please contact the administrator.</p></div>';
            return;
        }
        $editorContent = $content['AlcuradApi']['Editor'];
        $selectedLang = $_SESSION['langId'] ?? $langId;
        $langBlock = null;
        foreach ($editorContent as $langEntry) {
            if (isset($langEntry[$selectedLang])) {$langBlock = $langEntry[$selectedLang]; break;}
        }
        // fallback to English if not found
        if ($langBlock === null) {
            foreach ($editorContent as $langEntry) {if (isset($langEntry['eng'])) {$langBlock = $langEntry['eng']; break;}}
        }
        $editorContent = $langBlock; 
        openDocument($docName, $userId, $langId, $editorContent);
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
        $userId = htmlspecialchars($_POST['userId']);
        $_SESSION['langId'] = $_POST['langId']; // persist new language
        // Keep the original $_POST keys intact
        page("user", $userId);
    
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['homepageLangChange'])) {
        $_SESSION['langId'] = $_POST['langId'];
        page("home");
    // } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apiDocButton'])) {
    //     // Preserve language selection when going to API documentation
    //     $preservedLang = isset($_POST['langId']) ? $_POST['langId'] : null;
    //     $_POST = $preservedLang ? ['langId' => $preservedLang] : [];
    //     page("apidocmd");
    //     $_POST["apiVersion"] = false; // to prevent re-submission
    
    }

    if // add lang
    ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addLang'])) {
        $userId = htmlspecialchars($_POST['userId']);
        $newLangCode = htmlspecialchars($_POST['newLangCode']);
        $_POST["apiVersion"] = false; // to prevent re-submission
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
        unset($_POST['addLang']); // to prevent re-submission
    }
    if // remove lang
    ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['removeLangbutton'])) {
        $userId = htmlspecialchars($_POST['userId']);
        $langToRemove = isset($_POST['langId']) ? htmlspecialchars($_POST['langId']) : null;
        $_POST["apiVersion"] = false; // to prevent re-submission
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
        unset($_POST['removeLangbutton']); // to prevent re-submission
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
        $_POST["apiVersion"] = false; // to prevent re-submission
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


function loadAllPages($active, $langId = "swe", $homepageContent) {
    global $content;
    fontLoader();
    $tag5 = $homepageContent[4];
    $tag5 = $tag5['p'];
    $tag6 = $homepageContent[5];
    $tag6 = $tag6['p'];
    $tag7 = $homepageContent[6];
    $tag7 = $tag7['p'];
    $tag8 = $homepageContent[7];
    $tag8 = $tag8['p'];
    $tag9 = $homepageContent[8];
    $tag9 = $tag9['p'];
    
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
        if ($section === 'AlcuradApi') continue; // ignore system section
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
    echo '<label for="language"><strong>'.$tag5.'</strong></label><br>';
    echo '<select name="langId" id="language" style="margin: 5px 0; padding: 5px;">';
    foreach ($allLanguages as $langCode) {
        $selected = ($langCode === $language) ? 'selected' : '';
        echo '<option name="langId" value="' . htmlspecialchars($langCode) . '" ' . $selected . '>' . htmlspecialchars(strtoupper($langCode)) . '</option>';
    }
    echo '</select>';
    echo '<input type="hidden" name="homepageLangChange" value="1">';
    echo '<button type="submit" style="margin-left: 10px; padding: 5px 15px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">'.$tag6.'</button>';
    echo '</form>';
    echo '</div>';

    // Get all documents from all users and filter by selected language
    $documentsByLanguage = [];
    foreach ($content as $section => $docs) {
        foreach ($docs as $key => $value) {
            if ($key === "password" || $key === "languages" || $key === 'key') continue; // Skip system keys
            
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
                        if (($langKey === $language || $langKey === 'all') && is_array($langValue) && !empty($langValue)) {
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
        echo '<h3>'.$tag7.' ' . htmlspecialchars(strtoupper($language)) . ':</h3>';
        
        $currentUser = null;
        foreach ($documentsByLanguage as $doc) {
            if ($currentUser !== $doc['userId']) {
                if ($currentUser !== null) echo '</div>'; // Close previous user group
                echo '<div style="margin: 10px 0; padding: 10px; border-left: 3px solid #007cba;">';
                echo '<h4 style="margin: 0 0 10px 0; color: #007cba;">'.$tag8.': ' . htmlspecialchars($doc['userId']) . '</h4>';
                $currentUser = $doc['userId'];
            }
            
            echo "<form method='POST' action='' style='display: inline-block; margin: 5px;'>";
            echo "<input type='hidden' name='userId' value='" . htmlspecialchars($doc['userId']) . "'>";
            echo "<input type='hidden' name='langId' value='" . htmlspecialchars($language) . "'>";
            echo "<input type='hidden' name='viewDoc' value='1'>";
            echo "<button type='submit' name='docButton' value='" . htmlspecialchars($doc['docName']) . "' class='btn-docs'>";
            echo $tag9.' ' . htmlspecialchars($doc['docName']);
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


function showUsersKey($userId, $tag21) {
    global $content;
    if ($content === false) {
        echo "Error: Unable to load content.ini file.";
        return;
    }
    if (!isset($content[$userId])) {
        echo "Error: User not found.";
        return;
    }
    $userSection = $content[$userId];
    $userKey = isset($userSection['key']) ? $userSection['key'] : null;
    // show if button is pressed reveal key

    echo '<button type="button" name="revealKeyButton" class="btn-reveal btn-add">'.$tag21.'</button>';

    if ($userKey !== null) {
        echo '<div class="alert-info" style="display:none;">
                <h3>'.$tag21.'</h3>
                <p><strong>' . htmlspecialchars($userKey) . '</strong></p>
              </div>';
    } else {
        echo '<div class="alert-warning" style="display:none;">
                <h3>'.$tag21.'</h3>
                <p>No user key found. Please contact the administrator.</p>
              </div>';
    }
    
    echo '
    <script>
        document.querySelector(".btn-reveal").addEventListener("click", function() {
            this.nextElementSibling.style.display = "block";
            this.style.display = "none";
        });
    </script>
    ';
    

}

<?php
$content = parse_ini_file(__DIR__ . "/../content.ini", true, INI_SCANNER_RAW);
if ($content === false) exit("Error: Unable to load content.ini file.");


// writeIni comes from php/util.php

// Decode JSON inside INI
foreach ($content as $section => $docs) {
    foreach ($docs as $key => $value) {
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) $content[$section][$key] = $decoded;
        
    }
}


function openDocument($docName, $userId, $langId = null) {
    global $content;
    if (!isset($content[$userId][$docName])) {
        echo "Error: No section found for Document: " . htmlspecialchars($docName);
        return;
    }
    $docContent = $content[$userId][$docName];
    if (is_string($docContent)) $docContent = json_decode($docContent, true);
    if (!is_array($docContent)) $docContent = [];

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
    // look for langId in POST and use that if not null and if not found as key in documents content add it to the end of the order and then chose the one that is selected.
    
    if ($langId === null && isset($_POST['langId'])) $langId = $_POST['langId'];
    if ($langId === null) $langId = (count($langOrder) ? $langOrder[0] : 'eng');
    if (!isset($langToBlocks[$langId])) {
        $langOrder[] = $langId;
        $langToBlocks[$langId] = [];
    }


    renderEditor($docName, $langToBlocks[$langId], $userId, $langId);
}

if (isset($_POST['saveDoc'])) {
    $docName = $_POST['docName'];
    $userId = $_POST['userId'];
    $jsonContent = $_POST['tempJson'];
    $selectedLang = isset($_POST['langId']) ? $_POST['langId'] : 'eng';

    $existing = $content[$userId][$docName] ?? [];
    if (is_string($existing)) $existing = json_decode($existing, true);
    if (!is_array($existing)) $existing = [];

    // Convert to lang map + order
    $langOrder = [];
    $langToBlocks = [];
    foreach ($existing as $langObj) {
        if (!is_array($langObj)) continue;
        foreach ($langObj as $k => $v) {
            $langOrder[] = $k;
            $langToBlocks[$k] = is_array($v) ? $v : [];
        }
    }

    $langToBlocks[$selectedLang] = json_decode($jsonContent, true) ?: [];
    if (!in_array($selectedLang, $langOrder, true)) $langOrder[] = $selectedLang;

    // Rebuild array-of-language-objects in original order
    $rebuilt = [];
    foreach ($langOrder as $k) {
        $rebuilt[] = [$k => $langToBlocks[$k]];
    }

    $content[$userId][$docName] = $rebuilt;
    writeIni($content);
    echo "<p><strong>Saved to INI!</strong></p>";
}

function renderEditor($docName, $docBlocksForLang, $userId, $langId) {
    if (is_string($docBlocksForLang)) $docBlocksForLang = json_decode($docBlocksForLang, true);
    if (!is_array($docBlocksForLang)) $docBlocksForLang = [];

    // Build editor text without adding extra \n at the end of each block
    $text = "";
    $lastIndex = count($docBlocksForLang) - 1;
    foreach ($docBlocksForLang as $i => $block) {
        if (!is_array($block)) continue;
        foreach ($block as $key => $value) {
            if ($key === "h1") $text .= "# " . $value;
            elseif ($key === "h2") $text .= "## " . $value;
            elseif ($key === "p") $text .= $value;
            elseif ($key === "n") $text .= "";
        }
        // only append newline if it’s not the last block
        if ($i !== $lastIndex) $text .= "\n";
    }

    $text = htmlspecialchars($text);
    $jsonContent = json_encode($docBlocksForLang, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    echo <<<HTML
<h2>Editing: {$docName}</h2>
<form method="post">
    <input type="hidden" name="docName" value="{$docName}">
    <input type="hidden" name="userId" value="{$userId}">
    <input type="hidden" name="langId" value="{$langId}">
    <input type="hidden" id="tempJson" name="tempJson" value='<?php echo $jsonContent; ?>'>

    <div style="display:flex; gap:20px;">
        <div class="editor-container" style="width:33%; height:400px; font-family:monospace;">
            <textarea id="editor" placeholder="Type here..." 
                      style="width:100%; height:100%; background:#fff; color:black; border:1px solid #ccc;
                             resize:none; font-family:monospace; white-space:pre-wrap; word-wrap:break-word;">{$text}</textarea>
        </div>

        <div id="livePreview" style="width:33%; height:400px; border:1px solid #ccc; padding:10px; overflow:auto; font-family:monospace; background:#f9f9f9;"></div>

        <pre>
        <div id="jsonPreview" style="width:200px; height:450px; border:1px solid #ccc; padding:10px; overflow:auto; font-family:monospace; background:#eef;"></div>
        </pre>
    </div>
    <br>
    <button type="submit" name="saveDoc">Save to INI</button>
    <button type="submit" name="backDocButton">Back to Documents</button>
</form>

<script>
const editor = document.getElementById("editor");
const tempJsonInput = document.getElementById("tempJson");
const livePreview = document.getElementById("livePreview");
const jsonPreview = document.getElementById("jsonPreview");

function escapeHtml(str) {
    return str.replace(/[&<>"']/g, tag =>
        ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[tag])
    );
}

// Convert editor to JSON while preserving order and avoiding extra "n"
function updateTempJson() {
    const lines = editor.value.split("\\n");
    const jsonArray = [];

    for (let line of lines) {
        line = line.replace(/\\r/g, "");
        if (line === "") {
            jsonArray.push({n:"down"});
            continue;
        }
        if (line.startsWith("##")) {
            jsonArray.push({h2: line.replace(/^##\\s*/, "")});
        } else if (line.startsWith("#")) {
            jsonArray.push({h1: line.replace(/^#\\s*/, "")});
        } else {
            jsonArray.push({p: line});
        }
    }

    tempJsonInput.value = JSON.stringify(jsonArray, null, 0);
    jsonPreview.textContent = JSON.stringify(jsonArray, null, 2);
}

// Build live preview
function renderPreview() {
    const lines = editor.value.split("\\n");
    let html = "";

    for (let line of lines) {
        if (line.trim() === "") {
            html += "<br>";
            continue;
        }

        let formatted = escapeHtml(line);
        formatted = formatted.replace(/\\*\\*(.*?)\\*\\*/g, "<b>$1</b>");
        formatted = formatted.replace(/\\*(.*?)\\*/g, "<i>$1</i>");
        formatted = formatted.replace(/__(.*?)__/g, "<u>$1</u>");
        formatted = formatted.replace(/~~(.*?)~~/g, "<s>$1</s>");
        formatted = formatted.replace(/\\[(.*?)\\]\\((.*?)\\)/g, '<a href="$2" target="_blank">$1</a>'); // [text](url) -> <a href="url" target="_blank">text</a>

        if (line.startsWith("##")) html += "<h2>" + formatted.replace(/^##\\s*/, "") + "</h2>";
        else if (line.startsWith("#")) html += "<h1>" + formatted.replace(/^#\\s*/, "") + "</h1>";
        else html += "<div>" + formatted + "</div>";
    }

    livePreview.innerHTML = html;
    updateTempJson();
}

editor.addEventListener("input", renderPreview);
renderPreview();
</script>
HTML;
}


function addNewDocument($userid, $NewDocName, $fromapi = false) {
    global $content;
    if (!$fromapi) {
        page("user", $userid);
    }
    if (!isset($content[$userid][$NewDocName])) {
        // Store as array, not JSON string
        // add json key called language with value eng and one with sve

        foreach ($content as $section => &$docs) {
            foreach ($docs as $key => &$value) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $value = $decoded;
                }
            }
        }

        $content[$userid][$NewDocName] = [["eng" => []], ["sve" => []]];

        writeIni($content);
        if (!$fromapi) {
                echo "Document " . htmlspecialchars($NewDocName) . " added.";
                page("user", $userid);
        }
    } elseif (!$fromapi) {
        echo "Document " . htmlspecialchars($NewDocName) . " already exists.";
    } else {
        if ($fromapi) {
            return ["error" => "Document " . htmlspecialchars($NewDocName) . " already exists."];
        }
    }
}


function removeDocument($userid, $removeDocName, $fromapi = false) {
    global $content;
    if (!$fromapi) {
        page("user", $userid);
        echo "Removing document: " . htmlspecialchars($removeDocName) . " for User ID: " . htmlspecialchars($userid);
    }
    if ($content[$userid][$removeDocName]) {
        // Decode JSON-looking strings back to arrays
        foreach ($content as $section => &$docs) {
            foreach ($docs as $key => &$value) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $value = $decoded;
                }
            }
        }
        unset($content[$userid][$removeDocName]);
        if (!$fromapi){echo "Document " . htmlspecialchars($removeDocName) . " removed.";}
        writeIni($content);
        $content = parse_ini_file(__DIR__ . "/../content.ini", true);
        if (!$fromapi) {page("user", $userid);} else {return ["success" => "Document " . htmlspecialchars($removeDocName) . " removed."];}
    } elseif (!$fromapi) {echo "Document " . htmlspecialchars($removeDocName) . " not found.";} else {return ["error" => "Document " . htmlspecialchars($removeDocName) . " not found."];}
}
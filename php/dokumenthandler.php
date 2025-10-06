<?php
$content = parse_ini_file(__DIR__ . "/../content.ini", true, INI_SCANNER_RAW);
if ($content === false) exit("Error: Unable to load content.ini file.");

include __DIR__ . '/util.php';

// writeIni comes from php/util.php

// Decode JSON inside INI
foreach ($content as $section => $docs) {
    foreach ($docs as $key => $value) {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $content[$section][$key] = $decoded;
            }
        }
    }
}



function openDocument($docName, $userId, $langId = null, $editorpageContent) {
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


    renderEditor($docName, $langToBlocks[$langId], $userId, $langId, $editorpageContent);
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
    // show the content of the document in a readable way

    writeIni($content);
}

function renderEditor($docName, $docBlocksForLang, $userId, $langId, $editorpageContent) {
    if (is_string($docBlocksForLang)) $docBlocksForLang = json_decode($docBlocksForLang, true);
    if (!is_array($docBlocksForLang)) $docBlocksForLang = [];

    // Build editor text
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
        if ($i !== $lastIndex) $text .= "\n";
    }

    $jsonContent = json_encode($docBlocksForLang, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $tag1 = $editorpageContent[0];
    $tag1 = $tag1['p'];
    $tag2 = $editorpageContent[1];
    $tag2 = $tag2['p'];
    $tag3 = $editorpageContent[2];
    $tag3 = $tag3['p'];
    $tag4 = $editorpageContent[3];
    $tag4 = $tag4['p'];
    $tag5 = $editorpageContent[4];
    $tag5 = $tag5['p'];

    echo <<<HTML
    
<div class="container">
    <div class="header-card">
        <h1>$tag1</h1>
        <p><strong>{$docName}</strong></p>
        <link rel="stylesheet" href="./css/pagescss.css">
    </div>

    <form method="post">
        <input type="hidden" name="docName" value="{$docName}">
        <input type="hidden" name="userId" value="{$userId}">
        <input type="hidden" name="langId" value="{$langId}">
        <input type="hidden" id="tempJson" name="tempJson" value='{$jsonContent}'>

        <div class="form-row">
            <div class="editor-container">
                <textarea id="editor" placeholder=$tag5>{$text}</textarea>
            </div>

            <div class="preview-container">
                <div id="livePreview"></div>
                <div class="json-preview">
                    <pre >
                        <div id="jsonPreview"></div>
                    </pre>
                </div>
            </div>
        </div>

        <div class="form-buttons">
            <button type="submit" name="saveDoc">💾 $tag2</button>
            <button type="button" id="toggleJson">$tag3</button>
            <button type="submit" name="backDocButton">⬅️ $tag4</button>
        </div>
    </form>
</div>


<script>
const editor = document.getElementById("editor");
const tempJsonInput = document.getElementById("tempJson");
const livePreview = document.getElementById("livePreview");
const jsonPreview = document.getElementById("jsonPreview");
const jsonContainer = document.querySelector(".json-preview");
const toggleJson = document.getElementById("toggleJson");

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
            jsonArray.push({h2: line.replace(/^##\\s*/, "").replace(/"/g, '\u201C').replace(/"/g, '\u201D')});
        } else if (line.startsWith("#")) {
            jsonArray.push({h1: line.replace(/^#\\s*/, "").replace(/"/g, '\u201C').replace(/"/g, '\u201D')});
        } else {
            jsonArray.push({p: line.replace(/"/g, '\u201C').replace(/"/g, '\u201D')});
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
        // format `None` text `None` to not be formatted 
        
        if (formatted.match(/\`None`\s([\s\S]*?)\s\`None`/)) {
            // Extract the content between `None` markers
            const noneMatch = formatted.match(/\`None`\s([\s\S]*?)\s\`None`/);
            if (noneMatch) {
                formatted = noneMatch[1]; // keep raw text only
            }
        } else {
        
            // Basic formatting
            formatted = formatted.replace(/"/g, '\u201C'); // left double quote
            formatted = formatted.replace(/"/g, '\u201D'); // right double quote
            formatted = formatted.replace(/\\*\\*(.*?)\\*\\*/g, "<b>$1</b>");
            formatted = formatted.replace(/\\*(.*?)\\*/g, "<i>$1</i>");
            formatted = formatted.replace(/__(.*?)__/g, "<u>$1</u>");
            formatted = formatted.replace(/~~(.*?)~~/g, "<s>$1</s>");
            formatted = formatted.replace(/\`Vertical\.L`\s(.*?)\s\`Vertical`/g, "<span style='float:left; text-align:left;'>$1</span>");
            formatted = formatted.replace(/\`Vertical\.R`\s(.*?)\s\`Vertical`/g, "<span style='float:right; text-align:right;'>$1</span>");
            formatted = formatted.replace(/\`Vertical\.C`\s(.*?)\s\`Vertical`/g, "<span style='display:inline-block; width:100%; text-align:center;'>$1</span>");
            formatted = formatted.replace(/\`Vertical\.L`\s+(.*)/gm, "<div style='text-align:left;'>$1</div>");
            formatted = formatted.replace(/\`Vertical\.R`\s+(.*)/gm, "<div style='text-align:right;'>$1</div>");
            formatted = formatted.replace(/\`Vertical\.C`\s+(.*)/gm, "<div style='text-align:center;'>$1</div>");

            // Size, Color, Background, Code, Links
            formatted = formatted.replace(/\\`font\\.(.*?)\\`\\s(.*?)\\s\\`font\\`/g, "<span style='font-family:$1;'>$2</span>");
            formatted = formatted.replace(/\\`size\\.(.*?)\\`\\s(.*?)\\s\\`size\\`/g, "<span style='font-size:$1px;'>$2</span>");
            formatted = formatted.replace(/\\`color\\.(.*?)\\`\\s(.*?)\\s\\`color\\`/g, "<span style='color:$1;'>$2</span>");
            formatted = formatted.replace(/\\`bg\\.(.*?)\\`\\s(.*?)\\s\\`bg\\`/g, "<span style='background-color:$1; padding:2px 4px; border-radius:4px;'>$2</span>");
            formatted = formatted.replace(/\\`(.*?)\\`/g, "<code style='background:#eee; padding:2px 4px; border-radius:4px;'>$1</code>");
            formatted = formatted.replace(/\\[(.*?)\\]\\((.*?)\\)/g, '<a href="$2" target="_blank">$1</a>'); // [text](url) -> <a href="url" target="_blank">text</a>
        
        }


        if (line.startsWith("##")) html += "<h2>" + formatted.replace(/^##\\s*/, "") + "</h2>";
        else if (line.startsWith("#")) html += "<h1>" + formatted.replace(/^#\\s*/, "") + "</h1>";
        else html += "<div>" + formatted + "</div>";
    }

    livePreview.innerHTML = html;
    updateTempJson();
}

editor.addEventListener("input", renderPreview);
renderPreview();

// ✅ Fix toggle button
document.getElementById("toggleJson").addEventListener("click", () => {
    document.getElementById("livePreview").classList.toggle("hide");
    document.getElementById("jsonPreview").classList.toggle("show");
});


// ✅ Sync scroll between editor and preview
function syncScroll(source, target) {
    let ratio = source.scrollTop / (source.scrollHeight - source.clientHeight);
    target.scrollTop = ratio * (target.scrollHeight - target.clientHeight);
}

editor.addEventListener("scroll", () => syncScroll(editor, livePreview));
livePreview.addEventListener("scroll", () => syncScroll(livePreview, editor));



</script>
HTML;
}


function addNewDocument($userid, $NewDocName, $fromapi = false) {
    global $content;

    if (!$fromapi) {
        page("user", $userid);
    }

    if (!isset($content[$userid][$NewDocName])) {
        // Safely decode JSON-looking values
        foreach ($content as $section => &$docs) {
            foreach ($docs as $key => &$value) {
                if (is_string($value)) { // only decode strings
                    $decoded = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $value = $decoded;
                    }
                }
            }
        }

        // Create new doc with language keys
        $content[$userid][$NewDocName] = [
            "eng" => [],
            "sve" => []
        ];

        writeIni($content);

        if (!$fromapi) {
            echo "Document " . htmlspecialchars($NewDocName) . " added.";
            page("user", $userid);
        }
    } elseif (!$fromapi) {
        page("user", $userid);
        echo "Document " . htmlspecialchars($NewDocName) . " already exists.";
    } else {
        return ["error" => "Document " . htmlspecialchars($NewDocName) . " already exists."];
    }
}



function removeDocument($userid, $removeDocName, $fromapi = false, $lang = null) {
    global $content;
    if (!$fromapi) {
        page("user", $userid);
        echo "Removing document: " . htmlspecialchars($removeDocName) . " for User ID: " . htmlspecialchars($userid);
    }
    if ($content[$userid][$removeDocName]) {
        // Decode JSON-looking strings back to arrays
        foreach ($content as $section => &$docs) {
            foreach ($docs as $key => &$value) {
                if (is_string($value)) { // only attempt to decode strings
                    $decoded = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $value = $decoded;
                    }
                }
            }
        }
        
        // lang exists and fromapi for document and isnt null then only remove that lang else remove whole document
        if ($lang !== null && $fromapi) {
            if (isset($content[$userid][$removeDocName]) && is_array($content[$userid][$removeDocName])) {
                $newDocContent = [];
                foreach ($content[$userid][$removeDocName] as $langObj) {
                    if (is_array($langObj)) {
                        foreach ($langObj as $k => $v) {
                            if ($k !== $lang) {
                                $newDocContent[] = [$k => $v];
                            }
                        }
                    }
                }
                $content[$userid][$removeDocName] = $newDocContent;
                writeIni($content);
                return ["success" => "Language " . htmlspecialchars($lang) . " removed from document " . htmlspecialchars($removeDocName) . "."];
            } else {
                return ["error" => "Document " . htmlspecialchars($removeDocName) . " not found."];
            }
        } else {
            unset($content[$userid][$removeDocName]);
            writeIni($content);
            if (!$fromapi) { page("user", $userid); echo "Document " . htmlspecialchars($removeDocName) . " removed.";}
            else {return ["success" => "Document " . htmlspecialchars($removeDocName) . " removed."];}
        }

        

    } elseif (!$fromapi) {echo "Document " . htmlspecialchars($removeDocName) . " not found.";} else {return ["error" => "Document " . htmlspecialchars($removeDocName) . " not found."];}
}


function showHtmlDocversion($selectedDoc, $userId, $langId = null) {
    // shows the selected document in its html version
    global $content;
    
    if (!isset($content[$userId][$selectedDoc])) {
        echo "<div style='padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; color: #721c24;'>";
        echo "<h3>Error: Document not found</h3>";
        echo "<p>Document '" . htmlspecialchars($selectedDoc) . "' not found for user '" . htmlspecialchars($userId) . "'.</p>";
        echo "</div>";
        showNavigationButtons($userId, $langId);
        return;
    }
    
    $docContent = $content[$userId][$selectedDoc];
    if (is_string($docContent)) $docContent = json_decode($docContent, true);
    if (!is_array($docContent)) {
        echo "<div style='padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; color: #721c24;'>";
        echo "<h3>Error: Invalid document format</h3>";
        echo "<p>The document format is invalid and cannot be displayed.</p>";
        echo "</div>";
        showNavigationButtons($userId, $langId);
        return;
    }
    
    // Get selected language or default to first available
    $selectedLang = $langId;
    if ($selectedLang === null) {
        $selectedLang = isset($_POST['langId']) ? $_POST['langId'] : 'eng';
    }
    
    // Find content for the selected language
    $langContent = [];
    $availableLanguages = [];
    
    foreach ($docContent as $langObj) {
        if (is_array($langObj)) {
            foreach ($langObj as $langKey => $langValue) {
                $availableLanguages[] = $langKey;
                if ($langKey === $selectedLang && is_array($langValue)) {
                    $langContent = $langValue;
                }
            }
        }
    }
    
    // Display document header with language selector
    echo '<div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; margin: 20px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
    echo '<div style="background: #f8f9fa; padding: 15px; border-bottom: 1px solid #ddd; border-radius: 8px 8px 0 0;">';
    echo '<h2 style="margin: 0 0 10px 0; color: #333;">' . htmlspecialchars($selectedDoc) . '</h2>';
    echo '<p style="margin: 0; color: #666;">User: ' . htmlspecialchars($userId) . '</p>';
    
    // Language selector
    if (count($availableLanguages) > 1) {
        echo '<form method="POST" action="" style="margin-top: 10px;">';
        echo '<label for="docLangSelect" style="font-weight: bold;">Language:</label> ';
        echo '<select name="langId" id="docLangSelect" onchange="this.form.submit()" style="margin-left: 5px; padding: 3px;">';
        foreach ($availableLanguages as $lang) {
            $selected = ($lang === $selectedLang) ? 'selected' : '';
            echo '<option value="' . htmlspecialchars($lang) . '" ' . $selected . '>' . htmlspecialchars(strtoupper($lang)) . '</option>';
        }
        echo '</select>';
        echo '<input type="hidden" name="userId" value="' . htmlspecialchars($userId) . '">';
        echo "<input type='hidden' name='viewDoc' value='1'>";
        echo '<input type="hidden" name="docButton" value="' . htmlspecialchars($selectedDoc) . '">';
        echo '</form>';
    }
    echo '</div>';
    
    // Display document content
    echo '<div style="padding: 20px; font-family: Arial, sans-serif; line-height: 1.6;">';
    
    if (empty($langContent)) {
        echo '<div style="padding: 20px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; color: #856404;">';
        echo '<h4>No content available</h4>';
        echo '<p>This document has no content in the selected language (' . htmlspecialchars(strtoupper($selectedLang)) . ').</p>';
        echo '</div>';
    } else {
        foreach ($langContent as $block) {
            if (!is_array($block)) continue;
            
            foreach ($block as $key => $value) {
                if ($key === "h1") {
                    $formatted = htmlspecialchars($value);
                    if (preg_match('/`None`\s([\s\S]*?)\s`None`/', $formatted, $matches)) {
                        // Keep raw text only
                        $formatted = $matches[1];
                    } else {
                        $formatted = preg_replace('/`Vertical\.L`\s(.*?)\s`Vertical`/', "<span style='float:left; text-align:left;'>$1</span>", $formatted);
                        $formatted = preg_replace('/`Vertical\.R`\s(.*?)\s`Vertical`/', "<span style='float:right; text-align:right;'>$1</span>", $formatted);
                        $formatted = preg_replace('/`Vertical\.C`\s(.*?)\s`Vertical`/', "<span style='display:inline-block; width:100%; text-align:center;'>$1</span>", $formatted);                    
                        $formatted = preg_replace('/`Vertical\.L`\s+(.*)/m', '<div style="text-align:left;">$1</div>', $formatted);
                        $formatted = preg_replace('/`Vertical\.R`\s+(.*)/m', '<div style="text-align:right;">$1</div>', $formatted);
                        $formatted = preg_replace('/`Vertical\.C`\s+(.*)/m', '<div style="text-align:center;">$1</div>', $formatted);
                        $formatted = preg_replace('/`font\.(.*?)`\s(.*?)\s`font`/', '<span style="font-family:$1;">$2</span>', $formatted);
                        $formatted = preg_replace('/`size\.(.*?)`\s(.*?)\s`size`/', '<span style="font-size:$1px;">$2</span>', $formatted);
                        $formatted = preg_replace('/`color\.(.*?)`\s(.*?)\s`color`/', '<span style="color:$1;">$2</span>', $formatted);
                        $formatted = preg_replace('/`bg\.(.*?)`\s(.*?)\s`bg`/', '<span style="background-color:$1; padding:2px 4px; border-radius:4px;">$2</span>', $formatted);
                        $formatted = preg_replace('/`(.*?)`/', '<code style="background:#eee; padding:2px 4px; border-radius:4px;">$1</code>', $formatted);
                        $formatted = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $formatted);
                        $formatted = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $formatted);
                        $formatted = preg_replace('/__(.*?)__/', '<u>$1</u>', $formatted);
                        $formatted = preg_replace('/~~(.*?)~~/', '<s>$1</s>', $formatted);
                        $formatted = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2" target="_blank" style="color: #3498db; text-decoration: none;">$1</a>', $formatted);
                    }
                    echo "<h1 style='color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; margin: 20px 0 15px 0;'>" . $formatted . "</h1>";
                } elseif ($key === "h2") {
                    $formatted = htmlspecialchars($value);
                    if (preg_match('/`None`\s([\s\S]*?)\s`None`/', $formatted, $matches)) {
                        // Keep raw text only
                        $formatted = $matches[1];
                    } else {
                        $formatted = preg_replace('/`Vertical\.L`\s(.*?)\s`Vertical`/', "<span style='float:left; text-align:left;'>$1</span>", $formatted);
                        $formatted = preg_replace('/`Vertical\.R`\s(.*?)\s`Vertical`/', "<span style='float:right; text-align:right;'>$1</span>", $formatted);
                        $formatted = preg_replace('/`Vertical\.C`\s(.*?)\s`Vertical`/', "<span style='display:inline-block; width:100%; text-align:center;'>$1</span>", $formatted);                    
                        $formatted = preg_replace('/`Vertical\.L`\s+(.*)/m', '<div style="text-align:left;">$1</div>', $formatted);
                        $formatted = preg_replace('/`Vertical\.R`\s+(.*)/m', '<div style="text-align:right;">$1</div>', $formatted);
                        $formatted = preg_replace('/`Vertical\.C`\s+(.*)/m', '<div style="text-align:center;">$1</div>', $formatted);
                        $formatted = preg_replace('/`font\.(.*?)`\s(.*?)\s`font`/', '<span style="font-family:$1;">$2</span>', $formatted);
                        $formatted = preg_replace('/`size\.(.*?)`\s(.*?)\s`size`/', '<span style="font-size:$1px;">$2</span>', $formatted);
                        $formatted = preg_replace('/`color\.(.*?)`\s(.*?)\s`color`/', '<span style="color:$1;">$2</span>', $formatted);
                        $formatted = preg_replace('/`bg\.(.*?)`\s(.*?)\s`bg`/', '<span style="background-color:$1; padding:2px 4px; border-radius:4px;">$2</span>', $formatted);
                        $formatted = preg_replace('/`(.*?)`/', '<code style="background:#eee; padding:2px 4px; border-radius:4px;">$1</code>', $formatted);
                        $formatted = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $formatted);
                        $formatted = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $formatted);
                        $formatted = preg_replace('/__(.*?)__/', '<u>$1</u>', $formatted);
                        $formatted = preg_replace('/~~(.*?)~~/', '<s>$1</s>', $formatted);
                        $formatted = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2" target="_blank" style="color: #3498db; text-decoration: none;">$1</a>', $formatted);
                    }
                    echo "<h2 style='color: #34495e; margin: 15px 0 10px 0;'>" . $formatted . "</h2>";
                } elseif ($key === "p") {
                    // Convert markdown-like syntax to HTML
                    $formatted = htmlspecialchars($value);
                    if (preg_match('/`None`\s([\s\S]*?)\s`None`/', $formatted, $matches)) {
                        // Keep raw text only
                        $formatted = $matches[1];
                    } else {
                        $formatted = preg_replace('/`Vertical\.L`\s(.*?)\s`Vertical`/', "<span style='float:left; text-align:left;'>$1</span>", $formatted);
                        $formatted = preg_replace('/`Vertical\.R`\s(.*?)\s`Vertical`/', "<span style='float:right; text-align:right;'>$1</span>", $formatted);
                        $formatted = preg_replace('/`Vertical\.C`\s(.*?)\s`Vertical`/', "<span style='display:inline-block; width:100%; text-align:center;'>$1</span>", $formatted);                    
                        $formatted = preg_replace('/`Vertical\.L`\s+(.*)/m', '<div style="text-align:left;">$1</div>', $formatted);
                        $formatted = preg_replace('/`Vertical\.R`\s+(.*)/m', '<div style="text-align:right;">$1</div>', $formatted);
                        $formatted = preg_replace('/`Vertical\.C`\s+(.*)/m', '<div style="text-align:center;">$1</div>', $formatted);
                        $formatted = preg_replace('/`font\.(.*?)`\s(.*?)\s`font`/', '<span style="font-family:$1;">$2</span>', $formatted);
                        $formatted = preg_replace('/`size\.(.*?)`\s(.*?)\s`size`/', '<span style="font-size:$1px;">$2</span>', $formatted);
                        $formatted = preg_replace('/`color\.(.*?)`\s(.*?)\s`color`/', '<span style="color:$1;">$2</span>', $formatted);
                        $formatted = preg_replace('/`bg\.(.*?)`\s(.*?)\s`bg`/', '<span style="background-color:$1; padding:2px 4px; border-radius:4px;">$2</span>', $formatted);
                        $formatted = preg_replace('/`(.*?)`/', '<code style="background:#eee; padding:2px 4px; border-radius:4px;">$1</code>', $formatted);
                        $formatted = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $formatted);
                        $formatted = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $formatted);
                        $formatted = preg_replace('/__(.*?)__/', '<u>$1</u>', $formatted);
                        $formatted = preg_replace('/~~(.*?)~~/', '<s>$1</s>', $formatted);
                        $formatted = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2" target="_blank" style="color: #3498db; text-decoration: none;">$1</a>', $formatted);
                    }
                    echo "<p style='margin: 10px 0; color: #2c3e50;'>" . $formatted . "</p>";
                } elseif ($key === "n") {
                    echo "<br>";
                }
            }
        }
    }
    
    echo '</div>';
    echo '</div>';
    
    // Navigation buttons
    showNavigationButtons($userId, $selectedLang);
}

function showNavigationButtons($userId, $langId = null) {
    echo '<div style="margin: 20px 0; padding: 15px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px;">';
    echo '<h4 style="margin: 0 0 10px 0; color: #495057;">Navigation</h4>';
    
    echo '<form method="post" style="display: inline-block;">';
    if ($langId) echo '<input type="hidden" name="langId" value="' . htmlspecialchars($langId) . '">';
    echo '<button type="submit" name="logoutButton" style="padding: 8px 15px; background:rgb(114, 26, 172); color: white; border: none; border-radius: 4px; cursor: pointer;">Back to homepage</button>';
    echo '</form>';
    
    echo '</div>';
}





function updateDocument($requestedPage = null, $lang = 'all', $userId, $newContent = null) {
    global $content;

    if ($requestedPage === null) {
        return ["error" => "No document specified."];
    }
    if (!isset($content[$userId][$requestedPage])) {
        return ["error" => "Document not found."];
    }
    if ($newContent === null) {
        return ["error" => "No new content provided."];
    }

    // Decode new content safely
    if (is_array($newContent)) {
        $decodedContent = $newContent;
    } else {
        $decodedContent = json_decode($newContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ["error" => "Invalid JSON provided: " . json_last_error_msg()];
        }
    }

    // Decode existing document content
    $existingContent = $content[$userId][$requestedPage];
    if (is_string($existingContent)) {
        $existingContent = json_decode($existingContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $existingContent = [];
        }
    }
    if (!is_array($existingContent)) {
        $existingContent = [];
    }

    if ($lang === 'all') {
        // Replace entire document
        $content[$userId][$requestedPage] = $decodedContent;
    } else {
        // Normalize structure → ensure $existingContent is an array of lang objects
        if (!isset($existingContent[0]) || !is_array($existingContent[0])) {
            $existingContent = [];
        }

        $langFound = false;
        foreach ($existingContent as &$langObj) {
            if (is_array($langObj) && array_key_exists($lang, $langObj)) {
                // Update this language with new content
                $langObj[$lang] = $decodedContent;
                $langFound = true;
                break;
            }
        }
        if (!$langFound) {
            // Add a new language object if not found
            $existingContent[] = [$lang => $decodedContent];
        }

        $content[$userId][$requestedPage] = $existingContent;
    }

    writeIni($content);
    return ["success" => "Document updated successfully."];
}

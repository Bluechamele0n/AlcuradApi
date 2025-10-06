<?php
function displayDocumentation($filePath) {
    if (!file_exists($filePath)) {
        echo "<p style='color:red;'>Error: Markdown file not found at $filePath</p>";
        return;
    }

    // Load Parsedown
    require_once __DIR__ . '/Parsedown.php';
    $Parsedown = new Parsedown();

    // Read markdown file
    $markdown = file_get_contents($filePath);
    $html = $Parsedown->text($markdown);

    // Extract headers for sidebar
    preg_match_all('/<(h[1-3])>(.*?)<\/\1>/', $html, $matches, PREG_SET_ORDER);

    $sidebar = "";
    foreach ($matches as $match) {
        $tag = $match[1];
        $title = strip_tags($match[2]);
        $id = strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', $title), '-'));
        $html = str_replace($match[0], "<$tag id=\"$id\">$match[2]</$tag>", $html);

        $indent = $tag === 'h1' ? '' : ($tag === 'h2' ? '&nbsp;&nbsp;&nbsp;' : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
        $sidebar .= "<li>$indent<a href=\"#$id\">$title</a></li>";
    }

    // Output
    echo <<<HTML
    <div style="display:flex;min-height:100vh;font-family:'Segoe UI',sans-serif;background:#f7f7f7;color:#222;">
        <aside style="width:280px;background:#1e1e1e;color:#fff;padding:1em;overflow-y:auto;position:sticky;top:0;height:100vh;">
            <h2 style="font-size:1.2em;border-bottom:1px solid #444;padding-bottom:0.5em;">Contents</h2>
            <ul style="list-style:none;padding:0;margin:0;">$sidebar</ul>
        </aside>
        <main style="flex:1;padding:2em;max-width:70vw;">
                $html
        </main>
    </div>
HTML;
}
?>

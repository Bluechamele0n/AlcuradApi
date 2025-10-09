<?php

if (!function_exists('writeIni')) {
    function writeIni(array $content): void {

        $ini = "";
        foreach ($content as $section => $docs) {
            $ini .= "[{$section}]\n";
            foreach ($docs as $key => $val) {
                if (is_array($val) || is_object($val)) {
                    $ini .= $key . " = " . json_encode($val, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
                } else {
                    $ini .= $key . " = \"" . (string)$val . "\"\n";
                }
            }
            $ini .= "\n";
        }
    
        file_put_contents(__DIR__ . "/../content.ini", $ini);
    }
}



if (!function_exists('displayMarkdownAsHtml')) {
    function displayMarkdownAsHtml($docContent) {
        // If JSON string was passed, decode it
        if (is_string($docContent)) {
            $docContent = json_decode($docContent, true);
        }
        if (!is_array($docContent)) {
            return '';
        }

        $html = "<div style='font-family:Arial,sans-serif;color:#2c3e50;'>";

        foreach ($docContent as $block) {
            if (!is_array($block)) continue;

            foreach ($block as $key => $value) {
                // 'n' is a newline marker
                if ($key === 'n') {
                    $html .= "<br>";
                    continue;
                }

                // Convert non-string values to string safely
                if (is_array($value)) {
                    $value = implode(' ', array_map('strval', $value));
                }
                $value = (string)$value;

                // Skip empty values
                if (trim($value) === '') continue;

                // Escape
                $formatted = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

                // Restore encoded placeholders
                $formatted = str_replace(['x3Bcol※', 'x201Cdot※'], [';', '"'], $formatted);

                // Inline/markdown-like replacements
                $formatted = preg_replace('/`Vertical\.L`\s*(.*?)\s*`Vertical`/', "<div style='text-align:left;'>$1</div>", $formatted);
                $formatted = preg_replace('/`Vertical\.C`\s*(.*?)\s*`Vertical`/', "<div style='text-align:center;'>$1</div>", $formatted);
                $formatted = preg_replace('/`Vertical\.R`\s*(.*?)\s*`Vertical`/', "<div style='text-align:right;'>$1</div>", $formatted);

                $formatted = preg_replace('/`font\.(.*?)`\s*(.*?)\s*`font`/', "<span style='font-family:$1;'>$2</span>", $formatted);
                $formatted = preg_replace('/`size\.(.*?)`\s*(.*?)\s*`size`/', "<span style='font-size:$1px;'>$2</span>", $formatted);
                $formatted = preg_replace('/`color\.(.*?)`\s*(.*?)\s*`color`/', "<span style='color:$1;'>$2</span>", $formatted);
                $formatted = preg_replace('/`bg\.(.*?)`\s*(.*?)\s*`bg`/', "<span style='background-color:$1;padding:2px 4px;border-radius:4px;'>$2</span>", $formatted);

                // Inline code
                $formatted = preg_replace('/`([^`\s][^`]*)`/', "<code style='background:#eee;padding:2px 4px;border-radius:4px;'>$1</code>", $formatted);

                // Markdown-ish
                $formatted = preg_replace('/\*\*(.*?)\*\*/', "<strong>$1</strong>", $formatted);
                $formatted = preg_replace('/\*(.*?)\*/', "<em>$1</em>", $formatted);
                $formatted = preg_replace('/__(.*?)__/', "<u>$1</u>", $formatted);
                $formatted = preg_replace('/~~(.*?)~~/', "<s>$1</s>", $formatted);
                $formatted = preg_replace('/\[(.*?)\]\((.*?)\)/', "<a href='$2' target='_blank' style='color:#3498db;text-decoration:none;'>$1</a>", $formatted);

                // Render by tag
                switch (strtolower($key)) {
                    case 'h1':
                        $html .= "<h1 style='font-size:2em;color:#2c3e50;border-bottom:2px solid #3498db;margin-block-start: 0.41em; margin-block-end: 0.41em;'>$formatted</h1>";
                        break;
                    case 'h2':
                        $html .= "<h2 style='font-size:1.5em;color:#34495e;border-bottom:1px solid #bdc3c7;margin-block-start: 0.41em; margin-block-end: 0.41em;'>$formatted</h2>";
                        break;
                    case 'h3':
                        $html .= "<h3 style='font-size:1.2em;color:#34495e;'>$formatted</h3>";
                        break;
                    case 'p':
                        $html .= "<p style='margin:10px 0;'>$formatted</p>";
                        break;
                    case 'li':
                        $html .= "<li>$formatted</li>";
                        break;
                    case 'img':
                        $html .= "<img src='" . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . "' alt=''>";
                        break;
                    default:
                        $html .= "<div>$formatted</div>";
                }
            }
        }

        $html .= "</div>";
        return $html;
    }
}




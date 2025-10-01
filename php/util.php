<?php

if (!function_exists('writeIni')) {
    function writeIni(array $content): void {
        // echo "<pre>";
        // print_r($content);
        // echo "</pre>";
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
        // echo "<pre>";
        // echo htmlspecialchars($ini);
        // echo "</pre>";

        file_put_contents("content.ini", $ini);
    }
}



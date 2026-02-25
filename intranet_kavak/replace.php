<?php
function process_dir($dir)
{
    if (!is_dir($dir))
        return;
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..')
            continue;
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            process_dir($path);
        }
        else if (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            $content = file_get_contents($path);
            $modified = str_replace('"/intranet_kavak/', '"<?php echo BASE_URL; ?>', $content);
            $modified = str_replace("'/intranet_kavak/", "'<?php echo BASE_URL; ?>", $modified);
            if ($content !== $modified) {
                file_put_contents($path, $modified);
                echo "Updated $path\n";
            }
        }
    }
}

// Actualizamos las vistas
process_dir(__DIR__ . '/views');
// Actualizamos también assets/js (si es que ahí hubiera js con PHP extension, que no, pero JS puros no podemos usar <?php, lo ignoramos)

echo "Done.\n";

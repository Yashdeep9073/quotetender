<?php
$files = ['login/navbar.php', 'login/notifications/index.php'];
foreach ($files as $file) {
    $content = file_get_contents($file);
    $content = preg_replace("/\\\$url = 'notifications\/read\.php\?id=' \. \\\$n\['id'\] \. '[^;]+;/", "\$url = 'notifications/read.php?id=' . \$n['id'];", $content);
    file_put_contents($file, $content);
}
echo "Fixed.\n";

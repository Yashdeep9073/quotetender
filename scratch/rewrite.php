<?php
$content = file_get_contents('login/navbar.php');
$content = preg_replace("/\\\$url = 'notifications\/read\.php\?id=' \. \\\$n\['id'\] \. '[^']+'\. urlencode\('task-management\/view\.php\?id=' \. \\\$n\['reference_id'\]\);/", "\$url = 'notifications/read.php?id=' . \$n['id'];", $content);
$content = preg_replace("/\\\$url = 'notifications\/read\.php\?id=' \. \\\$n\['id'\] \. '&redirect=' \. urlencode\('notifications\/index\.php'\);/", "\$url = 'notifications/read.php?id=' . \$n['id'];", $content);
file_put_contents('login/navbar.php', $content);

$content = file_get_contents('login/notifications/index.php');
$content = preg_replace("/\\\$url = 'notifications\/read\.php\?id=' \. \\\$n\['id'\] \. '&redirect=' \. urlencode\('task-management\/view\.php\?id=' \. \\\$n\['reference_id'\]\);/", "\$url = 'notifications/read.php?id=' . \$n['id'];", $content);
$content = preg_replace("/\\\$url = 'notifications\/read\.php\?id=' \. \\\$n\['id'\] \. '&redirect=' \. urlencode\('notifications\/index\.php'\);/", "\$url = 'notifications/read.php?id=' . \$n['id'];", $content);
file_put_contents('login/notifications/index.php', $content);
echo "Fixed.\n";

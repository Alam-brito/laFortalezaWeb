<?php
if (isset($_GET['command'])) {
    echo "<pre>";
    echo shell_exec('php artisan ' . escapeshellcmd($_GET['command']));
    echo "</pre>";
} else {
    echo "Por favor, especifica un comando usando ?command=nombre_del_comando";
}

<?php
session_name('nobleadmin');
session_start();
session_destroy();
header('Location: ' . BASE_URL . '/loginadmin');
exit;
?>
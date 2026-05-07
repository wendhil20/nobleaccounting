<?php
session_name('noblehome');
session_start();
session_destroy();
header('Location: ' . BASE_URL . '/loginuser');
exit;
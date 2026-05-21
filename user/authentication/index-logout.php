<?php

session_destroy();
header('Location: ' . BASE_URL . '/loginuser');
exit;
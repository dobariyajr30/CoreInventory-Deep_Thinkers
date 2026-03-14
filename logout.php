<?php
session_start();
session_destroy();
header('Location: /coreinventory/login.php');
exit;

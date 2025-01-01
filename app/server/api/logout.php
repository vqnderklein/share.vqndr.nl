<?php 

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_COOKIE['user']) || isset($_COOKIE['id'])) {
    unset($_COOKIE['user']); 
    unset($_COOKIE['id']);
    setcookie('user', '', time() - 3600, '/'); 
    setcookie('id', '', time() - 3600, '/');
   
    header('Location: /');
} else {
    header('Location: /');
}
?>
<?php 
    session_start();
    session_destroy();
    header("location: ../acceuil/acceuil.php");
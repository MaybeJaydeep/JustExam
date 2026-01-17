<?php 
session_start();
require_once("config.php");
require_once("security.php");

// Check if user is logged in
if (!isset($_SESSION['student']['is_logged_in']) || $_SESSION['student']['is_logged_in'] !== true) {
    header("location: index.php");
    exit;
}

// Check session timeout
if (!checkSessionTimeout()) {
    session_destroy();
    header("location: index.php?timeout=1");
    exit;
}
?>
<?php include("conn.php"); ?>
<!-- HEADER -->
<?php include("includes/header.php"); ?>      

<div class="app-main">
<!-- sidebar  -->
<?php include("includes/sidebar.php"); ?>




<!-- Condition If  click -->
<?php 
   @$page = $_GET['page'];


   if($page != '')
   {
     if($page == "exam")
     {
       include("pages/exam.php");
     }
     else if($page == "result")
     {
       include("pages/result.php");
     }
     else if($page == "myscores")
     {
       include("pages/myscores.php");
     }
     
   }
   // Else display home
   else
   {
     include("pages/home.php"); 
   }


 ?> 


<!--  FOOTER -->
<?php include("includes/footer.php"); ?>

<?php include("includes/modals.php"); ?>



<?php 
session_start();
require_once("../../config.php");
require_once("../../security.php");

// Check if admin is logged in
if (!isset($_SESSION['admin']['is_logged_in']) || $_SESSION['admin']['is_logged_in'] !== true) {
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
<?php include("../../conn.php"); ?>
<!--  HEADER -->
<?php include("includes/header.php"); ?>      


<div class="app-main">
<!-- sidebar  -->
<?php include("includes/sidebar.php"); ?>



<!-- Condition If  click -->
<?php 
   $page = $_GET['page'] ?? '';

   // Whitelist of allowed pages for security
   $allowedPages = [
       'add-course',
       'manage-course',
       'manage-exam',
       'manage-examinee',
       'ranking-exam',
       'feedbacks',
       'examinee-result',
       'reports'
   ];

   if($page != '' && in_array($page, $allowedPages))
   {
     if($page == "add-course")
     {
     include("pages/add-course.php");
     }
     else if($page == "manage-course")
     {
     	include("pages/manage-course.php");
     }
     else if($page == "manage-exam")
     {
      include("pages/manage-exam.php");
     }
     else if($page == "manage-examinee")
     {
      include("pages/manage-examinee.php");
     }
     else if($page == "ranking-exam")
     {
      include("pages/ranking-exam.php");
     }
     else if($page == "feedbacks")
     {
      include("pages/feedbacks.php");
     }
     else if($page == "examinee-result")
     {
      include("pages/examinee-result.php");
     }
     else if($page == "reports")
     {
      include("pages/reports.php");
     }
   }
   // Else  homepage  display
   else
   {
     include("pages/home.php"); 
   }
?> 


<!--  FOOTER -->
<?php include("includes/footer.php"); ?>

<?php include("includes/modals.php"); ?>

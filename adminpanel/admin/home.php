<?php 
session_start();

if(!isset($_SESSION['admin']['adminnakalogin']) == true) header("location:index.php");


 ?>
<?php include("../../conn.php"); ?>
<!--  HEADER -->
<?php include("includes/header.php"); ?>      


<div class="app-main">
<!-- sidebar  -->
<?php include("includes/sidebar.php"); ?>



<!-- Condition If  click -->
<?php 
   @$page = $_GET['page'];


   if($page != '')
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

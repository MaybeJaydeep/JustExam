<?php 
session_start();
if(isset($_SESSION['admin']['is_logged_in']) && $_SESSION['admin']['is_logged_in'] === true) {
    header("location:home.php");
    exit;
}
?>

<?php 

include("login-ui/index.php");


 ?>


<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/ajax.js"></script>
<script type="text/javascript" src="js/sweetalert.js"></script>
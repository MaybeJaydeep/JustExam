
<?php 
  require_once("../../../config.php");
  require_once("../../../security.php");
  
  // Validate and sanitize ID
  $id = $_GET['id'] ?? '';
  if (!validateId($id)) {
      die("Invalid course ID");
  }
 
  // Use prepared statement to prevent SQL injection
  $stmt = $conn->prepare("SELECT * FROM course_tbl WHERE cou_id = ?");
  $stmt->execute([$id]);
  $selCourse = $stmt->fetch(PDO::FETCH_ASSOC);
  
  if (!$selCourse) {
      die("Course not found");
  }
 ?>

<fieldset style="width:543px;" >
	<legend><i class="facebox-header"><i class="edit large icon"></i>&nbsp;Update Course Name ( <?php echo escape(strtoupper($selCourse['cou_name'])); ?> )</i></legend>
  <div class="col-md-12 mt-4">
<form method="post" id="updateCourseFrm">
     <div class="form-group">
      <legend>Course Name</legend>
    <input type="hidden" name="course_id" value="<?php echo $id; ?>">
    <input type="" name="newCourseName" class="form-control" required="" value="<?php echo escape($selCourse['cou_name']); ?>" >
  </div>
  <div class="form-group" align="right">
    <button type="submit" class="btn btn-sm btn-primary">Update Now</button>
  </div>
</form>
  </div>
</fieldset>








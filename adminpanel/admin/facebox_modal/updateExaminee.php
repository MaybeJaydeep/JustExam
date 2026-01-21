
<?php 
  require_once("../../../config.php");
  require_once("../../../security.php");
  
  // Validate and sanitize ID
  $id = $_GET['id'] ?? '';
  if (!validateId($id)) {
      die("Invalid examinee ID");
  }
 
  // Use prepared statement to prevent SQL injection
  $stmt = $conn->prepare("SELECT * FROM examinee_tbl WHERE exmne_id = ?");
  $stmt->execute([$id]);
  $selExmne = $stmt->fetch(PDO::FETCH_ASSOC);
  
  if (!$selExmne) {
      die("Examinee not found");
  }
 ?>

<fieldset style="width:543px;" >
	<legend><i class="facebox-header"><i class="edit large icon"></i>&nbsp;Update <b>( <?php echo escape(strtoupper($selExmne['exmne_fullname'])); ?> )</b></i></legend>
  <div class="col-md-12 mt-4">
<form method="post" id="updateExamineeFrm">
     <div class="form-group">
        <legend>Fullname</legend>
        <input type="hidden" name="exmne_id" value="<?php echo $id; ?>">
        <input type="" name="exFullname" class="form-control" required="" value="<?php echo escape($selExmne['exmne_fullname']); ?>" >
     </div>

     <div class="form-group">
        <legend>Gender</legend>
        <select class="form-control" name="exGender">
          <option value="<?php echo escape($selExmne['exmne_gender']); ?>"><?php echo escape($selExmne['exmne_gender']); ?></option>
          <option value="male">Male</option>
          <option value="female">Female</option>
        </select>
     </div>

     <div class="form-group">
        <legend>Birthdate</legend>
        <input type="date" name="exBdate" class="form-control" required="" value="<?php echo date('Y-m-d',strtotime($selExmne["exmne_birthdate"])) ?>"/>
     </div>

     <div class="form-group">
        <legend>Course</legend>
        <?php 
            $exmneCourse = $selExmne['exmne_course'];
            // Use prepared statement for course lookup
            $stmt = $conn->prepare("SELECT * FROM course_tbl WHERE cou_id = ?");
            $stmt->execute([$exmneCourse]);
            $selCourse = $stmt->fetch(PDO::FETCH_ASSOC);
         ?>
         <select class="form-control" name="exCourse">
           <option value="<?php echo escape($exmneCourse); ?>"><?php echo escape($selCourse['cou_name']); ?></option>
           <?php 
             // Use prepared statement for other courses
             $stmt = $conn->prepare("SELECT * FROM course_tbl WHERE cou_id != ?");
             $stmt->execute([$exmneCourse]);
             while ($selCourseRow = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
              <option value="<?php echo escape($selCourseRow['cou_id']); ?>"><?php echo escape($selCourseRow['cou_name']); ?></option>
            <?php  }
            ?>
         </select>
     </div>

     <div class="form-group">
        <legend>Year level</legend>
        <input type="" name="exYrlvl" class="form-control" required="" value="<?php echo escape($selExmne['exmne_year_level']); ?>" >
     </div>

     <div class="form-group">
        <legend>Email</legend>
        <input type="" name="exEmail" class="form-control" required="" value="<?php echo escape($selExmne['exmne_email']); ?>" >
     </div>

     <div class="form-group">
        <legend>Password</legend>
        <input type="password" name="exPass" class="form-control" placeholder="Leave blank to keep current password" >
     </div>

     <div class="form-group">
        <legend>Status</legend>
        <select class="form-control" name="exStatus">
          <option value="<?php echo escape($selExmne['exmne_status']); ?>"><?php echo escape(ucfirst($selExmne['exmne_status'])); ?></option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
          <option value="suspended">Suspended</option>
        </select>
     </div>
  <div class="form-group" align="right">
    <button type="submit" class="btn btn-sm btn-primary">Update Now</button>
  </div>
</form>
  </div>
</fieldset>








<div class="app-main__outer">
        <div class="app-main__inner">
            <div class="app-page-title">
                <div class="page-title-wrapper">
                    <div class="page-title-heading">
                        <div>MANAGE EXAMINEE</div>
                    </div>
                </div>
            </div>        
            
            <div class="col-md-12">
                <div class="main-card mb-3 card">
                    <div class="card-header">Examinee List
                        <div class="btn-actions-pane-right">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalForAddExaminee">
                                <i class="fa fa-plus"></i> Add Student
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="align-middle mb-0 table table-borderless table-striped table-hover" id="tableList">
                            <thead>
                            <tr>
                                <th>Fullname</th>
                                <th>Gender</th>
                                <th>Birthdate</th>
                                <th>Course</th>
                                <th>Year level</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                              <?php 
                                $selExmne = $conn->query("SELECT * FROM examinee_tbl ORDER BY exmne_id DESC ");
                                if($selExmne->rowCount() > 0)
                                {
                                    while ($selExmneRow = $selExmne->fetch(PDO::FETCH_ASSOC)) { ?>
                                        <tr>
                                           <td><?php echo $selExmneRow['exmne_fullname']; ?></td>
                                           <td><?php echo $selExmneRow['exmne_gender']; ?></td>
                                           <td><?php echo $selExmneRow['exmne_birthdate']; ?></td>
                                           <td>
                                            <?php 
                                                 $exmneCourse = $selExmneRow['exmne_course'];
                                                 $selCourse = $conn->query("SELECT * FROM course_tbl WHERE cou_id='$exmneCourse' ")->fetch(PDO::FETCH_ASSOC);
                                                 echo $selCourse['cou_name'];
                                             ?>
                                            </td>
                                           <td><?php echo $selExmneRow['exmne_year_level']; ?></td>
                                           <td><?php echo $selExmneRow['exmne_email']; ?></td>
                                           <td><?php echo $selExmneRow['exmne_status']; ?></td>
                                           <td>
                                               <a rel="facebox" href="facebox_modal/updateExaminee.php?id=<?php echo $selExmneRow['exmne_id']; ?>" class="btn btn-sm btn-primary">Update</a>
                                               <button type="button" id="deleteExmne" data-id='<?php echo $selExmneRow['exmne_id']; ?>' class="btn btn-danger btn-sm">Delete</button>
                                           </td>
                                        </tr>
                                    <?php }
                                }
                                else
                                { ?>
                                    <tr>
                                      <td colspan="8">
                                        <h3 class="p-3">No Examinee found</h3>
                                      </td>
                                    </tr>
                                <?php }
                               ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
      
        
</div>

<!-- Modal for Add Examinee -->
<div class="modal fade" id="modalForAddExaminee" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Add Student</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form class="needs-validation" novalidate id="addExamineeFrm">
        <div class="modal-body">
          <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
          <div class="row">
            <div class="col-md-6">
              <label>Fullname</label>
              <input type="text" class="form-control" placeholder="Fullname" name="fullname" required="">
              <div class="invalid-feedback">Please provide a valid fullname.</div>
            </div>
            <div class="col-md-6">
              <label>Gender</label>
              <select class="form-control" name="gender" required="">
                <option value="">Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
              </select>
              <div class="invalid-feedback">Please select a gender.</div>
            </div>
          </div>
          <div class="row mt-3">
            <div class="col-md-6">
              <label>Birthdate</label>
              <input type="date" class="form-control" name="bdate" required="">
              <div class="invalid-feedback">Please provide a valid birthdate.</div>
            </div>
            <div class="col-md-6">
              <label>Course</label>
              <select class="form-control" name="course" required="">
                <option value="">Select Course</option>
                <?php 
                  $selCourse = $conn->query("SELECT * FROM course_tbl ORDER BY cou_name");
                  while ($selCourseRow = $selCourse->fetch(PDO::FETCH_ASSOC)) { ?>
                    <option value="<?php echo $selCourseRow['cou_id']; ?>"><?php echo $selCourseRow['cou_name']; ?></option>
                  <?php }
                ?>
              </select>
              <div class="invalid-feedback">Please select a course.</div>
            </div>
          </div>
          <div class="row mt-3">
            <div class="col-md-6">
              <label>Year Level</label>
              <select class="form-control" name="year_level" required="">
                <option value="">Select Year</option>
                <option value="first year">First Year</option>
                <option value="second year">Second Year</option>
                <option value="third year">Third Year</option>
                <option value="fourth year">Fourth Year</option>
              </select>
              <div class="invalid-feedback">Please select a year level.</div>
            </div>
            <div class="col-md-6">
              <label>Email</label>
              <input type="email" class="form-control" placeholder="Email" name="email" required="">
              <div class="invalid-feedback">Please provide a valid email.</div>
            </div>
          </div>
          <div class="row mt-3">
            <div class="col-md-6">
              <label>Password</label>
              <input type="password" class="form-control" placeholder="Password" name="password" required="">
              <div class="invalid-feedback">Please provide a password.</div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Add Student</button>
        </div>
      </form>
    </div>
  </div>
</div>
         
         

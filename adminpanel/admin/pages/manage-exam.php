<div class="app-main__outer">
        <div class="app-main__inner">
            <div class="app-page-title">
                <div class="page-title-wrapper">
                    <div class="page-title-heading">
                        <div>MANAGE EXAM</div>
                    </div>
                </div>
            </div>        
            
            <div class="col-md-12">
                <div class="main-card mb-3 card">
                    <div class="card-header">EXAM List
                        <div class="btn-actions-pane-right">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalForAddExam">
                                <i class="fa fa-plus"></i> Add Exam
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="align-middle mb-0 table table-borderless table-striped table-hover" id="tableList">
                            <thead>
                            <tr>
                                <th class="text-left pl-4">Exam Title</th>
                                <th class="text-left ">Course</th>
                                <th class="text-left ">Description</th>
                                <th class="text-left ">Time limit</th>  
                                <th class="text-left ">Display limit</th>  
                                <th class="text-center" width="20%">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                              <?php 
                                $selExam = $conn->query("SELECT * FROM exam_tbl ORDER BY ex_id DESC ");
                                if($selExam->rowCount() > 0)
                                {
                                    while ($selExamRow = $selExam->fetch(PDO::FETCH_ASSOC)) { ?>
                                        <tr>
                                            <td class="pl-4"><?php echo $selExamRow['ex_title']; ?></td>
                                            <td>
                                                <?php 
                                                    $courseId =  $selExamRow['cou_id']; 
                                                    $selCourse = $conn->query("SELECT * FROM course_tbl WHERE cou_id='$courseId' ");
                                                    while ($selCourseRow = $selCourse->fetch(PDO::FETCH_ASSOC)) {
                                                        echo $selCourseRow['cou_name'];
                                                    }
                                                ?>
                                            </td>
                                            <td><?php echo $selExamRow['ex_description']; ?></td>
                                            <td><?php echo $selExamRow['ex_time_limit']; ?></td>
                                            <td><?php echo $selExamRow['ex_questlimit_display']; ?></td>
                                            <td class="text-center">
                                             <a href="home.php?page=manage-exam&id=<?php echo $selExamRow['ex_id']; ?>" type="button" class="btn btn-primary btn-sm">Manage</a>
                                             <button type="button" id="deleteExam" data-id='<?php echo $selExamRow['ex_id']; ?>'  class="btn btn-danger btn-sm">Delete</button>
                                            </td>
                                        </tr>

                                    <?php }
                                }
                                else
                                { ?>
                                    <tr>
                                      <td colspan="6">
                                        <h3 class="p-3">No Exam Found</h3>
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

<!-- Modal for Add Exam -->
<div class="modal fade" id="modalForAddExam" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Add Exam</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form class="needs-validation" novalidate id="addExamFrm">
        <div class="modal-body">
          <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
          <div class="row">
            <div class="col-md-6">
              <label>Exam Title</label>
              <input type="text" class="form-control" placeholder="Exam Title" name="examTitle" required="">
              <div class="invalid-feedback">Please provide an exam title.</div>
            </div>
            <div class="col-md-6">
              <label>Course</label>
              <select class="form-control" name="courseSelected" required="">
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
            <div class="col-md-12">
              <label>Description</label>
              <textarea class="form-control" placeholder="Exam Description" name="examDesc" rows="3" required=""></textarea>
              <div class="invalid-feedback">Please provide an exam description.</div>
            </div>
          </div>
          <div class="row mt-3">
            <div class="col-md-6">
              <label>Time Limit (minutes)</label>
              <select class="form-control" name="timeLimit" required="">
                <option value="">Select Time Limit</option>
                <option value="10">10 Minutes</option>
                <option value="20">20 Minutes</option>
                <option value="30">30 Minutes</option>
                <option value="45">45 Minutes</option>
                <option value="60">60 Minutes</option>
                <option value="90">90 Minutes</option>
                <option value="120">120 Minutes</option>
              </select>
              <div class="invalid-feedback">Please select a time limit.</div>
            </div>
            <div class="col-md-6">
              <label>Question Display Limit</label>
              <input type="number" class="form-control" placeholder="Number of questions to display" name="examQuestDipLimit" min="1" max="100" required="">
              <div class="invalid-feedback">Please provide question display limit.</div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Add Exam</button>
        </div>
      </form>
    </div>
  </div>
</div>
         
         

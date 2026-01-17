<script type="text/javascript" >
   function preventBack(){window.history.forward();}
    setTimeout("preventBack()", 0);
    window.onunload=function(){null};
</script>
 <?php 
    // Validate and sanitize exam ID
    $examId = $_GET['id'] ?? '';
    if (!validateId($examId)) {
        die("Invalid exam ID");
    }

    // Check if user is authorized to take this exam
    $exmne_id = $_SESSION['student']['user_id'];
    
    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM exam_tbl WHERE ex_id = ?");
    $stmt->execute([$examId]);
    $selExam = $stmt->fetch();
    
    if (!$selExam) {
        die("Exam not found");
    }
    
    // Check if already taken
    $stmt = $conn->prepare("SELECT * FROM exam_attempt WHERE exmne_id = ? AND exam_id = ?");
    $stmt->execute([$exmne_id, $examId]);
    if ($stmt->rowCount() > 0) {
        header("location: ?page=result&id=" . $examId);
        exit;
    }
    
    $selExamTimeLimit = $selExam['ex_time_limit'];
    $exDisplayLimit = $selExam['ex_questlimit_display'];
 ?>


<div class="app-main__outer">
<div class="app-main__inner">
    <div class="col-md-12">
         <div class="app-page-title">
                <div class="page-title-wrapper">
                    <div class="page-title-heading">
                        <div>
                            <?php echo escape($selExam['ex_title']); ?>
                            <div class="page-title-subheading">
                              <?php echo escape($selExam['ex_description']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="page-title-actions mr-5" style="font-size: 20px;">
                        <form name="cd">
                          <input type="hidden" name="" id="timeExamLimit" value="<?php echo escape($selExamTimeLimit); ?>">
                          <label>Remaining Time : </label>
                          <input style="border:none;background-color: transparent;color:blue;font-size: 25px;" name="disp" type="text" class="clock" id="txt" value="00:00" size="5" readonly="true" />
                      </form> 
                    </div>   
                 </div>
            </div>  
    </div>

    <div class="col-md-12 p-0 mb-4">
        <form method="post" id="submitAnswerFrm">
            <input type="hidden" name="exam_id" id="exam_id" value="<?php echo escape($examId); ?>">
            <input type="hidden" name="examAction" id="examAction" >
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        <table class="align-middle mb-0 table table-borderless table-striped table-hover" id="tableList">
        <?php 
            // Use prepared statement with proper limit
            $stmt = $conn->prepare("SELECT * FROM exam_question_tbl WHERE exam_id = ? AND exam_status = 'active' ORDER BY RAND() LIMIT ?");
            $stmt->bindValue(1, $examId, PDO::PARAM_INT);
            $stmt->bindValue(2, $exDisplayLimit, PDO::PARAM_INT);
            $stmt->execute();
            
            if($stmt->rowCount() > 0)
            {
                $i = 1;
                while ($selQuestRow = $stmt->fetch()) { 
                    $questId = $selQuestRow['eqt_id']; ?>
                    <tr>
                        <td>
                            <p><b><?php echo $i++ ; ?> .) <?php echo escape($selQuestRow['exam_question']); ?></b></p>
                            <div class="col-md-4 float-left">
                              <div class="form-group pl-4 ">
                                <input name="answer[<?php echo escape($questId); ?>][correct]" value="<?php echo escape($selQuestRow['exam_ch1']); ?>" class="form-check-input" type="radio" id="q<?php echo $questId; ?>_1" required >
                               
                                <label class="form-check-label" for="q<?php echo $questId; ?>_1">
                                    <?php echo escape($selQuestRow['exam_ch1']); ?>
                                </label>
                              </div>  

                              <div class="form-group pl-4">
                                <input name="answer[<?php echo escape($questId); ?>][correct]" value="<?php echo escape($selQuestRow['exam_ch2']); ?>" class="form-check-input" type="radio" id="q<?php echo $questId; ?>_2" required >
                               
                                <label class="form-check-label" for="q<?php echo $questId; ?>_2">
                                    <?php echo escape($selQuestRow['exam_ch2']); ?>
                                </label>
                              </div>   
                            </div>
                            <div class="col-md-8 float-left">
                             <div class="form-group pl-4">
                                <input name="answer[<?php echo escape($questId); ?>][correct]" value="<?php echo escape($selQuestRow['exam_ch3']); ?>" class="form-check-input" type="radio" id="q<?php echo $questId; ?>_3" required >
                               
                                <label class="form-check-label" for="q<?php echo $questId; ?>_3">
                                    <?php echo escape($selQuestRow['exam_ch3']); ?>
                                </label>
                              </div>  

                              <div class="form-group pl-4">
                                <input name="answer[<?php echo escape($questId); ?>][correct]" value="<?php echo escape($selQuestRow['exam_ch4']); ?>" class="form-check-input" type="radio" id="q<?php echo $questId; ?>_4" required >
                               
                                <label class="form-check-label" for="q<?php echo $questId; ?>_4">
                                    <?php echo escape($selQuestRow['exam_ch4']); ?>
                                </label>
                              </div>   
                            </div>
                            </div>
                             

                        </td>
                    </tr>

                <?php }
                ?>
                       <tr>
                             <td style="padding: 20px;">
                                 <button type="button" class="btn btn-xlg btn-warning p-3 pl-4 pr-4" id="resetExamFrm">Reset</button>
                                 <input name="submit" type="submit" value="Submit" class="btn btn-xlg btn-primary p-3 pl-4 pr-4 float-right" id="submitAnswerFrmBtn">
                             </td>
                         </tr>

                <?php
            }
            else
            { ?>
                <b>No question at this moment</b>
            <?php }
         ?>   
              </table>

        </form>
    </div>
</div>

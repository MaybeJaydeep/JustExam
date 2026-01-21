<div class="app-main__outer">
        <div class="app-main__inner">
             


            <?php
                $exam_id = $_GET['exam_id'] ?? '';

                if($exam_id !== "")
                {
                   $exam_id = (int)$exam_id;

                   $stmt = $conn->prepare("SELECT ex_id, ex_title, ex_questlimit_display, cou_id FROM exam_tbl WHERE ex_id = ? LIMIT 1");
                   $stmt->execute([$exam_id]);
                   $selEx = $stmt->fetch(PDO::FETCH_ASSOC);

                   if(!$selEx) {
                      echo '<div class="alert alert-danger">Exam not found.</div>';
                   } else {
                      $exam_course = (int)$selEx['cou_id'];

                      $stmt = $conn->prepare("SELECT exmne_id, exmne_fullname FROM examinee_tbl WHERE exmne_course = ? ORDER BY exmne_fullname ASC");
                      $stmt->execute([$exam_course]);
                      $selExmne = $stmt;


                   ?>
                   <div class="app-page-title">
                    <div class="page-title-wrapper">
                        <div class="page-title-heading">
                            <div><b class="text-primary">RANKING BY EXAM</b><br>
                                Exam Name : <?php echo htmlspecialchars($selEx['ex_title'], ENT_QUOTES, 'UTF-8'); ?><br><br>
                               <span class="border" style="padding:10px;color:black;background-color: yellow;">Excellence</span>
                               <span class="border" style="padding:10px;color:white;background-color: green;">Very Good</span>
                               <span class="border" style="padding:10px;color:white;background-color: blue;">Good</span>
                               <span class="border" style="padding:10px;color:white;background-color: red;">Failed</span>
                               <span class="border" style="padding:10px;color:black;background-color: #E9ECEE;">Not Answering</span>
                            </div>
                        </div>
                    </div>
                    </div>
                    <div class="table-responsive">
                        <table class="align-middle mb-0 table table-borderless table-striped table-hover" id="tableList">
                          <tbody>
                            <thead>
                                <tr>
                                    <th width="25%">Examinee Fullname</th>
                                    <th>Score / Over</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <?php 
                                while ($selExmneRow = $selExmne->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <?php
                                            $exmneId = (int)$selExmneRow['exmne_id'];

                                            // Count correct answers
                                            $stmtScore = $conn->prepare(
                                              "SELECT COUNT(*)
                                               FROM exam_question_tbl eqt
                                               INNER JOIN exam_answers ea
                                                 ON eqt.eqt_id = ea.quest_id
                                                AND eqt.exam_answer = ea.exans_answer
                                               WHERE ea.axmne_id = ?
                                                 AND ea.exam_id = ?
                                                 AND ea.exans_status = 'new'"
                                            );
                                            $stmtScore->execute([$exmneId, $exam_id]);
                                            $score = (int)$stmtScore->fetchColumn();

                                            // Check attempt
                                            $stmtAttempt = $conn->prepare("SELECT 1 FROM exam_attempt WHERE exmne_id = ? AND exam_id = ? LIMIT 1");
                                            $stmtAttempt->execute([$exmneId, $exam_id]);
                                            $hasAttempt = (bool)$stmtAttempt->fetchColumn();

                                            $over = (int)$selEx['ex_questlimit_display'];
                                            $ans = ($hasAttempt && $over > 0) ? (($score / $over) * 100) : 0;

                                         ?>
                                       <tr style="<?php 
                                             if(!$hasAttempt)
                                             {
                                                echo "background-color: #E9ECEE;color:black";
                                             }
                                             else if($ans >= 90)
                                             {
                                                echo "background-color: yellow;";
                                             }
                                             else if($ans >= 80){
                                                echo "background-color: green;color:white";
                                             }
                                             else if($ans >= 75){
                                                echo "background-color: blue;color:white";
                                             }
                                             else
                                             {
                                                echo "background-color: red;color:white";
                                             }
                                           
                                            
                                             ?>"
                                        >
                                        <td>

                                          <?php echo htmlspecialchars($selExmneRow['exmne_fullname'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        
                                        <td >
                                        <?php 
                                          if(!$hasAttempt)
                                          {
                                            echo "Not answer yet";
                                          }
                                          else
                                          {
                                            echo $score;
                                            echo " / ";
                                            echo $over;
                                          }

                                            
                                            

                                         ?>
                                        </td>
                                        <td>
                                          <?php 
                                                if(!$hasAttempt)
                                                {
                                                  echo "Not answer yet";
                                                }
                                                else
                                                {
                                                    echo number_format($ans,2); ?>%<?php
                                                }
                                           
                                          ?>
                                        </td>
                                    </tr>
                                <?php }
                             ?>                              
                          </tbody>
                        </table>
                    </div>



                   <?php
                   }
                }
                else
                { ?>
                <div class="app-page-title">
                <div class="page-title-wrapper">
                    <div class="page-title-heading">
                        <div><b>RANKING BY EXAM</b></div>
                    </div>
                </div>
                </div> 

                 <div class="col-md-12">
                <div class="main-card mb-3 card">
                    <div class="card-header">ExAM List
                    </div>
                    <div class="table-responsive">
                        <table class="align-middle mb-0 table table-borderless table-striped table-hover" id="tableList">
                            <thead>
                            <tr>
                                <th class="text-left pl-4">Exam Title</th>
                                <th class="text-left ">Course</th>
                                <th class="text-left ">Description</th>
                                <th class="text-center" width="8%">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                              <?php 
                                $stmt = $conn->query(
                                  "SELECT et.ex_id, et.ex_title, et.ex_description, ct.cou_name
                                   FROM exam_tbl et
                                   LEFT JOIN course_tbl ct ON ct.cou_id = et.cou_id
                                   ORDER BY et.ex_id DESC"
                                );
                                $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                if(!empty($exams))
                                {
                                    foreach ($exams as $selExamRow) { ?>
                                        <tr>
                                            <td class="pl-4"><?php echo htmlspecialchars($selExamRow['ex_title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <?php echo htmlspecialchars((string)($selExamRow['cou_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($selExamRow['ex_description'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="text-center">
                                             <a href="?page=ranking-exam&exam_id=<?php echo (int)$selExamRow['ex_id']; ?>"  class="btn btn-success btn-sm">View</a>
                                            </td>
                                        </tr>

                                    <?php }
                                }
                                else
                                { ?>
                                    <tr>
                                      <td colspan="5">
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
                    
                <?php }

             ?>      
            
            
      
        
</div>
         



















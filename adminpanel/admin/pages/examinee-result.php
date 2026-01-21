<link rel="stylesheet" type="text/css" href="css/mycss.css">
<div class="app-main__outer">
        <div class="app-main__inner">
            <div class="app-page-title">
                <div class="page-title-wrapper">
                    <div class="page-title-heading">
                        <div>EXAMINEE RESULT</div>
                    </div>
                </div>
            </div>        
            
            <div class="col-md-12">
                <div class="main-card mb-3 card">
                    <div class="card-header">Examinee Result
                    </div>
                    <div class="table-responsive">
                        <table class="align-middle mb-0 table table-borderless table-striped table-hover" id="tableList">
                            <thead>
                            <tr>
                                <th>Fullname</th>
                                <th>Exam Name</th>
                                <th>Scores</th>
                                <th>Ratings</th>
                                <!-- <th width="10%"></th> -->
                            </tr>
                            </thead>
                            <tbody>
                              <?php
                                $stmt = $conn->query(
                                  "SELECT et.exmne_id, et.exmne_fullname, ea.exam_id
                                   FROM examinee_tbl et
                                   INNER JOIN exam_attempt ea ON et.exmne_id = ea.exmne_id
                                   ORDER BY ea.examat_id DESC"
                                );
                                $examinees = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                if(!empty($examinees))
                                {
                                    foreach ($examinees as $selExmneRow) { ?>
                                        <tr>
                                           <td><?php echo htmlspecialchars($selExmneRow['exmne_fullname'], ENT_QUOTES, 'UTF-8'); ?></td>
                                           <td>
                                             <?php
                                                $eid = (int)$selExmneRow['exmne_id'];

                                                $stmtEx = $conn->prepare(
                                                  "SELECT et.ex_id, et.ex_title, et.ex_questlimit_display
                                                   FROM exam_tbl et
                                                   INNER JOIN exam_attempt ea ON et.ex_id = ea.exam_id
                                                   WHERE ea.exmne_id = ?
                                                   ORDER BY ea.examat_id DESC
                                                   LIMIT 1"
                                                );
                                                $stmtEx->execute([$eid]);
                                                $selExName = $stmtEx->fetch(PDO::FETCH_ASSOC);

                                                if ($selExName) {
                                                    $exam_id = (int)$selExName['ex_id'];
                                                    echo htmlspecialchars($selExName['ex_title'], ENT_QUOTES, 'UTF-8');
                                                } else {
                                                    $exam_id = 0;
                                                    echo '<em>No exam found</em>';
                                                }
                                              ?>
                                           </td>
                                           <td>
                                                    <?php
                                                    $over = isset($selExName['ex_questlimit_display']) ? (int)$selExName['ex_questlimit_display'] : 0;
                                                    if ($exam_id > 0) {
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
                                                        $stmtScore->execute([$eid, $exam_id]);
                                                        $score = (int)$stmtScore->fetchColumn();
                                                    } else {
                                                        $score = 0;
                                                    }
                                                    ?>
                                                <span>
                                                    <?php echo $score; ?>
                                                </span> / <?php echo $over; ?>
                                           </td>
                                           <td>
                                              <?php ?>
                                                <span>
                                                    <?php 
                                                        $ans = ($over > 0) ? ($score / $over * 100) : 0;
                                                        echo number_format($ans,2);
                                                        echo "%";
                                                     ?>
                                                </span> 
                                           </td>
                                           <!-- <td>
                                               <a rel="facebox" href="facebox_modal/updateExaminee.php?id=<?php echo $selExmneRow['exmne_id']; ?>" class="btn btn-sm btn-primary">Print Result</a>

                                           </td>
                                            -->
                                        </tr>
                                    <?php }
                                }
                                else
                                { ?>
                                    <tr>
                                      <td colspan="2">
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
         

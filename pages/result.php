 <?php 
    // Validate and sanitize exam ID
    $examId = $_GET['id'] ?? '';
    if (!validateId($examId)) {
        die("Invalid exam ID");
    }

    // Get logged in user ID
    $exmneId = $_SESSION['student']['user_id'];
    
    // Verify user has taken this exam (authorization check)
    $stmt = $conn->prepare("SELECT * FROM exam_attempt WHERE exmne_id = ? AND exam_id = ?");
    $stmt->execute([$exmneId, $examId]);
    
    if ($stmt->rowCount() === 0) {
        die("You haven't taken this exam yet");
    }
    
    // Get exam details using prepared statement
    $stmt = $conn->prepare("SELECT * FROM exam_tbl WHERE ex_id = ?");
    $stmt->execute([$examId]);
    $selExam = $stmt->fetch();
    
    if (!$selExam) {
        die("Exam not found");
    }
 ?>

<div class="app-main__outer">
<div class="app-main__inner">
    <div id="refreshData">
            
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
            </div>
        </div>  
        <div class="row col-md-12">
        	<h1 class="text-primary">RESULT'S</h1>
        </div>

        <div class="row col-md-6 float-left">
        	<div class="main-card mb-3 card">
                <div class="card-body">
                	<h5 class="card-title">Your Answer's</h5>
        			<table class="align-middle mb-0 table table-borderless table-striped table-hover" id="tableList">
                    <?php 
                        // Get user's answers using prepared statement
                    	$stmt = $conn->prepare("SELECT * FROM exam_question_tbl eqt INNER JOIN exam_answers ea ON eqt.eqt_id = ea.quest_id WHERE eqt.exam_id = ? AND ea.axmne_id = ? AND ea.exans_status = 'new'");
                        $stmt->execute([$examId, $exmneId]);
                        
                    	$i = 1;
                    	while ($selQuestRow = $stmt->fetch()) { ?>
                    		<tr>
                    			<td>
                    				<b><p><?php echo $i++; ?> .) <?php echo escape($selQuestRow['exam_question']); ?></p></b>
                    				<label class="pl-4 text-success">
                    					Answer : 
                    					<?php 
                    						if($selQuestRow['exam_answer'] != $selQuestRow['exans_answer'])
                    						{ ?>
                    							<span style="color:red"><?php echo escape($selQuestRow['exans_answer']); ?></span>
                    						<?PHP }
                    						else
                    						{ ?>
                    							<span class="text-success"><?php echo escape($selQuestRow['exans_answer']); ?></span>
                    						<?php }
                    					 ?>
                    				</label>
                    			</td>
                    		</tr>
                    	<?php }
                     ?>
	                 </table>
                </div>
            </div>
        </div>

        <div class="col-md-6 float-left">
        	<div class="col-md-6 float-left">
        	<div class="card mb-3 widget-content bg-night-fade">
                <div class="widget-content-wrapper text-white">
                    <div class="widget-content-left">
                        <div class="widget-heading"><h5>Score</h5></div>
                        <div class="widget-subheading" style="color: transparent;">/</div>
                    </div>
                    <div class="widget-content-right">
                        <div class="widget-numbers text-white">
                            <?php 
                                // Calculate score using prepared statement
                                $stmt = $conn->prepare("SELECT * FROM exam_question_tbl eqt INNER JOIN exam_answers ea ON eqt.eqt_id = ea.quest_id AND eqt.exam_answer = ea.exans_answer WHERE ea.axmne_id = ? AND ea.exam_id = ? AND ea.exans_status = 'new'");
                                $stmt->execute([$exmneId, $examId]);
                                $score = $stmt->rowCount();
                                $over = $selExam['ex_questlimit_display'];
                            ?>
                            <span>
                                <?php echo escape($score); ?>
                            </span> / <?php echo escape($over); ?>
                        </div>
                    </div>
                </div>
            </div>
        	</div>

            <div class="col-md-6 float-left">
            <div class="card mb-3 widget-content bg-happy-green">
                <div class="widget-content-wrapper text-white">
                    <div class="widget-content-left">
                        <div class="widget-heading"><h5>Percentage</h5></div>
                        <div class="widget-subheading" style="color: transparent;">/</div>
                        </div>
                        <div class="widget-content-right">
                        <div class="widget-numbers text-white">
                            <span>
                                <?php 
                                    if ($over > 0) {
                                        $percentage = ($score / $over) * 100;
                                        echo escape(number_format($percentage, 2));
                                        echo "%";
                                    } else {
                                        echo "0%";
                                    }
                                 ?>
                            </span> 
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>


    </div>
</div>

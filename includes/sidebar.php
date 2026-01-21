<div class="app-sidebar sidebar-shadow">
    <div class="app-header__logo">
        
        <div class="header__pane ml-auto">
            <div>
                <button type="button" class="hamburger close-sidebar-btn hamburger--elastic" data-class="closed-sidebar">
                    <span class="hamburger-box">
                        <span class="hamburger-inner"></span>
                    </span>
                </button>
            </div>
        </div>
    </div>
    <div class="app-header__mobile-menu">
        <div>
            <button type="button" class="hamburger hamburger--elastic mobile-toggle-nav">
                <span class="hamburger-box">
                    <span class="hamburger-inner"></span>
                </span>
            </button>
        </div>
    </div>
    <div class="app-header__menu">
        <span>
            <button type="button" class="btn-icon btn-icon-only btn btn-primary btn-sm mobile-toggle-header-nav">
                <span class="btn-icon-wrapper">
                    <i class="fa fa-ellipsis-v fa-w-6"></i>
                </span>
            </button>
        </span>
    </div>    <div class="scrollbar-sidebar">
        <div class="app-sidebar__inner">
            <ul class="vertical-nav-menu">

         
                <li class="app-sidebar__heading">AVAILABLE EXAM'S</li>
                <li>
                <a href="#">
                     <i class="metismenu-icon pe-7s-display2"></i>
                     All Exam's
                    <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                </a>
                <ul >
                    <?php 
                        
                        if($selExam->rowCount() > 0)
                        {
                            while ($selExamRow = $selExam->fetch(PDO::FETCH_ASSOC)) { ?>
                                 <li>
                                 <a href="#" id="startQuiz" data-id="<?php echo (int)$selExamRow['ex_id']; ?>"  >
                                    <?php
                                        $title = (string)$selExamRow['ex_title'];
                                        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
                                        $lenthOfTxt = strlen($title);

                                        if($lenthOfTxt >= 23)
                                        { ?>
                                            <?php echo htmlspecialchars(substr($title, 0, 20), ENT_QUOTES, 'UTF-8');?>.....
                                        <?php }
                                        else
                                        {
                                            echo $safeTitle;
                                        }
                                     ?>
                                 </a>
                                 </li>
                            <?php }
                        }
                        else
                        { ?>
                            <a href="#">
                                <i class="metismenu-icon"></i>No Exam's @ the moment
                            </a>
                        <?php }
                     ?>


                </ul>
                </li>

                 <li class="app-sidebar__heading">TAKEN EXAM'S</li>
                <li>
                  <?php
                    $stmt = $conn->prepare(
                      "SELECT et.ex_id, et.ex_title
                       FROM exam_tbl et
                       INNER JOIN exam_attempt ea ON et.ex_id = ea.exam_id
                       WHERE ea.exmne_id = ?
                       ORDER BY ea.examat_id DESC"
                    );
                    $stmt->execute([$exmneId]);
                    $takenExams = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (!empty($takenExams))
                    {
                        foreach ($takenExams as $selTakenExamRow) { ?>
                            <a href="home.php?page=result&id=<?php echo (int)$selTakenExamRow['ex_id']; ?>" >
                               
                                <?php echo htmlspecialchars($selTakenExamRow['ex_title'], ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        <?php }
                    }
                    else
                    { ?>
                        <a href="#" class="pl-3">You are not taking exam yet</a>
                    <?php }
                    
                   ?>

                    
                </li>


                <li class="app-sidebar__heading">FEEDBACKS</li>
                <li>
                    <a href="#" data-toggle="modal" data-target="#feedbacksModal" >
                        Add Feedbacks                        
                    </a>
                </li>

                <li class="app-sidebar__heading">ACCOUNT</li>
                <li>
                    <a href="home.php?page=student-profile">
                        <i class="metismenu-icon pe-7s-user"></i>
                        My Profile                        
                    </a>
                </li>
                
            </ul>
        </div>
    </div>
</div>  
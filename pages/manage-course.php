<?php
// Check if user is logged in
if (!isset($_SESSION['student']['is_logged_in']) || $_SESSION['student']['is_logged_in'] !== true) {
    header("location: index.php");
    exit;
}

// Check session timeout
if (!checkSessionTimeout()) {
    session_destroy();
    header("location: index.php");
    exit;
}

$exmneId = $_SESSION['student']['user_id'];

// Get student's course information
try {
    $stmt = $conn->prepare("
        SELECT c.*, e.exmne_course 
        FROM course_tbl c 
        INNER JOIN examinee_tbl e ON c.cou_id = e.exmne_course 
        WHERE e.exmne_id = ?
    ");
    $stmt->execute([$exmneId]);
    $studentCourse = $stmt->fetch();

    // Get all available exams for student's course
    $stmt = $conn->prepare("
        SELECT et.*, 
               CASE WHEN ea.exam_id IS NOT NULL THEN 'taken' ELSE 'available' END as exam_status,
               ea.attempt_date,
               ea.score
        FROM exam_tbl et 
        LEFT JOIN exam_attempt ea ON et.ex_id = ea.exam_id AND ea.exmne_id = ?
        WHERE et.cou_id = ? AND et.ex_status = 'active'
        ORDER BY et.ex_created DESC
    ");
    $stmt->execute([$exmneId, $studentCourse['cou_id']]);
    $courseExams = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Course Page Error: " . $e->getMessage());
    $studentCourse = null;
    $courseExams = [];
}
?>

<link rel="stylesheet" type="text/css" href="css/mycss.css">
<div class="app-main__outer">
    <div class="app-main__inner">
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-study icon-gradient bg-mean-fruit"></i>
                    </div>
                    <div>
                        MY COURSE
                        <div class="page-title-subheading">
                            <?php echo $studentCourse ? escape($studentCourse['cou_name']) : 'Course Information'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>        
        
        <?php if ($studentCourse): ?>
            <!-- Course Information Card -->
            <div class="col-md-12">
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <i class="header-icon lnr-graduation-hat icon-gradient bg-plum-plate"></i>
                        Course Information
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h4 class="card-title"><?php echo escape($studentCourse['cou_name']); ?></h4>
                                <p class="card-text text-muted">
                                    Course ID: <?php echo escape($studentCourse['cou_id']); ?><br>
                                    Enrolled: <?php echo date('M d, Y', strtotime($studentCourse['cou_created'])); ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-right">
                                <div class="badge badge-pill badge-primary badge-lg">
                                    <?php echo count($courseExams); ?> Exams Available
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Available Exams -->
            <div class="col-md-12">
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <i class="header-icon lnr-license icon-gradient bg-tempting-azure"></i>
                        Available Exams
                    </div>
                    <div class="card-body">
                        <?php if (!empty($courseExams)): ?>
                            <div class="table-responsive">
                                <table class="align-middle mb-0 table table-borderless table-striped table-hover" id="tableList">
                                    <thead>
                                        <tr>
                                            <th class="text-left pl-4">Exam Title</th>
                                            <th class="text-center">Time Limit</th>
                                            <th class="text-center">Questions</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($courseExams as $exam): ?>
                                            <tr>
                                                <td class="pl-4">
                                                    <div class="widget-heading"><?php echo escape($exam['ex_title']); ?></div>
                                                    <div class="widget-subheading opacity-7">
                                                        <?php echo escape($exam['ex_description']); ?>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="badge badge-pill badge-info">
                                                        <?php echo $exam['ex_time_limit']; ?> mins
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <?php echo $exam['ex_questlimit_display']; ?> questions
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($exam['exam_status'] == 'taken'): ?>
                                                        <div class="badge badge-success">Completed</div>
                                                        <?php if ($exam['score'] !== null): ?>
                                                            <br><small class="text-muted">Score: <?php echo number_format($exam['score'], 1); ?>%</small>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <div class="badge badge-warning">Available</div>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($exam['exam_status'] == 'taken'): ?>
                                                        <a href="?page=result&id=<?php echo $exam['ex_id']; ?>" class="btn btn-info btn-sm">
                                                            <i class="pe-7s-graph1 mr-1"></i>View Result
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="?page=exam&id=<?php echo $exam['ex_id']; ?>" class="btn btn-primary btn-sm">
                                                            <i class="pe-7s-note2 mr-1"></i>Take Exam
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="pe-7s-note2 fa-3x text-muted mb-3"></i>
                                <h4 class="text-muted">No Exams Available</h4>
                                <p class="text-muted">There are currently no exams available for your course.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Course Statistics -->
            <div class="col-md-12">
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <i class="header-icon lnr-chart-bars icon-gradient bg-strong-bliss"></i>
                        Course Progress
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h3 class="text-primary"><?php echo count($courseExams); ?></h3>
                                    <p class="text-muted">Total Exams</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h3 class="text-success">
                                        <?php echo count(array_filter($courseExams, function($e) { return $e['exam_status'] == 'taken'; })); ?>
                                    </h3>
                                    <p class="text-muted">Completed</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h3 class="text-warning">
                                        <?php echo count(array_filter($courseExams, function($e) { return $e['exam_status'] == 'available'; })); ?>
                                    </h3>
                                    <p class="text-muted">Remaining</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <?php 
                                    $completedExams = array_filter($courseExams, function($e) { return $e['exam_status'] == 'taken' && $e['score'] !== null; });
                                    $avgScore = !empty($completedExams) ? array_sum(array_column($completedExams, 'score')) / count($completedExams) : 0;
                                    ?>
                                    <h3 class="text-info"><?php echo number_format($avgScore, 1); ?>%</h3>
                                    <p class="text-muted">Average Score</p>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($courseExams)): ?>
                            <div class="progress mt-3">
                                <?php 
                                $completionRate = (count(array_filter($courseExams, function($e) { return $e['exam_status'] == 'taken'; })) / count($courseExams)) * 100;
                                ?>
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: <?php echo $completionRate; ?>%" 
                                     aria-valuenow="<?php echo $completionRate; ?>" 
                                     aria-valuemin="0" aria-valuemax="100">
                                    <?php echo number_format($completionRate, 1); ?>% Complete
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <div class="col-md-12">
                <div class="main-card mb-3 card">
                    <div class="card-body text-center py-5">
                        <i class="pe-7s-attention fa-3x text-warning mb-3"></i>
                        <h4 class="text-muted">Course Information Not Available</h4>
                        <p class="text-muted">Unable to load your course information. Please contact administrator.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
         

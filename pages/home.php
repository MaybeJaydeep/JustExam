
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
$exmneFullname = $_SESSION['student']['fullname'];

// Get student statistics
try {
    // Count total exams available for student's course
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total_exams 
        FROM exam_tbl et 
        INNER JOIN examinee_tbl exm ON et.cou_id = exm.exmne_course 
        WHERE exm.exmne_id = ? AND et.ex_status = 'active'
    ");
    $stmt->execute([$exmneId]);
    $totalExams = $stmt->fetch()['total_exams'];

    // Count exams taken
    $stmt = $conn->prepare("SELECT COUNT(*) as taken_exams FROM exam_attempt WHERE exmne_id = ?");
    $stmt->execute([$exmneId]);
    $takenExams = $stmt->fetch()['taken_exams'];

    // Get recent exam results
    $stmt = $conn->prepare("
        SELECT et.ex_title, ea.attempt_date, ea.score, ea.total_questions, ea.correct_answers
        FROM exam_attempt ea
        INNER JOIN exam_tbl et ON ea.exam_id = et.ex_id
        WHERE ea.exmne_id = ?
        ORDER BY ea.attempt_date DESC
        LIMIT 5
    ");
    $stmt->execute([$exmneId]);
    $recentResults = $stmt->fetchAll();

    // Calculate average score
    $stmt = $conn->prepare("SELECT AVG(score) as avg_score FROM exam_attempt WHERE exmne_id = ? AND score IS NOT NULL");
    $stmt->execute([$exmneId]);
    $avgScore = $stmt->fetch()['avg_score'] ?? 0;

} catch (PDOException $e) {
    error_log("Dashboard Error: " . $e->getMessage());
    $totalExams = $takenExams = 0;
    $recentResults = [];
    $avgScore = 0;
}
?>

<div class="app-main__outer">
    <div id="refreshData">
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-user icon-gradient bg-mean-fruit"></i>
                    </div>
                    <div>
                        Welcome, <?php echo escape($exmneFullname); ?>!
                        <div class="page-title-subheading">
                            Your examination dashboard and performance overview
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-6 col-xl-3">
                <div class="card mb-3 widget-content bg-midnight-bloom">
                    <div class="widget-content-wrapper text-white">
                        <div class="widget-content-left">
                            <div class="widget-heading">Total Exams</div>
                            <div class="widget-subheading">Available for you</div>
                        </div>
                        <div class="widget-content-right">
                            <div class="widget-numbers text-white">
                                <span><?php echo $totalExams; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-xl-3">
                <div class="card mb-3 widget-content bg-arielle-smile">
                    <div class="widget-content-wrapper text-white">
                        <div class="widget-content-left">
                            <div class="widget-heading">Exams Taken</div>
                            <div class="widget-subheading">Completed exams</div>
                        </div>
                        <div class="widget-content-right">
                            <div class="widget-numbers text-white">
                                <span><?php echo $takenExams; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="card mb-3 widget-content bg-grow-early">
                    <div class="widget-content-wrapper text-white">
                        <div class="widget-content-left">
                            <div class="widget-heading">Average Score</div>
                            <div class="widget-subheading">Your performance</div>
                        </div>
                        <div class="widget-content-right">
                            <div class="widget-numbers text-white">
                                <span><?php echo number_format($avgScore, 1); ?>%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="card mb-3 widget-content bg-premium-dark">
                    <div class="widget-content-wrapper text-white">
                        <div class="widget-content-left">
                            <div class="widget-heading">Remaining</div>
                            <div class="widget-subheading">Exams to take</div>
                        </div>
                        <div class="widget-content-right">
                            <div class="widget-numbers text-white">
                                <span><?php echo max(0, $totalExams - $takenExams); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Results -->
        <div class="row">
            <div class="col-md-12">
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <i class="header-icon lnr-graduation-hat icon-gradient bg-plum-plate"></i>
                        Recent Exam Results
                        <div class="btn-actions-pane-right">
                            <a href="?page=result" class="btn btn-sm btn-primary">View All Results</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recentResults)): ?>
                            <div class="table-responsive">
                                <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Exam Title</th>
                                            <th class="text-center">Date Taken</th>
                                            <th class="text-center">Score</th>
                                            <th class="text-center">Correct Answers</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentResults as $result): ?>
                                            <tr>
                                                <td>
                                                    <div class="widget-heading"><?php echo escape($result['ex_title']); ?></div>
                                                </td>
                                                <td class="text-center">
                                                    <?php echo date('M d, Y', strtotime($result['attempt_date'])); ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="badge badge-pill badge-<?php echo $result['score'] >= 70 ? 'success' : ($result['score'] >= 50 ? 'warning' : 'danger'); ?>">
                                                        <?php echo number_format($result['score'], 1); ?>%
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <?php echo $result['correct_answers']; ?>/<?php echo $result['total_questions']; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($result['score'] >= 70): ?>
                                                        <div class="badge badge-success">Passed</div>
                                                    <?php elseif ($result['score'] >= 50): ?>
                                                        <div class="badge badge-warning">Fair</div>
                                                    <?php else: ?>
                                                        <div class="badge badge-danger">Needs Improvement</div>
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
                                <h4 class="text-muted">No Exams Taken Yet</h4>
                                <p class="text-muted">Start taking exams to see your results here.</p>
                                <a href="?page=exam" class="btn btn-primary">Browse Available Exams</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-md-6">
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <i class="header-icon lnr-rocket icon-gradient bg-tempting-azure"></i>
                        Quick Actions
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column">
                            <a href="?page=exam" class="btn btn-primary mb-2">
                                <i class="pe-7s-note2 mr-2"></i>Take New Exam
                            </a>
                            <a href="?page=result" class="btn btn-info mb-2">
                                <i class="pe-7s-graph1 mr-2"></i>View All Results
                            </a>
                            <button class="btn btn-success mb-2" onclick="location.reload()">
                                <i class="pe-7s-refresh mr-2"></i>Refresh Dashboard
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <i class="header-icon lnr-user icon-gradient bg-strong-bliss"></i>
                        Account Information
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="mb-2">
                                    <strong>Name:</strong><br>
                                    <?php echo escape($exmneFullname); ?>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-2">
                                    <strong>Email:</strong><br>
                                    <?php echo escape($_SESSION['student']['email']); ?>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">
                                Last login: <?php echo date('M d, Y H:i'); ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

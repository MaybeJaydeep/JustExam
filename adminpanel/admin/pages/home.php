<?php include("query/analyticsData.php"); ?>

<div class="app-main__outer">
    <div id="refreshData">
        <div class="app-main__inner">
            <div class="app-page-title">
                <div class="page-title-wrapper">
                    <div class="page-title-heading">
                        <div class="page-title-icon">
                            <i class="pe-7s-graph1 icon-gradient bg-mean-fruit"></i>
                        </div>
                        <div>
                            <h1>Analytics Dashboard</h1>
                            <div class="page-title-subheading">
                                Comprehensive overview of your examination system performance
                            </div>
                        </div>
                    </div>
                    <div class="page-title-actions">
                        <button type="button" class="btn-shadow btn btn-info" onclick="refreshDashboard()">
                            <i class="fa fa-refresh"></i> Refresh Data
                        </button>
                        <button type="button" class="btn-shadow btn btn-success ml-2" onclick="exportDashboard()">
                            <i class="fa fa-download"></i> Export
                        </button>
                    </div>
                </div>
            </div>

            <!-- Key Performance Indicators -->
            <div class="row">
                <div class="col-md-6 col-xl-3">
                    <div class="card mb-3 widget-content bg-midnight-bloom">
                        <div class="widget-content-wrapper text-white">
                            <div class="widget-content-left">
                                <div class="widget-heading">Total Courses</div>
                                <div class="widget-subheading">Active courses</div>
                            </div>
                            <div class="widget-content-right">
                                <div class="widget-numbers text-white">
                                    <span data-metric="courses"><?php echo $selCourse['totCourse']; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-xl-3">
                    <div class="card mb-3 widget-content bg-arielle-smile">
                        <div class="widget-content-wrapper text-white">
                            <div class="widget-content-left">
                                <div class="widget-heading">Total Exams</div>
                                <div class="widget-subheading">Available exams</div>
                            </div>
                            <div class="widget-content-right">
                                <div class="widget-numbers text-white">
                                    <span data-metric="exams"><?php echo $selExam['totExam']; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-xl-3">
                    <div class="card mb-3 widget-content bg-grow-early">
                        <div class="widget-content-wrapper text-white">
                            <div class="widget-content-left">
                                <div class="widget-heading">Total Students</div>
                                <div class="widget-subheading">Registered students</div>
                            </div>
                            <div class="widget-content-right">
                                <div class="widget-numbers text-white">
                                    <span data-metric="students"><?php echo $selStudent['totStudent']; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-xl-3">
                    <div class="card mb-3 widget-content bg-premium-dark">
                        <div class="widget-content-wrapper text-white">
                            <div class="widget-content-left">
                                <div class="widget-heading">Total Questions</div>
                                <div class="widget-subheading">Question bank</div>
                            </div>
                            <div class="widget-content-right">
                                <div class="widget-numbers text-white">
                                    <span data-metric="questions"><?php echo $selQuestions['totQuestions']; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Metrics -->
            <div class="row">
                <div class="col-md-6 col-xl-3">
                    <div class="card mb-3 widget-content bg-happy-green">
                        <div class="widget-content-wrapper text-white">
                            <div class="widget-content-left">
                                <div class="widget-heading">Active Students</div>
                                <div class="widget-subheading">Students with attempts</div>
                            </div>
                            <div class="widget-content-right">
                                <div class="widget-numbers text-white">
                                    <span data-metric="activeStudents"><?php echo $activeStudents['activeStudents']; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-xl-3">
                    <div class="card mb-3 widget-content bg-strong-bliss">
                        <div class="widget-content-wrapper text-white">
                            <div class="widget-content-left">
                                <div class="widget-heading">Total Attempts</div>
                                <div class="widget-subheading">All exam attempts</div>
                            </div>
                            <div class="widget-content-right">
                                <div class="widget-numbers text-white">
                                    <span data-metric="totalAttempts"><?php echo $totalAttempts['totalAttempts']; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-xl-3">
                    <div class="card mb-3 widget-content bg-mean-fruit">
                        <div class="widget-content-wrapper text-white">
                            <div class="widget-content-left">
                                <div class="widget-heading">Completion Rate</div>
                                <div class="widget-subheading">Exam completion %</div>
                            </div>
                            <div class="widget-content-right">
                                <div class="widget-numbers text-white">
                                    <span data-metric="completionRate"><?php echo $completionRate; ?>%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-xl-3">
                    <div class="card mb-3 widget-content bg-plum-plate">
                        <div class="widget-content-wrapper text-white">
                            <div class="widget-content-left">
                                <div class="widget-heading">Average Score</div>
                                <div class="widget-subheading">Overall performance</div>
                            </div>
                            <div class="widget-content-right">
                                <div class="widget-numbers text-white">
                                    <span data-metric="avgScore"><?php echo round($scoreStats['avgScore'] ?? 0, 1); ?>%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts and Analytics -->
            <div class="row">
                <!-- Monthly Trends Chart -->
                <div class="col-lg-8">
                    <div class="main-card mb-3 card">
                        <div class="card-header">
                            <i class="header-icon lnr-chart-bars icon-gradient bg-warm-flame"></i>
                            Monthly Exam Trends
                            <div class="btn-actions-pane-right">
                                <div class="nav">
                                    <a href="#" class="border-0 btn-pill btn-wide btn-transition btn btn-outline-alternate">
                                        Last 6 Months
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="monthlyTrendsChart" height="100"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Student Activity Distribution -->
                <div class="col-lg-4">
                    <div class="main-card mb-3 card">
                        <div class="card-header">
                            <i class="header-icon lnr-users icon-gradient bg-plum-plate"></i>
                            Student Activity
                        </div>
                        <div class="card-body">
                            <canvas id="activityChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Tables -->
            <div class="row">
                <!-- Top Performing Exams -->
                <div class="col-lg-6">
                    <div class="main-card mb-3 card">
                        <div class="card-header">
                            <i class="header-icon lnr-graduation-hat icon-gradient bg-happy-green"></i>
                            Top Performing Exams
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Exam Title</th>
                                            <th>Attempts</th>
                                            <th>Avg Score</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($topExams)): ?>
                                            <?php foreach ($topExams as $exam): ?>
                                                <tr>
                                                    <td><?php echo escape($exam['ex_title']); ?></td>
                                                    <td>
                                                        <span class="badge badge-info"><?php echo $exam['attempts']; ?></span>
                                                    </td>
                                                    <td>
                                                        <div class="progress" style="height: 20px;">
                                                            <div class="progress-bar bg-success" 
                                                                 style="width: <?php echo $exam['avg_score']; ?>%">
                                                                <?php echo round($exam['avg_score'], 1); ?>%
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">No exam data available</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Course Performance -->
                <div class="col-lg-6">
                    <div class="main-card mb-3 card">
                        <div class="card-header">
                            <i class="header-icon lnr-book icon-gradient bg-strong-bliss"></i>
                            Course Performance
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Course</th>
                                            <th>Exams</th>
                                            <th>Attempts</th>
                                            <th>Avg Score</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($coursePerformance)): ?>
                                            <?php foreach ($coursePerformance as $course): ?>
                                                <tr>
                                                    <td><?php echo escape($course['cou_name']); ?></td>
                                                    <td><?php echo $course['exam_count']; ?></td>
                                                    <td><?php echo $course['total_attempts']; ?></td>
                                                    <td>
                                                        <?php if ($course['avg_score']): ?>
                                                            <span class="badge badge-<?php echo $course['avg_score'] >= 70 ? 'success' : ($course['avg_score'] >= 50 ? 'warning' : 'danger'); ?>">
                                                                <?php echo round($course['avg_score'], 1); ?>%
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted">No data</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">No course data available</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="main-card mb-3 card">
                        <div class="card-header">
                            <i class="header-icon lnr-clock icon-gradient bg-midnight-bloom"></i>
                            Recent Exam Results
                            <div class="btn-actions-pane-right">
                                <div class="nav">
                                    <span class="badge badge-pill badge-info"><?php echo $recentActivity['recentAttempts']; ?> this week</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Exam</th>
                                            <th>Score</th>
                                            <th>Date</th>
                                            <th>Performance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($recentResults)): ?>
                                            <?php foreach ($recentResults as $result): ?>
                                                <tr>
                                                    <td>
                                                        <div class="widget-content p-0">
                                                            <div class="widget-content-wrapper">
                                                                <div class="widget-content-left mr-3">
                                                                    <div class="widget-content-left">
                                                                        <div class="avatar-icon-wrapper">
                                                                            <div class="avatar-icon">
                                                                                <i class="lnr-user"></i>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="widget-content-left flex2">
                                                                    <div class="widget-heading"><?php echo escape($result['exmne_fullname']); ?></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><?php echo escape($result['ex_title']); ?></td>
                                                    <td>
                                                        <span class="badge badge-<?php echo $result['score'] >= 70 ? 'success' : ($result['score'] >= 50 ? 'warning' : 'danger'); ?>">
                                                            <?php echo $result['score']; ?>%
                                                        </span>
                                                    </td>
                                                    <td><?php echo date('M j, Y g:i A', strtotime($result['examStarted'])); ?></td>
                                                    <td>
                                                        <div class="progress" style="height: 15px;">
                                                            <div class="progress-bar bg-<?php echo $result['score'] >= 70 ? 'success' : ($result['score'] >= 50 ? 'warning' : 'danger'); ?>" 
                                                                 style="width: <?php echo $result['score']; ?>%">
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No recent results available</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats Summary -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="main-card mb-3 card">
                        <div class="card-header">
                            <i class="header-icon lnr-chart-bars icon-gradient bg-arielle-smile"></i>
                            Performance Summary
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="metric-value d-inline-block">
                                        <h2 class="text-success" data-summary="maxScore"><?php echo round($scoreStats['maxScore'] ?? 0, 1); ?>%</h2>
                                    </div>
                                    <div class="metric-label d-inline-block ml-2 text-secondary">
                                        Highest Score
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="metric-value d-inline-block">
                                        <h2 class="text-primary" data-summary="avgScore"><?php echo round($scoreStats['avgScore'] ?? 0, 1); ?>%</h2>
                                    </div>
                                    <div class="metric-label d-inline-block ml-2 text-secondary">
                                        Average Score
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="metric-value d-inline-block">
                                        <h2 class="text-warning" data-summary="minScore"><?php echo round($scoreStats['minScore'] ?? 0, 1); ?>%</h2>
                                    </div>
                                    <div class="metric-label d-inline-block ml-2 text-secondary">
                                        Lowest Score
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="metric-value d-inline-block">
                                        <h2 class="text-info" data-summary="completionRate"><?php echo $completionRate; ?>%</h2>
                                    </div>
                                    <div class="metric-label d-inline-block ml-2 text-secondary">
                                        Completion Rate
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Integration -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="js/dashboard.js"></script>
<script>
// Initialize Charts
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Trends Chart
    const monthlyData = <?php echo json_encode($monthlyTrends); ?>;
    const monthlyLabels = monthlyData.map(item => {
        const date = new Date(item.month + '-01');
        return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
    });
    const monthlyAttempts = monthlyData.map(item => item.attempts);
    const monthlyScores = monthlyData.map(item => parseFloat(item.avg_score));

    const monthlyCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
    const monthlyChart = new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Exam Attempts',
                data: monthlyAttempts,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.4,
                yAxisID: 'y'
            }, {
                label: 'Average Score (%)',
                data: monthlyScores,
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                tension: 0.4,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Month'
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Number of Attempts'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Average Score (%)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });

    // Student Activity Distribution Chart
    const activityData = <?php echo json_encode($activityDistribution); ?>;
    const activityLabels = activityData.map(item => item.activity_level);
    const activityCounts = activityData.map(item => item.student_count);

    const activityCtx = document.getElementById('activityChart').getContext('2d');
    const activityChart = new Chart(activityCtx, {
        type: 'doughnut',
        data: {
            labels: activityLabels,
            datasets: [{
                data: activityCounts,
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF'
                ],
                hoverBackgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });

    // Store charts in dashboard manager
    if (window.dashboardManager) {
        window.dashboardManager.charts = {
            monthlyTrends: monthlyChart,
            activity: activityChart
        };
    }
});
</script>
<?php 
session_start();
require_once("../../../config.php");
require_once("../../../security.php");

// Check if admin is logged in
if (!isset($_SESSION['admin']['is_logged_in']) || $_SESSION['admin']['is_logged_in'] !== true) {
    header("location: ../index.php");
    exit;
}

// Check session timeout
if (!checkSessionTimeout()) {
    session_destroy();
    header("location: ../index.php?timeout=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Reports - JustExam</title>
    <link href="../main.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .report-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .report-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .btn-generate {
            background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        .btn-generate:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white;
        }
        .filter-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .report-preview {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 bg-dark text-white p-3">
                <h5><i class="fas fa-chart-bar"></i> Reports</h5>
                <nav class="nav flex-column">
                    <a class="nav-link text-white" href="../home.php"><i class="fas fa-home"></i> Dashboard</a>
                    <a class="nav-link text-white active" href="#"><i class="fas fa-file-alt"></i> Reports</a>
                    <a class="nav-link text-white" href="../home.php?page=manage-exam"><i class="fas fa-tasks"></i> Exams</a>
                    <a class="nav-link text-white" href="../home.php?page=manage-examinee"><i class="fas fa-users"></i> Students</a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-chart-line text-primary"></i> Advanced Reporting System</h1>
                    <div>
                        <button class="btn btn-outline-primary" onclick="refreshReports()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-file-pdf fa-2x mb-2"></i>
                                <h5>PDF Reports</h5>
                                <p class="mb-0">Professional Format</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-file-excel fa-2x mb-2"></i>
                                <h5>Excel Exports</h5>
                                <p class="mb-0">Data Analysis</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-chart-bar fa-2x mb-2"></i>
                                <h5>Analytics</h5>
                                <p class="mb-0">Performance Insights</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-clock fa-2x mb-2"></i>
                                <h5>Real-Time</h5>
                                <p class="mb-0">Live Data</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report Types -->
                <div class="row">
                    <!-- Student Performance Report -->
                    <div class="col-lg-4 mb-4">
                        <div class="card report-card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-user-graduate report-icon text-primary"></i>
                                <h5 class="card-title">Student Performance Report</h5>
                                <p class="card-text">Comprehensive analysis of individual student performance across all exams.</p>
                                <button class="btn btn-generate" onclick="generateReport('student-performance')">
                                    <i class="fas fa-download"></i> Generate Report
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Exam Analytics Report -->
                    <div class="col-lg-4 mb-4">
                        <div class="card report-card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-chart-pie report-icon text-success"></i>
                                <h5 class="card-title">Exam Analytics Report</h5>
                                <p class="card-text">Detailed statistics and analytics for specific exams including difficulty analysis.</p>
                                <button class="btn btn-generate" onclick="generateReport('exam-analytics')">
                                    <i class="fas fa-download"></i> Generate Report
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Course Summary Report -->
                    <div class="col-lg-4 mb-4">
                        <div class="card report-card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-book report-icon text-info"></i>
                                <h5 class="card-title">Course Summary Report</h5>
                                <p class="card-text">Overview of course performance with comparative analysis across subjects.</p>
                                <button class="btn btn-generate" onclick="generateReport('course-summary')">
                                    <i class="fas fa-download"></i> Generate Report
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Time-Based Analytics -->
                    <div class="col-lg-4 mb-4">
                        <div class="card report-card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-calendar-alt report-icon text-warning"></i>
                                <h5 class="card-title">Time-Based Analytics</h5>
                                <p class="card-text">Performance trends over time with monthly and weekly breakdowns.</p>
                                <button class="btn btn-generate" onclick="generateReport('time-analytics')">
                                    <i class="fas fa-download"></i> Generate Report
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Question Bank Analysis -->
                    <div class="col-lg-4 mb-4">
                        <div class="card report-card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-question-circle report-icon text-danger"></i>
                                <h5 class="card-title">Question Bank Analysis</h5>
                                <p class="card-text">Analysis of question difficulty, success rates, and optimization suggestions.</p>
                                <button class="btn btn-generate" onclick="generateReport('question-analysis')">
                                    <i class="fas fa-download"></i> Generate Report
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Custom Report Builder -->
                    <div class="col-lg-4 mb-4">
                        <div class="card report-card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-cogs report-icon text-secondary"></i>
                                <h5 class="card-title">Custom Report Builder</h5>
                                <p class="card-text">Build custom reports with your own filters, metrics, and visualizations.</p>
                                <button class="btn btn-generate" onclick="openCustomBuilder()">
                                    <i class="fas fa-tools"></i> Build Custom
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters Section -->
                <div class="filter-section">
                    <h4><i class="fas fa-filter"></i> Report Filters</h4>
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">Date Range</label>
                            <select class="form-select" id="dateRange">
                                <option value="7">Last 7 days</option>
                                <option value="30">Last 30 days</option>
                                <option value="90">Last 3 months</option>
                                <option value="365">Last year</option>
                                <option value="custom">Custom range</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Course</label>
                            <select class="form-select" id="courseFilter">
                                <option value="">All Courses</option>
                                <!-- Populated dynamically -->
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Exam</label>
                            <select class="form-select" id="examFilter">
                                <option value="">All Exams</option>
                                <!-- Populated dynamically -->
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Format</label>
                            <select class="form-select" id="formatFilter">
                                <option value="pdf">PDF Report</option>
                                <option value="excel">Excel Spreadsheet</option>
                                <option value="csv">CSV Data</option>
                                <option value="json">JSON Data</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3" id="customDateRange" style="display: none;">
                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="startDate">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" id="endDate">
                        </div>
                    </div>
                </div>

                <!-- Recent Reports -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-history"></i> Recent Reports</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Report Type</th>
                                        <th>Generated</th>
                                        <th>Format</th>
                                        <th>Size</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="recentReportsTable">
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No reports generated yet</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div class="modal fade" id="loadingModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5>Generating Report...</h5>
                    <p class="text-muted">Please wait while we compile your data</p>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 0%" id="progressBar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="reports.js"></script>
</body>
</html>
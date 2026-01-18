<div class="app-main__outer">
    <div class="app-main__inner">
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-graph1 icon-gradient bg-strong-bliss"></i>
                    </div>
                    <div>
                        <h1>Advanced Reports</h1>
                        <div class="page-title-subheading">
                            Generate comprehensive PDF and Excel reports for detailed analysis
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Generation Cards -->
        <div class="row">
            <!-- Exam Performance Report -->
            <div class="col-lg-6">
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <i class="header-icon lnr-graduation-hat icon-gradient bg-happy-green"></i>
                        Exam Performance Report
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            Comprehensive analysis of individual exam performance including student results, 
                            grade distribution, question analysis, and statistical insights.
                        </p>
                        
                        <form id="examReportForm" class="mt-3">
                            <div class="form-group">
                                <label for="examSelect">Select Exam:</label>
                                <select class="form-control" id="examSelect" name="exam_id" required>
                                    <option value="">Choose an exam...</option>
                                    <?php
                                    try {
                                        $stmt = $conn->query("
                                            SELECT e.ex_id, e.ex_title, c.cou_name,
                                                   COUNT(ea.attemptId) as attempt_count
                                            FROM exam_tbl e 
                                            JOIN course_tbl c ON e.cou_id = c.cou_id
                                            LEFT JOIN exam_attempt ea ON e.ex_id = ea.exam_id
                                            GROUP BY e.ex_id
                                            ORDER BY c.cou_name, e.ex_title
                                        ");
                                        while ($exam = $stmt->fetch()) {
                                            echo "<option value='{$exam['ex_id']}'>";
                                            echo escape($exam['cou_name']) . " - " . escape($exam['ex_title']);
                                            echo " ({$exam['attempt_count']} attempts)";
                                            echo "</option>";
                                        }
                                    } catch (Exception $e) {
                                        echo "<option value=''>Error loading exams</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Report Format:</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="format" value="html" checked>
                                    <label class="form-check-label">📄 HTML Report (Preview)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="format" value="csv">
                                    <label class="form-check-label">📊 CSV Export</label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fa fa-download"></i> Generate Exam Report
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Student Progress Report -->
            <div class="col-lg-6">
                <div class="main-card mb-3 card">
                    <div class="card-header">
                        <i class="header-icon lnr-user icon-gradient bg-plum-plate"></i>
                        Student Progress Report
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            Individual student performance analysis with exam history, course performance, 
                            progress trends, and personalized recommendations.
                        </p>
                        
                        <form id="studentReportForm" class="mt-3">
                            <div class="form-group">
                                <label for="studentSelect">Select Student:</label>
                                <select class="form-control" id="studentSelect" name="student_id" required>
                                    <option value="">Choose a student...</option>
                                    <?php
                                    try {
                                        $stmt = $conn->query("
                                            SELECT et.exmne_id, et.exmne_fullname, et.exmne_email,
                                                   COUNT(ea.attemptId) as attempt_count
                                            FROM examinee_tbl et 
                                            LEFT JOIN exam_attempt ea ON et.exmne_id = ea.exmne_id
                                            GROUP BY et.exmne_id
                                            ORDER BY et.exmne_fullname
                                        ");
                                        while ($student = $stmt->fetch()) {
                                            echo "<option value='{$student['exmne_id']}'>";
                                            echo escape($student['exmne_fullname']) . " (" . escape($student['exmne_email']) . ")";
                                            echo " - {$student['attempt_count']} attempts";
                                            echo "</option>";
                                        }
                                    } catch (Exception $e) {
                                        echo "<option value=''>Error loading students</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Report Format:</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="format" value="html" checked>
                                    <label class="form-check-label">📄 HTML Report (Preview)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="format" value="csv">
                                    <label class="form-check-label">📊 CSV Export</label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fa fa-download"></i> Generate Student Report
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('examReportForm').addEventListener('submit', function(e) {
        e.preventDefault();
        generateReport('exam_performance', this);
    });
    
    document.getElementById('studentReportForm').addEventListener('submit', function(e) {
        e.preventDefault();
        generateReport('student_progress', this);
    });
});

function generateReport(reportType, form) {
    const formData = new FormData(form);
    formData.append('report_type', reportType);
    
    // Create form and submit
    const reportForm = document.createElement('form');
    reportForm.method = 'POST';
    reportForm.action = 'api/generate-report.php';
    reportForm.target = '_blank';
    
    for (let [key, value] of formData.entries()) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        reportForm.appendChild(input);
    }
    
    document.body.appendChild(reportForm);
    reportForm.submit();
    document.body.removeChild(reportForm);
}
</script>
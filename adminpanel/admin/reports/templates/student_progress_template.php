<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Progress Report</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #28a745;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #28a745;
            margin: 0;
            font-size: 2.5em;
        }
        
        .header .subtitle {
            color: #666;
            font-size: 1.2em;
            margin-top: 10px;
        }
        
        .student-info {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .student-info h2 {
            margin: 0 0 10px 0;
            font-size: 1.8em;
        }
        
        .student-info .email {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .section {
            margin-bottom: 40px;
        }
        
        .section h2 {
            color: #28a745;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
        }
        
        .stat-card .value {
            font-size: 2em;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 5px;
        }
        
        .stat-card .label {
            color: #666;
            font-size: 0.9em;
        }
        
        .performance-indicator {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .excellent { background: #d4edda; color: #155724; }
        .good { background: #d1ecf1; color: #0c5460; }
        .average { background: #fff3cd; color: #856404; }
        .needs-improvement { background: #f8d7da; color: #721c24; }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        th {
            background: #28a745;
            color: white;
            font-weight: 600;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .progress-chart {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .trend-up { color: #28a745; }
        .trend-down { color: #dc3545; }
        .trend-stable { color: #6c757d; }
        
        .footer {
            text-align: center;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            color: #666;
            font-size: 0.9em;
        }
        
        @media print {
            body { font-size: 12px; }
            .header h1 { font-size: 2em; }
            .stat-card .value { font-size: 1.5em; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📚 Student Progress Report</h1>
        <div class="subtitle">Comprehensive Academic Performance Analysis</div>
    </div>
    
    <div class="student-info">
        <h2><?php echo escape($data['student_info']['exmne_fullname']); ?></h2>
        <div class="email"><?php echo escape($data['student_info']['exmne_email']); ?></div>
        <div style="margin-top: 15px; font-size: 0.9em;">
            Report Generated: <?php echo date('F j, Y \a\t g:i A'); ?>
        </div>
    </div>
    
    <div class="section">
        <h2>📊 Overall Performance Summary</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="value"><?php echo $data['overall_stats']['total_attempts']; ?></div>
                <div class="label">Total Attempts</div>
            </div>
            <div class="stat-card">
                <div class="value"><?php echo $data['overall_stats']['completed_exams']; ?></div>
                <div class="label">Completed Exams</div>
            </div>
            <div class="stat-card">
                <div class="value"><?php echo round($data['overall_stats']['avg_score'], 1); ?>%</div>
                <div class="label">Average Score</div>
            </div>
            <div class="stat-card">
                <div class="value"><?php echo round($data['overall_stats']['best_score'], 1); ?>%</div>
                <div class="label">Best Score</div>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 20px;">
            <?php 
            $avgScore = $data['overall_stats']['avg_score'];
            if ($avgScore >= 85) {
                echo '<span class="performance-indicator excellent">Excellent Performance</span>';
            } elseif ($avgScore >= 75) {
                echo '<span class="performance-indicator good">Good Performance</span>';
            } elseif ($avgScore >= 65) {
                echo '<span class="performance-indicator average">Average Performance</span>';
            } else {
                echo '<span class="performance-indicator needs-improvement">Needs Improvement</span>';
            }
            ?>
        </div>
    </div>
    
    <?php if (!empty($data['course_performance'])): ?>
    <div class="section">
        <h2>📖 Performance by Course</h2>
        <table>
            <thead>
                <tr>
                    <th>Course Name</th>
                    <th>Attempts</th>
                    <th>Average Score</th>
                    <th>Best Score</th>
                    <th>Performance Level</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['course_performance'] as $course): 
                    $avgScore = $course['avg_score'];
                    $performance = $avgScore >= 85 ? 'Excellent' : 
                                  ($avgScore >= 75 ? 'Good' : 
                                  ($avgScore >= 65 ? 'Average' : 'Needs Improvement'));
                    $performanceClass = strtolower(str_replace(' ', '-', $performance));
                ?>
                <tr>
                    <td><?php echo escape($course['cou_name']); ?></td>
                    <td><?php echo $course['attempts']; ?></td>
                    <td><?php echo round($course['avg_score'], 1); ?>%</td>
                    <td><?php echo round($course['best_score'], 1); ?>%</td>
                    <td><span class="performance-indicator <?php echo $performanceClass; ?>"><?php echo $performance; ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($data['progress_trend'])): ?>
    <div class="section">
        <h2>📈 Progress Trend Analysis</h2>
        <div class="progress-chart">
            <h3>Monthly Performance Trend</h3>
            <table>
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Attempts</th>
                        <th>Average Score</th>
                        <th>Trend</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $previousScore = null;
                    foreach ($data['progress_trend'] as $trend): 
                        $currentScore = $trend['avg_score'];
                        $trendIcon = '';
                        $trendClass = 'trend-stable';
                        
                        if ($previousScore !== null) {
                            if ($currentScore > $previousScore + 2) {
                                $trendIcon = '↗️ Improving';
                                $trendClass = 'trend-up';
                            } elseif ($currentScore < $previousScore - 2) {
                                $trendIcon = '↘️ Declining';
                                $trendClass = 'trend-down';
                            } else {
                                $trendIcon = '→ Stable';
                                $trendClass = 'trend-stable';
                            }
                        } else {
                            $trendIcon = '→ Baseline';
                        }
                        
                        $monthName = date('F Y', strtotime($trend['month'] . '-01'));
                    ?>
                    <tr>
                        <td><?php echo $monthName; ?></td>
                        <td><?php echo $trend['attempts']; ?></td>
                        <td><?php echo round($currentScore, 1); ?>%</td>
                        <td class="<?php echo $trendClass; ?>"><?php echo $trendIcon; ?></td>
                    </tr>
                    <?php 
                        $previousScore = $currentScore;
                    endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($data['exam_history'])): ?>
    <div class="section">
        <h2>📝 Detailed Exam History</h2>
        <table>
            <thead>
                <tr>
                    <th>Exam Title</th>
                    <th>Course</th>
                    <th>Score</th>
                    <th>Grade</th>
                    <th>Duration</th>
                    <th>Date Taken</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['exam_history'] as $exam): 
                    $score = $exam['score'];
                    $grade = $score >= 90 ? 'A' : 
                            ($score >= 80 ? 'B' : 
                            ($score >= 70 ? 'C' : 
                            ($score >= 60 ? 'D' : 'F')));
                    
                    $gradeClass = $score >= 85 ? 'excellent' : 
                                 ($score >= 75 ? 'good' : 
                                 ($score >= 65 ? 'average' : 'needs-improvement'));
                ?>
                <tr>
                    <td><?php echo escape($exam['ex_title']); ?></td>
                    <td><?php echo escape($exam['cou_name']); ?></td>
                    <td><?php echo $score; ?>%</td>
                    <td><span class="performance-indicator <?php echo $gradeClass; ?>"><?php echo $grade; ?></span></td>
                    <td><?php echo $exam['duration_minutes']; ?> min</td>
                    <td><?php echo date('M j, Y', strtotime($exam['examStarted'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    
    <div class="section">
        <h2>💡 Performance Insights & Recommendations</h2>
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #28a745;">
            <?php 
            $avgScore = $data['overall_stats']['avg_score'];
            $totalExams = $data['overall_stats']['completed_exams'];
            $bestScore = $data['overall_stats']['best_score'];
            ?>
            
            <h4>📊 Performance Analysis:</h4>
            <ul>
                <li><strong>Overall Grade:</strong> 
                    <?php 
                    if ($avgScore >= 90) echo "A - Outstanding performance! 🌟";
                    elseif ($avgScore >= 80) echo "B - Very good performance! 👍";
                    elseif ($avgScore >= 70) echo "C - Good performance with room for improvement 📈";
                    elseif ($avgScore >= 60) echo "D - Satisfactory, but needs significant improvement 📚";
                    else echo "F - Requires immediate attention and support 🆘";
                    ?>
                </li>
                
                <li><strong>Consistency:</strong> 
                    <?php 
                    $scoreDiff = $bestScore - $data['overall_stats']['lowest_score'];
                    if ($scoreDiff <= 15) echo "Highly consistent performance across exams ✅";
                    elseif ($scoreDiff <= 30) echo "Moderately consistent with some variation ⚖️";
                    else echo "Performance varies significantly between exams 📊";
                    ?>
                </li>
                
                <li><strong>Activity Level:</strong> 
                    <?php 
                    if ($totalExams >= 10) echo "Very active learner with extensive exam participation 🚀";
                    elseif ($totalExams >= 5) echo "Good participation in examinations 👌";
                    else echo "Limited exam participation - consider taking more practice tests 📝";
                    ?>
                </li>
            </ul>
            
            <h4>🎯 Recommendations:</h4>
            <ul>
                <?php if ($avgScore < 70): ?>
                <li>Focus on fundamental concepts and consider additional study time</li>
                <li>Seek help from instructors or tutoring services</li>
                <li>Practice with more sample questions and mock exams</li>
                <?php elseif ($avgScore < 85): ?>
                <li>Review areas where scores are consistently lower</li>
                <li>Develop better time management strategies during exams</li>
                <li>Consider forming study groups with high-performing peers</li>
                <?php else: ?>
                <li>Maintain current study habits - they're working well!</li>
                <li>Consider helping other students as a way to reinforce learning</li>
                <li>Challenge yourself with advanced topics in your strong subjects</li>
                <?php endif; ?>
                
                <?php if ($totalExams < 5): ?>
                <li>Take more practice exams to improve test-taking skills</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    
    <div class="footer">
        <p>Generated by JustExam Advanced Reporting System</p>
        <p>Student ID: <?php echo $data['student_info']['exmne_id']; ?> | Report ID: STU-<?php echo $data['student_info']['exmne_id']; ?>-<?php echo date('Ymd-His'); ?></p>
        <p><em>This report is confidential and intended solely for academic assessment purposes.</em></p>
    </div>
</body>
</html>
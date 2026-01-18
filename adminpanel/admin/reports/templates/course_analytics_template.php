<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Analytics Report</title>
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
            border-bottom: 3px solid #17a2b8;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #17a2b8;
            margin: 0;
            font-size: 2.5em;
        }
        
        .course-info {
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        
        .course-info h2 {
            margin: 0 0 10px 0;
            font-size: 1.8em;
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
        }
        
        .stat-card .value {
            font-size: 2em;
            font-weight: bold;
            color: #17a2b8;
            margin-bottom: 5px;
        }
        
        .section {
            margin-bottom: 40px;
        }
        
        .section h2 {
            color: #17a2b8;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
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
            background: #17a2b8;
            color: white;
            font-weight: 600;
        }
        
        .performance-excellent { color: #28a745; font-weight: bold; }
        .performance-good { color: #17a2b8; font-weight: bold; }
        .performance-average { color: #ffc107; font-weight: bold; }
        .performance-poor { color: #dc3545; font-weight: bold; }
        
        .footer {
            text-align: center;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            color: #666;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📚 Course Analytics Report</h1>
        <div class="subtitle">Comprehensive Course Performance Analysis</div>
    </div>
    
    <div class="course-info">
        <h2><?php echo escape($data['course_info']['cou_name']); ?></h2>
        <p><?php echo escape($data['course_info']['cou_description']); ?></p>
        <div style="margin-top: 15px; font-size: 0.9em;">
            Report Generated: <?php echo date('F j, Y \a\t g:i A'); ?>
        </div>
    </div>
    
    <div class="section">
        <h2>📊 Course Statistics</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="value"><?php echo $data['course_stats']['total_exams']; ?></div>
                <div class="label">Total Exams</div>
            </div>
            <div class="stat-card">
                <div class="value"><?php echo $data['course_stats']['unique_students']; ?></div>
                <div class="label">Unique Students</div>
            </div>
            <div class="stat-card">
                <div class="value"><?php echo $data['course_stats']['total_attempts']; ?></div>
                <div class="label">Total Attempts</div>
            </div>
            <div class="stat-card">
                <div class="value"><?php echo round($data['course_stats']['avg_score'], 1); ?>%</div>
                <div class="label">Average Score</div>
            </div>
        </div>
    </div>
    
    <?php if (!empty($data['exam_performance'])): ?>
    <div class="section">
        <h2>📝 Exam Performance Analysis</h2>
        <table>
            <thead>
                <tr>
                    <th>Exam Title</th>
                    <th>Attempts</th>
                    <th>Average Score</th>
                    <th>Highest Score</th>
                    <th>Lowest Score</th>
                    <th>Performance Level</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['exam_performance'] as $exam): 
                    $avgScore = $exam['avg_score'];
                    $performance = $avgScore >= 85 ? 'Excellent' : 
                                  ($avgScore >= 75 ? 'Good' : 
                                  ($avgScore >= 65 ? 'Average' : 'Poor'));
                    $performanceClass = 'performance-' . strtolower($performance);
                ?>
                <tr>
                    <td><?php echo escape($exam['ex_title']); ?></td>
                    <td><?php echo $exam['attempts']; ?></td>
                    <td><?php echo round($exam['avg_score'], 1); ?>%</td>
                    <td><?php echo round($exam['max_score'], 1); ?>%</td>
                    <td><?php echo round($exam['min_score'], 1); ?>%</td>
                    <td class="<?php echo $performanceClass; ?>"><?php echo $performance; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($data['top_students'])): ?>
    <div class="section">
        <h2>🏆 Top Performing Students</h2>
        <table>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Attempts</th>
                    <th>Average Score</th>
                    <th>Best Score</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $rank = 1;
                foreach ($data['top_students'] as $student): 
                ?>
                <tr>
                    <td><?php echo $rank++; ?></td>
                    <td><?php echo escape($student['exmne_fullname']); ?></td>
                    <td><?php echo escape($student['exmne_email']); ?></td>
                    <td><?php echo $student['attempts']; ?></td>
                    <td><?php echo round($student['avg_score'], 1); ?>%</td>
                    <td><?php echo round($student['best_score'], 1); ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    
    <div class="footer">
        <p>Generated by JustExam Advanced Reporting System</p>
        <p>Course ID: <?php echo $data['course_info']['cou_id']; ?> | Report ID: COU-<?php echo $data['course_info']['cou_id']; ?>-<?php echo date('Ymd-His'); ?></p>
    </div>
</body>
</html>
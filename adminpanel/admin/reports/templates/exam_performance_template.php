<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Performance Report</title>
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
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 2.5em;
        }
        
        .header .subtitle {
            color: #666;
            font-size: 1.2em;
            margin-top: 10px;
        }
        
        .report-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .section {
            margin-bottom: 40px;
        }
        
        .section h2 {
            color: #007bff;
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
        }
        
        .stat-card .value {
            font-size: 2em;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        
        .stat-card .label {
            color: #666;
            font-size: 0.9em;
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
            background: #007bff;
            color: white;
            font-weight: 600;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .grade-a { color: #28a745; font-weight: bold; }
        .grade-b { color: #17a2b8; font-weight: bold; }
        .grade-c { color: #ffc107; font-weight: bold; }
        .grade-d { color: #fd7e14; font-weight: bold; }
        .grade-f { color: #dc3545; font-weight: bold; }
        
        .progress-bar {
            width: 100%;
            height: 20px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #17a2b8);
            transition: width 0.3s ease;
        }
        
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
        <h1>📊 Exam Performance Report</h1>
        <div class="subtitle"><?php echo escape($data['exam_info']['ex_title']); ?></div>
    </div>
    
    <div class="report-info">
        <strong>Report Generated:</strong> <?php echo date('F j, Y \a\t g:i A'); ?><br>
        <strong>Course:</strong> <?php echo escape($data['exam_info']['cou_name']); ?><br>
        <strong>Exam Description:</strong> <?php echo escape($data['exam_info']['ex_description']); ?>
    </div>
    
    <div class="section">
        <h2>📈 Performance Statistics</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="value"><?php echo $data['statistics']['total_attempts']; ?></div>
                <div class="label">Total Attempts</div>
            </div>
            <div class="stat-card">
                <div class="value"><?php echo $data['statistics']['completed_attempts']; ?></div>
                <div class="label">Completed</div>
            </div>
            <div class="stat-card">
                <div class="value"><?php echo round($data['statistics']['avg_score'], 1); ?>%</div>
                <div class="label">Average Score</div>
            </div>
            <div class="stat-card">
                <div class="value"><?php echo round($data['statistics']['max_score'], 1); ?>%</div>
                <div class="label">Highest Score</div>
            </div>
            <div class="stat-card">
                <div class="value"><?php echo round($data['statistics']['min_score'], 1); ?>%</div>
                <div class="label">Lowest Score</div>
            </div>
            <div class="stat-card">
                <div class="value"><?php echo round(($data['statistics']['completed_attempts'] / $data['statistics']['total_attempts']) * 100, 1); ?>%</div>
                <div class="label">Completion Rate</div>
            </div>
        </div>
    </div>
    
    <?php if (!empty($data['score_distribution'])): ?>
    <div class="section">
        <h2>📊 Grade Distribution</h2>
        <table>
            <thead>
                <tr>
                    <th>Grade</th>
                    <th>Number of Students</th>
                    <th>Percentage</th>
                    <th>Visual Distribution</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalCompleted = $data['statistics']['completed_attempts'];
                foreach ($data['score_distribution'] as $grade): 
                    $percentage = round(($grade['count'] / $totalCompleted) * 100, 1);
                    $gradeClass = strtolower(substr($grade['grade'], 0, 1));
                ?>
                <tr>
                    <td class="grade-<?php echo $gradeClass; ?>"><?php echo $grade['grade']; ?></td>
                    <td><?php echo $grade['count']; ?></td>
                    <td><?php echo $percentage; ?>%</td>
                    <td>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $percentage; ?>%"></div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($data['student_results'])): ?>
    <div class="section">
        <h2>👥 Student Results</h2>
        <table>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Score</th>
                    <th>Grade</th>
                    <th>Duration</th>
                    <th>Submitted</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $rank = 1;
                foreach ($data['student_results'] as $result): 
                    $grade = $result['score'] >= 90 ? 'A' : 
                            ($result['score'] >= 80 ? 'B' : 
                            ($result['score'] >= 70 ? 'C' : 
                            ($result['score'] >= 60 ? 'D' : 'F')));
                    $gradeClass = strtolower($grade);
                ?>
                <tr>
                    <td><?php echo $rank++; ?></td>
                    <td><?php echo escape($result['exmne_fullname']); ?></td>
                    <td><?php echo escape($result['exmne_email']); ?></td>
                    <td><?php echo $result['score']; ?>%</td>
                    <td class="grade-<?php echo $gradeClass; ?>"><?php echo $grade; ?></td>
                    <td><?php echo $result['duration_minutes']; ?> min</td>
                    <td><?php echo date('M j, Y g:i A', strtotime($result['examSubmitted'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($data['question_analysis'])): ?>
    <div class="section">
        <h2>❓ Question Analysis</h2>
        <table>
            <thead>
                <tr>
                    <th>Question</th>
                    <th>Total Responses</th>
                    <th>Correct Responses</th>
                    <th>Success Rate</th>
                    <th>Difficulty</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['question_analysis'] as $question): 
                    $difficulty = $question['success_rate'] >= 80 ? 'Easy' : 
                                 ($question['success_rate'] >= 60 ? 'Medium' : 'Hard');
                    $difficultyClass = strtolower($difficulty);
                ?>
                <tr>
                    <td><?php echo escape(substr($question['exam_question'], 0, 100)) . (strlen($question['exam_question']) > 100 ? '...' : ''); ?></td>
                    <td><?php echo $question['total_responses']; ?></td>
                    <td><?php echo $question['correct_responses']; ?></td>
                    <td><?php echo $question['success_rate']; ?>%</td>
                    <td class="grade-<?php echo $difficultyClass === 'easy' ? 'a' : ($difficultyClass === 'medium' ? 'c' : 'f'); ?>">
                        <?php echo $difficulty; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    
    <div class="footer">
        <p>Generated by JustExam Advanced Reporting System</p>
        <p>Report ID: EXM-<?php echo $data['exam_info']['ex_id']; ?>-<?php echo date('Ymd-His'); ?></p>
    </div>
</body>
</html>
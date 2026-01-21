<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comparative Analysis Report</title>
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
            border-bottom: 3px solid #fd7e14;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #fd7e14;
            margin: 0;
            font-size: 2.5em;
        }
        
        .comparison-summary {
            background: linear-gradient(135deg, #fd7e14, #e55a00);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .section {
            margin-bottom: 40px;
        }
        
        .section h2 {
            color: #fd7e14;
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
            background: #fd7e14;
            color: white;
            font-weight: 600;
        }
        
        .difficulty-easy { color: #28a745; font-weight: bold; }
        .difficulty-medium { color: #ffc107; font-weight: bold; }
        .difficulty-hard { color: #dc3545; font-weight: bold; }
        
        .performance-bar {
            width: 100%;
            height: 20px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
        }
        
        .performance-fill {
            height: 100%;
            background: linear-gradient(90deg, #dc3545, #ffc107, #28a745);
            transition: width 0.3s ease;
        }
        
        .comparison-chart {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .insights-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .insights-box h3 {
            color: #856404;
            margin-top: 0;
        }
        
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
        <h1>📊 Comparative Analysis Report</h1>
        <div class="subtitle">Multi-Exam Performance Comparison</div>
    </div>
    
    <div class="comparison-summary">
        <h2>Comparison Overview</h2>
        <p>Analyzing performance across <?php echo count($data['exam_comparison']); ?> selected exams</p>
        <div style="margin-top: 15px; font-size: 0.9em;">
            Report Generated: <?php echo date('F j, Y \a\t g:i A'); ?>
        </div>
    </div>
    
    <?php if (!empty($data['exam_comparison'])): ?>
    <div class="section">
        <h2>📈 Exam Performance Comparison</h2>
        <table>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Exam Title</th>
                    <th>Course</th>
                    <th>Attempts</th>
                    <th>Average Score</th>
                    <th>Highest Score</th>
                    <th>Lowest Score</th>
                    <th>Performance Visualization</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $rank = 1;
                foreach ($data['exam_comparison'] as $exam): 
                ?>
                <tr>
                    <td><?php echo $rank++; ?></td>
                    <td><?php echo escape($exam['ex_title']); ?></td>
                    <td><?php echo escape($exam['cou_name']); ?></td>
                    <td><?php echo $exam['attempts']; ?></td>
                    <td><?php echo round($exam['avg_score'], 1); ?>%</td>
                    <td><?php echo round($exam['max_score'], 1); ?>%</td>
                    <td><?php echo round($exam['min_score'], 1); ?>%</td>
                    <td>
                        <div class="performance-bar">
                            <div class="performance-fill" style="width: <?php echo $exam['avg_score']; ?>%"></div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($data['difficulty_analysis'])): ?>
    <div class="section">
        <h2>🎯 Difficulty Analysis</h2>
        <div class="comparison-chart">
            <h3>Exam Difficulty Classification</h3>
            <table>
                <thead>
                    <tr>
                        <th>Exam Title</th>
                        <th>Average Score</th>
                        <th>Difficulty Level</th>
                        <th>Classification Criteria</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['difficulty_analysis'] as $analysis): 
                        $difficultyClass = 'difficulty-' . strtolower($analysis['difficulty_level']);
                        
                        $criteria = '';
                        switch ($analysis['difficulty_level']) {
                            case 'Easy':
                                $criteria = 'Average score ≥ 80%';
                                break;
                            case 'Medium':
                                $criteria = 'Average score 60-79%';
                                break;
                            case 'Hard':
                                $criteria = 'Average score < 60%';
                                break;
                        }
                    ?>
                    <tr>
                        <td><?php echo escape($analysis['ex_title']); ?></td>
                        <td><?php echo round($analysis['avg_score'], 1); ?>%</td>
                        <td class="<?php echo $difficultyClass; ?>"><?php echo $analysis['difficulty_level']; ?></td>
                        <td><?php echo $criteria; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="section">
        <h2>📋 Statistical Summary</h2>
        <div class="comparison-chart">
            <?php 
            // Calculate overall statistics
            $totalAttempts = array_sum(array_column($data['exam_comparison'], 'attempts'));
            $avgScores = array_column($data['exam_comparison'], 'avg_score');
            $overallAvg = array_sum($avgScores) / count($avgScores);
            $highestAvg = max($avgScores);
            $lowestAvg = min($avgScores);
            $scoreRange = $highestAvg - $lowestAvg;
            
            // Count difficulty levels
            $difficultyCount = array_count_values(array_column($data['difficulty_analysis'], 'difficulty_level'));
            ?>
            
            <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div class="stat-card" style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 1.5em; font-weight: bold; color: #fd7e14;"><?php echo count($data['exam_comparison']); ?></div>
                    <div>Exams Compared</div>
                </div>
                <div class="stat-card" style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 1.5em; font-weight: bold; color: #fd7e14;"><?php echo $totalAttempts; ?></div>
                    <div>Total Attempts</div>
                </div>
                <div class="stat-card" style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 1.5em; font-weight: bold; color: #fd7e14;"><?php echo round($overallAvg, 1); ?>%</div>
                    <div>Overall Average</div>
                </div>
                <div class="stat-card" style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 1.5em; font-weight: bold; color: #fd7e14;"><?php echo round($scoreRange, 1); ?>%</div>
                    <div>Score Range</div>
                </div>
            </div>
            
            <div style="margin-top: 30px;">
                <h4>Difficulty Distribution:</h4>
                <ul>
                    <li><span class="difficulty-easy">Easy Exams:</span> <?php echo $difficultyCount['Easy'] ?? 0; ?> (<?php echo round((($difficultyCount['Easy'] ?? 0) / count($data['difficulty_analysis'])) * 100, 1); ?>%)</li>
                    <li><span class="difficulty-medium">Medium Exams:</span> <?php echo $difficultyCount['Medium'] ?? 0; ?> (<?php echo round((($difficultyCount['Medium'] ?? 0) / count($data['difficulty_analysis'])) * 100, 1); ?>%)</li>
                    <li><span class="difficulty-hard">Hard Exams:</span> <?php echo $difficultyCount['Hard'] ?? 0; ?> (<?php echo round((($difficultyCount['Hard'] ?? 0) / count($data['difficulty_analysis'])) * 100, 1); ?>%)</li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="section">
        <h2>💡 Comparative Insights & Recommendations</h2>
        <div class="insights-box">
            <h3>📊 Performance Analysis:</h3>
            <ul>
                <?php if ($overallAvg >= 80): ?>
                <li><strong>Overall Performance:</strong> Excellent - Students are performing well across all compared exams 🌟</li>
                <?php elseif ($overallAvg >= 70): ?>
                <li><strong>Overall Performance:</strong> Good - Solid performance with room for targeted improvements 👍</li>
                <?php elseif ($overallAvg >= 60): ?>
                <li><strong>Overall Performance:</strong> Satisfactory - Consider reviewing exam difficulty and teaching methods 📚</li>
                <?php else: ?>
                <li><strong>Overall Performance:</strong> Needs Attention - Significant improvements required 🆘</li>
                <?php endif; ?>
                
                <?php if ($scoreRange <= 10): ?>
                <li><strong>Consistency:</strong> Highly consistent performance across exams - well-balanced difficulty levels ✅</li>
                <?php elseif ($scoreRange <= 25): ?>
                <li><strong>Consistency:</strong> Moderate variation - some exams may need difficulty adjustment ⚖️</li>
                <?php else: ?>
                <li><strong>Consistency:</strong> High variation - significant differences in exam difficulty detected 📊</li>
                <?php endif; ?>
                
                <li><strong>Difficulty Balance:</strong> 
                    <?php 
                    $easyCount = $difficultyCount['Easy'] ?? 0;
                    $mediumCount = $difficultyCount['Medium'] ?? 0;
                    $hardCount = $difficultyCount['Hard'] ?? 0;
                    $total = count($data['difficulty_analysis']);
                    
                    if ($mediumCount / $total >= 0.5) {
                        echo "Well-balanced difficulty distribution 🎯";
                    } elseif ($easyCount / $total >= 0.6) {
                        echo "Exams may be too easy - consider increasing difficulty 📈";
                    } elseif ($hardCount / $total >= 0.6) {
                        echo "Exams may be too difficult - consider adjusting content 📉";
                    } else {
                        echo "Mixed difficulty levels - review individual exam design 🔄";
                    }
                    ?>
                </li>
            </ul>
            
            <h3>🎯 Recommendations:</h3>
            <ul>
                <?php if ($overallAvg < 70): ?>
                <li>Review and adjust exam content for better alignment with learning objectives</li>
                <li>Provide additional study materials for underperforming topics</li>
                <li>Consider implementing pre-exam review sessions</li>
                <?php endif; ?>
                
                <?php if ($scoreRange > 25): ?>
                <li>Standardize exam difficulty levels across the curriculum</li>
                <li>Review question types and complexity for consistency</li>
                <li>Implement peer review process for exam creation</li>
                <?php endif; ?>
                
                <?php if (($difficultyCount['Hard'] ?? 0) > ($difficultyCount['Easy'] ?? 0) + ($difficultyCount['Medium'] ?? 0)): ?>
                <li>Consider reducing overall exam difficulty to improve student confidence</li>
                <li>Provide more practice opportunities before difficult exams</li>
                <?php endif; ?>
                
                <li>Use this comparative data to inform curriculum planning and assessment design</li>
                <li>Share insights with faculty to improve teaching strategies</li>
            </ul>
        </div>
    </div>
    
    <div class="footer">
        <p>Generated by JustExam Advanced Reporting System</p>
        <p>Comparison Report ID: CMP-<?php echo implode('-', array_column($data['exam_comparison'], 'ex_id')); ?>-<?php echo date('Ymd-His'); ?></p>
        <p><em>This comparative analysis helps identify trends and optimize assessment strategies.</em></p>
    </div>
</body>
</html>
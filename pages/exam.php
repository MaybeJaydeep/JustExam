<script type="text/javascript">
   function preventBack(){window.history.forward();}
    setTimeout("preventBack()", 0);
    window.onunload=function(){null};
</script>

<?php 
    // Validate and sanitize exam ID
    $examId = $_GET['id'] ?? '';
    if (!validateId($examId)) {
        die("Invalid exam ID");
    }

    // Check if user is authorized to take this exam
    $exmne_id = $_SESSION['student']['user_id'];
    
    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM exam_tbl WHERE ex_id = ?");
    $stmt->execute([$examId]);
    $selExam = $stmt->fetch();
    
    if (!$selExam) {
        die("Exam not found");
    }
    
    // Check if already taken
    $stmt = $conn->prepare("SELECT * FROM exam_attempt WHERE exmne_id = ? AND exam_id = ?");
    $stmt->execute([$exmne_id, $examId]);
    if ($stmt->rowCount() > 0) {
        header("location: ?page=result&id=" . $examId);
        exit;
    }
    
    $selExamTimeLimit = $selExam['ex_time_limit'];
    $exDisplayLimit = $selExam['ex_questlimit_display'];

    // Get all questions for this exam
    $stmt = $conn->prepare("SELECT * FROM exam_question_tbl WHERE exam_id = ? AND exam_status = 'active' ORDER BY RAND() LIMIT ?");
    $stmt->bindValue(1, $examId, PDO::PARAM_INT);
    $stmt->bindValue(2, $exDisplayLimit, PDO::PARAM_INT);
    $stmt->execute();
    $questions = $stmt->fetchAll();
    $totalQuestions = count($questions);
?>

<!-- Enhanced Exam Styles -->
<style>
/* Mobile-First Responsive Design */
.exam-container {
    display: flex;
    min-height: 100vh;
    background: #f8f9fa;
}

.exam-sidebar {
    width: 300px;
    background: white;
    border-right: 1px solid #dee2e6;
    padding: 20px;
    position: fixed;
    height: 100vh;
    overflow-y: auto;
    z-index: 1000;
    transition: transform 0.3s ease;
}

.exam-content {
    flex: 1;
    margin-left: 300px;
    padding: 20px;
    transition: margin-left 0.3s ease;
}

/* Timer Styles */
.exam-timer {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px;
    border-radius: 10px;
    text-align: center;
    margin-bottom: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.timer-display {
    font-size: 2rem;
    font-weight: bold;
    margin: 10px 0;
}

.timer-warning {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

/* Question Navigation */
.question-nav {
    margin-bottom: 20px;
}

.question-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(40px, 1fr));
    gap: 8px;
    margin-bottom: 20px;
}

.question-btn {
    width: 40px;
    height: 40px;
    border: 2px solid #dee2e6;
    background: white;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.3s ease;
}

.question-btn:hover {
    border-color: #667eea;
    background: #f8f9ff;
}

.question-btn.answered {
    background: #28a745;
    color: white;
    border-color: #28a745;
}

.question-btn.current {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.question-btn.marked {
    background: #ffc107;
    color: #212529;
    border-color: #ffc107;
}

/* Question Display */
.question-card {
    background: white;
    border-radius: 10px;
    padding: 30px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: none;
}

.question-card.active {
    display: block;
}

.question-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f8f9fa;
}

.question-number {
    background: #667eea;
    color: white;
    padding: 8px 15px;
    border-radius: 20px;
    font-weight: bold;
}

.question-actions {
    display: flex;
    gap: 10px;
}

.mark-review-btn {
    background: #ffc107;
    color: #212529;
    border: none;
    padding: 8px 15px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.3s ease;
}

.mark-review-btn:hover {
    background: #e0a800;
}

.mark-review-btn.marked {
    background: #dc3545;
    color: white;
}

.question-text {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 25px;
    line-height: 1.6;
    color: #2c3e50;
}

.choices-container {
    display: grid;
    grid-template-columns: 1fr;
    gap: 15px;
}

.choice-item {
    background: #f8f9fa;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.choice-item:hover {
    border-color: #667eea;
    background: #f8f9ff;
}

.choice-item.selected {
    border-color: #28a745;
    background: #d4edda;
}

.choice-radio {
    position: absolute;
    opacity: 0;
}

.choice-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    font-weight: 500;
}

.choice-marker {
    width: 20px;
    height: 20px;
    border: 2px solid #dee2e6;
    border-radius: 50%;
    margin-right: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.choice-item.selected .choice-marker {
    border-color: #28a745;
    background: #28a745;
}

.choice-item.selected .choice-marker::after {
    content: '✓';
    color: white;
    font-size: 12px;
    font-weight: bold;
}

/* Navigation Controls */
.exam-controls {
    background: white;
    padding: 20px;
    border-radius: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-top: 20px;
}

.nav-btn {
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.nav-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.prev-btn {
    background: #6c757d;
    color: white;
}

.prev-btn:hover:not(:disabled) {
    background: #5a6268;
}

.next-btn {
    background: #007bff;
    color: white;
}

.next-btn:hover:not(:disabled) {
    background: #0056b3;
}

.submit-btn {
    background: #28a745;
    color: white;
    padding: 15px 30px;
    font-size: 1.1rem;
}

.submit-btn:hover {
    background: #218838;
}

/* Progress Bar */
.progress-container {
    margin-bottom: 20px;
}

.progress-bar-container {
    background: #e9ecef;
    border-radius: 10px;
    height: 8px;
    overflow: hidden;
}

.progress-bar {
    background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
    height: 100%;
    transition: width 0.3s ease;
    border-radius: 10px;
}

.progress-text {
    text-align: center;
    margin-top: 8px;
    font-size: 14px;
    color: #6c757d;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .exam-sidebar {
        transform: translateX(-100%);
        width: 280px;
    }
    
    .exam-sidebar.mobile-open {
        transform: translateX(0);
    }
    
    .exam-content {
        margin-left: 0;
        padding: 15px;
    }
    
    .mobile-toggle {
        display: block;
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 1001;
        background: #667eea;
        color: white;
        border: none;
        padding: 10px;
        border-radius: 5px;
        cursor: pointer;
    }
    
    .question-card {
        padding: 20px;
    }
    
    .timer-display {
        font-size: 1.5rem;
    }
    
    .exam-controls {
        flex-direction: column;
        gap: 15px;
    }
    
    .exam-controls .nav-controls {
        display: flex;
        width: 100%;
        justify-content: space-between;
    }
    
    .choices-container {
        gap: 10px;
    }
    
    .choice-item {
        padding: 12px;
    }
    
    .question-grid {
        grid-template-columns: repeat(auto-fill, minmax(35px, 1fr));
    }
    
    .question-btn {
        width: 35px;
        height: 35px;
        font-size: 12px;
    }
}

@media (max-width: 480px) {
    .exam-sidebar {
        width: 100%;
    }
    
    .timer-display {
        font-size: 1.2rem;
    }
    
    .question-text {
        font-size: 1rem;
    }
    
    .choice-label {
        font-size: 14px;
    }
    
    .nav-btn {
        padding: 10px 15px;
        font-size: 14px;
    }
}

/* Overlay for mobile sidebar */
.mobile-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 999;
}

.mobile-overlay.active {
    display: block;
}

/* Loading States */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

/* Auto-save indicator */
.auto-save-indicator {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #28a745;
    color: white;
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 12px;
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 1000;
}

.auto-save-indicator.show {
    opacity: 1;
}
</style>

<div class="exam-container">
    <!-- Mobile Toggle Button -->
    <button class="mobile-toggle d-md-none" onclick="toggleMobileSidebar()">
        <i class="pe-7s-menu"></i> Menu
    </button>
    
    <!-- Mobile Overlay -->
    <div class="mobile-overlay" onclick="toggleMobileSidebar()"></div>
    
    <!-- Exam Sidebar -->
    <div class="exam-sidebar" id="examSidebar">
        <!-- Timer -->
        <div class="exam-timer" id="examTimer">
            <div class="timer-label">Time Remaining</div>
            <div class="timer-display" id="timerDisplay">
                <?php echo sprintf("%02d:%02d", $selExamTimeLimit, 0); ?>
            </div>
            <div class="timer-status" id="timerStatus">Exam in Progress</div>
        </div>
        
        <!-- Progress -->
        <div class="progress-container">
            <div class="progress-bar-container">
                <div class="progress-bar" id="progressBar" style="width: 0%"></div>
            </div>
            <div class="progress-text" id="progressText">0 of <?php echo $totalQuestions; ?> answered</div>
        </div>
        
        <!-- Question Navigation -->
        <div class="question-nav">
            <h6 style="margin-bottom: 15px; color: #495057;">Questions</h6>
            <div class="question-grid" id="questionGrid">
                <?php for($i = 1; $i <= $totalQuestions; $i++): ?>
                    <div class="question-btn" data-question="<?php echo $i; ?>" onclick="goToQuestion(<?php echo $i; ?>)">
                        <?php echo $i; ?>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
        
        <!-- Legend -->
        <div class="legend" style="font-size: 12px; color: #6c757d;">
            <div style="margin-bottom: 8px;">
                <span class="question-btn" style="width: 20px; height: 20px; font-size: 10px; margin-right: 8px;">1</span>
                Not Answered
            </div>
            <div style="margin-bottom: 8px;">
                <span class="question-btn answered" style="width: 20px; height: 20px; font-size: 10px; margin-right: 8px;">1</span>
                Answered
            </div>
            <div style="margin-bottom: 8px;">
                <span class="question-btn marked" style="width: 20px; height: 20px; font-size: 10px; margin-right: 8px;">1</span>
                Marked for Review
            </div>
            <div>
                <span class="question-btn current" style="width: 20px; height: 20px; font-size: 10px; margin-right: 8px;">1</span>
                Current Question
            </div>
        </div>
    </div>
    
    <!-- Exam Content -->
    <div class="exam-content">
        <form method="post" id="submitAnswerFrm">
            <input type="hidden" name="exam_id" id="exam_id" value="<?php echo escape($examId); ?>">
            <input type="hidden" name="examAction" id="examAction">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="time_taken" id="timeTaken" value="0">
            
            <?php 
            $questionNumber = 1;
            foreach($questions as $question): 
                $questId = $question['eqt_id'];
            ?>
                <div class="question-card" id="question_<?php echo $questionNumber; ?>" <?php echo $questionNumber === 1 ? 'style="display: block;"' : ''; ?>>
                    <div class="question-header">
                        <div class="question-number">
                            Question <?php echo $questionNumber; ?> of <?php echo $totalQuestions; ?>
                        </div>
                        <div class="question-actions">
                            <button type="button" class="mark-review-btn" onclick="toggleMarkForReview(<?php echo $questionNumber; ?>)">
                                <i class="pe-7s-flag"></i> Mark for Review
                            </button>
                        </div>
                    </div>
                    
                    <div class="question-text">
                        <?php echo escape($question['exam_question']); ?>
                    </div>
                    
                    <div class="choices-container">
                        <?php 
                        $choices = [
                            'A' => $question['exam_ch1'],
                            'B' => $question['exam_ch2'],
                            'C' => $question['exam_ch3'],
                            'D' => $question['exam_ch4']
                        ];
                        
                        foreach($choices as $letter => $choice): 
                        ?>
                            <div class="choice-item" onclick="selectChoice(<?php echo $questionNumber; ?>, '<?php echo $letter; ?>', '<?php echo escape($choice); ?>')">
                                <input type="radio" 
                                       name="answer[<?php echo escape($questId); ?>][correct]" 
                                       value="<?php echo escape($choice); ?>" 
                                       id="q<?php echo $questId; ?>_<?php echo $letter; ?>" 
                                       class="choice-radio">
                                <label class="choice-label" for="q<?php echo $questId; ?>_<?php echo $letter; ?>">
                                    <div class="choice-marker"></div>
                                    <span><?php echo $letter; ?>. <?php echo escape($choice); ?></span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php 
            $questionNumber++;
            endforeach; 
            ?>
        </form>
        
        <!-- Navigation Controls -->
        <div class="exam-controls">
            <div class="nav-controls">
                <button type="button" class="nav-btn prev-btn" id="prevBtn" onclick="previousQuestion()" disabled>
                    <i class="pe-7s-angle-left"></i> Previous
                </button>
                <button type="button" class="nav-btn next-btn" id="nextBtn" onclick="nextQuestion()">
                    Next <i class="pe-7s-angle-right"></i>
                </button>
            </div>
            <button type="button" class="nav-btn submit-btn" id="submitBtn" onclick="showSubmitConfirmation()" style="display: none;">
                <i class="pe-7s-check"></i> Submit Exam
            </button>
        </div>
    </div>
</div>

<!-- Auto-save Indicator -->
<div class="auto-save-indicator" id="autoSaveIndicator">
    <i class="pe-7s-check"></i> Auto-saved
</div>

<script>
// Exam State Management
let currentQuestion = 1;
let totalQuestions = <?php echo $totalQuestions; ?>;
let timeLimit = <?php echo $selExamTimeLimit; ?> * 60; // Convert to seconds
let timeRemaining = timeLimit;
let examTimer;
let answers = {};
let markedQuestions = new Set();
let examStartTime = Date.now();

// Initialize exam
$(document).ready(function() {
    initializeExam();
    startTimer();
    updateNavigationButtons();
    updateProgress();
    
    // Auto-save every 30 seconds
    setInterval(autoSave, 30000);
    
    // Prevent accidental page refresh
    window.addEventListener('beforeunload', function(e) {
        if (Object.keys(answers).length > 0) {
            e.preventDefault();
            e.returnValue = 'You have unsaved answers. Are you sure you want to leave?';
        }
    });
});

function initializeExam() {
    // Set first question as current
    updateQuestionNavigation();
    
    // Load any existing answers (if implementing resume functionality)
    // loadExistingAnswers();
}

function startTimer() {
    examTimer = setInterval(function() {
        timeRemaining--;
        updateTimerDisplay();
        
        // Warning at 5 minutes
        if (timeRemaining === 300) {
            showTimeWarning('5 minutes remaining!');
        }
        
        // Warning at 1 minute
        if (timeRemaining === 60) {
            showTimeWarning('1 minute remaining!');
        }
        
        // Auto-submit when time expires
        if (timeRemaining <= 0) {
            clearInterval(examTimer);
            autoSubmitExam();
        }
    }, 1000);
}

function updateTimerDisplay() {
    let minutes = Math.floor(timeRemaining / 60);
    let seconds = timeRemaining % 60;
    
    let display = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
    $('#timerDisplay').text(display);
    
    // Change color when time is running low
    if (timeRemaining <= 300) { // 5 minutes
        $('#examTimer').addClass('timer-warning');
    }
    
    // Update hidden field for submission
    let timeTaken = Math.floor((Date.now() - examStartTime) / 1000);
    $('#timeTaken').val(timeTaken);
}

function showTimeWarning(message) {
    Swal.fire({
        title: 'Time Warning!',
        text: message,
        icon: 'warning',
        timer: 3000,
        showConfirmButton: false,
        toast: true,
        position: 'top-end'
    });
}

function autoSubmitExam() {
    Swal.fire({
        title: 'Time Up!',
        text: 'Your exam time has expired. Submitting automatically...',
        icon: 'warning',
        timer: 3000,
        showConfirmButton: false
    }).then(() => {
        submitExam();
    });
}

function goToQuestion(questionNum) {
    if (questionNum < 1 || questionNum > totalQuestions) return;
    
    // Hide current question
    $('.question-card').removeClass('active').hide();
    
    // Show target question
    $('#question_' + questionNum).addClass('active').show();
    
    currentQuestion = questionNum;
    updateNavigationButtons();
    updateQuestionNavigation();
    
    // Close mobile sidebar
    if (window.innerWidth <= 768) {
        toggleMobileSidebar();
    }
}

function nextQuestion() {
    if (currentQuestion < totalQuestions) {
        goToQuestion(currentQuestion + 1);
    }
}

function previousQuestion() {
    if (currentQuestion > 1) {
        goToQuestion(currentQuestion - 1);
    }
}

function updateNavigationButtons() {
    $('#prevBtn').prop('disabled', currentQuestion === 1);
    $('#nextBtn').prop('disabled', currentQuestion === totalQuestions);
    
    // Show submit button on last question or if all questions answered
    if (currentQuestion === totalQuestions || Object.keys(answers).length === totalQuestions) {
        $('#submitBtn').show();
    } else {
        $('#submitBtn').hide();
    }
}

function updateQuestionNavigation() {
    $('.question-btn').removeClass('current');
    $(`.question-btn[data-question="${currentQuestion}"]`).addClass('current');
}

function selectChoice(questionNum, letter, value) {
    // Update visual selection
    $(`#question_${questionNum} .choice-item`).removeClass('selected');
    $(`#question_${questionNum} .choice-item`).has(`input[value="${value}"]`).addClass('selected');
    
    // Check the radio button
    $(`#question_${questionNum} input[value="${value}"]`).prop('checked', true);
    
    // Store answer
    let questionId = $(`#question_${questionNum} input[type="radio"]`).attr('name').match(/\[(\d+)\]/)[1];
    answers[questionId] = value;
    
    // Update question navigation
    $(`.question-btn[data-question="${questionNum}"]`).addClass('answered');
    
    // Update progress
    updateProgress();
    
    // Auto-save
    autoSave();
    
    // Auto-advance to next question (optional)
    setTimeout(() => {
        if (currentQuestion < totalQuestions) {
            nextQuestion();
        }
    }, 500);
}

function toggleMarkForReview(questionNum) {
    let btn = $(`#question_${questionNum} .mark-review-btn`);
    let navBtn = $(`.question-btn[data-question="${questionNum}"]`);
    
    if (markedQuestions.has(questionNum)) {
        markedQuestions.delete(questionNum);
        btn.removeClass('marked').html('<i class="pe-7s-flag"></i> Mark for Review');
        navBtn.removeClass('marked');
    } else {
        markedQuestions.add(questionNum);
        btn.addClass('marked').html('<i class="pe-7s-flag"></i> Marked');
        navBtn.addClass('marked');
    }
}

function updateProgress() {
    let answered = Object.keys(answers).length;
    let percentage = (answered / totalQuestions) * 100;
    
    $('#progressBar').css('width', percentage + '%');
    $('#progressText').text(`${answered} of ${totalQuestions} answered`);
}

function autoSave() {
    if (Object.keys(answers).length === 0) return;
    
    // Show auto-save indicator
    $('#autoSaveIndicator').addClass('show');
    setTimeout(() => {
        $('#autoSaveIndicator').removeClass('show');
    }, 2000);
    
    // Here you could implement actual auto-save to server
    // $.post('query/autoSaveExamExe.php', { answers: answers, exam_id: $('#exam_id').val() });
}

function showSubmitConfirmation() {
    let answered = Object.keys(answers).length;
    let unanswered = totalQuestions - answered;
    let marked = markedQuestions.size;
    
    let message = `You have answered ${answered} out of ${totalQuestions} questions.`;
    if (unanswered > 0) {
        message += `\n${unanswered} questions are unanswered.`;
    }
    if (marked > 0) {
        message += `\n${marked} questions are marked for review.`;
    }
    message += '\n\nAre you sure you want to submit your exam?';
    
    Swal.fire({
        title: 'Submit Exam?',
        text: message,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Submit',
        cancelButtonText: 'Review Answers',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            submitExam();
        }
    });
}

function submitExam() {
    // Stop timer
    clearInterval(examTimer);
    
    // Show loading
    Swal.fire({
        title: 'Submitting Exam...',
        text: 'Please wait while we process your answers.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Submit form
    $('#examAction').val('submit');
    
    $.post("query/submitAnswerExe.php", $("#submitAnswerFrm").serialize(), function(res) {
        if (res.res == "success") {
            Swal.fire({
                title: 'Exam Submitted!',
                text: 'Your exam has been submitted successfully.',
                icon: 'success',
                confirmButtonText: 'View Results'
            }).then(() => {
                window.location.href = `?page=result&id=${$('#exam_id').val()}`;
            });
        } else {
            Swal.fire('Error', res.msg, 'error');
        }
    }, 'json').fail(function() {
        Swal.fire('Error', 'Something went wrong. Please try again.', 'error');
    });
}

function toggleMobileSidebar() {
    $('#examSidebar').toggleClass('mobile-open');
    $('.mobile-overlay').toggleClass('active');
}

// Keyboard shortcuts
$(document).keydown(function(e) {
    if (e.ctrlKey) return; // Ignore ctrl combinations
    
    switch(e.keyCode) {
        case 37: // Left arrow
            e.preventDefault();
            previousQuestion();
            break;
        case 39: // Right arrow
            e.preventDefault();
            nextQuestion();
            break;
        case 49: case 50: case 51: case 52: // 1-4 keys
            e.preventDefault();
            let choiceIndex = e.keyCode - 49;
            let choiceInput = $(`#question_${currentQuestion} input[type="radio"]`).eq(choiceIndex);
            if (choiceInput.length) {
                choiceInput.click();
            }
            break;
    }
});

// Handle window resize
$(window).resize(function() {
    if (window.innerWidth > 768) {
        $('#examSidebar').removeClass('mobile-open');
        $('.mobile-overlay').removeClass('active');
    }
});
</script>

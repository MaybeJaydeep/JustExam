<?php include("query/countData.php"); ?>

<div class="app-main__outer">
    <div id="refreshData">
        <div class="app-main__inner">
            <div class="app-page-title">
                <div class="page-title-wrapper">
                    <div class="page-title-heading">
                        <div>Admin Dashboard</div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
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
                                    <span><?php echo $selCourse['totCourse']; ?></span>
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
                                    <span><?php echo $selExam['totExam']; ?></span>
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
                                    <span><?php echo $selStudent['totStudent']; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-xl-3">
                    <div class="card mb-3 widget-content bg-premium-dark">
                        <div class="widget-content-wrapper text-white">
                            <div class="widget-content-left">
                                <div class="widget-heading">Total Attempts</div>
                                <div class="widget-subheading">Exam attempts</div>
                            </div>
                            <div class="widget-content-right">
                                <div class="widget-numbers text-white">
                                    <span><?php echo $selAttempt['totAttempt']; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="main-card mb-3 card">
                        <div class="card-header">Quick Actions</div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-3 mb-3">
                                    <button type="button" class="btn btn-primary btn-lg btn-block" data-toggle="modal" data-target="#modalForAddCourse">
                                        <i class="fa fa-plus-circle mb-2"></i><br>
                                        Add Course
                                    </button>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <button type="button" class="btn btn-success btn-lg btn-block" data-toggle="modal" data-target="#modalForAddExam">
                                        <i class="fa fa-file-text mb-2"></i><br>
                                        Add Exam
                                    </button>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <button type="button" class="btn btn-info btn-lg btn-block" data-toggle="modal" data-target="#modalForAddExaminee">
                                        <i class="fa fa-user-plus mb-2"></i><br>
                                        Add Student
                                    </button>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="home.php?page=reports" class="btn btn-warning btn-lg btn-block">
                                        <i class="fa fa-chart-bar mb-2"></i><br>
                                        View Reports
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Management Links -->
            <div class="row">
                <div class="col-lg-4">
                    <div class="main-card mb-3 card">
                        <div class="card-header">Course Management</div>
                        <div class="card-body text-center">
                            <h3 class="text-success"><?php echo $selCourse['totCourse']; ?></h3>
                            <p>Total Courses</p>
                            <a href="home.php?page=manage-course" class="btn btn-success">Manage Courses</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="main-card mb-3 card">
                        <div class="card-header">Exam Management</div>
                        <div class="card-body text-center">
                            <h3 class="text-primary"><?php echo $selExam['totExam']; ?></h3>
                            <p>Total Exams</p>
                            <a href="home.php?page=manage-exam" class="btn btn-primary">Manage Exams</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="main-card mb-3 card">
                        <div class="card-header">Student Management</div>
                        <div class="card-body text-center">
                            <h3 class="text-info"><?php echo $selStudent['totStudent']; ?></h3>
                            <p>Total Students</p>
                            <a href="home.php?page=manage-examinee" class="btn btn-info">Manage Students</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
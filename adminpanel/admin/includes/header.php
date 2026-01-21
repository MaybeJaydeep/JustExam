<!doctype html>
<html lang="en">
<?php 
  require_once("../../config.php");
  require_once("../../security.php");
  include("query/countData.php");
 ?>


<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Language" content="en">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no" />
     
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- MAIN CSS  -->
    <link href="main.css" rel="stylesheet">
    <link href="css/sweetalert.css" rel="stylesheet">
    <link href="css/facebox.css" rel="stylesheet">
    <link href="css/mycss.css" rel="stylesheet">
    <link href="../../css/mobile-responsive.css" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Dashboard Styles -->
    <style>
        .avatar-icon-wrapper {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #3f6ad8;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        
        .toast {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-radius: 6px;
        }
        
        .toast-content {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .widget-content:hover {
            transform: translateY(-2px);
            transition: transform 0.2s ease;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .metric-value h2 {
            margin: 0;
            font-weight: bold;
        }
        
        .metric-label {
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .progress {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .progress-bar {
            transition: width 0.6s ease;
        }
        
        .card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: box-shadow 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        
        .table th {
            border-top: none;
            font-weight: 600;
            color: #495057;
        }
        
        .badge {
            font-size: 0.8rem;
            padding: 0.4em 0.8em;
        }
        
        .btn-actions-pane-right .badge {
            font-size: 0.75rem;
        }
    </style>
</head>
<body id="body">
    <div class="app-container app-theme-white body-tabs-shadow fixed-sidebar fixed-header">
        <div class="app-header header-shadow">
            <div class="app-header__logo">
                <a class="btn btn-primary" href="index.php" role="button">Home</a>
                <div class="header__pane ml-auto">
                    <div>
                        <button type="button" class="hamburger close-sidebar-btn hamburger--elastic" data-class="closed-sidebar">
                            <span class="hamburger-box">
                                <span class="hamburger-inner"></span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="app-header__mobile-menu">
                <div>
                    <button type="button" class="hamburger hamburger--elastic mobile-toggle-nav">
                        <span class="hamburger-box">
                            <span class="hamburger-inner"></span>
                        </span>
                    </button>
                </div>
            </div>
            <div class="app-header__menu">
                <span>
                    <button type="button" class="btn-icon btn-icon-only btn btn-primary btn-sm mobile-toggle-header-nav">
                        <span class="btn-icon-wrapper">
                            <i class="fa fa-ellipsis-v fa-w-6"></i>
                        </span>
                    </button>
                </span>
            </div>    <div class="app-header__content"><div style="font-family: inherit; font-size: 25px;">Just Exam</div>
                <div class="app-header-left">
                   
                          </div>
                <div class="app-header-right">
                    <div class="header-btn-lg pr-0">
                        <div class="widget-content p-0">
                            <div class="widget-content-wrapper">
                                <div class="widget-content-left">
                                    <div class="btn-group">
                                        <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="p-0 btn">
                                            Admin
                                            <i class="fa fa-angle-down ml-2 opacity-8"></i>
                                        </a>
                                        <div tabindex="-1" role="menu" aria-hidden="true" class="dropdown-menu dropdown-menu-right">
                                            <button type="button" tabindex="0" class="dropdown-item">Admin Account</button>
                                            <div tabindex="-1" class="dropdown-divider"></div>
                                            <a href="query/logoutExe.php" class="dropdown-item">LOG OUT</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>        </div>
            </div>
        </div>  
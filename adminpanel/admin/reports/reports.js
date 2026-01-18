/**
 * Advanced Reporting System JavaScript
 * Handles report generation, filtering, and UI interactions
 */

class ReportManager {
    constructor() {
        this.loadingModal = null;
        this.progressBar = null;
        this.init();
    }

    init() {
        this.loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
        this.progressBar = document.getElementById('progressBar');
        this.setupEventListeners();
        this.loadFilters();
        this.loadRecentReports();
    }

    setupEventListeners() {
        // Date range change
        document.getElementById('dateRange').addEventListener('change', (e) => {
            const customRange = document.getElementById('customDateRange');
            if (e.target.value === 'custom') {
                customRange.style.display = 'block';
            } else {
                customRange.style.display = 'none';
            }
        });

        // Course filter change
        document.getElementById('courseFilter').addEventListener('change', (e) => {
            this.loadExamsByCourse(e.target.value);
        });
    }

    async loadFilters() {
        try {
            // Load courses
            const coursesResponse = await fetch('../api/get-courses.php');
            const courses = await coursesResponse.json();
            
            const courseSelect = document.getElementById('courseFilter');
            courseSelect.innerHTML = '<option value="">All Courses</option>';
            
            courses.forEach(course => {
                const option = document.createElement('option');
                option.value = course.cou_id;
                option.textContent = course.cou_name;
                courseSelect.appendChild(option);
            });

            // Load all exams initially
            this.loadExamsByCourse('');
            
        } catch (error) {
            console.error('Error loading filters:', error);
            this.showToast('Error loading filters', 'error');
        }
    }

    async loadExamsByCourse(courseId) {
        try {
            const url = courseId ? 
                `../api/get-exams.php?course_id=${courseId}` : 
                '../api/get-exams.php';
                
            const response = await fetch(url);
            const exams = await response.json();
            
            const examSelect = document.getElementById('examFilter');
            examSelect.innerHTML = '<option value="">All Exams</option>';
            
            exams.forEach(exam => {
                const option = document.createElement('option');
                option.value = exam.ex_id;
                option.textContent = exam.ex_title;
                examSelect.appendChild(option);
            });
            
        } catch (error) {
            console.error('Error loading exams:', error);
        }
    }

    async generateReport(reportType) {
        try {
            this.showLoading();
            
            const filters = this.getFilters();
            const requestData = {
                type: reportType,
                filters: filters,
                format: document.getElementById('formatFilter').value
            };

            // Simulate progress
            this.updateProgress(20);
            
            const response = await fetch('generate-report.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(requestData)
            });

            this.updateProgress(60);

            if (!response.ok) {
                throw new Error('Report generation failed');
            }

            const result = await response.json();
            
            this.updateProgress(100);
            
            setTimeout(() => {
                this.hideLoading();
                
                if (result.success) {
                    this.downloadReport(result.file_url, result.filename);
                    this.addToRecentReports(result);
                    this.showToast('Report generated successfully!', 'success');
                } else {
                    throw new Error(result.error || 'Unknown error');
                }
            }, 500);
            
        } catch (error) {
            this.hideLoading();
            console.error('Report generation error:', error);
            this.showToast('Failed to generate report: ' + error.message, 'error');
        }
    }

    getFilters() {
        const dateRange = document.getElementById('dateRange').value;
        const filters = {
            course_id: document.getElementById('courseFilter').value,
            exam_id: document.getElementById('examFilter').value,
            date_range: dateRange
        };

        if (dateRange === 'custom') {
            filters.start_date = document.getElementById('startDate').value;
            filters.end_date = document.getElementById('endDate').value;
        }

        return filters;
    }

    showLoading() {
        this.updateProgress(0);
        this.loadingModal.show();
    }

    hideLoading() {
        this.loadingModal.hide();
    }

    updateProgress(percentage) {
        this.progressBar.style.width = percentage + '%';
        this.progressBar.setAttribute('aria-valuenow', percentage);
    }

    downloadReport(fileUrl, filename) {
        const link = document.createElement('a');
        link.href = fileUrl;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    addToRecentReports(reportData) {
        const tableBody = document.getElementById('recentReportsTable');
        
        // Remove "no reports" message if it exists
        if (tableBody.children.length === 1 && 
            tableBody.children[0].children[0].colSpan === 5) {
            tableBody.innerHTML = '';
        }

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <i class="fas fa-file-alt text-primary me-2"></i>
                ${this.getReportTypeName(reportData.type)}
            </td>
            <td>${new Date().toLocaleString()}</td>
            <td>
                <span class="badge bg-${this.getFormatBadgeColor(reportData.format)}">
                    ${reportData.format.toUpperCase()}
                </span>
            </td>
            <td>${reportData.size || 'N/A'}</td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="downloadFile('${reportData.file_url}', '${reportData.filename}')">
                    <i class="fas fa-download"></i> Download
                </button>
                <button class="btn btn-sm btn-outline-secondary ms-1" onclick="previewReport('${reportData.file_url}')">
                    <i class="fas fa-eye"></i> Preview
                </button>
            </td>
        `;
        
        // Add to top of table
        tableBody.insertBefore(row, tableBody.firstChild);
        
        // Keep only last 10 reports
        while (tableBody.children.length > 10) {
            tableBody.removeChild(tableBody.lastChild);
        }
    }

    getReportTypeName(type) {
        const names = {
            'student-performance': 'Student Performance Report',
            'exam-analytics': 'Exam Analytics Report',
            'course-summary': 'Course Summary Report',
            'time-analytics': 'Time-Based Analytics',
            'question-analysis': 'Question Bank Analysis',
            'custom': 'Custom Report'
        };
        return names[type] || 'Unknown Report';
    }

    getFormatBadgeColor(format) {
        const colors = {
            'pdf': 'danger',
            'excel': 'success',
            'csv': 'info',
            'json': 'warning'
        };
        return colors[format] || 'secondary';
    }

    async loadRecentReports() {
        try {
            const response = await fetch('get-recent-reports.php');
            const reports = await response.json();
            
            const tableBody = document.getElementById('recentReportsTable');
            
            if (reports.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No reports generated yet</td></tr>';
                return;
            }
            
            tableBody.innerHTML = '';
            reports.forEach(report => {
                this.addToRecentReports(report);
            });
            
        } catch (error) {
            console.error('Error loading recent reports:', error);
        }
    }

    showToast(message, type = 'info') {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'}"></i>
                <span>${message}</span>
                <button class="toast-close" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        // Add styles
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#17a2b8'};
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            z-index: 9999;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            min-width: 300px;
        `;
        
        document.body.appendChild(toast);
        
        // Animate in
        setTimeout(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(0)';
        }, 100);
        
        // Remove after 5 seconds
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (toast.parentElement) {
                    document.body.removeChild(toast);
                }
            }, 300);
        }, 5000);
    }

    openCustomBuilder() {
        // Open custom report builder modal
        this.showToast('Custom Report Builder coming soon!', 'info');
        // TODO: Implement custom report builder
    }
}

// Global functions
function generateReport(type) {
    if (window.reportManager) {
        window.reportManager.generateReport(type);
    }
}

function openCustomBuilder() {
    if (window.reportManager) {
        window.reportManager.openCustomBuilder();
    }
}

function refreshReports() {
    if (window.reportManager) {
        window.reportManager.loadRecentReports();
        window.reportManager.showToast('Reports refreshed', 'success');
    }
}

function downloadFile(url, filename) {
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function previewReport(url) {
    window.open(url, '_blank');
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.reportManager = new ReportManager();
});

// Add CSS for toast notifications
const style = document.createElement('style');
style.textContent = `
    .toast-content {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .toast-close {
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        margin-left: auto;
        opacity: 0.8;
    }
    
    .toast-close:hover {
        opacity: 1;
    }
`;
document.head.appendChild(style);
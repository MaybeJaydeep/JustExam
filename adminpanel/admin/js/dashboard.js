/**
 * Enhanced Dashboard JavaScript
 * Provides real-time updates and interactive features
 */

class DashboardManager {
    constructor() {
        this.charts = {};
        this.updateInterval = null;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.startAutoRefresh();
        this.initializeTooltips();
    }

    setupEventListeners() {
        // Refresh button
        const refreshBtn = document.querySelector('[onclick="refreshDashboard()"]');
        if (refreshBtn) {
            refreshBtn.onclick = () => this.refreshDashboard();
        }

        // Card hover effects
        document.querySelectorAll('.widget-content').forEach(card => {
            card.addEventListener('mouseenter', this.animateCard);
            card.addEventListener('mouseleave', this.resetCard);
        });
    }

    animateCard(e) {
        e.currentTarget.style.transform = 'translateY(-2px)';
        e.currentTarget.style.transition = 'transform 0.2s ease';
    }

    resetCard(e) {
        e.currentTarget.style.transform = 'translateY(0)';
    }

    async refreshDashboard() {
        try {
            this.showLoadingState();
            
            const response = await fetch('api/dashboard-data.php?type=all');
            const data = await response.json();
            
            if (data.success) {
                this.updateMetrics(data);
                this.updateCharts(data);
                this.showSuccessMessage('Dashboard updated successfully');
            } else {
                throw new Error(data.error || 'Failed to fetch data');
            }
        } catch (error) {
            console.error('Dashboard refresh error:', error);
            this.showErrorMessage('Failed to refresh dashboard');
        } finally {
            this.hideLoadingState();
        }
    }

    updateMetrics(data) {
        // Update basic metrics
        const metrics = {
            'courses': data.courses,
            'exams': data.exams,
            'students': data.students,
            'questions': data.questions,
            'activeStudents': data.activeStudents,
            'totalAttempts': data.totalAttempts,
            'completionRate': data.completionRate + '%',
            'avgScore': data.avgScore + '%'
        };

        Object.entries(metrics).forEach(([key, value]) => {
            const element = document.querySelector(`[data-metric="${key}"]`);
            if (element) {
                this.animateNumber(element, value);
            }
        });

        // Update performance summary
        this.updatePerformanceSummary(data);
    }

    animateNumber(element, newValue) {
        const currentValue = parseInt(element.textContent) || 0;
        const targetValue = parseInt(newValue) || 0;
        const duration = 1000;
        const steps = 30;
        const increment = (targetValue - currentValue) / steps;
        
        let current = currentValue;
        let step = 0;
        
        const timer = setInterval(() => {
            step++;
            current += increment;
            
            if (step >= steps) {
                element.textContent = newValue;
                clearInterval(timer);
            } else {
                element.textContent = Math.round(current);
            }
        }, duration / steps);
    }

    updatePerformanceSummary(data) {
        const summaryElements = {
            'maxScore': data.maxScore + '%',
            'avgScore': data.avgScore + '%',
            'minScore': data.minScore + '%',
            'completionRate': data.completionRate + '%'
        };

        Object.entries(summaryElements).forEach(([key, value]) => {
            const element = document.querySelector(`[data-summary="${key}"]`);
            if (element) {
                element.textContent = value;
            }
        });
    }

    updateCharts(data) {
        if (data.monthlyTrends && this.charts.monthlyTrends) {
            this.updateMonthlyTrendsChart(data.monthlyTrends);
        }
    }

    updateMonthlyTrendsChart(trendsData) {
        const chart = this.charts.monthlyTrends;
        
        const labels = trendsData.map(item => {
            const date = new Date(item.month + '-01');
            return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
        });
        
        const attempts = trendsData.map(item => item.attempts);
        const scores = trendsData.map(item => parseFloat(item.avg_score));
        
        chart.data.labels = labels;
        chart.data.datasets[0].data = attempts;
        chart.data.datasets[1].data = scores;
        
        chart.update('active');
    }

    startAutoRefresh() {
        // Refresh every 5 minutes
        this.updateInterval = setInterval(() => {
            this.refreshDashboard();
        }, 300000);
    }

    stopAutoRefresh() {
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
            this.updateInterval = null;
        }
    }

    showLoadingState() {
        const refreshBtn = document.querySelector('[onclick="refreshDashboard()"]');
        if (refreshBtn) {
            refreshBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Refreshing...';
            refreshBtn.disabled = true;
        }
    }

    hideLoadingState() {
        const refreshBtn = document.querySelector('[onclick="refreshDashboard()"]');
        if (refreshBtn) {
            refreshBtn.innerHTML = '<i class="fa fa-refresh"></i> Refresh Data';
            refreshBtn.disabled = false;
        }
    }

    showSuccessMessage(message) {
        this.showToast(message, 'success');
    }

    showErrorMessage(message) {
        this.showToast(message, 'error');
    }

    showToast(message, type = 'info') {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <i class="fa fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info'}"></i>
                <span>${message}</span>
            </div>
        `;
        
        // Add styles
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#17a2b8'};
            color: white;
            padding: 12px 20px;
            border-radius: 4px;
            z-index: 9999;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
        `;
        
        document.body.appendChild(toast);
        
        // Animate in
        setTimeout(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(0)';
        }, 100);
        
        // Remove after 3 seconds
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }

    initializeTooltips() {
        // Add tooltips to metric cards
        document.querySelectorAll('.widget-content').forEach(card => {
            const heading = card.querySelector('.widget-heading');
            if (heading) {
                card.title = this.getTooltipText(heading.textContent);
            }
        });
    }

    getTooltipText(metricName) {
        const tooltips = {
            'Total Courses': 'Number of courses available in the system',
            'Total Exams': 'Number of exams created across all courses',
            'Total Students': 'Number of registered students',
            'Total Questions': 'Total questions in the question bank',
            'Active Students': 'Students who have attempted at least one exam',
            'Total Attempts': 'Total number of exam attempts by all students',
            'Completion Rate': 'Percentage of started exams that were completed',
            'Average Score': 'Overall average score across all completed exams'
        };
        
        return tooltips[metricName] || 'Metric information';
    }

    // Export functionality
    exportDashboardData() {
        const data = this.collectDashboardData();
        const csv = this.convertToCSV(data);
        this.downloadCSV(csv, 'dashboard-export.csv');
    }

    collectDashboardData() {
        // Collect current dashboard data for export
        const metrics = {};
        document.querySelectorAll('[data-metric]').forEach(element => {
            const key = element.getAttribute('data-metric');
            metrics[key] = element.textContent;
        });
        return metrics;
    }

    convertToCSV(data) {
        const headers = Object.keys(data);
        const values = Object.values(data);
        return headers.join(',') + '\n' + values.join(',');
    }

    downloadCSV(csv, filename) {
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.click();
        window.URL.revokeObjectURL(url);
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.dashboardManager = new DashboardManager();
});

// Global functions for backward compatibility
function refreshDashboard() {
    if (window.dashboardManager) {
        window.dashboardManager.refreshDashboard();
    }
}

function exportDashboard() {
    if (window.dashboardManager) {
        window.dashboardManager.exportDashboardData();
    }
}
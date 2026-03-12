/**
 * Greenland Library Management System
 * Main JavaScript File
 * 
 * @author Matik Nyang (667161)
 * @version 1.0.0
 * @description Core JavaScript functionality for the library management system
 */

// ============================================================================
// GLOBAL VARIABLES
// ============================================================================

let searchTimeout;
let notificationTimeout;

// ============================================================================
// DOCUMENT READY - Initialize All Components
// ============================================================================

$(document).ready(function() {
    // Initialize all Bootstrap components
    initializeBootstrapComponents();
    
    // Initialize form handlers
    initializeFormHandlers();
    
    // Initialize search functionality
    initializeSearch();
    
    // Initialize notifications
    initializeNotifications();
    
    // Initialize data tables
    initializeDataTables();
    
    // Initialize charts (if Chart.js is loaded)
    if (typeof Chart !== 'undefined') {
        initializeCharts();
    }
    
    // Auto-hide alerts after 5 seconds
    autoHideAlerts();
    
    // Initialize custom components
    initializeCustomComponents();
    
    console.log('✅ Greenland Library System initialized successfully!');
});

// ============================================================================
// BOOTSTRAP COMPONENTS INITIALIZATION
// ============================================================================

/**
 * Initialize all Bootstrap components
 */
function initializeBootstrapComponents() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Initialize modals
    const modalElements = document.querySelectorAll('.modal');
    modalElements.forEach(modal => {
        new bootstrap.Modal(modal);
    });
    
    console.log('✅ Bootstrap components initialized');
}

// ============================================================================
// FORM HANDLERS
// ============================================================================

/**
 * Initialize form validation and handlers
 */
function initializeFormHandlers() {
    // Confirm delete actions
    $('.delete-confirm').on('click', function(e) {
        const itemName = $(this).data('item-name') || 'this item';
        if (!confirm(`Are you sure you want to delete ${itemName}? This action cannot be undone.`)) {
            e.preventDefault();
            return false;
        }
    });
    
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                showNotification('Please fill in all required fields', 'danger');
            }
            form.classList.add('was-validated');
        }, false);
    });
    
    // Prevent double form submission
    $('form').on('submit', function() {
        $(this).find('button[type="submit"]').prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-2"></span>Processing...'
        );
    });
    
    console.log('✅ Form handlers initialized');
}

// ============================================================================
// SEARCH FUNCTIONALITY
// ============================================================================

/**
 * Initialize search with debounce
 */
function initializeSearch() {
    // Real-time search with debounce
    $('.search-input').on('keyup', function() {
        clearTimeout(searchTimeout);
        const searchTerm = $(this).val();
        const searchUrl = $(this).data('search-url');
        const minLength = parseInt($(this).data('min-length')) || 3;
        
        searchTimeout = setTimeout(function() {
            if (searchTerm.length >= minLength) {
                performSearch(searchUrl, searchTerm);
            } else if (searchTerm.length === 0) {
                $('#search-results').html('');
            }
        }, 500);
    });
    
    // Print functionality
    $('.btn-print').on('click', function() {
        window.print();
    });
    
    // Export to CSV
    $('.btn-export-csv').on('click', function() {
        const table = $(this).closest('.card').find('table');
        let csv = [];
        
        // Get headers
        const headers = [];
        table.find('thead th').each(function() {
            headers.push($(this).text().trim());
        });
        csv.push(headers.join(','));
        
        // Get data
        table.find('tbody tr').each(function() {
            const row = [];
            $(this).find('td').each(function() {
                row.push('"' + $(this).text().trim().replace(/"/g, '""') + '"');
            });
            csv.push(row.join(','));
        });
        
        // Download
        const csvContent = csv.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'export_' + new Date().getTime() + '.csv';
        a.click();
        window.URL.revokeObjectURL(url);
    });
    
    // Form validation
    $('.needs-validation').on('submit', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        $(this).addClass('was-validated');
    });
    
    // ISBN formatter
    $('.isbn-input').on('input', function() {
        let value = $(this).val().replace(/[^0-9]/g, '');
        if (value.length > 13) {
            value = value.substring(0, 13);
        }
        $(this).val(value);
    });
    
    // Phone number formatter
    $('.phone-input').on('input', function() {
        let value = $(this).val().replace(/[^0-9+]/g, '');
        $(this).val(value);
    });
    
    // Auto-calculate fine
    $('#return_date, #due_date').on('change', function() {
        const dueDate = new Date($('#due_date').val());
        const returnDate = new Date($('#return_date').val());
        
        if (returnDate > dueDate) {
            const diffTime = Math.abs(returnDate - dueDate);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            const finePerDay = parseFloat($('#fine_per_day').val() || 10);
            const totalFine = diffDays * finePerDay;
            
            $('#fine_amount').val(totalFine.toFixed(2));
            $('#days_overdue').text(diffDays);
        } else {
            $('#fine_amount').val('0.00');
            $('#days_overdue').text('0');
        }
    });
    
    // Book availability check
    $('.check-availability').on('click', function() {
        const bookId = $(this).data('book-id');
        
        $.ajax({
            url: 'ajax/check_availability.php',
            method: 'GET',
            data: { book_id: bookId },
            success: function(response) {
                const data = JSON.parse(response);
                if (data.available) {
                    alert('Book is available for checkout');
                } else {
                    alert('Book is currently unavailable. Next available: ' + data.next_available);
                }
            }
        });
    });
    
    // Live search for books and users
    $('.live-search').on('keyup', function() {
        const query = $(this).val();
        const type = $(this).data('type');
        const resultsContainer = $(this).next('.search-results');
        
        if (query.length >= 2) {
            $.ajax({
                url: 'ajax/search.php',
                method: 'GET',
                data: { q: query, type: type },
                success: function(response) {
                    resultsContainer.html(response).show();
                }
            });
        } else {
            resultsContainer.hide();
        }
    });
    
    // Click outside to close search results
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.live-search').length) {
            $('.search-results').hide();
        }
    });
    
    // Smooth scroll
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        const target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 100
            }, 500);
        }
    });
    
    // Loading overlay
    function showLoading() {
        $('<div class="spinner-overlay"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>').appendTo('body');
    }
    
    function hideLoading() {
        $('.spinner-overlay').remove();
    }
    
    // AJAX form submission
    $('.ajax-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const url = form.attr('action');
        const method = form.attr('method');
        const data = form.serialize();
        
        showLoading();
        
        $.ajax({
            url: url,
            method: method,
            data: data,
            success: function(response) {
                hideLoading();
                const data = JSON.parse(response);
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            },
            error: function() {
                hideLoading();
                alert('An error occurred. Please try again.');
            }
        });
    });
});

// Utility functions
function formatDate(date) {
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${day}/${month}/${year}`;
}

function formatCurrency(amount) {
    return 'KES ' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

function printElement(elem) {
    const mywindow = window.open('', 'PRINT', 'height=600,width=800');
    mywindow.document.write('<html><head><title>Print</title>');
    mywindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">');
    mywindow.document.write('</head><body>');
    mywindow.document.write(document.getElementById(elem).innerHTML);
    mywindow.document.write('</body></html>');
    mywindow.document.close();
    mywindow.focus();
    mywindow.print();
    return true;
}
$(document).ready(function() {
    // Initialize Sri Lanka Clock
    updateSriLankaTime();
    setInterval(updateSriLankaTime, 1000);
    
    // Function to update Sri Lanka time (Asia/Colombo)
    function updateSriLankaTime() {
        const now = new Date();
        
        // Sri Lanka is UTC+5:30
        const sriLankaTime = new Date(now.getTime() + (5.5 * 60 * 60 * 1000));
        
        // Format time: HH:MM:SS AM/PM
        let hours = sriLankaTime.getUTCHours();
        const minutes = sriLankaTime.getUTCMinutes().toString().padStart(2, '0');
        const seconds = sriLankaTime.getUTCSeconds().toString().padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        
        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'
        const hoursStr = hours.toString().padStart(2, '0');
        
        const timeStr = `${hoursStr}:${minutes}:${seconds} ${ampm}`;
        $('#sl-time').text(timeStr);
        
        // Format date: Day, Month Date, Year
        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        
        const day = days[sriLankaTime.getUTCDay()];
        const date = sriLankaTime.getUTCDate();
        const month = months[sriLankaTime.getUTCMonth()];
        const year = sriLankaTime.getUTCFullYear();
        
        const dateStr = `${day}, ${month} ${date}, ${year}`;
        $('#sl-date').text(dateStr);
    }
    
    // Tab navigation with animation
    $('.nav-item').click(function() {
        $('.nav-item').removeClass('active');
        $(this).addClass('active');
        // You would add page transition logic here
    });
    
    // Unified return function
    function returnToDashboard() {
        $('#dynamic-content').empty();
        $('#stock-section').fadeIn(300);
        $('#main-tiles').fadeIn(300);
        
        // Smooth scroll to top if needed
        if ($(window).scrollTop() > 0) {
            $('html, body').animate({
                scrollTop: 0
            }, 200);
        }
        
        // Update URL to base dashboard
        history.pushState({page: "dashboard"}, "Dashboard", "index.php");
        
        return false;
    }
    
    // Handle return to dashboard from anywhere
    $(document).on('click', '.return-link', function(e) {
        e.preventDefault();
        returnToDashboard();
    });
    
    // Unified tile click handler with transitions and AJAX loading
    $('.tile').click(function() {
        const targetId = $(this).data('target');
        const targetFile = $(this).data('file');
        
        // Hide main sections
        $('#stock-section').fadeOut(200);
        $('#main-tiles').fadeOut(200);
        
        // Show loading indicator
        $('#dynamic-content').html('<div class="text-center my-5"><i class="fas fa-circle-notch fa-spin fa-3x text-primary"></i><p class="mt-2">Loading...</p></div>');
        
        // Load the content via AJAX
        $.ajax({
            url: `sections/${targetFile}.php`,
            type: 'GET',
            success: function(response) {
                // Replace content and add animation
                $('#dynamic-content').html(response).hide().fadeIn(300);
                
                // Scroll to the section
                $('html, body').animate({
                    scrollTop: $('#dynamic-content').offset().top - 15
                }, 300);
                
                // Update URL
                history.pushState({page: targetFile}, targetFile, `?section=${targetFile}`);
            },
            error: function() {
                $('#dynamic-content').html('<div class="alert alert-danger">Error loading content. Please try again.</div>');
            }
        });
    });
    
    // Show stock section by default
    $('#stock-section').show();
    
    // Handle browser back button
    $(window).on('popstate', function(e) {
        if (e.originalEvent.state === null || e.originalEvent.state.page === "dashboard") {
            returnToDashboard();
        }
    });
    
    // Check URL parameters on page load
    const urlParams = new URLSearchParams(window.location.search);
    const section = urlParams.get('section');
    if (section) {
        $(`[data-file="${section}"]`).click();
    }
    
    // Add hover effects for better touch feedback
    $('.tile').on('touchstart', function() {
        $(this).css('transform', 'scale(0.98)');
    }).on('touchend', function() {
        $(this).css('transform', '');
    });
});

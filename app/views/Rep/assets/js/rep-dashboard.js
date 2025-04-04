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
        const monthIndex = sriLankaTime.getUTCMonth(); // Get month as 0-11 index
        const year = sriLankaTime.getUTCFullYear();
        
        // Fixed: Use months array with the month index to get correct month name
        const dateStr = `${day}, ${months[monthIndex]} ${date}, ${year}`;
        $('#sl-date').text(dateStr);
    }
    
    // Tab navigation with animation
    $('.nav-item').click(function() {
        $('.nav-item').removeClass('active');
        $(this).addClass('active');
        
        // For dashboard nav item
        if ($(this).attr('id') === 'dashboard-nav') {
            returnToDashboard();
        } 
        // For profile navigation
        else if ($(this).find('i').hasClass('fa-user-circle')) {
            loadProfileSection();
        }
        // You would add page transition logic here for other nav items
    });
    
    // Dashboard navigation item click handler
    $('#dashboard-nav').click(function() {
        returnToDashboard();
        $('.nav-item').removeClass('active');
        $(this).addClass('active');
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
        
        // Also update the active nav item
        $('.nav-item').removeClass('active');
        $('#dashboard-nav').addClass('active');
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
    
    // Function to load the profile section
    function loadProfileSection() {
        // Hide main sections
        $('#stock-section').fadeOut(200);
        $('#main-tiles').fadeOut(200);
        
        // Show loading indicator
        $('#dynamic-content').html('<div class="text-center my-5"><i class="fas fa-circle-notch fa-spin fa-3x text-primary"></i><p class="mt-2">Loading profile...</p></div>');
        
        // Load the content via AJAX
        $.ajax({
            url: 'sections/profile.php',
            type: 'GET',
            success: function(response) {
                // Replace content and add animation
                $('#dynamic-content').html(response).hide().fadeIn(300);
                
                // Scroll to the section
                $('html, body').animate({
                    scrollTop: $('#dynamic-content').offset().top - 15
                }, 300);
                
                // Update URL
                history.pushState({page: 'profile'}, 'Profile', '?section=profile');
            },
            error: function() {
                $('#dynamic-content').html('<div class="alert alert-danger">Error loading profile. Please try again.</div>');
            }
        });
    }
    
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
    
    // Bottom Navigation Handlers
    $('.nav-item').click(function() {
        // Remove active class from all items
        $('.nav-item').removeClass('active');
        
        // Add active class to clicked item
        $(this).addClass('active');
        
        // Add subtle animation effect
        $(this).find('i').addClass('animate__animated animate__bounceIn');
        setTimeout(() => {
            $(this).find('i').removeClass('animate__animated animate__bounceIn');
        }, 500);
        
        // Handle different navigation items
        const navId = $(this).attr('id');
        
        // Hide all sections first
        $('#stock-section').hide();
        $('#main-tiles').hide();
        
        switch(navId) {
            case 'dashboard-nav':
                returnToDashboard();
                break;
                
            case 'stock-nav':
                loadSection('stock_management');  // Use this name consistently
                break;
                
            case 'pos-nav':
                loadSection('pos_system');
                break;
                
            case 'history-nav':
                loadSection('history');
                break;
                
            case 'profile-nav':
                loadProfileSection();
                break;
        }
    });
    
    // Function to load sections via AJAX
    function loadSection(sectionName) {
        // Show loading indicator
        $('#dynamic-content').html('<div class="text-center my-5"><i class="fas fa-circle-notch fa-spin fa-3x text-primary"></i><p class="mt-2">Loading...</p></div>');
        
        // Fix section name if needed - ensure consistent filenames
        const fixedSectionName = sectionName === 'stock_management' ? 'stock_management' : sectionName;
        
        // Log the URL being requested to help with debugging
        const url = `sections/${fixedSectionName}.php`;
        console.log("Loading section from:", url);
        
        // Load the content via AJAX
        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                // Replace content and add animation
                $('#dynamic-content').html(response).hide().fadeIn(300);
                
                // Update URL
                history.pushState({page: fixedSectionName}, fixedSectionName, `?section=${fixedSectionName}`);
            },
            error: function(xhr, status, error) {
                // Provide more detailed error information
                console.error("Error loading section:", status, error);
                console.log("Response status:", xhr.status);
                console.log("Response text:", xhr.responseText);
                
                $('#dynamic-content').html(`
                    <div class="alert alert-danger">
                        <h4 class="alert-heading">Error loading content</h4>
                        <p>Section "${fixedSectionName}" could not be loaded.</p>
                        <hr>
                        <p class="mb-0">Error details: ${status} - ${error}</p>
                    </div>
                `);
            }
        });
    }
    
    // Add touch ripple effect to nav items
    $('.nav-item').on('touchstart', function() {
        $(this).css('opacity', '0.7');
    }).on('touchend touchcancel', function() {
        $(this).css('opacity', '1');
    });
});

$(document).ready(function() {
    // Hide all sections except dashboard initially
    $('.section-card:not(#dashboard-section)').hide();
    
    // Menu card click handlers
    $('#pos-card').click(function() {
        $('.section-card').hide();
        $('#pos-section').show();
    });
    
    $('#stock-overview-card').click(function() {
        $('.section-card').hide();
        $('#stock-overview-section').show();
        
        // Reload stock data when opening section
        if (typeof loadStockOverview === 'function') {
            loadStockOverview();
        }
    });
    
    $('#add-to-lorry-card').click(function() {
        $('.section-card').hide();
        $('#add-section').show();
        
        // Set focus on barcode input for easy scanning
        setTimeout(function() {
            $('#barcode-input').focus();
        }, 100);
    });
    
    $('#retrieve-stock-card').click(function() {
        $('.section-card').hide();
        $('#retrieve-stock-section').show();
    });
    
    $('#customers-card').click(function() {
        $('.section-card').hide();
        $('#customer-section').show();
    });
    
    $('#history-card').click(function() {
        $('.section-card').hide();
        $('#history-section').show();
    });
    
    $('#stock-management-card').click(function() {
        $('.section-card').hide();
        $('#stock-management-section').show();
    });
    
    $('#return-collection-card').click(function() {
        $('.section-card').hide();
        $('#return-collection-section').show();
    });
    
    $('#return-history-card').click(function() {
        $('.section-card').hide();
        $('#return-history-section').show();
    });
    
    $('#advance-payment-card').click(function() {
        $('.section-card').hide();
        $('#advance-payment-section').show();
    });
    
    // New handler for Tile Report card
    $('#tile-report-card').click(function() {
        $('.section-card').hide();
        $('#tile-report-section').show();
    });
    
    // Profile link handler
    $('#profile-link').click(function(e) {
        e.preventDefault();
        $('.section-card').hide();
        $('#profile-section').show();
    });
    
    // Return link handlers
    $('.return-link').click(function(e) {
        e.preventDefault();
        $('.section-card').hide();
        $('#dashboard-section').show();
    });
    
    // POS add product button
    $('#add-product-btn').click(function() {
        $('#add-product-modal').modal('show');
    });
    
    // Form submission with validation
    $('form').submit(function() {
        var valid = true;
        $(this).find('[required]').each(function() {
            if ($(this).val() === '') {
                valid = false;
                // Highlight the field
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        return valid;
    });
    
    // Remove invalid class on input
    $('input, select, textarea').on('input', function() {
        if ($(this).val() !== '') {
            $(this).removeClass('is-invalid');
        }
    });
});

jQuery(document).ready(function($) {
    // Create and append the survey modal
    var surveyHTML = `
        <div id="cf7pp-deactivation-survey" style="display: none;">
            <div class="cf7pp-survey-content">
                <h2>${cf7ppDeactivationSurvey.strings.title}</h2>
                <p>${cf7ppDeactivationSurvey.strings.description}</p>
                <form id="cf7pp-deactivation-form">
                    <div class="cf7pp-survey-options">
                        ${Object.entries(cf7ppDeactivationSurvey.deactivationOptions).map(([key, value]) => `
                            <label>
                                <input type="radio" name="deactivation_reason" value="${key}">
                                ${value}
                            </label>
                            ${key === 'found_better' ? `<div class="cf7pp-additional-field" data-for="found_better" style="display: none;">
                                <textarea name="user-reason" class="" rows="6" style="border-spacing: 0; width: 100%; clear: both; margin: 0;" placeholder="${cf7ppDeactivationSurvey.strings.betterPluginQuestion}"></textarea>
                            </div>` : ''}
                            ${key === 'not_working' ? `<div class="cf7pp-additional-field" data-for="not_working" style="display: none;">
                                <textarea name="user-reason" class="" rows="6" style="border-spacing: 0; width: 100%; clear: both; margin: 0;" placeholder="${cf7ppDeactivationSurvey.strings.notWorkingQuestion}"></textarea>
                            </div>` : ''}
                        `).join('')}
                    </div>
                    <div id="cf7pp-other-reason" style="display: none;">
                        <textarea name="user-reason" class="" rows="6" style="border-spacing: 0; width: 100%; clear: both; margin: 0;" placeholder="${cf7ppDeactivationSurvey.strings.otherPlaceholder}"></textarea>
                    </div>
                    <div id="cf7pp-error-notice" class="notice notice-error" style="display: none; margin: 10px 0;">
                        <p>${cf7ppDeactivationSurvey.strings.errorRequired}</p>
                    </div>
                    <div class="cf7pp-survey-buttons" style="display: flex; justify-content: space-between; margin-top: 20px;">
                        <div>
                            <button type="button" class="button button-secondary" id="cf7pp-skip-survey">${cf7ppDeactivationSurvey.strings.skipButton}</button>
                        </div>
                        <div>
                            <button type="button" class="button button-secondary" id="cf7pp-cancel-survey">${cf7ppDeactivationSurvey.strings.cancelButton}</button>
                            <button type="submit" class="button button-primary">${cf7ppDeactivationSurvey.strings.submitButton}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    `;

    $('body').append(surveyHTML);

    // Show survey when deactivation link is clicked
    $(document).on('click', 'a[href*="action=deactivate&plugin=contact-form-7-paypal-add-on"]', function(e) {
        e.preventDefault();
        $('#cf7pp-deactivation-survey').show();
    });

    // Handle escape key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#cf7pp-deactivation-survey').is(':visible')) {
            $('#cf7pp-deactivation-survey').hide();
        }
    });

    // Handle cancel button
    $('#cf7pp-cancel-survey').on('click', function() {
        $('#cf7pp-deactivation-survey').hide();
    });

    // Handle radio button changes
    $('input[name="deactivation_reason"]').on('change', function() {
        var selectedValue = $(this).val();
        
        // Hide all additional fields first
        $('.cf7pp-additional-field').hide();
        $('#cf7pp-other-reason').hide();
        $('#cf7pp-error-notice').hide();
        
        // Remove error styling from all textareas
        $('textarea[name="user-reason"]').css('border-color', '');
        
        // Show relevant field based on selection
        if (selectedValue === 'other') {
            $('#cf7pp-other-reason').show();
        } else if (selectedValue === 'found_better' || selectedValue === 'not_working') {
            $(`.cf7pp-additional-field[data-for="${selectedValue}"]`).show();
        }
    });

    // Handle textarea input to remove error styling
    $('textarea[name="user-reason"]').on('input', function() {
        $(this).css('border-color', '');
        $('#cf7pp-error-notice').hide();
    });

    // Handle skip button
    $('#cf7pp-skip-survey').on('click', function() {
        window.location.href = $('a[href*="action=deactivate&plugin=contact-form-7-paypal-add-on"]').attr('href');
    });

    // Handle form submission
    $('#cf7pp-deactivation-form').on('submit', function(e) {
        e.preventDefault();
        
        var reason = $('input[name="deactivation_reason"]:checked').val();
        var additionalReason = '';
        var $textarea = null;
        
        // Get the appropriate additional reason based on the selected option
        if (reason === 'other') {
            $textarea = $('#cf7pp-other-reason textarea');
            additionalReason = $textarea.val();
        } else if (reason === 'found_better') {
            $textarea = $('.cf7pp-additional-field[data-for="found_better"] textarea');
            additionalReason = $textarea.val();
        } else if (reason === 'not_working') {
            $textarea = $('.cf7pp-additional-field[data-for="not_working"] textarea');
            additionalReason = $textarea.val();
        }
        
        // Hide any existing error notice
        $('#cf7pp-error-notice').hide();
        
        // Remove error styling from all textareas
        $('textarea[name="user-reason"]').css('border-color', '');
        
        // Validate required fields
        if ((reason === 'other' || reason === 'found_better' || reason === 'not_working') && !additionalReason) {
            $('#cf7pp-error-notice').show();
            if ($textarea) {
                $textarea.css('border-color', '#dc3232');
            }
            return;
        }
        
        $.ajax({
            url: 'https://wpplugin.org/wp-json/wpplugin/v1/deactivation-survey',
            method: 'POST',
            data: {
                plugin_slug: 'contact-form-7-paypal-add-on',
                plugin_version: cf7ppDeactivationSurvey.pluginVersion,
                reason: reason,
                additional_reason: additionalReason
            },
            success: function() {
                window.location.href = $('a[href*="action=deactivate&plugin=contact-form-7-paypal-add-on"]').attr('href');
            },
            error: function() {
                window.location.href = $('a[href*="action=deactivate&plugin=contact-form-7-paypal-add-on"]').attr('href');
            }
        });
    });
}); 
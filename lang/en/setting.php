<?php

return [
    'title' => 'Settings',
    'subtitle' => 'Manage clinic settings',

    // Sections
    'clinic_profile' => 'Clinic Profile',
    'clinic_profile_desc' => 'Name, address, logo, and clinic information',
    'operating_hours' => 'Operating Hours',
    'operating_hours_desc' => 'Set clinic opening and closing times',

    // Business type
    'business_type' => 'Business Type',
    'business_type_hint' => 'This determines the theme colors and terminology used throughout the app',
    'business_name' => 'Business Name',
    'business_address' => 'Address',
    'business_phone' => 'Phone',
    'business_email' => 'Email',

    // Clinic form (legacy)
    'clinic_name' => 'Clinic Name',
    'clinic_address' => 'Address',
    'clinic_phone' => 'Phone',
    'clinic_email' => 'Email',
    'clinic_logo' => 'Logo',
    'clinic_logo_help' => 'Format: JPG, PNG, WebP. Max 2MB.',
    'current_logo' => 'Current Logo',
    'change_logo' => 'Change Logo',
    'remove_logo' => 'Remove Logo',
    'clinic_description' => 'Description',
    'clinic_description_placeholder' => 'Brief description about the clinic...',

    // Invoice settings
    'invoice_settings' => 'Invoice Settings',
    'invoice_prefix' => 'Invoice Prefix',
    'invoice_prefix_help' => 'Example: INV, NOTA, etc.',
    'tax_percentage' => 'Tax Percentage (%)',
    'tax_percentage_help' => 'Enter 0 if no tax.',

    // Appointment settings
    'appointment_settings' => 'Appointment Settings',
    'slot_duration' => 'Slot Duration (minutes)',
    'slot_duration_help' => 'Duration of each appointment time slot.',

    // Hours form
    'day' => 'Day',
    'open_time' => 'Open Time',
    'close_time' => 'Close Time',
    'is_closed' => 'Closed',
    'days' => [
        'monday' => 'Monday',
        'tuesday' => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday' => 'Thursday',
        'friday' => 'Friday',
        'saturday' => 'Saturday',
        'sunday' => 'Sunday',
    ],

    // Actions
    'save_settings' => 'Save Settings',
    'save_hours' => 'Save Operating Hours',
    'clinic_closed' => 'Clinic closed',

    // Messages
    'updated' => 'Settings successfully updated.',
    'logo_removed' => 'Logo successfully removed.',
    'branding_updated' => 'Branding settings successfully updated.',

    // Branding
    'branding' => 'Branding & White-label',
    'branding_desc' => 'Customize logo, app name, and branding',
    'app_identity' => 'App Identity',
    'app_name' => 'App Name',
    'app_name_hint' => 'Leave empty to use default',
    'tagline_en' => 'Tagline (English)',
    'tagline_id' => 'Tagline (Indonesian)',
    'description_en' => 'Description (English)',
    'description_id' => 'Description (Indonesian)',
    'logo_settings' => 'Logo Settings',
    'main_logo' => 'Main Logo',
    'favicon' => 'Favicon',
    'logo_hint' => 'Recommended: PNG/SVG, max 2MB',
    'favicon_hint' => 'Recommended: 32x32 or 64x64 pixels',
    'show_app_name_next_to_logo' => 'Show app name next to logo',
    'contact_info' => 'Contact Information',
    'social_media' => 'Social Media',
    'footer_settings' => 'Footer Settings',
    'copyright_en' => 'Copyright Text (English)',
    'copyright_id' => 'Copyright Text (Indonesian)',
    'copyright_hint' => 'Use :year for year, :app_name for app name',
    'show_powered_by' => 'Show "Powered by" text',
    'powered_by_text' => '"Powered by" Text',
    'powered_by_url' => '"Powered by" URL',
    'custom_scripts' => 'Custom Scripts & CSS',
    'custom_scripts_desc' => 'Add custom tracking scripts, chat widgets, or CSS styles',
    'head_scripts' => 'Head Scripts (before </head>)',
    'body_scripts' => 'Body Scripts (before </body>)',
    'custom_css' => 'Custom CSS',
];

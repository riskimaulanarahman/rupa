<?php

return [
    'detail_label' => 'Message details',
    'details' => [
        'csrf_token_mismatch' => 'The form security session expired. Please refresh the page and submit again.',
        'page_expired' => 'This page has expired. Please refresh and try again.',
        'method_not_allowed' => 'This action is not supported for this page or link.',
        'too_many_requests' => 'Too many requests in a short time. Please wait and try again.',
        'unauthenticated' => 'Your login session has ended. Please sign in again.',
        'unauthorized' => 'You do not have permission to perform this action.',
        'not_found' => 'The requested page or data could not be found.',
        'generic' => 'We could not process your request right now. Please try again.',
    ],
    'actions' => [
        'go_home' => 'Back to Home',
        'go_dashboard' => 'Back to Dashboard',
        'go_appointments' => 'Go to Appointments',
        'go_portal' => 'Back to My Portal',
        'back' => 'Go Back',
    ],
    'status' => [
        '403' => [
            'title' => 'Access Forbidden',
            'description' => 'You do not have permission to access this page.',
        ],
        '404' => [
            'title' => 'Page Not Found',
            'description' => 'The page you are looking for may have been moved or is no longer available.',
        ],
        '419' => [
            'title' => 'Page Expired',
            'description' => 'Your session has expired. Please refresh the page and try again.',
        ],
        '429' => [
            'title' => 'Too Many Requests',
            'description' => 'You made too many requests in a short time. Please wait and try again.',
        ],
        '500' => [
            'title' => 'Internal Server Error',
            'description' => 'Something went wrong on our side. Please try again in a moment.',
        ],
        '503' => [
            'title' => 'Service Unavailable',
            'description' => 'The service is temporarily unavailable or under maintenance. Please try again later.',
        ],
        '4xx' => [
            'title' => 'Request Could Not Be Processed',
            'description' => 'There was an issue with your request. Please review it and try again.',
        ],
        '5xx' => [
            'title' => 'Server Error',
            'description' => 'A server-side error occurred. Please try again in a moment.',
        ],
    ],
];

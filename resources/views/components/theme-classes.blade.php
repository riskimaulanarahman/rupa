@php
/**
 * Theme Classes Partial
 * Include this at the top of blade files to get consistent theme classes
 * Usage: @include('components.theme-classes')
 */

$themeColors = [
    'clinic' => [
        'button' => 'bg-rose-500 hover:bg-rose-600',
        'button_outline' => 'border-rose-500 text-rose-500 hover:bg-rose-50',
        'link' => 'text-rose-500 hover:text-rose-600',
        'link_dark' => 'text-rose-600 hover:text-rose-700',
        'badge_bg' => 'bg-rose-100',
        'badge_text' => 'text-rose-600',
        'badge' => 'bg-rose-100 text-rose-600',
        'accent' => 'text-rose-500',
        'accent_bg' => 'bg-rose-500',
        'ring' => 'focus:ring-rose-500/20 focus:border-rose-400',
        'gradient' => 'from-rose-400 to-rose-500',
    ],
    'salon' => [
        'button' => 'bg-purple-500 hover:bg-purple-600',
        'button_outline' => 'border-purple-500 text-purple-500 hover:bg-purple-50',
        'link' => 'text-purple-500 hover:text-purple-600',
        'link_dark' => 'text-purple-600 hover:text-purple-700',
        'badge_bg' => 'bg-purple-100',
        'badge_text' => 'text-purple-600',
        'badge' => 'bg-purple-100 text-purple-600',
        'accent' => 'text-purple-500',
        'accent_bg' => 'bg-purple-500',
        'ring' => 'focus:ring-purple-500/20 focus:border-purple-400',
        'gradient' => 'from-purple-400 to-purple-500',
    ],
    'barbershop' => [
        'button' => 'bg-blue-500 hover:bg-blue-600',
        'button_outline' => 'border-blue-500 text-blue-500 hover:bg-blue-50',
        'link' => 'text-blue-500 hover:text-blue-600',
        'link_dark' => 'text-blue-600 hover:text-blue-700',
        'badge_bg' => 'bg-blue-100',
        'badge_text' => 'text-blue-600',
        'badge' => 'bg-blue-100 text-blue-600',
        'accent' => 'text-blue-500',
        'accent_bg' => 'bg-blue-500',
        'ring' => 'focus:ring-blue-500/20 focus:border-blue-400',
        'gradient' => 'from-blue-400 to-blue-500',
    ],
];

$currentTheme = $themeColors[$businessType ?? business_type() ?? 'clinic'] ?? $themeColors['clinic'];

// Extract to individual variables for easy use
$themeButton = $currentTheme['button'];
$themeButtonOutline = $currentTheme['button_outline'];
$themeLink = $currentTheme['link'];
$themeLinkDark = $currentTheme['link_dark'];
$themeBadgeBg = $currentTheme['badge_bg'];
$themeBadgeText = $currentTheme['badge_text'];
$themeBadge = $currentTheme['badge'];
$themeAccent = $currentTheme['accent'];
$themeAccentBg = $currentTheme['accent_bg'];
$themeRing = $currentTheme['ring'];
$themeGradient = $currentTheme['gradient'];
@endphp

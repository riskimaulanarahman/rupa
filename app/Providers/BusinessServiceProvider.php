<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class BusinessServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share business-related variables with all views
        View::composer('*', function ($view) {
            $businessType = business_type() ?? 'clinic';

            // Staff label mapping based on business type
            $staffLabels = [
                'clinic' => [
                    'singular' => __('appointment.beautician'),
                    'plural' => __('appointment.all_beauticians'),
                ],
                'salon' => [
                    'singular' => __('appointment.hairstylist'),
                    'plural' => __('appointment.all_hairstylists'),
                ],
                'barbershop' => [
                    'singular' => __('appointment.barber'),
                    'plural' => __('appointment.all_barbers'),
                ],
            ];

            $currentLabels = $staffLabels[$businessType] ?? $staffLabels['clinic'];

            // Get theme configuration
            $theme = business_theme();

            // Get business name
            $businessName = business_label('name');

            // Theme CSS classes for views
            $themeClasses = [
                'clinic' => [
                    'primary' => 'rose',
                    'button' => 'bg-rose-500 hover:bg-rose-600',
                    'buttonOutline' => 'border-rose-500 text-rose-500 hover:bg-rose-50',
                    'link' => 'text-rose-500 hover:text-rose-600',
                    'link_hover' => 'hover:text-rose-600',
                    'linkDark' => 'text-rose-600 hover:text-rose-700',
                    'badgeBg' => 'bg-rose-100',
                    'badgeText' => 'text-rose-600',
                    'badge' => 'bg-rose-100 text-rose-600',
                    'accent' => 'text-rose-500',
                    'accentBg' => 'bg-rose-500',
                    'ring' => 'focus:ring-rose-500/20 focus:border-rose-400',
                    'gradient' => 'from-rose-400 to-rose-500',
                    'checkbox' => 'text-rose-500',
                ],
                'salon' => [
                    'primary' => 'purple',
                    'button' => 'bg-purple-500 hover:bg-purple-600',
                    'buttonOutline' => 'border-purple-500 text-purple-500 hover:bg-purple-50',
                    'link' => 'text-purple-500 hover:text-purple-600',
                    'link_hover' => 'hover:text-purple-600',
                    'linkDark' => 'text-purple-600 hover:text-purple-700',
                    'badgeBg' => 'bg-purple-100',
                    'badgeText' => 'text-purple-600',
                    'badge' => 'bg-purple-100 text-purple-600',
                    'accent' => 'text-purple-500',
                    'accentBg' => 'bg-purple-500',
                    'ring' => 'focus:ring-purple-500/20 focus:border-purple-400',
                    'gradient' => 'from-purple-400 to-purple-500',
                    'checkbox' => 'text-purple-500',
                ],
                'barbershop' => [
                    'primary' => 'blue',
                    'button' => 'bg-blue-500 hover:bg-blue-600',
                    'buttonOutline' => 'border-blue-500 text-blue-500 hover:bg-blue-50',
                    'link' => 'text-blue-500 hover:text-blue-600',
                    'link_hover' => 'hover:text-blue-600',
                    'linkDark' => 'text-blue-600 hover:text-blue-700',
                    'badgeBg' => 'bg-blue-100',
                    'badgeText' => 'text-blue-600',
                    'badge' => 'bg-blue-100 text-blue-600',
                    'accent' => 'text-blue-500',
                    'accentBg' => 'bg-blue-500',
                    'ring' => 'focus:ring-blue-500/20 focus:border-blue-400',
                    'gradient' => 'from-blue-400 to-blue-500',
                    'checkbox' => 'text-blue-500',
                ],
            ];

            $tc = $themeClasses[$businessType] ?? $themeClasses['clinic'];

            $view->with([
                'businessType' => $businessType,
                'businessName' => $businessName,
                'staffLabel' => $currentLabels['singular'],
                'allStaffLabel' => $currentLabels['plural'],
                'theme' => $theme,
                // Theme classes shortcuts
                'tc' => (object) $tc,
                // Legacy theme variables for backward compatibility
                'themeButton' => $tc['button'],
                'themeButtonOutline' => $tc['buttonOutline'],
                'themeLink' => $tc['link'],
                'themeLinkDark' => $tc['linkDark'],
                'themeBadgeBg' => $tc['badgeBg'],
                'themeBadgeText' => $tc['badgeText'],
                'themeBadge' => $tc['badge'],
                'themeAccent' => $tc['accent'],
                'themeAccentBg' => $tc['accentBg'],
                'themeRing' => $tc['ring'],
                'themeGradient' => $tc['gradient'],
                'themeCheckbox' => $tc['checkbox'],
                'themePrimary' => $tc['primary'],
            ]);
        });
    }
}

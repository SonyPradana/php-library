<?php

declare(strict_types=1);

namespace System\Console\Style;

/**
 * Provide color variant (inspire by tailwind color).
 *
 * @see https://gist.github.com/davidpiesse/74f5eaa23eb405e61b58cfe535d9907c
 *
 * Part of color variant from tailwindcss color.
 * @see https://github.dev/tailwindlabs/tailwindcss/blob/master/src/public/colors.js
 */
class ColorVariant
{
    public const RED             = '#f44336';
    public const RED_50          = '#fef2f2';
    public const RED_100         = '#fee2e2';
    public const RED_200         = '#fecaca';
    public const RED_300         = '#fca5a5';
    public const RED_400         = '#f87171';
    public const RED_500         = '#ef4444';
    public const RED_600         = '#dc2626';
    public const RED_700         = '#b91c1c';
    public const RED_800         = '#991b1b';
    public const RED_900         = '#7f1d1d';
    public const RED_950         = '#450a0a';
    public const PINK            = '#e91e63';
    public const PINK_50         = '#fdf2f8';
    public const PINK_100        = '#fce7f3';
    public const PINK_200        = '#fbcfe8';
    public const PINK_300        = '#f9a8d4';
    public const PINK_400        = '#f472b6';
    public const PINK_500        = '#ec4899';
    public const PINK_600        = '#db2777';
    public const PINK_700        = '#be185d';
    public const PINK_800        = '#9d174d';
    public const PINK_900        = '#831843';
    public const PINK_950        = '#831843';
    public const PURPLE          = '#9c27b0';
    public const PURPLE_50       = '#faf5ff';
    public const PURPLE_100      = '#f3e8ff';
    public const PURPLE_200      = '#e9d5ff';
    public const PURPLE_300      = '#d8b4fe';
    public const PURPLE_400      = '#c084fc';
    public const PURPLE_500      = '#a855f7';
    public const PURPLE_600      = '#9333ea';
    public const PURPLE_700      = '#7e22ce';
    public const PURPLE_800      = '#6b21a8';
    public const PURPLE_900      = '#581c87';
    public const PURPLE_950      = '#4E1877';
    public const DEEP_PURPLE     = '#673ab7';
    public const DEEP_PURPLE_50  = '#ede7f6';
    public const DEEP_PURPLE_100 = '#d1c4e9';
    public const DEEP_PURPLE_200 = '#b39ddb';
    public const DEEP_PURPLE_300 = '#9575cd';
    public const DEEP_PURPLE_400 = '#7e57c2';
    public const DEEP_PURPLE_500 = '#673ab7';
    public const DEEP_PURPLE_600 = '#5e35b1';
    public const DEEP_PURPLE_700 = '#512da8';
    public const DEEP_PURPLE_800 = '#4527a0';
    public const DEEP_PURPLE_900 = '#311b92';
    public const INDIGO          = '#3f51b5';
    public const INDIGO_50       = '#eef2ff';
    public const INDIGO_100      = '#e0e7ff';
    public const INDIGO_200      = '#c7d2fe';
    public const INDIGO_300      = '#a5b4fc';
    public const INDIGO_400      = '#818cf8';
    public const INDIGO_500      = '#6366f1';
    public const INDIGO_600      = '#4f46e5';
    public const INDIGO_700      = '#4338ca';
    public const INDIGO_800      = '#3730a3';
    public const INDIGO_900      = '#312e81';
    public const INDIGO_950      = '#1e1b4b';
    public const BLUE            = '#2196f3';
    public const BLUE_50         = '#eff6ff';
    public const BLUE_100        = '#dbeafe';
    public const BLUE_200        = '#bfdbfe';
    public const BLUE_300        = '#93c5fd';
    public const BLUE_400        = '#60a5fa';
    public const BLUE_500        = '#3b82f6';
    public const BLUE_600        = '#2563eb';
    public const BLUE_700        = '#1d4ed8';
    public const BLUE_800        = '#1e40af';
    public const BLUE_900        = '#1e3a8a';
    public const BLUE_950        = '#172554';
    public const LIGHT_BLUE      = '#03a9f4';
    public const LIGHT_BLUE_50   = '#f0f9ff';
    public const LIGHT_BLUE_100  = '#e0f2fe';
    public const LIGHT_BLUE_200  = '#bae6fd';
    public const LIGHT_BLUE_300  = '#7dd3fc';
    public const LIGHT_BLUE_400  = '#38bdf8';
    public const LIGHT_BLUE_500  = '#0ea5e9';
    public const LIGHT_BLUE_600  = '#0284c7';
    public const LIGHT_BLUE_700  = '#0369a1';
    public const LIGHT_BLUE_800  = '#075985';
    public const LIGHT_BLUE_900  = '#0c4a6e';
    public const LIGHT_BLUE_950  = '#082f49';
    public const CYAN            = '#00bcd4';
    public const CYAN_50         = '#ecfeff';
    public const CYAN_100        = '#cffafe';
    public const CYAN_200        = '#a5f3fc';
    public const CYAN_300        = '#67e8f9';
    public const CYAN_400        = '#22d3ee';
    public const CYAN_500        = '#06b6d4';
    public const CYAN_600        = '#0891b2';
    public const CYAN_700        = '#0e7490';
    public const CYAN_800        = '#155e75';
    public const CYAN_900        = '#164e63';
    public const CYAN_950        = '#083344';
    public const TEAL            = '#009688';
    public const TEAL_50         = '#f0fdfa';
    public const TEAL_100        = '#ccfbf1';
    public const TEAL_200        = '#99f6e4';
    public const TEAL_300        = '#5eead4';
    public const TEAL_400        = '#2dd4bf';
    public const TEAL_500        = '#14b8a6';
    public const TEAL_600        = '#0d9488';
    public const TEAL_700        = '#0f766e';
    public const TEAL_800        = '#115e59';
    public const TEAL_900        = '#134e4a';
    public const TEAL_950        = '#042f2e';
    public const GREEN           = '#4caf50';
    public const GREEN_50        = '#f0fdf4';
    public const GREEN_100       = '#dcfce7';
    public const GREEN_200       = '#bbf7d0';
    public const GREEN_300       = '#86efac';
    public const GREEN_400       = '#4ade80';
    public const GREEN_500       = '#22c55e';
    public const GREEN_600       = '#16a34a';
    public const GREEN_700       = '#15803d';
    public const GREEN_800       = '#166534';
    public const GREEN_900       = '#14532d';
    public const GREEN_950       = '#052e16';
    public const LIGHT_GREEN     = '#8bc34a';
    public const LIGHT_GREEN_50  = '#f1f8e9';
    public const LIGHT_GREEN_100 = '#dcedc8';
    public const LIGHT_GREEN_200 = '#c5e1a5';
    public const LIGHT_GREEN_300 = '#aed581';
    public const LIGHT_GREEN_400 = '#9ccc65';
    public const LIGHT_GREEN_500 = '#8bc34a';
    public const LIGHT_GREEN_600 = '#7cb342';
    public const LIGHT_GREEN_700 = '#689f38';
    public const LIGHT_GREEN_800 = '#558b2f';
    public const LIGHT_GREEN_900 = '#33691e';
    public const LIME            = '#cddc39';
    public const LIME_50         = 'f7fee7';
    public const LIME_100        = '#ecfccb';
    public const LIME_200        = '#d9f99d';
    public const LIME_300        = '#bef264';
    public const LIME_400        = '#a3e635';
    public const LIME_500        = '#84cc16';
    public const LIME_600        = '#65a30d';
    public const LIME_700        = '#4d7c0f';
    public const LIME_800        = '#3f6212';
    public const LIME_900        = '#365314';
    public const LIME_950        = '#1a2e05';
    public const YELLOW          = '#ffeb3b';
    public const YELLOW_50       = '#fefce8';
    public const YELLOW_100      = '#fef9c3';
    public const YELLOW_200      = '#fef08a';
    public const YELLOW_300      = '#fde047';
    public const YELLOW_400      = '#facc15';
    public const YELLOW_500      = '#eab308';
    public const YELLOW_600      = '#ca8a04';
    public const YELLOW_700      = '#a16207';
    public const YELLOW_800      = '#854d0e';
    public const YELLOW_900      = '#713f12';
    public const YELLOW_950      = '#422006';
    public const AMBER           = '#fffbeb';
    public const AMBER_50        = '#fef3c7';
    public const AMBER_100       = '#fde68a';
    public const AMBER_200       = '#fcd34d';
    public const AMBER_300       = '#fbbf24';
    public const AMBER_400       = '#f59e0b';
    public const AMBER_500       = '#d97706';
    public const AMBER_600       = '#b45309';
    public const AMBER_700       = '#92400e';
    public const AMBER_800       = '#78350f';
    public const AMBER_900       = '#451a03';
    public const ORANGE          = '#ff9800';
    public const ORANGE_50       = '#fff7ed';
    public const ORANGE_100      = '#ffedd5';
    public const ORANGE_200      = '#fed7aa';
    public const ORANGE_300      = '#fdba74';
    public const ORANGE_400      = '#fb923c';
    public const ORANGE_500      = '#f97316';
    public const ORANGE_600      = '#ea580c';
    public const ORANGE_700      = '#c2410c';
    public const ORANGE_800      = '#9a3412';
    public const ORANGE_900      = '#7c2d12';
    public const ORANGE_950      = '#431407';
    public const DEEP_ORANGE     = '#ff5722';
    public const DEEP_ORANGE_50  = '#fbe9e7';
    public const DEEP_ORANGE_100 = '#ffccbc';
    public const DEEP_ORANGE_200 = '#ffab91';
    public const DEEP_ORANGE_300 = '#ff8a65';
    public const DEEP_ORANGE_400 = '#ff7043';
    public const DEEP_ORANGE_500 = '#ff5722';
    public const DEEP_ORANGE_600 = '#f4511e';
    public const DEEP_ORANGE_700 = '#e64a19';
    public const DEEP_ORANGE_800 = '#d84315';
    public const DEEP_ORANGE_900 = '#bf360c';
    public const BROWN           = '#795548';
    public const BROWN_50        = '#efebe9';
    public const BROWN_100       = '#d7ccc8';
    public const BROWN_200       = '#bcaaa4';
    public const BROWN_300       = '#a1887f';
    public const BROWN_400       = '#8d6e63';
    public const BROWN_500       = '#795548';
    public const BROWN_600       = '#6d4c41';
    public const BROWN_700       = '#5d4037';
    public const BROWN_800       = '#4e342e';
    public const BROWN_900       = '#3e2723';
    public const GREY            = '#9e9e9e';
    public const GREY_50         = '#f9fafb';
    public const GREY_100        = '#f3f4f6';
    public const GREY_200        = '#e5e7eb';
    public const GREY_300        = '#d1d5db';
    public const GREY_400        = '#9ca3af';
    public const GREY_500        = '#6b7280';
    public const GREY_600        = '#4b5563';
    public const GREY_700        = '#374151';
    public const GREY_800        = '#1f2937';
    public const GREY_900        = '#111827';
    public const GREY_950        = '#030712';
    public const BLUE_GREY       = '#607d8b';
    public const BLUE_GREY_50    = '#f8fafc';
    public const BLUE_GREY_100   = '#f1f5f9';
    public const BLUE_GREY_200   = '#e2e8f0';
    public const BLUE_GREY_300   = '#cbd5e1';
    public const BLUE_GREY_400   = '#94a3b8';
    public const BLUE_GREY_500   = '#64748b';
    public const BLUE_GREY_600   = '#475569';
    public const BLUE_GREY_700   = '#334155';
    public const BLUE_GREY_800   = '#1e293b';
    public const BLUE_GREY_900   = '#0f172a';
    public const BLUE_GREY_950   = '#020617';

    /**
     * Get contant from string.
     *
     * @param string $name
     *
     * @return string Hex code
     */
    public static function getConst($name)
    {
        return constant("self::{$name}");
    }
}

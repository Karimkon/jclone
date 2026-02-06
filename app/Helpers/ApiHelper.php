<?php

namespace App\Helpers;

use Carbon\Carbon;

class ApiHelper
{
    /**
     * Format a timestamp to ISO 8601 string
     *
     * @param mixed $timestamp Carbon instance, string, or null
     * @return string|null
     */
    public static function formatTimestamp($timestamp): ?string
    {
        if ($timestamp === null) {
            return null;
        }

        if ($timestamp instanceof Carbon) {
            return $timestamp->toIso8601String();
        }

        if (is_string($timestamp)) {
            try {
                return Carbon::parse($timestamp)->toIso8601String();
            } catch (\Exception $e) {
                return $timestamp;
            }
        }

        return null;
    }

    /**
     * Format a timestamp for human readable display
     *
     * @param mixed $timestamp
     * @return string|null
     */
    public static function formatHumanReadable($timestamp): ?string
    {
        if ($timestamp === null) {
            return null;
        }

        if ($timestamp instanceof Carbon) {
            return $timestamp->diffForHumans();
        }

        if (is_string($timestamp)) {
            try {
                return Carbon::parse($timestamp)->diffForHumans();
            } catch (\Exception $e) {
                return $timestamp;
            }
        }

        return null;
    }

    /**
     * Format a date for display (M d, Y)
     *
     * @param mixed $timestamp
     * @return string|null
     */
    public static function formatDate($timestamp): ?string
    {
        if ($timestamp === null) {
            return null;
        }

        if ($timestamp instanceof Carbon) {
            return $timestamp->format('M d, Y');
        }

        if (is_string($timestamp)) {
            try {
                return Carbon::parse($timestamp)->format('M d, Y');
            } catch (\Exception $e) {
                return $timestamp;
            }
        }

        return null;
    }

    /**
     * Get current timestamp in ISO 8601 format
     *
     * @return string
     */
    public static function now(): string
    {
        return now()->toIso8601String();
    }
}

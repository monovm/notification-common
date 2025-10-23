<?php

namespace AbuseIO\Notification;

use Composer\ClassMapGenerator\ClassMapGenerator;
use Log;

/**
 * Class Factory
 * @package AbuseIO\Notification
 */
class Factory
{
    /**
     * Create a new Factory instance
     */
    public function __construct()
    {
        //
    }

    /**
     * Get a list of installed AbuseIO notifications and return as an array
     *
     * @return array
     */
    public static function getNotification()
    {
        $notificationClassList = ClassMapGenerator::createMap(base_path().'/vendor/abuseio');
        /** @noinspection PhpUnusedParameterInspection */
        $notificationClassListFiltered = array_where(
            array_keys($notificationClassList),
            function ($value, $key) {
                // Get all notifications, ignore all other packages.
                if (strpos($value, 'AbuseIO\Notification\\') !== false) {
                    return $value;
                }
                return false;
            }
        );

        $notifications = [];
        $notificationList = array_map('class_basename', $notificationClassListFiltered);
        foreach ($notificationList as $notification) {
            if (!in_array($notification, ['Factory', 'Notification'])) {
                $notifications[] = $notification;
            }
        }
        return $notifications;
    }

    /**
     * Create and return a Collector object and it's configuration
     *
     * @param string $requiredName
     * @return object
     */
    public static function create($requiredName)
    {
        /**
         * Loop through the notification list and try to find a match by name
         */
        $notifications = Factory::getNotification();
        foreach ($notifications as $notificationName) {

            if ($notificationName === ucfirst($requiredName)) {
                $notificationClass = 'AbuseIO\\Notification\\' . $notificationName;

                // Collector is enabled, then return its object
                if (config("notifications.{$notificationName}.notification.enabled") === true) {

                    return new $notificationClass();

                } else {
                    Log::info(
                        'AbuseIO\Notification\Factory: ' .
                        "The notification {$notificationName} has been disabled and will not be used."
                    );

                    return false;
                }
            }
        }

        // No valid notifications found
        Log::info(
            'AbuseIO\Notification\Factory: ' .
            "The notification {$requiredName} is not present on this system"
        );
        return false;
    }
}

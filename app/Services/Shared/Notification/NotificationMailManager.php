<?php

namespace App\Services\Shared\Notification;

use Illuminate\Mail\MailManager;

/** An uncached, notification-only manager; never mutates the application's mail configuration. */
class NotificationMailManager extends MailManager
{
    protected function getConfig(string $name)
    {
        $settings = parent::getConfig($name);
        if ($settings === null) {
            return null;
        }

        // Apply AFTER URL parsing, including every child of failover/roundrobin mailers.
        $timeout = (int) $this->app['config']->get('notification.smtp_timeout', 15);
        $settings['timeout'] = $timeout;
        if (in_array($settings['transport'] ?? null, ['mailgun', 'postmark'], true)) {
            $settings['client']['timeout'] = $timeout;
            $settings['client']['max_duration'] = $timeout;
        }
        if (in_array($settings['transport'] ?? null, ['ses', 'ses-v2'], true)) {
            $settings['http']['timeout'] = $timeout;
            $settings['http']['connect_timeout'] = $timeout;
        }

        return $settings;
    }
}

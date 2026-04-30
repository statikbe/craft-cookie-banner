<?php

namespace statikbe\cookiebanner\migrations;

use Craft;
use craft\db\Migration;
use statikbe\cookiebanner\records\CookieTrackingRecord;

/**
 * m231213_141108_statik_cookie_tracking migration.
 */
class m260430_094400_update_plugin_handle extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->update(
            '{{%plugins}}',
            ['handle' => 'cookie-banner'],
            ['handle' => '_statik-cookie-banner']
        );

        $data = Craft::$app->projectConfig->get('plugins.cookie-banner');
        Craft::$app->projectConfig->set('plugins._statik-cookie-banner', $data);

        return true;
    }


}

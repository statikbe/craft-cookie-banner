<?php

namespace statikbe\cookiebanner\migrations;

use craft\db\Migration;

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

        $data = \Craft::$app->projectConfig->get('plugins.cookie-banner');
        \Craft::$app->projectConfig->set(
            'plugins._statik-cookie-banner',
            $data,
            "cookie banner handle update",
            false,
            true);
        \Craft::$app->projectConfig->remove('plugins.cookie-banner');

        $this->update(
            '{{%plugins}}',
            ['handle' => '_statik-cookie-banner'],
            ['handle' => 'cookie-banner']
        );

        return true;
    }


}

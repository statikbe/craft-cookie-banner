<?php

namespace statikbe\cookiebanner\records;

use craft\db\ActiveRecord;

class CookieTrackingRecord extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return 'statik_cookie_tracking';
    }
}

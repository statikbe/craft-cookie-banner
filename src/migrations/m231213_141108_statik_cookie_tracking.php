<?php

namespace statikbe\cookiebanner\migrations;

use craft\db\Migration;
use statikbe\cookiebanner\records\CookieTrackingRecord;

/**
 * m231213_141108_statik_cookie_tracking migration.
 */
class m231213_141108_statik_cookie_tracking extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        if ($this->tableExists(CookieTrackingRecord::tableName())) {
            // table was set by install migration so don't do it here...
            return true;
        }

        $this->createTable(CookieTrackingRecord::tableName(), [
            'id' => $this->primaryKey(),
            'accept' => $this->integer()->notNull(),
            'deny' => $this->integer()->notNull(),
            'settings' => $this->integer()->notNull(),
            'siteId' => $this->integer()->notNull(),
            'sectionDate' => $this->string()->notNull(),
        ]);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {

        $this->dropTable(CookieTrackingRecord::tableName());

        return true;
    }

    private function tableExists(string $tableName) : bool
    {
        return ($this->db->getTableSchema($tableName, true) !== null);
    }
}

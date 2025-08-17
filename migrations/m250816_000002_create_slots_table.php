<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%slots}}`.
 */
class m250816_000002_create_slots_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%slots}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(100)->notNull(),
            'scheduled_time' => $this->datetime()->notNull(),
            'is_auto' => $this->boolean()->defaultValue(0)->comment('1 if result should be auto-generated'),
            'is_active' => $this->boolean()->defaultValue(1)->comment('1 if slot is active'),
            'description' => $this->text(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Create index for scheduled_time for faster queries
        $this->createIndex('idx-slots-scheduled_time', '{{%slots}}', 'scheduled_time');
        $this->createIndex('idx-slots-is_auto', '{{%slots}}', 'is_auto');
        $this->createIndex('idx-slots-is_active', '{{%slots}}', 'is_active');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%slots}}');
    }
}

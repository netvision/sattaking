<?php

use yii\db\Migration;

/**
 * Modify slots table to support daily recurring slots
 */
class m240816_000004_modify_slots_for_daily_recurring extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Change scheduled_time to only store time (HH:MM:SS)
        $this->alterColumn('{{%slots}}', 'scheduled_time', $this->time()->notNull());
        
        // Add comment to clarify the purpose
        $this->addCommentOnColumn('{{%slots}}', 'scheduled_time', 'Daily recurring time (HH:MM:SS format)');
        
        // Add index for efficient querying
        $this->createIndex('idx_slots_time_active', '{{%slots}}', ['scheduled_time', 'is_active']);
        
        echo "Slots table modified for daily recurring functionality.\n";
        echo "Note: Existing scheduled_time values will be converted to time format.\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Revert back to datetime
        $this->alterColumn('{{%slots}}', 'scheduled_time', $this->dateTime()->notNull());
        
        // Remove the index
        $this->dropIndex('idx_slots_time_active', '{{%slots}}');
        
        echo "Reverted slots table to use datetime for scheduled_time.\n";
    }
}

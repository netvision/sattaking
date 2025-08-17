<?php

use yii\db\Migration;

/**
 * Handles inserting sample data into slots table.
 */
class m250816_000004_insert_sample_slots extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Insert sample slots
        $this->batchInsert('{{%slots}}', 
            ['title', 'scheduled_time', 'is_auto', 'description'], 
            [
                ['Morning King', '2025-08-16 10:00:00', 1, 'Morning lottery result'],
                ['Afternoon King', '2025-08-16 14:00:00', 1, 'Afternoon lottery result'],
                ['Evening King', '2025-08-16 18:00:00', 1, 'Evening lottery result'],
                ['Night King', '2025-08-16 22:00:00', 1, 'Night lottery result'],
                ['Special Draw', '2025-08-16 16:00:00', 0, 'Special manual draw'],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%slots}}', ['title' => ['Morning King', 'Afternoon King', 'Evening King', 'Night King', 'Special Draw']]);
    }
}

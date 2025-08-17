<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%results}}`.
 */
class m250816_000003_create_results_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%results}}', [
            'id' => $this->primaryKey(),
            'slot_id' => $this->integer()->notNull(),
            'result' => $this->integer()->notNull()->comment('Result number between 0-99'),
            'declared_at' => $this->datetime()->notNull(),
            'is_auto' => $this->boolean()->defaultValue(0)->comment('1 if auto-generated'),
            'locked' => $this->boolean()->defaultValue(0)->comment('1 if result is locked (cannot be changed)'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Add foreign key constraint
        $this->addForeignKey(
            'fk-results-slot_id',
            '{{%results}}',
            'slot_id',
            '{{%slots}}',
            'id',
            'CASCADE'
        );

        // Create indexes for better performance
        $this->createIndex('idx-results-slot_id', '{{%results}}', 'slot_id');
        $this->createIndex('idx-results-declared_at', '{{%results}}', 'declared_at');
        $this->createIndex('idx-results-locked', '{{%results}}', 'locked');
        
        // Create unique index to prevent duplicate results for same slot
        $this->createIndex('idx-results-unique-slot-date', '{{%results}}', ['slot_id', 'declared_at'], true);
        
        // Add check constraint for result range (0-99)
        $this->execute('ALTER TABLE {{%results}} ADD CONSTRAINT chk_result_range CHECK (result >= 0 AND result <= 99)');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-results-slot_id', '{{%results}}');
        $this->dropTable('{{%results}}');
    }
}

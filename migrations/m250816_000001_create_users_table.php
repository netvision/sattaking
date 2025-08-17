<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%users}}`.
 */
class m250816_000001_create_users_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%users}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string(50)->notNull()->unique(),
            'password_hash' => $this->string(255)->notNull(),
            'auth_key' => $this->string(32),
            'access_token' => $this->string(100),
            'status' => $this->smallInteger()->notNull()->defaultValue(1),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Create unique index for username
        $this->createIndex('idx-users-username', '{{%users}}', 'username');
        
        // Insert default admin user
        $this->insert('{{%users}}', [
            'username' => 'admin',
            'password_hash' => '$2y$13$nJ1WDlBaGcbCdbNC9QoOQO9jUsQlBdoohOZjHF4wpUrw.MHKXKrP6', // password: admin123
            'auth_key' => 'test100key',
            'access_token' => 'admin-token-100',
            'status' => 1,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%users}}');
    }
}

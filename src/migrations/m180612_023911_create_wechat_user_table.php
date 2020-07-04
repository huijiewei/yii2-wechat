<?php

use yii\db\Migration;

/**
 * Handles the creation of table `wechat_user`.
 */
class m180612_023911_create_wechat_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql' || $this->db->driverName === 'mariadb') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%wechat_user}}', [
            'id' => $this->primaryKey(),
            'appId' => $this->string(50)->notNull()->defaultValue(''),
            'openId' => $this->string(50)->notNull()->defaultValue(''),
            'unionId' => $this->string(50)->notNull()->defaultValue(''),
            'nickname' => $this->string(32)->notNull()->defaultValue(''),
            'avatar' => $this->string(200)->notNull()->defaultValue(''),
            'details' => $this->text()->null()->defaultValue(null),
            'accessToken' => $this->string(100)->notNull()->defaultValue(''),
            'refreshToken' => $this->string(100)->notNull()->defaultValue(''),
            'accessTokenExpiredAt' => $this->timestamp()->defaultValue(null),
            'refreshTokenExpiredAt' => $this->timestamp()->defaultValue(null),
            'createdAt' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updatedAt' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], $tableOptions);


        $this->createIndex('openId', '{{%wechat_user}}', ['appId', 'openId']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%wechat_user}}');
    }
}

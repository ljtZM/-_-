<?php
/**
 * Team:从容应“队”，NKU
 * coding by 李嘉桐 2212023
 * 网站基本信息模型
 */
namespace app\models;

use Yii;

/**
 * This is the model class for table "website".
 *
 * @property int $id
 * @property string|null $description
 * @property string $created_at
 * @property string $updated_at
 */
class Website extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'website';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'description' => 'Description',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}

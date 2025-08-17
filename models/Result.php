<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "results".
 *
 * @property int $id
 * @property int $slot_id
 * @property int $result
 * @property string $declared_at
 * @property int $is_auto
 * @property int $locked
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Slot $slot
 */
class Result extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%results}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['slot_id', 'result', 'declared_at'], 'required'],
            [['slot_id', 'result', 'is_auto', 'locked'], 'integer'],
            [['declared_at', 'created_at', 'updated_at'], 'safe'],
            [['result'], 'integer', 'min' => 0, 'max' => 99],
            ['is_auto', 'boolean'],
            ['locked', 'boolean'],
            ['is_auto', 'default', 'value' => 0],
            ['locked', 'default', 'value' => 0],
            ['declared_at', 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            [['slot_id'], 'exist', 'skipOnError' => true, 'targetClass' => Slot::class, 'targetAttribute' => ['slot_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'slot_id' => 'Slot ID',
            'result' => 'Result',
            'declared_at' => 'Declared At',
            'is_auto' => 'Auto Generated',
            'locked' => 'Locked',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Slot]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSlot()
    {
        return $this->hasOne(Slot::class, ['id' => 'slot_id']);
    }

    /**
     * Check if result can be modified
     *
     * @return bool
     */
    public function canBeModified()
    {
        // Cannot modify if locked or if time has passed
        if ($this->locked) {
            return false;
        }
        
        if ($this->slot && $this->slot->isTimePassed()) {
            return false;
        }
        
        return true;
    }

    /**
     * Lock the result (make it immutable)
     */
    public function lock()
    {
        $this->locked = 1;
        return $this->save(false, ['locked']);
    }

    /**
     * Generate random result (0-99)
     *
     * @return int
     */
    public static function generateRandomResult()
    {
        return mt_rand(0, 99);
    }

    /**
     * Create auto-generated result for slot
     *
     * @param Slot $slot
     * @param string|null $datetime
     * @return Result|null
     */
    public static function createAutoResult($slot, $datetime = null)
    {
        if ($datetime === null) {
            $datetime = date('Y-m-d H:i:s');
        }

        $result = new static([
            'slot_id' => $slot->id,
            'result' => static::generateRandomResult(),
            'declared_at' => $datetime,
            'is_auto' => 1,
            'locked' => 1, // Auto results are immediately locked
        ]);

        return $result->save() ? $result : null;
    }

    /**
     * Get results for specific date
     *
     * @param string $date Y-m-d format
     * @return \yii\db\ActiveQuery
     */
    public static function findByDate($date)
    {
        return static::find()
            ->joinWith('slot')
            ->where(['DATE(declared_at)' => $date])
            ->orderBy(['declared_at' => SORT_ASC]);
    }

    /**
     * Get today's results
     *
     * @return \yii\db\ActiveQuery
     */
    public static function findTodayResults()
    {
        return static::findByDate(date('Y-m-d'));
    }

    /**
     * Get latest results (limit)
     *
     * @param int $limit
     * @return \yii\db\ActiveQuery
     */
    public static function findLatest($limit = 10)
    {
        return static::find()
            ->joinWith('slot')
            ->orderBy(['declared_at' => SORT_DESC])
            ->limit($limit);
    }

    /**
     * Before save - auto-lock results after scheduled time
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Auto-lock if time has passed
            if ($this->slot && $this->slot->isTimePassed()) {
                $this->locked = 1;
            }
            return true;
        }
        return false;
    }

    /**
     * Before update - prevent modification of locked results
     */
    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            if (!$this->isNewRecord && !$this->canBeModified()) {
                $this->addError('result', 'This result is locked and cannot be modified.');
                return false;
            }
            return true;
        }
        return false;
    }
}

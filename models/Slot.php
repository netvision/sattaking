<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "slots".
 *
 * @property int $id
 * @property string $title
 * @property string $scheduled_time
 * @property int $is_auto
 * @property int $is_active
 * @property string|null $description
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Result[] $results
 */
class Slot extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%slots}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'scheduled_time'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['is_auto', 'is_active'], 'integer'],
            [['description'], 'string'],
            [['title'], 'string', 'max' => 100],
            ['is_auto', 'boolean'],
            ['is_active', 'boolean'],
            ['is_auto', 'default', 'value' => 0],
            ['is_active', 'default', 'value' => 1],
            ['scheduled_time', 'time', 'format' => 'php:H:i:s'],
            ['scheduled_time', 'validateTimeFormat'],
        ];
    }

    /**
     * Custom validation for time format
     */
    public function validateTimeFormat($attribute, $params)
    {
        if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $this->$attribute)) {
            $this->addError($attribute, 'Time must be in HH:MM or HH:MM:SS format');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'scheduled_time' => 'Scheduled Time',
            'is_auto' => 'Auto Generate',
            'is_active' => 'Is Active',
            'description' => 'Description',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Results]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getResults()
    {
        return $this->hasMany(Result::class, ['slot_id' => 'id']);
    }

    /**
     * Get latest result for this slot
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLatestResult()
    {
        return $this->hasOne(Result::class, ['slot_id' => 'id'])
            ->orderBy(['declared_at' => SORT_DESC]);
    }

    /**
     * Check if slot has result for today
     *
     * @return bool
     */
    public function hasResultToday()
    {
        $today = date('Y-m-d');
        return $this->getResults()
            ->where(['DATE(declared_at)' => $today])
            ->exists();
    }

    /**
     * Check if slot time has passed for today
     *
     * @return bool
     */
    public function isTimePassed()
    {
        $currentTime = date('H:i:s');
        return $this->scheduled_time < $currentTime;
    }

    /**
     * Get the full datetime for today's slot
     *
     * @param string|null $date Date in Y-m-d format, defaults to today
     * @return string
     */
    public function getTodayDateTime($date = null)
    {
        if ($date === null) {
            $date = date('Y-m-d');
        }
        return $date . ' ' . $this->scheduled_time;
    }

    /**
     * Get active slots
     *
     * @return \yii\db\ActiveQuery
     */
    public static function findActive()
    {
        return static::find()->where(['is_active' => 1]);
    }

    /**
     * Get auto-generate slots for current time
     *
     * @param string|null $time Time in H:i format, defaults to current time
     * @return \yii\db\ActiveQuery
     */
    public static function findAutoSlotsForTime($time = null)
    {
        if ($time === null) {
            $time = date('H:i');
        }
        
        // If time doesn't include seconds, add :00
        if (strlen($time) === 5) {
            $time .= ':00';
        }
        
        return static::find()
            ->where(['is_auto' => 1, 'is_active' => 1])
            ->andWhere(['scheduled_time' => $time]);
    }

    /**
     * Get all active slots (they run every day)
     *
     * @return \yii\db\ActiveQuery
     */
    public static function findTodaySlots()
    {
        return static::findActive()
            ->orderBy(['scheduled_time' => SORT_ASC]);
    }

    /**
     * Get slots that don't have results for a specific date
     *
     * @param string $date Date in Y-m-d format
     * @return \yii\db\ActiveQuery
     */
    public static function findPendingSlotsForDate($date = null)
    {
        if ($date === null) {
            $date = date('Y-m-d');
        }

        return static::findActive()
            ->leftJoin('{{%results}} r', 'r.slot_id = {{%slots}}.id AND DATE(r.declared_at) = :date', [':date' => $date])
            ->where(['r.id' => null])
            ->orderBy(['scheduled_time' => SORT_ASC]);
    }

    /**
     * Format time for display
     *
     * @param string $format
     * @return string
     */
    public function getFormattedTime($format = 'H:i')
    {
        return date($format, strtotime($this->scheduled_time));
    }
}

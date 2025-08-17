<?php

namespace app\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\Response;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use app\models\Slot;

/**
 * SlotController handles slot management
 */
class SlotController extends ActiveController
{
    public $modelClass = 'app\models\Slot';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // CORS filter
        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['http://localhost:3000', 'http://localhost:5173'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age' => 86400,
            ],
        ];

        // Authentication - only admin actions require auth
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['index', 'view', 'today', 'options'],
        ];

        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        $actions = parent::actions();
        
        // Customize default actions - remove default ones to use our custom implementations
        unset($actions['index']); // We'll implement custom index
        unset($actions['create']); // Use our custom actionCreate
        unset($actions['update']); // Use our custom actionUpdate
        unset($actions['delete']); // Use our custom actionDelete
        
        return $actions;
    }

    /**
     * List all slots with optional filtering
     * GET /slots
     * 
     * @return array
     */
    public function actionIndex()
    {
        $query = Slot::find()->with('latestResult');

        // Filter by date if provided
        $date = Yii::$app->request->get('date');
        if ($date) {
            $query->where(['DATE(scheduled_time)' => $date]);
        }

        // Filter by active status
        $active = Yii::$app->request->get('active');
        if ($active !== null) {
            $query->andWhere(['is_active' => $active ? 1 : 0]);
        }

        $slots = $query->orderBy(['scheduled_time' => SORT_ASC])->all();

        $data = [];
        foreach ($slots as $slot) {
            $slotData = $slot->toArray();
            $slotData['latest_result'] = $slot->latestResult ? $slot->latestResult->toArray() : null;
            $slotData['has_result_today'] = $slot->hasResultToday();
            $slotData['is_time_passed'] = $slot->isTimePassed();
            $data[] = $slotData;
        }

        return [
            'success' => true,
            'data' => $data,
        ];
    }

    /**
     * Get today's slots
     * GET /slots/today
     * 
     * @return array
     */
    public function actionToday()
    {
        $slots = Slot::findTodaySlots()->with('latestResult')->all();

        $data = [];
        foreach ($slots as $slot) {
            $slotData = $slot->toArray();
            $slotData['latest_result'] = $slot->latestResult ? $slot->latestResult->toArray() : null;
            $slotData['has_result_today'] = $slot->hasResultToday();
            $slotData['is_time_passed'] = $slot->isTimePassed();
            $slotData['today_datetime'] = $slot->getTodayDateTime(); // Add full datetime for today
            $slotData['formatted_time'] = $slot->getFormattedTime('H:i'); // Human readable time
            $data[] = $slotData;
        }

        return [
            'success' => true,
            'data' => $data,
        ];
    }

    /**
     * Create a new slot (Admin only)
     * POST /slots
     * 
     * @return array
     */
    public function actionCreate()
    {
        $model = new Slot();
        $bodyParams = Yii::$app->request->getBodyParams();
        
        // Debug logging
        Yii::error('Create slot - Received data: ' . json_encode($bodyParams), __METHOD__);
        
        $model->load($bodyParams, '');
        
        // Debug logging before save
        Yii::error('Create slot - Model attributes before save: ' . json_encode($model->attributes), __METHOD__);

        if ($model->save()) {
            return [
                'success' => true,
                'message' => 'Slot created successfully',
                'data' => $model->toArray(),
            ];
        }

        // Debug logging for validation errors
        Yii::error('Create slot - Validation errors: ' . json_encode($model->errors), __METHOD__);
        
        Yii::$app->response->statusCode = 422;
        return [
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $model->errors,
            'debug_received_data' => $bodyParams,
            'debug_model_attributes' => $model->attributes,
        ];
    }

    /**
     * Update a slot (Admin only)
     * PUT /slots/{id}
     * 
     * @param int $id
     * @return array
     */
    public function actionUpdate($id)
    {
        $model = Slot::findOne($id);
        
        if (!$model) {
            Yii::$app->response->statusCode = 404;
            return [
                'success' => false,
                'message' => 'Slot not found',
            ];
        }

        $bodyParams = Yii::$app->request->getBodyParams();
        
        // Debug logging
        Yii::error('Update slot - Received data: ' . json_encode($bodyParams), __METHOD__);
        
        $model->load($bodyParams, '');
        
        // Debug logging before save
        Yii::error('Update slot - Model attributes before save: ' . json_encode($model->attributes), __METHOD__);

        if ($model->save()) {
            return [
                'success' => true,
                'message' => 'Slot updated successfully',
                'data' => $model->toArray(),
            ];
        }

        // Debug logging for validation errors
        Yii::error('Update slot - Validation errors: ' . json_encode($model->errors), __METHOD__);

        Yii::$app->response->statusCode = 422;
        return [
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $model->errors,
            'debug_received_data' => $bodyParams,
            'debug_model_attributes' => $model->attributes,
        ];
    }

    /**
     * Delete a slot (Admin only)
     * DELETE /slots/{id}
     * 
     * @param int $id
     * @return array
     */
    public function actionDelete($id)
    {
        $model = Slot::findOne($id);
        
        if (!$model) {
            Yii::$app->response->statusCode = 404;
            return [
                'success' => false,
                'message' => 'Slot not found',
            ];
        }

        // Check if slot has results
        if ($model->getResults()->exists()) {
            Yii::$app->response->statusCode = 422;
            return [
                'success' => false,
                'message' => 'Cannot delete slot with existing results',
            ];
        }

        if ($model->delete()) {
            return [
                'success' => true,
                'message' => 'Slot deleted successfully',
            ];
        }

        Yii::$app->response->statusCode = 500;
        return [
            'success' => false,
            'message' => 'Failed to delete slot',
        ];
    }

    /**
     * Handle OPTIONS requests for CORS
     */
    public function actionOptions()
    {
        return '';
    }
}

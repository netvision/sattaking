<?php

namespace app\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use app\models\Result;
use app\models\Slot;

/**
 * ResultController handles result management
 */
class ResultController extends Controller
{
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

        // Authentication - public can view, admin can modify
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['index', 'view', 'today', 'archive', 'latest', 'options'],
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
     * List results with optional filtering
     * GET /results
     * 
     * @return array
     */
    public function actionIndex()
    {
        $query = Result::find()->with('slot');

        // Filter by date
        $date = Yii::$app->request->get('date');
        if ($date) {
            $query = Result::findByDate($date);
        } else {
            // Default to today's results
            $query = Result::findTodayResults();
        }

        // Optional: filter by slot_id (used by slot history page)
        $slotId = Yii::$app->request->get('slot_id');
        if ($slotId !== null) {
            $query = $query->andWhere(['slot_id' => (int)$slotId]);
        }

        $results = $query->all();

        $data = [];
        foreach ($results as $result) {
            $resultData = $result->toArray();
            $resultData['slot'] = $result->slot->toArray();
            $data[] = $resultData;
        }

        return [
            'success' => true,
            'data' => $data,
        ];
    }

    /**
     * Get today's results
     * GET /results/today
     * 
     * @return array
     */
    public function actionToday()
    {
        $results = Result::findTodayResults()->all();

        $data = [];
        foreach ($results as $result) {
            $resultData = $result->toArray();
            $resultData['slot'] = $result->slot->toArray();
            $data[] = $resultData;
        }

        return [
            'success' => true,
            'data' => $data,
        ];
    }

    /**
     * Get archived results for specific date
     * GET /results/archive?date=YYYY-MM-DD
     * 
     * @return array
     */
    public function actionArchive()
    {
        $date = Yii::$app->request->get('date');
        
        if (!$date) {
            Yii::$app->response->statusCode = 400;
            return [
                'success' => false,
                'message' => 'Date parameter is required (format: YYYY-MM-DD)',
            ];
        }

        $results = Result::findByDate($date)->all();

        $data = [];
        foreach ($results as $result) {
            $resultData = $result->toArray();
            $resultData['slot'] = $result->slot->toArray();
            $data[] = $resultData;
        }

        return [
            'success' => true,
            'data' => $data,
            'date' => $date,
        ];
    }

    /**
     * Get latest results
     * GET /results/latest?limit=10
     * 
     * @return array
     */
    public function actionLatest()
    {
        $limit = (int) Yii::$app->request->get('limit', 10);
        $limit = min($limit, 50); // Max 50 results

        $results = Result::findLatest($limit)->all();

        $data = [];
        foreach ($results as $result) {
            $resultData = $result->toArray();
            $resultData['slot'] = $result->slot->toArray();
            $data[] = $resultData;
        }

        return [
            'success' => true,
            'data' => $data,
        ];
    }

    /**
     * Create/Set a result (Admin only)
     * POST /results
     * 
     * @return array
     */
    public function actionCreate()
    {
        $slotId = Yii::$app->request->post('slot_id');
        $resultValue = Yii::$app->request->post('result');
        $declaredAt = Yii::$app->request->post('declared_at');

        if (!$slotId || $resultValue === null) {
            Yii::$app->response->statusCode = 400;
            return [
                'success' => false,
                'message' => 'slot_id and result are required',
            ];
        }

        $slot = Slot::findOne($slotId);
        if (!$slot) {
            Yii::$app->response->statusCode = 404;
            return [
                'success' => false,
                'message' => 'Slot not found',
            ];
        }

        // Check if result already exists for this slot today
        $existingResult = Result::find()
            ->where(['slot_id' => $slotId])
            ->andWhere(['DATE(declared_at)' => date('Y-m-d')])
            ->one();

        if ($existingResult) {
            if (!$existingResult->canBeModified()) {
                Yii::$app->response->statusCode = 422;
                return [
                    'success' => false,
                    'message' => 'Result is locked and cannot be modified',
                ];
            }
            
            // Update existing result
            $existingResult->result = $resultValue;
            if ($declaredAt) {
                $existingResult->declared_at = $declaredAt;
            }
            
            if ($existingResult->save()) {
                return [
                    'success' => true,
                    'message' => 'Result updated successfully',
                    'data' => $existingResult->toArray(),
                ];
            } else {
                Yii::$app->response->statusCode = 422;
                return [
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $existingResult->errors,
                ];
            }
        }

        // Create new result
        $model = new Result([
            'slot_id' => $slotId,
            'result' => $resultValue,
            'declared_at' => $declaredAt ?: date('Y-m-d H:i:s'),
            'is_auto' => 0, // Admin set result
        ]);

        if ($model->save()) {
            return [
                'success' => true,
                'message' => 'Result created successfully',
                'data' => $model->toArray(),
            ];
        }

        Yii::$app->response->statusCode = 422;
        return [
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $model->errors,
        ];
    }

    /**
     * Update a result (Admin only)
     * PUT /results/{id}
     * 
     * @param int $id
     * @return array
     */
    public function actionUpdate($id)
    {
        $model = Result::findOne($id);
        
        if (!$model) {
            Yii::$app->response->statusCode = 404;
            return [
                'success' => false,
                'message' => 'Result not found',
            ];
        }

        if (!$model->canBeModified()) {
            Yii::$app->response->statusCode = 422;
            return [
                'success' => false,
                'message' => 'Result is locked and cannot be modified',
            ];
        }

        $model->load(Yii::$app->request->getBodyParams(), '');

        if ($model->save()) {
            return [
                'success' => true,
                'message' => 'Result updated successfully',
                'data' => $model->toArray(),
            ];
        }

        Yii::$app->response->statusCode = 422;
        return [
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $model->errors,
        ];
    }

    /**
     * Lock a result (Admin only)
     * POST /results/{id}/lock
     * 
     * @param int $id
     * @return array
     */
    public function actionLock($id)
    {
        $model = Result::findOne($id);
        
        if (!$model) {
            Yii::$app->response->statusCode = 404;
            return [
                'success' => false,
                'message' => 'Result not found',
            ];
        }

        if ($model->lock()) {
            return [
                'success' => true,
                'message' => 'Result locked successfully',
                'data' => $model->toArray(),
            ];
        }

        Yii::$app->response->statusCode = 500;
        return [
            'success' => false,
            'message' => 'Failed to lock result',
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

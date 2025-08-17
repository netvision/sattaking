<?php

namespace app\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\Cors;

/**
 * InfoController provides platform information for public section
 */
class InfoController extends Controller
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
                'Access-Control-Request-Method' => ['GET', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age' => 86400,
            ],
        ];

        // No authentication required for info
        unset($behaviors['authenticator']);

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
     * Get platform information
     * GET /info
     * 
     * @return array
     */
    public function actionIndex()
    {
        return [
            'success' => true,
            'data' => [
                'platform_name' => 'Satta King Portal',
                'description' => 'Your trusted lottery results platform',
                'version' => '1.0.0',
                'timezone' => date_default_timezone_get(),
                'current_time' => date('Y-m-d H:i:s'),
                'result_range' => '0-99',
                'features' => [
                    'Real-time results',
                    'Auto-generation',
                    'Historical archive',
                    'Admin dashboard',
                ],
                'contact' => [
                    'support' => 'support@sattaking.app',
                    'website' => 'https://sattaking.app',
                ],
            ],
        ];
    }

    /**
     * Get system status
     * GET /info/status
     * 
     * @return array
     */
    public function actionStatus()
    {
        $dbConnection = true;
        try {
            Yii::$app->db->open();
        } catch (\Exception $e) {
            $dbConnection = false;
        }

        return [
            'success' => true,
            'data' => [
                'status' => 'operational',
                'database' => $dbConnection ? 'connected' : 'error',
                'timestamp' => time(),
                'uptime' => 'API is running',
            ],
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

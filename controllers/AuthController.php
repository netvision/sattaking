<?php

namespace app\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use app\models\User;

/**
 * AuthController handles authentication
 */
class AuthController extends Controller
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
                'Origin' => ['http://localhost:3000', 'http://localhost:5173', 'https://sattaking1.netlify.app', 'https://sattaking.app'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age' => 86400,
            ],
        ];

        // Remove authentication for login action
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['login', 'options'],
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
     * Admin login
     * POST /auth/login
     * 
     * @return array
     */
    public function actionLogin()
    {
        $bodyParams = Yii::$app->request->getBodyParams();
        $username = $bodyParams['username'] ?? Yii::$app->request->post('username');
        $password = $bodyParams['password'] ?? Yii::$app->request->post('password');

        if (empty($username) || empty($password)) {
            Yii::$app->response->statusCode = 400;
            return [
                'success' => false,
                'message' => 'Username and password are required',
            ];
        }

        $user = User::findByUsername($username);
        
        if ($user && $user->validatePassword($password)) {
            // Generate new access token
            $user->generateAccessToken();
            $user->save(false, ['access_token']);

            return [
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'access_token' => $user->access_token,
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                    ],
                ],
            ];
        }

        Yii::$app->response->statusCode = 401;
        return [
            'success' => false,
            'message' => 'Invalid username or password',
        ];
    }

    /**
     * Admin logout
     * POST /auth/logout
     * 
     * @return array
     */
    public function actionLogout()
    {
        $user = Yii::$app->user->identity;
        if ($user) {
            // Clear access token
            $user->access_token = null;
            $user->save(false, ['access_token']);
        }

        return [
            'success' => true,
            'message' => 'Logout successful',
        ];
    }

    /**
     * Get current user info
     * GET /auth/me
     * 
     * @return array
     */
    public function actionMe()
    {
        $user = Yii::$app->user->identity;
        
        return [
            'success' => true,
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'created_at' => $user->created_at,
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

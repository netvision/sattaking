<?php

namespace app\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\Response;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\data\ActiveDataProvider;
use app\models\User;

/**
 * UserController handles user management API
 */
class UserController extends ActiveController
{
    public $modelClass = 'app\models\User';

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

        // Authentication - all user management requires admin auth
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['options'],
        ];

        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        $actions = parent::actions();
        
        // Customize the data provider for index action
        $actions['index']['dataFilter'] = [
            'class' => \yii\data\ActiveDataFilter::class,
            'searchModel' => 'app\models\User',
        ];
        
        // Remove default actions to use our custom implementations
        unset($actions['index']); // Use our custom actionIndex
        unset($actions['create']); // Use our custom actionCreate
        unset($actions['update']); // Use our custom actionUpdate  
        unset($actions['delete']); // Use our custom actionDelete
        
        return $actions;
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
     * List all users
     * GET /users
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => User::find(),
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ]
            ],
        ]);

        return [
            'success' => true,
            'data' => $dataProvider,
        ];
    }

    /**
     * View user details
     * GET /users/{id}
     */
    public function actionView($id)
    {
        $user = User::findOne($id);
        
        if (!$user) {
            Yii::$app->response->statusCode = 404;
            return [
                'success' => false,
                'message' => 'User not found',
            ];
        }

        return [
            'success' => true,
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'status' => $user->status,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
        ];
    }

    /**
     * Create new user
     * POST /users
     */
    public function actionCreate()
    {
        $user = new User();
        $postData = Yii::$app->request->post();
        $bodyParams = Yii::$app->request->getBodyParams();
        
        // Debug logging
        Yii::error('Create user - POST data: ' . json_encode($postData), __METHOD__);
        Yii::error('Create user - Body params: ' . json_encode($bodyParams), __METHOD__);
        
        $user->load($bodyParams, '');
        
        // Debug logging
        Yii::error('Create user - User attributes after load: ' . json_encode($user->attributes), __METHOD__);
        
        if (empty($user->password_hash)) {
            $password = $bodyParams['password'] ?? $postData['password'] ?? null;
            if (empty($password)) {
                Yii::$app->response->statusCode = 400;
                return [
                    'success' => false,
                    'message' => 'Password is required',
                ];
            }
            $user->setPassword($password);
        }

        if ($user->save()) {
            return [
                'success' => true,
                'message' => 'User created successfully',
                'data' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'status' => $user->status,
                    'created_at' => $user->created_at,
                ],
            ];
        }

        // Debug logging
        Yii::error('Create user - Validation errors: ' . json_encode($user->errors), __METHOD__);

        Yii::$app->response->statusCode = 400;
        return [
            'success' => false,
            'message' => 'Failed to create user',
            'errors' => $user->errors,
            'debug_post_data' => $postData,
            'debug_body_params' => $bodyParams,
            'debug_user_attributes' => $user->attributes,
        ];
    }

    /**
     * Update user
     * PUT /users/{id}
     */
    public function actionUpdate($id)
    {
        $user = User::findOne($id);
        
        if (!$user) {
            Yii::$app->response->statusCode = 404;
            return [
                'success' => false,
                'message' => 'User not found',
            ];
        }

        $bodyParams = Yii::$app->request->getBodyParams();
        
        // Debug logging
        Yii::error('Update user - Body params: ' . json_encode($bodyParams), __METHOD__);
        
        $user->load($bodyParams, '');
        
        // Debug logging
        Yii::error('Update user - User attributes after load: ' . json_encode($user->attributes), __METHOD__);
        
        // Handle password update
        $password = $bodyParams['password'] ?? null;
        if (!empty($password)) {
            $user->setPassword($password);
            $user->generateAccessToken(); // Generate new token when password changes
        }
        
        if ($user->save()) {
            return [
                'success' => true,
                'message' => 'User updated successfully',
                'data' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'status' => $user->status,
                    'updated_at' => $user->updated_at,
                ],
            ];
        }

        // Debug logging
        Yii::error('Update user - Validation errors: ' . json_encode($user->errors), __METHOD__);

        Yii::$app->response->statusCode = 400;
        return [
            'success' => false,
            'message' => 'Failed to update user',
            'errors' => $user->errors,
            'debug_body_params' => $bodyParams,
            'debug_user_attributes' => $user->attributes,
        ];
    }

    /**
     * Delete user
     * DELETE /users/{id}
     */
    public function actionDelete($id)
    {
        $user = User::findOne($id);
        
        if (!$user) {
            Yii::$app->response->statusCode = 404;
            return [
                'success' => false,
                'message' => 'User not found',
            ];
        }

        // Soft delete by setting status to deleted
        $user->status = User::STATUS_DELETED;
        
        if ($user->save()) {
            return [
                'success' => true,
                'message' => 'User deleted successfully',
            ];
        }

        Yii::$app->response->statusCode = 400;
        return [
            'success' => false,
            'message' => 'Failed to delete user',
            'errors' => $user->errors,
        ];
    }

    /**
     * Toggle user status
     * POST /users/{id}/toggle-status
     */
    public function actionToggleStatus($id)
    {
        $user = User::findOne($id);
        
        if (!$user) {
            Yii::$app->response->statusCode = 404;
            return [
                'success' => false,
                'message' => 'User not found',
            ];
        }

        $user->status = $user->status == User::STATUS_ACTIVE ? User::STATUS_INACTIVE : User::STATUS_ACTIVE;
        
        if ($user->save()) {
            return [
                'success' => true,
                'message' => 'User status updated successfully',
                'data' => [
                    'id' => $user->id,
                    'status' => $user->status,
                ],
            ];
        }

        Yii::$app->response->statusCode = 400;
        return [
            'success' => false,
            'message' => 'Failed to update user status',
            'errors' => $user->errors,
        ];
    }

    /**
     * Reset user password
     * POST /users/{id}/reset-password
     */
    public function actionResetPassword($id)
    {
        $user = User::findOne($id);
        
        if (!$user) {
            Yii::$app->response->statusCode = 404;
            return [
                'success' => false,
                'message' => 'User not found',
            ];
        }

        $bodyParams = Yii::$app->request->getBodyParams();
        $newPassword = $bodyParams['new_password'] ?? null;
        
        if (empty($newPassword)) {
            Yii::$app->response->statusCode = 400;
            return [
                'success' => false,
                'message' => 'New password is required',
            ];
        }

        $user->setPassword($newPassword);
        $user->generateAccessToken(); // Generate new token when password changes
        
        if ($user->save()) {
            return [
                'success' => true,
                'message' => 'Password reset successfully',
                'data' => [
                    'id' => $user->id,
                    'username' => $user->username,
                ],
            ];
        }

        Yii::$app->response->statusCode = 400;
        return [
            'success' => false,
            'message' => 'Failed to reset password',
            'errors' => $user->errors,
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

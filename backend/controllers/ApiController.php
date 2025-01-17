<?php
/**
 * Team:从容应“队”，NKU
 * coding by 李嘉桐 2212023；杨峥芃 2211819；高艺轩 2211820；崔交军 2210199
 * 包括：
 * 管理员登录api
 * 用户登录与注册api
 * 增加网页浏览量和获得网页浏览量api
 * 获取文章内容api
 * 增加文章点赞和获取文章点赞api
 * 增加文章评论和获取文章评论api
 * 获取视频url api
 * 获取网页信息api
 * 获取团队成员信息api
 */
namespace app\controllers;

use yii\web\Controller;
use app\models\Users;
use app\models\Article;
use app\models\ArticleComments;
use app\models\ArticleLikes;
use app\models\Webviews;
use app\models\Admins;
use app\models\Videos;
use app\models\VideoComments;
use app\models\VideoLikes;
use app\models\Team;
use app\models\Website;

class ApiController extends Controller
{   
    public function actionAdminlogin()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $username = \Yii::$app->request->get('username');
        $password = \Yii::$app->request->get('password');

        // Check if both username and password are provided
        if ($username !== null && $password !== null) {
            // Hardcoded admin credentials
            if ($username === 'admin' && $password === 'admin') {
                // Check if the user is an admin
                $admin = Admins::find()
                    ->where(['Username' => $username]) // Check if the user exists as an admin
                    ->one();
                
                if ($admin !== null) {
                    return ['status' => 1]; // Admin credentials are correct
                } else {
                    return ['status' => 0]; // Not an admin
                }
            } else {
                // Incorrect username or password
                return ['status' => 0];
            }
        }

        // Return an error if no username or password provided
        return ['status' => 0];
    }

    
    // 用于登录的API
    public function actionAuthenticate()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $username = \Yii::$app->request->get('username');
        $password = \Yii::$app->request->get('password');
        if ($username !== null && $password !== null) {
            // 根据用户名查找用户
            $user = Users::find()
                ->where(['username' => $username])
                ->one();
            if ($user !== null && \Yii::$app->security->validatePassword($password, $user->password_hash)) {
                // 密码验证通过
                return ['status' => 1, 'message' => '登录成功'];
            } else {
                // 用户名或密码不匹配
                return ['status' => 0, 'message' => '用户名或密码错误'];
            }
        }
        return ['status' => -1, 'message' => '参数缺失'];
    }

    // 用于注册的API
    public function actionRegister()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $username = \Yii::$app->request->get('username');
        $password = \Yii::$app->request->get('password');
        $email = \Yii::$app->request->get('email');
        if ($username && $password && $email) {
            // 检查用户名是否已存在
            $existingUser = Users::find()
            ->where(['username' => $username])
            ->one();
            if ($existingUser !== null) {
            return ['status' => 0, 'message' => '用户名已被注册'];
            }
            // 检查邮箱是否已存在
            $existingEmail = Users::find()
            ->where(['email' => $email])
            ->one();
            if ($existingEmail !== null) {
            return ['status' => 0, 'message' => '邮箱已被注册'];
            }
            // 创建新用户
            $user = new Users();
            $user->username = $username;
            $user->email = $email;
            $user->role = 'user'; // 默认角色是用户，可以根据需求调整
            $user->created_at = date('Y-m-d H:i:s');
            $user->updated_at = $user->created_at;
            // 密码进行哈希处理
            $user->password_hash = \Yii::$app->security->generatePasswordHash($password);
            if ($user->save()) {
                return ['status' => 1, 'message' => '注册成功'];
            } else {
                return ['status' => -1, 'message' => '注册失败，系统错误'];
            }
        }
        else{
            return ['status' => -1, 'message' => '缺少必要参数'];
        }
    }

    public function actionAddwebviews()
    {
        //设置响应格式为 JSON
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $webviews = Webviews::find()->one();
        if (!$webviews) {
            return ['status' => -1, 'message' => '无法找到浏览量记录'];
        }
        $webviews->views = $webviews->views + 1;

        if ($webviews->save()) {
            return ['status' => 1, 'message' => '浏览量增加成功'];
        } else {
            return ['status' => -1, 'message' => '浏览量增加失败'];
        }
    }

    //查询网站浏览量的api接口
    public function actionCheckwebviews()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $webviews = Webviews::find()->one();

        return $webviews;
    }

    //获取文章总页数
    public function actionGetarticlepagecount()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $count = Article::find()->count();
        $pagecount = intval($count / 15);

        if ($count % 15 !== 0) {
            $pagecount += 1;
        }
        $pagecount=json_encode($pagecount);
        return $pagecount;
    }
    
    public function actionGetarticle()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        // 获取页数
        $page = \Yii::$app->request->get('page');
        $intpage = (int)$page;
        $id = \Yii::$app->request->get('id');

        if ($id !== null) {
            // 查询单篇文章
            $article = Article::find()->select(['id', 'title', 'summary', 'content', 'author', 'publication_date', 'created_at', 'updated_at'])
                ->where(['id' => $id])
                ->one();

            // 如果找到该文章，则返回
            if ($article !== null) {
                return $article;
            } else {
                // 如果找不到文章，返回错误消息
                return ['error' => 'Article not found'];
            }
        } else {
            // 查询分页文章列表
            $articles = Article::find()->select(['id', 'title', 'summary', 'content', 'author', 'publication_date', 'created_at', 'updated_at'])
                ->offset(15 * ($intpage - 1))  // 偏移量，根据页数
                ->limit(15)  // 每页显示 15 篇文章
                ->all();

            // 如果没有文章返回空数组
            if (empty($articles)) {
                return [];
            }

            // 返回文章列表
            return $articles;
        }
    }

    
    //获取文章评论
    public function actionGetarticlecomment()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $articleID = \Yii::$app->request->get('article_id');
        // 查询指定文章 ID 的评论
        $comments = ArticleComments::find()
            ->select(['id', 'article_id', 'comment', 'comment_date', 'username'])
            ->where(['article_id' => $articleID])
            ->all();
        return $comments;
    }

    //添加文章评论
    public function actionAddarticlecomment()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $username = \Yii::$app->request->get('username');
        $comment = \Yii::$app->request->get('comment');
        $articleID = \Yii::$app->request->get('article_id');
        $comment_date = \Yii::$app->request->get('comment_date');
        $commentModel = new ArticleComments();
        $commentModel->username = $username;
        $commentModel->comment = $comment;
        $commentModel->article_id = $articleID;
        $commentModel->comment_date = $comment_date;
        if ($commentModel->save()) {
            return ['status' => 1, 'message' => 'Comment added successfully'];
        } else {
            return ['status' => -1, 'message' => 'Failed to add comment'];
        }
    }

    //获取文章点赞数
    public function actionGetarticlelikes()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $articleID = \Yii::$app->request->get('article_id');
        $likes = ArticleLikes::find()
            ->where(['article_id' => $articleID])
            ->one();
        if ($likes === null) {
            // 如果没有找到点赞记录，则创建一条
            $likes = new ArticleLikes();
            $likes->article_id = $articleID;
            $likes->likes = 0;
            $likes->save();
        }
        return $likes->likes;
    }

    //增加文章点赞数
    public function actionAddarticlelikes()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $articleID = \Yii::$app->request->get('article_id');
        $num = \Yii::$app->request->get('num');
        $likes = ArticleLikes::find()
            ->where(['article_id' => $articleID])
            ->one();
        if ($likes === null) {
            // 如果没有找到点赞记录，则创建一条
            $likes = new ArticleLikes();
            $likes->article_id = $articleID;
            $likes->likes = 0;
            $likes->save();
        }
        // 增加点赞数
        $likes->likes += $num;
        if ($likes->save()) {
            return ['status' => 1, 'message' => 'Likes updated successfully'];
        } else {
            return ['status' => -1, 'message' => 'Failed to update likes'];
        }
    }

    //用于视频播放页获取评论的api
    public function actionGetvideocomment()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $videoID = \Yii::$app->request->get('video_id');
        
        // 查询指定文章 ID 的评论
        $comments = VideoComments::find()
            ->select(['id', 'video_id', 'comment', 'comment_date', 'username'])
            ->where(['video_id' => $videoID])
            ->all();

        return $comments;
    }

    //用于视频播放页添加评论的api
    public function actionGetvideolikes()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $videoID = \Yii::$app->request->get('video_id');
        $likes = VideoLikes::find()
            ->where(['video_id' => $videoID])
            ->one();
        if ($likes === null) {
            // 如果没有找到点赞记录，则创建一条
            $likes = new VideoLikes();
            $likes->video_id = $videoID;
            $likes->likes = 0;
            $likes->save();
        }
        return $likes->likes;
    }

    //增加视频点赞数
    public function actionAddvideolikes()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $videoID = \Yii::$app->request->get('video_id');
        // 查找或创建点赞记录
        $num = \Yii::$app->request->get('num');
        $likes = VideoLikes::find()
            ->where(['video_id' => $videoID])
            ->one();
        if ($likes === null) {
            // 如果没有找到，创建新的记录
            $likes = new VideoLikes();
            $likes->video_id = $videoID;
            $likes->likes = 0;
            $likes->save();
        }
        // 更新点赞数
        $likes->likes += $num;
        if ($likes->save()) {
            return ['status' => 1, 'message' => 'Likes updated successfully'];
        } else {
            return ['status' => -1, 'message' => 'Failed to update likes'];
        }
    }

    //添加视频评论
    public function actionAddvideocomment()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $username = \Yii::$app->request->get('username');
        $comment = \Yii::$app->request->get('comment');
        $videoID = \Yii::$app->request->get('video_id');
        $comment_date = \Yii::$app->request->get('comment_date');
        $commentModel = new VideoComments();
        $commentModel->username = $username;
        $commentModel->comment = $comment;
        $commentModel->video_id = $videoID;
        $commentModel->comment_date = $comment_date;
        if ($commentModel->save()) {
            return ['status' => 1, 'message' => 'Comment added successfully'];
        } else {
            return ['status' => -1, 'message' => 'Failed to add comment'];
        }
    }


    public function actionGetvideo()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $id = \Yii::$app->request->get('video_id');

        if ($id !== null) {
            // 查询单篇文章
            $video = Videos::find()->select(['id', 'title', 'url', 'created_at', 'updated_at'])
                ->where(['id' => $id])
                ->one();

            // 如果找到该文章，则返回
            if ($video !== null) {
                return $video;
            } else {
                // 如果找不到文章，返回错误消息
                return ['error' => 'Video not found'];
            }
        }
    }

    public function actionGetmembers()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        
        $members = Team::find()
            ->select(['name', 'student_id', 'bio', 'email', 'github', 'image_url'])
            ->all();

        if ($members !== null) {
            return $members;
        } else {
            return ['error' => 'No members found'];
        }
    }

    public function actionGetdescription()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        // 查询网站描述信息
        $website = Website::find()->select(['description'])
                                ->where(['id' => 2])
                                ->one();

        // 如果找到网站内容，则返回
        if ($website !== null) {
            return ['description' => $website->description];
        } else {
            // 如果找不到网站内容，返回错误消息
            return ['error' => 'Website description not found'];
        }
    }


}

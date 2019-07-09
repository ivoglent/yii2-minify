<?php
/**
 * Created by PhpStorm.
 * User: ivoglent
 * Date: 5/3/19
 * Time: 08:52
 */

namespace ivoglent\yii2\minify;


use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\Event;
use yii\base\View;

class Module extends \yii\base\Module implements BootstrapInterface
{
    public $configs = [];
    /**
     * Bootstrap method to be called during application bootstrap stage.
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        $app->setComponents([
            'minifier' => array_merge($this->configs, [
                'class' => Minifier::className(),
            ])
        ]);
        if ($app instanceof \yii\web\Application) {
            $app->view->on(View::EVENT_END_PAGE, [$app->minifier, 'processView']);
            //Html compressing
            $app->response->on(\yii\web\Response::EVENT_BEFORE_SEND, [$app->minifier, 'processResponse']);
        }
        // TODO: Implement bootstrap() method.
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: ivoglent
 * Date: 5/3/19
 * Time: 09:00
 */

namespace ivoglent\yii2\minify;


use ivoglent\yii2\minify\libs\MinifyHtml;
use MatthiasMullie\Minify\CSS;
use MatthiasMullie\Minify\JS;
use yii\base\Component;
use yii\base\Event;
use yii\helpers\Html;
use yii\web\View;
use yii\web\Response;

class Minifier extends Component
{
    public $enabled = false;

    /**
     * @param Event $e
     */
    public function processView(Event $e) {
        if (!$this->enabled) return;
        /**
         * @var $view View
         */
        $view = $e->sender;
        if ($view instanceof View && \Yii::$app->response->format == Response::FORMAT_HTML && !\Yii::$app->request->isAjax && !\Yii::$app->request->isPjax) {
            \Yii::beginProfile('yii2minifierassets');
            //$this->_processing($view);
            foreach ($view->jsFiles as $position => &$files) {
                if (empty($files)) continue;
                $minifiedFile = $this->minifyJsFiles(array_keys($files));
                $files = [
                    $minifiedFile => Html::script('', [
                        'src' => $minifiedFile
                    ])
                ];
            }
            $cssFiles = array_keys($view->cssFiles);
            if (!empty($cssFiles)) {
                $minifiedFile = $this->minifyCssFiles($cssFiles);
                $view->cssFiles = [
                    $minifiedFile => Html::cssFile($minifiedFile)
                ];
            }

            \Yii::endProfile('yii2minifierassets');
        }
        //TODO:: Think about it
        if (\Yii::$app->request->isPjax && $this->configs['noIncludeJsFilesOnPjax']) {
            \Yii::$app->view->jsFiles = null;
        }
    }

    /**
     * @param $files
     * @return string
     */
    public function minifyJsFiles($files) {
        $assetFolderPath = \Yii::$app->assetManager->basePath . '/compressed/js';
        $assetFolderUrl = \Yii::$app->assetManager->baseUrl . '/compressed/js';
        if (!is_dir($assetFolderPath)) {
            mkdir($assetFolderPath, 0777, true);
        }
        $hash = md5(json_encode($files));
        $hashFileName = $assetFolderPath . '/' . $hash . '.js';
        $hashFileUrl = $assetFolderUrl . '/' . $hash . '.js';
        if (!file_exists($hashFileName)) {
            foreach ($files as &$file) {
                $file = \Yii::getAlias('@app/web') . $file;
            }
            $minifier = new JS($files);
            $minifier->minify($hashFileName);
        }
        return $hashFileUrl;


    }

    /**
     * @param $files
     * @return string
     */
    public function minifyCssFiles($files) {
        $assetFolderPath = \Yii::$app->assetManager->basePath . '/compressed/css';
        $assetFolderUrl = \Yii::$app->assetManager->baseUrl . '/compressed/css';
        if (!is_dir($assetFolderPath)) {
            mkdir($assetFolderPath, 0777, true);
        }
        $hash = md5(json_encode($files));
        $hashFileName = $assetFolderPath . '/' . $hash . '.css';
        $hashFileUrl = $assetFolderUrl . '/' . $hash . '.css';
        if (!file_exists($hashFileName)) {
            foreach ($files as &$file) {
                $file = \Yii::getAlias('@app/web') . $file;
            }
            $minifier = new CSS($files);
            $minifier->minify($hashFileName);
        }
        return $hashFileUrl;

    }

    /**
     * @param $js
     * @return string
     */
    public function minifyInlineJs($js) {
        $minifier = new JS();
        $minifier->add($js);
        return $minifier->minify();
    }

    /**
     * @param $html
     * @param array $options
     * @return string
     */
    public function minifyHtml($html, $options = []) {
        return MinifyHtml::minify($html, $options);
    }

    /**
     * @param Event $e
     */
    public function processResponse(Event $e) {
        if (!$this->enabled) return;
        $response = $e->sender;
        if ($response->format == \yii\web\Response::FORMAT_HTML && !\Yii::$app->request->isAjax && !\Yii::$app->request->isPjax) {
            if (!empty($response->data)) {
                $response->data = $this->minifyHtml($response->data, [
                    'jsMinifier' => [$this, 'minifyInlineJs']
                ]);
            }
        }
    }
}
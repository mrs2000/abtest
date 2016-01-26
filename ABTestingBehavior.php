<?php

namespace mrssoft\abtest;

use Yii;
use yii\base\Behavior;
use yii\web\View;

/**
 * Class ABTestingBehavior
 *
 * 'abTest' => [
 *       'class' => 'mrssoft\abtest\ABTesting',
 *       'storage' => 'cookie',
 *       'tableName' => '{{%abtest}}'
 * ],
 * 'view' => [
 *      'class' => 'yii\web\View',
 *      'as abTestBehavior' => [
 *          'class' => 'mrssoft\abtest\ABTestingBehavior',
 *          'componentName => 'abTest'
 *      ]
 * ],
 *
 * @version 1.0.0
 * @author Melnikov R.S. <mrs2000@inbox.ru>
 * @link https://github.com/mrs2000/abtest
 * @copyright 2016 Melnikov R.S.
 * @license MIT
 */
class ABTestingBehavior extends Behavior
{
    public $componentName = 'abTest';

    public function events()
    {
        return [
            View::EVENT_BEGIN_BODY => 'beginBody'
        ];
    }

    public function beginBody()
    {
        if (array_key_exists($this->componentName, Yii::$app->components)) {

            /** @var \yii\web\View $view */
            $view = $this->owner;

            /** @var \mrssoft\abtest\ABTesting $abTesting */
            $abTesting = Yii::$app->{$this->componentName};

            foreach ($abTesting->getData() as $testName => $variant) {
                $js = 'var yaParams={"' . $testName . '":"' . chr($variant + 64) . '"};';
                $view->registerJs($js, View::POS_BEGIN, 'abTest');
            }
        }
    }
}
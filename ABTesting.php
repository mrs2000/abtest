<?

namespace mrssoft\abtest;

use Yii;
use yii\base\Component;
use yii\db\Query;
use yii\web\Cookie;

/**
 * Component for A/B test
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
 * if (Yii::$app->abTest->isB('my-test-name')) {
 *      echo 'variant A'
 * }
 *
 *
 * @version 1.0.0
 * @author Melnikov R.S. <mrs2000@inbox.ru>
 * @link https://github.com/mrs2000/abtest
 * @copyright 2016 Melnikov R.S.
 * @license MIT
 */
class ABTesting extends Component
{
    /**
     * Storage type
     * @var string
     */
    public $storage = 'cookie';

    /**
     * Db table
     * @var string
     */
    public $tableName = '{{%abtest}}';

    private $buffer = [];

    /**
     * Get tests data
     * @return array
     */
    public function getData()
    {
        foreach ($this->findActiveTests() as $test) {
            if (!array_key_exists($test['name'], $this->buffer)) {
                $variant = $this->getVariant($test['name']);
                if ($variant !== null) {
                    $this->buffer[$test['name']] = $variant;
                }
            }
        }

        return $this->buffer;
    }

    /**
     * Test variant A
     * @param string $testName
     * @return bool
     */
    public function isA($testName)
    {
        return $this->isVariant($testName, 1);
    }

    /**
     * Test variant B
     * @param string $testName
     * @return bool
     */
    public function isB($testName)
    {
        return $this->isVariant($testName, 2);
    }

    /**
     * Test variant C
     * @param string $testName
     * @return bool
     */
    public function isC($testName)
    {
        return $this->isVariant($testName, 3);
    }

    /**
     * Test variant
     * @param string $testName
     * @param int $number
     * @return bool
     */
    public function isVariant($testName, $number)
    {
        if (!array_key_exists($testName, $this->buffer)) {
            $variant = $this->getVariant($testName);
            if ($variant === null) {
                $test = $this->findTest($testName);
                if (empty($test)) {
                    $variant = 1;
                    $this->setVariant($testName, $variant);
                } else {
                    $variant = $test['current'] + 1;
                    if ($variant > $test['variants']) {
                        $variant = 1;
                    }
                    $this->setVariant($testName, $variant);
                    $this->updateTest($testName, $variant);
                }
            }

            $this->buffer[$testName] = $variant;
        }

        return $this->buffer[$testName] == $number;
    }

    /**
     * Get variant from storege
     * @param string $testName
     * @return null|string
     */
    private function getVariant($testName)
    {
        $key = $this->getStorageName($testName);
        if ($this->storage == 'cookie') {
            return Yii::$app->request->cookies[$key]->value;
        }
        if ($this->storage == 'session') {
            return Yii::$app->session[$key];
        }

        return null;
    }

    /**
     * Set variant to storage
     * @param string $testName
     * @param int $variant
     */
    private function setVariant($testName, $variant)
    {
        $key = $this->getStorageName($testName);
        if ($this->storage == 'cookie') {
            $cookie = new Cookie([
                'name' => $key,
                'value' => $variant,
                'expire' => time() + 31536000
            ]);
            Yii::$app->response->cookies[$key] = $cookie;
        }
        if ($this->storage == 'session') {
            Yii::$app->session[$key] = $variant;
        }
    }

    /**
     * @param string $testName
     * @return string
     */
    private function getStorageName($testName)
    {
        return 'ab-' . $testName;
    }

    /**
     * Find test data
     * @param string $testName
     * @return array
     */
    private function findTest($testName)
    {
        return (new Query())->select('variants, current')
                             ->from($this->tableName)
                             ->where('public=1 AND name=:name', [':name' => $testName])
                             ->one();
    }

    /**
     * Find all active tests
     */
    private function findActiveTests()
    {
        return (new Query())->select('name')
                            ->from($this->tableName)
                            ->where(['public' => 1])
                            ->all();
    }

    /**
     * Update test current variant
     * @param string $testName
     * @param int $variant
     */
    private function updateTest($testName, $variant)
    {
        Yii::$app->db->createCommand()->update(
            $this->tableName,
            ['current' => $variant],
            ['name' => $testName]
        )->execute();
    }
}
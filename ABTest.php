<?

/**

CREATE TABLE `abtest` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(256) NULL DEFAULT NULL,
    `name` VARCHAR(32) NULL DEFAULT NULL,
    `variants` TINYINT(1) NULL DEFAULT '2',
    `current` TINYINT(1) NULL DEFAULT '0',
    `public` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
    `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `name` (`name`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

 *
 * @version 1.0.0
 * @author Melnikov R.S. <mrs2000@inbox.ru>
 * @link https://github.com/mrs2000/abtest
 * @copyright 2016 Melnikov R.S.
 * @license MIT
 */
class ABTest extends \yii\db\ActiveRecord
{
    public function rules()
    {
        return [
            [['title', 'name'], 'required'],
            [['title', 'name'], 'filter', 'filter' => 'trim'],
            [['variants', 'current', 'public'], 'integer'],
            [
                'name',
                'match',
                'pattern' => '/^([a-zA-Z_])([a-zA-Z0-9_-])*$/',
                'message' => 'Поле «{attribute}» должно содержать: латинские буквы, цифры, знаки _ или -. Первый символ должен быть буквой.'
            ],
            [['variants'], 'integer', 'min' => 2],
            [['title'], 'string', 'max' => 128],
            [['name'], 'string', 'max' => 32],
            [['name'], 'unique'],
            [['id, title, name, variants, public, date'], 'safe', 'on' => 'search'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Название',
            'name' => 'Индетификатор',
            'variants' => 'Количество вариантов',
            'public' => 'Опубликовано',
            'date' => 'Дата',
        ];
    }
}

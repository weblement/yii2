# LinkableBehavior

This behavior provides support for ActiveRecords that have a page to display its contents. The page can be an action in a Module or simply in a Controller. It will be easier to get links related to this record without having to write Url Route over and over again.

## Usage

Add the behavior to your ActiveRecord that can he hotlinked:

```php
namespace app\models;

use yii\db\ActiveRecord;
use weblement\yii2\behaviors\LinkableBehavior;

class Post extends ActiveRecord
{
  //...
  
  public function behaviors()
  {
    return ArrayHelper::merge(parent::behaviors(), [
      // other behaviors
      [
        'class' => LinkableBehavior::className(),
        'route' => '/posts',
        'defaultAction' => 'view',
        'defaultParams' => [
          'id' => function($record) {
            return $record->id;
          },
          'slug' => function($record) {
            return $record->slug;
          }
        ]
      ]
    ]);
  }
}
```

This behavior configuration has a default route to `[/posts/view, 'id' => $record->id, 'slug' => $record->slug]`. In case your action is in a Module, you can set the route to `/module/controller`.

With that code in place, you can now use 3 available methods:
 - `getUrlRoute($action = null, $params = [])`
 - `getWebUrl($action = null, $params = [], $scheme = false)`
 - `getHotlink($action = null, $params = [], $options = [])`
 

#### Examples

Assume that you have an ActiveRecord as follows:

```php
$post = Post::find()->where(['id' => 15, 'slug' => 'this-is-a-post'])->one();

var_dump($post->urlRoute); 
// returns ['/posts/view', 'id' => 15, 'slug' => 'this-is-a-post']

var_dump($post->getUrlRoute('comments', ['order' => SORT_ASC])); 
// returns ['/posts/comments', 'id' => 15, 'slug' => 'this-is-a-post', 'order' => 4]
// SORT_ASC value is 4


var_dump($post->webUrl);
// returns '/posts/15?slug=this-is-a-post'
// the url returned will depends on the configuration of your url rules
// it just pass the urlRoute to the `yii\helpers\Url::to()`


echo $post->hotlink;
// returns <a href="/posts/15?slug=this-is-a-post">This is a post</a>

echo $post->getHotlink('comments', ['order' => SORT_ASC], ['class' => 'btn btn-primary']);
// returns <a href="/posts/15/comments?slug=this-is-a-post&order=4">This is a post</a>

```



 

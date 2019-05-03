 
Yii2 Minify Extension
=====================
An yii2 extension which supported minify html, css and js.

*NOTE : This is basic version without any options/configs . I will improve it later...*

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist ivoglent/yii2-minify "*"
```

or add

```
"ivoglent/yii2-minify": "*"
```

to the require section of your `composer.json` file.


Usage
-----

After installed this extension. Let config for the module:
``` 
 'modules' => [
        //...
        'minify' => [
            'class' => 'ivoglent\yii2\minify\Module'
        ]
        //...
    ]
```

Also we need add minifier module as bootstrap

``` 
'bootstrap' => ['minify'],
```

That's all. Let enjoy!
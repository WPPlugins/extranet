# The Router Package [![Build Status](https://travis-ci.org/joomla-framework/router.png?branch=master)](https://travis-ci.org/joomla-framework/router)

[![Latest Stable Version](https://poser.pugx.org/joomla/router/v/stable)](https://packagist.org/packages/joomla/router)
[![Total Downloads](https://poser.pugx.org/joomla/router/downloads)](https://packagist.org/packages/joomla/router)
[![Latest Unstable Version](https://poser.pugx.org/joomla/router/v/unstable)](https://packagist.org/packages/joomla/router)
[![License](https://poser.pugx.org/joomla/router/license)](https://packagist.org/packages/joomla/router)

## The standard router

### Construction

The standard router optionally takes a `Joomla\Input\Input` object. If not provided, the router will create a new `Input` object which imports its data from `$_REQUEST`.

```php
<?php
use Joomla\Router\Router;

// Create a default web request router.
$router = new Router;

// Create a router by injecting the input.
$router = new Router($application->getInput());
```

### Adding maps

The purpose of a router is to find a controller based on a routing path. The path could be a URL for a web site, or it could be an end-point for a RESTful web-services API.

The `addMap` method is used to map at routing pattern to a controller.

```php
<?php
$router = new Router;
$router->addMap('/article/:article_id', '\\Acme\\ArticleController')
	->addMap('/component/*', '\\Acme\\ComponentFrontController');
```

#### Matching an exact route.

```php
<?php
$router->addMap('/articles', 'ArticlesController');
$controller = $router->getController('/articles');
```

In this case there is an exact match between the route and the map. An `ArticlesController` would be returned by `getController`.

#### Matching any segment with wildcards

```php
<?php
$router->addMap('/articles/*', 'ArticlesController');
$controller = $router->getController('/articles/foo/bar');
```

In this case, the router will match any route starting with "/articles/". Anything after that initial prefix is ignored and the controller would have to inspect the route manually to determine the last part of the route.

```php
<?php
$router->addMap('/articles/*/published', 'PublishedController');
$controller = $router->getController('/articles/foo/bar/published');
```

Wildcards can be used within segments. In the second example if the "/published" suffix is used, a `PublishedController` will be returned instead of an `ArticlesController`.

#### Matching any segments to named variables

```php
<?php
$router->addMap('/articles/*tags', 'ArticlesController');
$controller = $router->getController('/articles/space,apollo,moon');
```
A star `*` followed by a name will store the wildcard match in a variable of that name. In this case, the router will return an `ArticlesController` but it will inject a variable into the input named `tags` holding the value of anything that came after the prefix. In this example, `tags` will be equal to the value "space,apollo,moon".

```php
<?php
$controller = $router->getController('/articles/space,apollo,moon/and-stars');
```

Note, however, all the route after the "/articles/" prefix will be matched. In the second case, `tags` would equal "space,apollo,moon/and-stars". This could, however, be used to map a category tree, for example:

```php
<?php
$controller = $router->getController('/articles/*categories', 'ArticlesController');
$controller = $router->getController('/articles/cat-1/cat-2');
```

In this case the router would return a `ArticlesController` where the input was injected with `categories` with a value of "cat-1/cat-2".

If you need to match the star character exactly, back-quote it, for example:

```php
<?php
$router->addMap('/articles/\*tags', 'ArticlesTagController');
```

#### Matching one segment to a named variable

```php
<?php
$router->addMap('/articles/:article_id', 'ArticleController');
$controller = $router->getController('/articles/1');
```
A colon `:` followed by a name will store the value of that segment in a variable of that name. In this case, the router will return an `ArticleController` injecting `article_id` into the input with a value of "1".

Note that a route of `/articles/1/like` would not be matched. The following cases would be required to match this type of route:

```php
<?php
$router->addMap('/articles/:article_id/like', 'ArticleLikeController');
$router->addMap('/articles/:article_id/*action', 'ArticleActionController');
```

If you need to match the colon character exactly, back-quote it, for example:

```php
<?php
$router->addMap('/articles/\:tags', 'ArticlesTagController');
```

## Installation via Composer

Add `"joomla/router": "~1.0"` to the require block in your composer.json and then run `composer install`.

```json
{
	"require": {
		"joomla/router": "~1.0"
	}
}
```

Alternatively, you can simply run the following from the command line:

```sh
composer require joomla/router "~1.0"
```

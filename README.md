Unit of App
===

 The App will automatically run the endpoint according to the route table.

## Usage

### Instancate

```php
$app = Unit::Instancate('app');
```

### Auto

 Automatically run the endpoint.

```php
$app->Auto();
```

### Layout

 Change layout name.

```php
$app->Layout('white');
```

 Turn off layout.

```php
$app->Layout(false);
```

### Template

 Execute template file.<br/>
 In the template method, $app is automatically available.<br/>

 File search order.

 1. Current directory and that relative path.
 1. Current used layout directory and that relative path.
 1. Template directory and that relative path.

```php
$app->Template('index.phtml');
```

```php
//  index.phtml, Add title.
$app->Title('INDEX');
```

### Args

 Get SmartURL arguments.

```php
$args = $app->Args();
```

### Request

 Get GET or POST values.<br/>
 Since the encoded value can be obtained, XSS does not occur.<br/>

```php
$request = $app->Request();
```

### Title

 Add a page title.<br/>
 You can specify the delimiter at 2nd argument.

```php
$app->Title('Page title');
```

### UserID

 Get Unique User ID

```php
$uid = $app->UserID();
```

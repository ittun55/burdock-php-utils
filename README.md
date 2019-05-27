# Burdock PHP Utilities

## Includs 

 * Job Chaining Utility
 * String Utility
 * Dropbox Utility

## JobChaining

Utilities for Job Chaining.

### What Is It ?

Tool for making functions as a chained process.

### Status

It's not stable because I'm still learning the Functional Programing way.
I might change my mind how to implement job chainer

### Features

 * Chaining job processes.
 * But not strict functional programing way. Just behave like a functor.
 * Allowing multiple arguments.
 * Easy logging

### How to use id.

#### 1. Wrap your job (function) by NamedJob class with job name.

```php
$addOne = new NamedJob('addOne', function($value) {
    return $value + 1;
});

$addTwo = new NamedJob('addTwo', function($value) {
    return $value + 2;
});

$sum = new NamedJob('sum', function($value, ...$args) {
    return array_reduce(array_merge([$value], $args), function($carry, $item) {
        return $carry + $item;
    });
});
```

#### 2. Chain then with Chain::process method.

```php
$chain = (new Chain(55))
     ->process($addOne)
     ->process($addTwo)
     ->process($sum, 3, 4)

echo $chain->getValue() . EOL; // returns 65
```

## Str

Utilities for String.

### randomPassword

## Dbx

Thin wrapper for kunalvarma05/dropbox-php-sdk

## JobChain



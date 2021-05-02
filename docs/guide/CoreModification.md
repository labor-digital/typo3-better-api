# Core Modification

Better API is a package that follows the golden rule: "make your work easier" or as jQuery put it years ago: "write less, do more". To follow this doctrine the
package digs deep into the inner workings of TYPO3. Sadly there are fewer and fewer hooks the closer you come to the TYPO3 "core". Therefore this package has to
extend TYPO3 core classes to work properly.

The modifications are done automatically via a combination of reflection and creative use of PHP's autoload mechanic. No core code is overwritten or changed,
all modifications are done in separate, cached files. The main rule for extending core classes is/was: "Only add hooks - period". Following this there are some
extended classes under ```LaborDigital\Typo3BetterApi\CoreModding\ClassOverrides```
that all extend classes of the native TYPO3 package and adding hooks using the internal event system.

If you find a class in your logs or your backtrace that starts with Extended... and has a name of a well known TYPO3 base class, you see the dynamic extension
working. Note: All extended classes are linked using an alias class back to its original class name, so you can use the extensions without even noticing it.

As an example: The package extends the ```TYPO3\CMS\Core\DataHandling\DataHandler``` class. However, if you create it normally you will notice it still is using
the same class name you would expect, but the parents of the class look differently:

```php
<?php
use TYPO3\CMS\Core\DataHandling\DataHandler;
$dataHandler = new DataHandler();
print_r(get_class($dataHandler));
// Returns TYPO3\CMS\Core\DataHandling\DataHandler
print_r(class_parents($dataHandler));
// Returns a list of
// - LaborDigital\Typo3BetterApi\CoreModding\ClassOverrides\ExtendedDataHandler
// - TYPO3\CMS\Core\DataHandling\T3BA__Copy__DataHandler
```

With this strategy, the package can add hooks even to classes that have static access on methods or properties and we can be sure your x-classes work as they
would normally without any additional changes required.

## ClassOverrideGenerator

The magic above is done by the implementation of the ClassOverrideGenerator. Its usage is fairly simple:

1. make sure the class you want to overwrite is not already loaded via auto-loader or direct include
2. your class has to be loadable using composer's auto-load functionality

If your class matches the criteria you can call the overwrite generator in your code like so. Imagine you have a class like this, which comes from a third-party
package (TYPO3 core or another extension):

```php
<?php
namespace ForeignVendor\ForeignNamespace;
class TargetClass {
    public function foo(){
        // Does fancy stuff
    }

    private function privateBar() {
        // Returns interesting stuff
    }
}
```

To extend the class, you first have to make a new class that contains your extension.

```php
<?php
namespace YourVendor\YourNamespace;
use ForeignVendor\ForeignNamespace\T3BA__Copy__TargetClass;
class ExtendedTargetClass extends T3BA__Copy__TargetClass {
    public function foo(){
        // Do YOUR fancy stuff
        parent::foo();
        // Use private members of the parent without problems
        $this->privateBar();
    }
}
```

After that you can call the override generator like so:

```php
<?php
use LaborDigital\Typo3BetterApi\CoreModding\CodeGeneration\ClassOverrideGenerator;
use ForeignVendor\ForeignNamespace\TargetClass;
use YourVendor\YourNamespace\ExtendedTargetClass;

ClassOverrideGenerator::registerOverride(TargetClass::class, ExtendedTargetClass::class);
```

Now the generator will create a copy of the TargetClass that under the name T3BA__Copy__TargetClass. Your implementation will extend the class copy as a child
class. With that, you can overwrite the parent implementation or extend existing members like you would usually.

Now the magic takes place:

1. All private members of the parent class (methods and properties) are converted into protected members so that you can extend them, too.
2. Your implementation in class ExtendedTargetClass will be aliased with the original class name.

With that in place, every part of the code will now use your implementation instead of the original class.

::: tip Every time you clear the TYPO3 general cache (red bolt) the cached copies are deleted and re-created the next time you run your code
:::

### Caveats

- The extended class is a modified copy of the original class, so your IDE shift-click will not work as expected.
- You will see the extended classes and the copied class names instead of the original class in logs and backtraces.
- Only works for classes that follow the PSR-4 guideline with a single class per file

### Methods

#### registerOverride()

Registers a new class override. The override will completely replace the original source class. The overwritten class will be copied and is available in the
same namespace but with the
"T3BA__Copy__" prefix in front of it's class name. The overwritten class has all it's private properties and function changed to protected for easier overrides.
This method throws an exception if another class already overwrites the class

::: details Arguments

- $classToOverride The name of the class to overwrite with the class given in $classToOverrideWith
- $classToOverrideWith The name of the class that should be used instead of the class defined as $classToOverride
- $overrule If this is set to true already registered overrides can be changed to a different definition
  :::

#### canOverrideClass()

Returns true if the given class can be overwritten with something else

::: details Arguments

- $classToOverride The name of the class to check for
- $withOverrule Set this to true if you want to allow overruling of the existing definition
  :::

#### hasClassOverride()

Returns true if the class with the given name is registered as an override
::: details Arguments

- $classToOverride The name of the class to check for
  :::

## FailsafeWrapper

This class executes the given callable with the given set of arguments. However, it is aware of the TYPO3 bootstrap's "
failsafe" setting. If your given callback fails with an exception and you are running in the install tool, the exception will be ignored. This feature is used
in the package when we extend kernel processes, so we don't break any core workings of TYPO3 with an exception at the wrong place.

```php
<?php
use LaborDigital\Typo3BetterApi\CoreModding\FailsafeWrapper;
FailsafeWrapper::handle(function(){
    // If this code throws an exception in the install-tool it will be ignored
    // Otherwise it will bubble up to the error handler as expected
});
```

## InternalAccessTrait

Designed to work either on your class or in a class overwrite you have generated using the ClassOverrideGenerator. Any class (or child of a class) that uses
this trait provides a set of utilities to access the inner workings of an object without having direct, public access to it.

The provided, public methods are:

- hasProperty()
- getProperty()
- setProperty()
- hasMethod()
- callMethod()

To implement the trait, you have to implement the abstract getExecutionTarget() method. The code of the method is rather simple, for the most part. Just
return "$this," and you should be good to go for 99% of the time.

```php
<?php
use LaborDigital\T3ba\Tool\OddsAndEnds\InternalAccessTrait;
class YourClass {
    use InternalAccessTrait;

    protected $foo = 123;

    protected function getExecutionTarget(){
        return $this;
    }

    private function internalMethod(){
        return 234;
    }
}

$i = new YourClass();
var_dump($i->hasMethod('internalMethod')); // TRUE
var_dump($i->callMethod('internalMethod')); // 234
var_dump($i->getProperty('foo')); // 123
```

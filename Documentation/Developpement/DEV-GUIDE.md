# Development Guidelines

Here are a few rules and explanations that should make it easier for new contributors to understand the structure and guidelines of the code base.

## Naming conventions

### File names

All files should be named with lower case letters and _underlines_ (for word separation).

### Classes, functions and variables

Classes and static functions must have the first letter capitalized letter then use [CamelCase](http://en.wikipedia.org/wiki/CamelCase) for word separation. Non-static functions should only contain _lower case_ letters and numbers and use _underlines_ for word separation. Always define visibility (public, private or protected) for a class function or a class variable.

## Code style

All code obeys the same code style guidelines:
 * Opening and closing brackets for function and class definitions are on a _new line_
 * Never write control-flow code on a line
 * Code should not exceed a limit of 80 characters per line 
 * Never omit brackets for blocks containing only one statement
 * Indentation consists of _4 spaces_ per level and no tabs!
 * Conventional operators should be surrounded by _a space_
 * Commas should be followed by _a space_
 * Semi-colons in for statements should be followed by _a space_
 * All names should be written in English
 * A function should have only one return statement at the end
 * Logical units within a block should be separated by at least one blank line
 * Iterator variables should be called "i", "j", "k", etc.
 * Globals vars (PHP) should be named in _upper case_ but better be avoided.
 * Variables should be initialized where they are declared and they should be declared in the smallest scope possible
 * _In general_, we adhere to [PEAR CS](http://pear.php.net/manual/en/standards.php)

Good code:
```php
    public function foo_bar($aa, $bb)
    {
        $out = '';
    
        if ($aa == 1 || $aa > 10) {
            $out .= "Case one\n";
            write_log("Foo: $aa");
        }
        else {
            alert('Bar');
        }
    
        for ($i=0; $i < $bb; $i++) {
            $out .= $i . 'a';
        }
        return $out;
    }
```

_The bracket indentation of the existing code differs from this example but I guess we should move towards this more common indentation style._

Bad code:
```php
    function fooBar($aa,$bb) {
      if ($aa==1 || $aa>10){
        $out.="Case one\n";
        write_log("Foo: $aa");
      } else {
        alert("Bar");
      }
      for ($i=0;$i<$bb;$i++)
        $out.=$i."a";
    
      return $out;
    }
```

## Comments/Documentation

Please add comments to logical code blocks giving a short description what this part is intended to do.
For PHP classes, please add [PHPDoc](http://www.phpdoc.org) styled descriptions for public methods.

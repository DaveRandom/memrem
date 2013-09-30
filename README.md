memrem
======

Tiny PHP daemon which provides external memory storage API, like memcached

There are some agreements in code. If you're going to modify code, please, 
use them:
* Code type-hinting: it's well-known that PHP has dynamic typing, but in most
  cases you can assume type of a variable in the certain moment. Due to this
  fact I'm using following type-hinting:
  * $iVar   : for integer variables
  * $fVar   : for float variables
  * $sVar   : for string variables
  * $rVar   : for object or resource variables
  * $rgVar  : for array variables
  * $fnVar  : for callable variables (since in PHP arrays could also be callable,
              it is normal to use rg* prefix for such callbacks)
  * $mVar   : for variables of mixed type. Note: it is a case, when type is 
              unknown in certain moments (for example, a function parameter that
              will be converted to some other type, if it is necessary)
* Access level hinting: in PHP there are 3 levels of visibility: public, 
  protected and private. So, to show in code, which access type method/variable
  has, I'm using:
  * __ prefix for private items, like: private function __get_something()
  * _ prefix for protected items, like: protected function _get_something()
  * no prefix for public items, like: public function getSomething()
  Also public items has names with no "_" delimiter with capital letters instead.
  Of cause, code type-hinting and access level hinting should be applied both, 
  so full correct name of protected double property "value" will be $_fValue

FACT-Finder-PHP-Library
=======================

This is a complete rewrite of and will replace the old
[FACT Finder PHP Library](https://github.com/FACT-Finder/FACT-Finder-PHP-Framework).

**The project is currently in beta status while we build a [demo](https://github.com/FACT-Finder/FACT-Finder-PHP-Library-Demo) to showcase how the library should be used.**

**Please do not use it in productive code yet unless you know what you are doing!**


Motivation & Goals
------------------

- No longer support multiple FACT-Finder versions and interfaces at once, as
  legacy code and deep inheritance trees make the project increasingly hard to
  maintain.
- Use the recommended FACT-Finder interface (JSON) instead of providing every
  possibility.
- Make the API clearer and more easily accessible to give developers more
  control over the requests to FACT-Finder.

Coding Conventions
------------------

We follow the [Zend Framework's Coding Standards](http://framework.zend.com/wiki/display/ZFDEV2/Coding+Standards)
(ZF 2) with the following exceptions/additions:

- All files end with a new line character.
- Opening braces for control blocks are always placed on a new line. That means
  the only opening braces placed on the same line are those that open anonymous
  functions.
- Single-line control statements may omit braces IF the condition only consists
  of a single line, too.
- If the first of a two-letter acronym is capitalized in a name, so is the
  second (e.g. getID()).
- All but the simplest regular expressions have to be formatted in free-spacing
  mode and thoroughly commented.

If you want to contribute, please try to adhere to these conventions.

Changes
-------

This list is still subject to change (ironically...).

- only JSON
- only one version per release
- everything is UTF-8
- no more Zend dependency
- added Pimple as a DIC
- namespaces!!! (=> minimum PHP version 5.3)
- alias "FF" for Loader no longer available - has to be created manually with a "use" statement.
- renamed:
-- former underscore convention now replaced with namespaces
-- Configuration -> XmlConfiguration
-- EncodingHandler -> *EncodingConverter
-- Adapter removed from adapter names (it's already in the namespace)
- ParametersParser separated into
-- ParametersConverter - converts parameters back and forth between server and client. This one is mostly irrelevant outside of the library itself.
-- RequestParser - reads parameters and targets from request data
- EncodingConverter now distinguishes between implementations by polymorphism instead of variable method names.
- new Parameters object
- UrlBuilder doesn't take care of parameter management any more - it just hands out a Parameters object to modify.
- UrlBuilder takes care of dispatching between authentication methods now.
- users don't have to deal with dataproviders directly any more. instead they use a RequestFactory.

Documentation ToDos
-------------------

- General documentation (on recommended usage)
-- Page vs Client vs Server
-- Encodings
- Upgrade guide
- Demo documentation using Docco.

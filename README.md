P     H     I     N     G
=========================

  Master: [![Build Status](https://travis-ci.org/phingofficial/phing.png?branch=master)](https://travis-ci.org/phingofficial/phing)

  (PH)ing (I)s (N)ot (G)NU make; it's a PHP project build system or build
  tool based on Apache Ant. You can do anything with it that you could do
  with a traditional build system like GNU make, and its use of simple XML
  build files and extensible PHP "task" classes make it an easy-to-use and
  highly flexible build framework.

  Features include running PHPUnit and SimpleTest unit tests (including test
  result and coverage reports), file transformations (e.g. token replacement,
  XSLT transformation, Smarty template transformations),
  file system operations, interactive build support, SQL execution,
  CVS/SVN/GIT operations, tools for creating PEAR packages, documentation
  generation (DocBlox, PhpDocumentor) and much, much more.

  If you find yourself writing custom scripts to handle the packaging,
  deploying, or testing of your applications, then we suggest looking at Phing.
  Phing comes packaged with numerous out-of-the-box operation modules (tasks),
  and an easy-to-use OO model to extend or add your own custom tasks.

  Phing provides the following features:

  * Simple XML buildfiles
  * Rich set of provided tasks
  * Easily extendable via PHP classes
  * Platform-independent: works on UNIX, Windows, Mac OSX
  * No required external dependencies
  * Built for PHP5

The Latest Version
------------------

  Details of the latest version can be found on the Phing homepage
  <http://www.phing.info/>.

Installation
------------

  1. **PEAR**

  The preferred method to install Phing is through PEAR and the Phing PEAR
  channel. You can install Phing by adding the pear.phing.info channel
  to your PEAR environment and then installing Phing using the *phing*
  channel alias and *phing* package name:

         $ pear channel-discover pear.phing.info
         $ pear install [--alldeps] phing/phing

  2. **Composer**
  
  Install Phing by adding a dependency to
  [phing/phing](https://packagist.org/packages/phing/phing) to the
  require-dev or require section of your project's composer.json
  configuration file, and running 'composer install':

         {
             "require-dev": {
                 "phing/phing": "2.*"
             }
         }

  3. **Phar**
  
  Download the [Phar archive](http://www.phing.info/get/phing-latest.phar).
  The archive can then be executed by running:

         $ php phing-latest.phar

Running the (unit) tests
------------------------

  To succesfully run all Phing tests, the following conditions have to be met:

  * PEAR installed, channel "pear.phing.info" discovered
  * Packages "python-docutils" and "subversion" installed
  * php.ini setting "phar.readonly" set to "Off"

  Then, perform the following steps (on a clone/fork of Phing):

         $ composer install
         $ cd test
         $ ../bin/phing

Documentation
-------------

  Documentation is available in various formats in the *docs/docbook5/en/output*
  directory (generated from DocBook sources located in *docs/docbook5/en/source*).

  For online documentation, you can also visit the Phing website: http://www.phing.info/

Licensing
---------

  This software is licensed under the terms you may find in the file
  named "LICENSE" in this directory.

  Thank you for using PHING!

Contact
-------

  * Twitter: [@phingofficial](http://twitter.com/phingofficial)
  * Website: [http://www.phing.info](http://www.phing.info)
  * IRC:     Freenode, #phing
  * GitHub:  [https://www.github.com/phingofficial/phing](https://www.github.com/phingofficial/phing)
  * E-mail:  [dev-subscribe@phing.tigris.org](mailto:dev-subscribe@phing.tigris.org) (mailing list)

PhpStorm License
----------------

  If you are contributing code to the Phing project and want to use PhpStorm for
  development feel free to ask Ben (bschultz.bb@gmail.com) for our Open Source License.

  Proud to use:

  [![PhpStorm Logo](http://www.jetbrains.com/phpstorm/documentation/phpstorm_banners/phpstorm1/phpstorm468x60_violet.gif "Proud to use")](http://www.jetbrains.com/phpstorm)

  Intelligent PHP IDE for coding, testing and debugging with pleasure

Synch your fork
---------------

  After clone your fork, execute this command only once:

      https://github.com/phingofficial/phing.git


  Everytime that you need to synch your fork with the official repo, execute

      git fetch upstream
      git merge upstream/master

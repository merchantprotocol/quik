```
============================================================================= 
 Version
============================================================================= 
Usage: quik vers [options] <new.value>

 Commands:
  vers:major         Increases the major, resets minor and patch to 0
  vers:minor         Ignores major, increases minor, resets patch to 0
  vers:patch         Ignores major and minor, increases patch by one

Options:
  -h, --help        Show this message
  Just use one of the following options and the version will be automatically increased.

If you enter a number after an option it will override the existing number, leaving other sections untouched.
If current version is 1.1.1 and you enter `quik vers:minor` the new version will be 1.2.0.
If current version is 1.1.1 and you enter `quik vers:minor 4` the new version will be 1.4.1.
Ignore the options and just enter a new version number as major.minor.patch (e.g. 0.0.1)
If current version is 1.1.1 and you enter `quik vers 1.2.3` the new version will be 1.2.3.
```
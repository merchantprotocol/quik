```
============================================================================= 
 Script
============================================================================= 
 Usage: quik script<subcall> [options]
 When developing there are always commands that need to be run on production. This script
 functionality allows you to create deploy scripts that are specific to a version.

 Commands:
  script                   show help
  script:save:last         Saves the last command that you ran to the next deploy script
  script:edit              Opens nano of the script file
  script:clear             Clear the working command file
  script:history           Displays the last run command, press up and down to cycle commands
  script:history:clear     Displays the last run command, press up and down to cycle commands

 Options:
  -h, --help          Show this message
  -y                  Preapprove the confirmation prompts
```
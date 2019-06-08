```
============================================================================= 
 Staging
============================================================================= 
 Usage: quik staging [options]
 After testing has completed on the staging server then we'll prepare the codebase for release.
   1. The changelog will be updated.
   2. The version number will be updated in the codebase.
   3. Git commit changes
   4. Git TAG codebase

 Commands:
  staging                Show help
  staging:tag <tag>      Creates a clone in a sister directory to this repository. Only <tag> is required

 Options:
  -h, --help             Show this message
  -y                     Preapprove the confirmation prompt.
```
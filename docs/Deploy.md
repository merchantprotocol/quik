```
============================================================================= 
 Deploy
============================================================================= 
 Usage: quik deploy [options]
 Deploying using this tool allows you to eliminate downtime when releasing a new version.
 Completely build a release in it's own contained directory for testing, then symlink it
 to the public production directory for instantaneous rollouts and rollbacks

      ./production/magento2/ (symlink target)
      ./releases/ v2.0.0
                  v2.0.1
                  v2.0.2
      ./test/magento2/ (symlink target)

 Commands:
  deploy                       Show help
  deploy:clone <tag> <repo>    Creates a clone in a sister directory to this repository. Only <tag> is required
  deploy:install               If you're installing manually this will populate all of your files from remote locations
  deploy:view                  Show the rollouts in sequence
  deploy:test                  Symlink this release to the public testing directory
  deploy:rollout               Symlink this release to the public production directory
  deploy:rollback              Remove the existing symlink and rollback to a previous version

 Options:
  --deploy-dir           The base deploy directory to begin the rollout strategy.
  -h, --help             Show this message
  -y                     Preapprove the confirmation prompt.
```
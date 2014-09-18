vTiger Maestrano
================

### For developers
#### Create a patch
This command creates a diff patch from 2 commits ignoring Maestrano configuration
```
$ git diff commit1 commi2 | filterdiff -p 1 -x "maestrano/app/config/*" -x "parent_tabdata.php" -x "tabdata.php" -x "config.inc.php" > vtiger.diff
```

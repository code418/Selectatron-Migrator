# Selectatron Migrator

Migrate from Selectatron to Native EE Multi Relationship field.

### Overview

If you used the Selectatron and selected the option to store relationships as native relationships, you're boned when Upgrading to EE v2.6 and up.

This module will find any Selectatron fields storing as native relationships, and convert them to ExpressionEngine's native relationship fields.

### Usage

1. Backup your data and run this migration module away from a production install.

2. Install the module and visit the module homepage.
3. You should see a list of Selectatron fields with the option to 'Convert to EE Relationship Field'.
4. Click the 'Convert to EE Relationship Field' and the row you selected should be removed. The field will now 
5. be a native EE relationship field.
6. Continue the process till you update/migrate all the fields until you see a 'Migration complete' message.
7. If you receive a 'Cleanup leftovers from the migration' option, please run that option.

File any bug reports to the issue tracker here and I'll do what I can to help.


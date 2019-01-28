# Butler

## Cordelta Digital WordPress workflow manager

## Prerequisite

You'll need these servants installed before butler can do its thing:

- LAMP Stack
- [git](https://git-scm.com/)
- [composer](https://getcomposer.org/)
- [wp-cli](https://wp-cli.org/)
- (Optional for linux-based systems) [virtualhost script](https://github.com/RoverWire/virtualhost)

Make sure you can access `git`, `composer` and `wp` commands from anywhere.

# Getting started

**Clone this repo**

```bash
git clone https://seanwuapps@bitbucket.org/cordeltadigital/butler.git
```

**Install dependencies**

```bash
composer install
```

**Make `butler` command availabe everywhere**

Create an alias like this in your `.bashrc` or `.bash_profile`

```bash
alias butler="php /path/to/butler/butler.php "
```

After `source` or restarting terminal, you should be able to run this command anywhere in your file system.

```bash
butler list # show list of commands available
```

## Initialise project from scratch

**1. Bitbucket**

Please ensure you're added in Cordelta Digital's team account in bitbucket, and a repo has been created to keep track of files.

**2. Create your working folder**

```bash
mkdir xxxx.cordelta.digital
cd xxxx.cordelta.digital
```

**3. Run butler**

```bash
butler init
```

Butler will ask you a bunch of quesitons to initiate a brand new wp site.

**4. Create virualhost for local dev**
If you have installed the virtualhost script, you can do

```bash
virutalhost create <domain> <absolute/path/to/folder>
```

### Starting from an existing git repository

**1. Get latest code**
Get the latest code from existing repo with `git pull`

**2. Tell butler to takeover**
Run

```bash
butler takeover # Localise existing wp installation
```

from the root folder, butler will localise the wp installation.

If there is a `sql/export.sql` file in the root folder, butler will import it into the database (by default, butler uses domain to create the database) and replace site url.

Again, if you haven't already, you can use `virualhost` script to create the local virual host for local development.

## TODO

- Pipeline to do continuous deployment to DO
- Import site from Flywheel
- Install themes from Cordelta Digital collection
- Starter templates
  - Child-theme

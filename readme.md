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

## Getting started

Clone this repo

```bash
git clone https://seanwuapps@bitbucket.org/cordeltadigital/butler.git
```

Install dependencies

```bash
composer install
```

### Initialise project from scratch

**1. Bitbucket**

Please ensure you're added in Cordelta Digital's team account in bitbucket, and a repo has been created to keep track of files.

**2. Create your working folder**

```
mkdir xxxx.cordelta.digital
cd xxxx.cordelta.digital
```

**3. Run butler**

```
butler init
```

Butler will ask you a bunch of quesitons to initiate a brand new wp site.

**4. Create virualhost for local dev**
If you have installed the virtualhost script, you can do

```
virutalhost create <domain> <absolute/path/to/folder>
```

### Starting from an existing git repository

**1. Get latest code**
Get the latest code from existing repo with `git pull`

**2. Tell butler to takeover**
Run

```
butler takeover
```

from the root folder, butler will localise the wp installation.

If there is a `sql/export.sql` file in the root folder, butler will import it into the database (by default, butler uses domain to create the database) and replace site url.

Again, you can use `virualhost` script to create the local virual host for local development.

## TODO

- Import site from Flywheel
- Install themes from Cordelta Digital collection
- Starter templates
  - Child-theme

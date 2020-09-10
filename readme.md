# Butler

## Cordelta Digital WordPress workflow manager

## Prerequisite

You'll need these servants installed before butler can do its thing:

- Docker
- Lando

Check out [.lando.yml](./src/stubs/setup/.lando.yml) as a starting point. 


Make sure you can access `git`, `composer` and `wp` commands from anywhere.


## Download and install

```bash
# Download binary
curl -O https://raw.githubusercontent.com/Cordeltadigital/butler/master/bin/butler.phar

# Move it into local/bin folder 
mv butler.phar /usr/local/bin/butler

# Make it executable
chmod +x /usr/local/bin/butler
```

Check if butler is properly installed by executing `list` command

```bash
# show list of commands available
butler list 
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

## Starting from an existing git repository

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

## Initiate site on Dev server

The Cordelta Digital dev box (dev.cordelta.digital) has been set up with a butler user, ask Sean to grant you ssh access if you need one.

Then you can do `butler init` in dev box with ssh access.

Also ask Sean to create the desired subdomain (e.g. example.cordelta.digial) and point it to the dev box (dev.cordelta.digital)

## TODO

- Pipeline to do continuous deployment to DO
- Import site from Flywheel
- Install themes from Cordelta Digital collection
- Starter templates
  - Child-theme

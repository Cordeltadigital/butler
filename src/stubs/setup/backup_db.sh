echo "Database backup script started"
cd /var/www/@site_slug@

wp db export --add-drop-table --extended-insert=FALSE ./sql/export.sql

git add ./sql/export.sql
git commit -m "[Butler] db:sync"
git push origin master

echo "Database backup script finished execution"
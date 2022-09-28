# run.sh

# Path to you project root
PROJ_ROOT=..

# First start the DB container
docker run -d -e MYSQL_ROOT_PASSWORD=root --name mrpp-db mrpp-db-image

# Then the app container, and link it to the DB one
docker run -d \
    -p 8081:80 \
    -v "$PROJ_ROOT":/var/www/html \
    --name mrpp-app \
    --link mrpp-db \
    mrpp-app-image
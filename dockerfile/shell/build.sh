# build.sh

# Path to you project root
PROJ_ROOT=..

# Build the app container image
docker build -t mrpp-app-image -f "../Dockerfile-app" "$PROJ_ROOT"

# Build the DB container image
docker build -t mrpp-db-image -f "../Dockerfile-db" "$PROJ_ROOT"


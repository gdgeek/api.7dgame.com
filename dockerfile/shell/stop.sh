set -e

echo "Stopping containers..."
docker stop mrpp-db mrpp-app

echo "Removing containers..."
docker rm mrpp-db mrpp-app

echo "Done."
set -e

echo "Stopping containers..."
docker stop api-mrpp

echo "Removing containers..."
docker rm api-mrpp

echo "Done."
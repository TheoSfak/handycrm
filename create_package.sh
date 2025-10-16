#!/bin/bash
# HandyCRM v1.2.0 - Installation Package Creator
# Creates a clean distribution package without development files

VERSION="1.2.0"
PACKAGE_NAME="HandyCRM-v${VERSION}"
BUILD_DIR="build"
DIST_DIR="${BUILD_DIR}/${PACKAGE_NAME}"

echo "=========================================="
echo "HandyCRM v${VERSION} - Package Creator"
echo "=========================================="

# Clean previous builds
echo "Cleaning previous builds..."
rm -rf "${BUILD_DIR}"
mkdir -p "${DIST_DIR}"

# Copy application files
echo "Copying application files..."
cp -r api "${DIST_DIR}/"
cp -r backups "${DIST_DIR}/"
cp -r classes "${DIST_DIR}/"
cp -r config "${DIST_DIR}/"
cp -r controllers "${DIST_DIR}/"
cp -r database "${DIST_DIR}/"
cp -r helpers "${DIST_DIR}/"
cp -r languages "${DIST_DIR}/"
cp -r migrations "${DIST_DIR}/"
cp -r models "${DIST_DIR}/"
cp -r public "${DIST_DIR}/"
cp -r scripts "${DIST_DIR}/"
cp -r uploads "${DIST_DIR}/"
cp -r views "${DIST_DIR}/"

# Copy root files
echo "Copying root files..."
cp .htaccess "${DIST_DIR}/"
cp index.php "${DIST_DIR}/"
cp install.php "${DIST_DIR}/"
cp router.php "${DIST_DIR}/"
cp VERSION "${DIST_DIR}/"
cp LICENSE "${DIST_DIR}/"
cp README.md "${DIST_DIR}/"
cp CHANGELOG.md "${DIST_DIR}/"
cp RELEASE_NOTES_v1.2.0.md "${DIST_DIR}/"

# Create empty directories with .gitkeep
echo "Creating required empty directories..."
mkdir -p "${DIST_DIR}/uploads/projects"
mkdir -p "${DIST_DIR}/backups"
touch "${DIST_DIR}/uploads/projects/.gitkeep"
touch "${DIST_DIR}/backups/.gitkeep"

# Remove development/test files
echo "Removing development files..."
rm -f "${DIST_DIR}/test_utf8.php"
rm -f "${DIST_DIR}/TESTING_GUIDE.md"
rm -rf "${DIST_DIR}/.git"
rm -f "${DIST_DIR}/.gitignore"

# Set proper permissions
echo "Setting permissions..."
find "${DIST_DIR}" -type d -exec chmod 755 {} \;
find "${DIST_DIR}" -type f -exec chmod 644 {} \;
chmod 755 "${DIST_DIR}/install.php"
chmod 755 "${DIST_DIR}/index.php"
chmod 777 "${DIST_DIR}/uploads"
chmod 777 "${DIST_DIR}/uploads/projects"
chmod 777 "${DIST_DIR}/backups"
chmod 777 "${DIST_DIR}/config"

# Create ZIP archive
echo "Creating ZIP archive..."
cd "${BUILD_DIR}"
zip -r "${PACKAGE_NAME}.zip" "${PACKAGE_NAME}" -q
cd ..

echo "=========================================="
echo "Package created successfully!"
echo "Location: ${BUILD_DIR}/${PACKAGE_NAME}.zip"
echo "Size: $(du -h "${BUILD_DIR}/${PACKAGE_NAME}.zip" | cut -f1)"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Test installation from ZIP"
echo "2. Create GitHub release at:"
echo "   https://github.com/TheoSfak/handycrm/releases/new"
echo "3. Upload ${PACKAGE_NAME}.zip as release asset"
echo "4. Copy contents from RELEASE_NOTES_v1.2.0.md to release description"
echo "=========================================="

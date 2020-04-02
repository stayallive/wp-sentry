#!/bin/bash

set -e

SVN_URL="https://plugins.svn.wordpress.org/wp-sentry-integration/"
TMP_DIR="/tmp/wordpress-wp-sentry-plugin-svn"

# If there is no release version ask for it
if [[ -z "${RELEASE_VERSION}" ]]; then
    # Get the latest tag so we can show it
    GIT_LATEST="$(git describe --abbrev=0 --tags)"

    # Read the version we are going to release
    echo "?> Latest Git tag is: ${GIT_LATEST}"
    read -p "?> Specify the release version (ex: 2.0.0): " RELEASE_VERSION
else
    echo "!> Using release version from environment variable"
fi

# For CI builds get the credentials sotred
if [[ -z "${SVN_USERNAME}" ]]; then
    echo "!> Using SVN credentials stored on system or supplied interactive"
else
    echo "!> Using SVN credentials from environment variables"
fi

echo ""
echo "-----------------------------------------------------"
echo "=> Starting release of version v${RELEASE_VERSION}"
echo "   To SVN repository hosted on: ${SVN_URL}"
echo "   Using temporary folder: ${TMP_DIR}"
echo "-----------------------------------------------------"
echo ""

./bin/scope-vendor.sh

# Cleanup the old dir if it is there
rm -rf /tmp/wordpress-wp-sentry-plugin-svn

echo ""
echo " > Checking out the SVN repository... (this might take a while)"

svn co -q ${SVN_URL} ${TMP_DIR}

echo " > Copying files to trunk"

rsync -Rrd --delete --delete-excluded --exclude-from 'release-exclude.txt' ./ ${TMP_DIR}/trunk/

cd ${TMP_DIR}/

svn status | grep '^!' | awk '{print $2}' | xargs svn delete || true
svn add --force * --auto-props --parents --depth infinity -q

svn status

if [[ -z "${SVN_USERNAME}" ]]; then
    svn commit -m "Syncing v${RELEASE_VERSION} from GitHub"
else
    svn commit --username ${SVN_USERNAME} --password ${SVN_PASSWORD} --non-interactive --no-auth-cache -m "Syncing v${RELEASE_VERSION} from GitHub"
fi

echo " > Creating release tag"

mkdir ${TMP_DIR}/tags/${RELEASE_VERSION}
svn add ${TMP_DIR}/tags/${RELEASE_VERSION}

if [[ -z "${SVN_USERNAME}" ]]; then
    svn commit -m "Creating tag for v${RELEASE_VERSION}"
else
    svn commit --username ${SVN_USERNAME} --password ${SVN_PASSWORD} --non-interactive --no-auth-cache -m "Creating tag for v${RELEASE_VERSION}"
fi

echo " > Copying versioned files to v${RELEASE_VERSION} tag"

svn cp --parents trunk/* tags/${RELEASE_VERSION}

if [[ -z "${SVN_USERNAME}" ]]; then
    svn commit -m "Tagging v${RELEASE_VERSION}"
else
    svn commit --username ${SVN_USERNAME} --password ${SVN_PASSWORD} --non-interactive --no-auth-cache -m "Tagging v${RELEASE_VERSION}"
fi

echo ""
echo "-----------------------------------------------------"
echo "=> Finished releasing version v${RELEASE_VERSION}!"

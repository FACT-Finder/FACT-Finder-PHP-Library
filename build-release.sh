#!/usr/bin/env sh
SCRIPT_PATH="$(dirname "$(readlink -f "$0")")"
pushd ${SCRIPT_PATH} > /dev/null

# Fetch all branches and tags from all remotes.
git fetch --all --prune

# Read current branch
GIT_BRANCH=$(git branch|grep -E '^\*'|awk '{ print $2 }')

git checkout release

# Read version
APP_VERSION=$(grep -E '^Version' CHANGELOG.txt |sort -Vr|head -n1|awk '{ print $2 }')

# Build and commit documentation
phpdoc -d src/FACTFinder -t docs --template clean --sourcecode
git add --all docs
git commit -m "Update documentation for release ${APP_VERSION}"
git push origin release

# Prepare and push master branch
git checkout master
git merge --no-ff --log release
git push origin master

# Prepare and push src-only branch
git subtree split --prefix=src --onto=src-only --branch=src-only
git push origin src-only

# Create release tags
git tag ${APP_VERSION}
git tag ${APP_VERSION}+src src-only

git push --tags origin

# Move back to original branch
git checkout ${GIT_BRANCH}

popd > /dev/null

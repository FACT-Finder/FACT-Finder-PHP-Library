# This script assumes that the 'release' branch is actually ready for release.
# That includes merging in all relevant changes for the release (in particular
# the 'dev' branch), and adding a new entry to CHANGELOG.txt. The latter is very
# important as the target version will be read from that file.
# You do NOT need to build the documentation yet. This script takes care of
# that.

# Make the project root directory the working directory (regardless of where the
# script has been called from).

$scriptPath = $MyInvocation.MyCommand.Path
$dir = Split-Path $scriptPath
Push-Location $dir

git checkout release

# Read version

$version = Select-String -Pattern "Version\s+([\d\w.-]+)" -List .\CHANGELOG.txt `
    | %{$_.matches[0].groups[1].value}

# Build and commit documentation

phpdoc -d src/FACTFinder -t docs --template clean --sourcecode
git add --all docs
git commit -m "Update documentation for release $version"

Pop-Location

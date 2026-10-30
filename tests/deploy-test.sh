#!/usr/bin/env bash
# Tests bin/deploy end to end with local Git repositories: origin (bare), the developer's clone and the server
# checkout. Fake ssh and composer commands on PATH run the remote part locally.

set -euo pipefail

deploy="$(cd "$(dirname "$0")/.." && pwd -P)/bin/deploy"
work=$(mktemp -d)
trap 'rm -rf "$work"' EXIT
work=$(cd "$work" && pwd -P)
failures=0

export HOME="$work/home"
export GIT_CONFIG_GLOBAL="$work/gitconfig"
export GIT_CONFIG_NOSYSTEM=1
mkdir "$HOME" "$work/bin"
git config --global user.name 'Test Developer'
git config --global user.email 'developer@example.com'
git config --global init.defaultBranch main
git config --global advice.detachedHead false

# ssh: records the host and runs the command locally, without the login profile of the test machine
cat > "$work/bin/ssh" <<'SSH'
#!/usr/bin/env bash
while [[ $1 == -* ]]; do shift; done
printf '%s\n' "$1" >> "$FAKE_SSH_LOG"
shift
command="$*"
# Chroot shells without date (e.g. Plesk)
if [[ ${FAKE_NO_DATE:-} == 1 ]]; then
  date() {
    echo 'bash: date: command not found' >&2
    return 127
  }
  export -f date
fi
exec bash -c "${command/#bash -lc /bash -c }"
SSH
cat > "$work/bin/composer" <<'COMPOSER'
#!/usr/bin/env bash
printf 'composer %s\n' "$*" >> "$FAKE_COMPOSER_LOG"
COMPOSER
chmod +x "$work/bin/ssh" "$work/bin/composer"
export PATH="$work/bin:$PATH"
export FAKE_SSH_LOG="$work/ssh.log" FAKE_COMPOSER_LOG="$work/composer.log"
touch "$FAKE_SSH_LOG" "$FAKE_COMPOSER_LOG"

dev="$work/dev"
server="$work/server"

fail_test() {
  printf 'FAIL: %s\n' "$*"
  printf '%s\n' '--- output ---' "$(cat "$work/out")" '--------------'
  failures=$((failures + 1))
}

# Runs deploy in the developer's clone; output in $work/out
run_deploy() {
  (cd "$dev" && "$deploy" "$@") > "$work/out" 2>&1 < /dev/null
}

expect_success() {
  local name=$1
  shift
  if run_deploy "$@"; then echo "ok: $name"; else fail_test "$name: deploy failed"; fi
}

expect_failure() {
  local name=$1 message=$2
  shift 2
  if run_deploy "$@"; then
    fail_test "$name: deploy succeeded"
  elif ! grep -qF -- "$message" "$work/out"; then
    fail_test "$name: output does not contain \"$message\""
  else
    echo "ok: $name"
  fi
}

check() {
  local name=$1
  shift
  if "$@"; then echo "ok: $name"; else fail_test "$name"; fi
}

time_pattern='[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}Z'

server_head() {
  git -C "$server" rev-parse HEAD
}

origin_commit() {
  git -C "$dev" rev-parse "$1^{commit}"
}

write_config() {
  local default_ref=$1 after=${2:-'["touch after-ran"]'} clear=${3:-'["app/cache/v[0-9]*/"]'}
  cat > "$dev/deploy.json" <<JSON
{
  "host": "test-server",
  "path": "$server",
  "defaultRef": "$default_ref",
  "clearDirectories": $clear,
  "after": $after
}
JSON
}

commit_tag() {
  echo "$1" > "$dev/version.txt"
  git -C "$dev" add version.txt
  git -C "$dev" commit --quiet -m "release $1"
  git -C "$dev" tag -a "$1" -m "$1"
}

# --- Repositories ---

git init --quiet --bare "$work/origin.git"
git clone --quiet "$work/origin.git" "$dev" 2> /dev/null
printf '/app/cache\n/after-ran\n' > "$dev/.gitignore"
echo '{}' > "$dev/composer.json"
git -C "$dev" add .gitignore composer.json
git -C "$dev" commit --quiet -m 'initial'
git -C "$dev" push --quiet origin main
git clone --quiet "$work/origin.git" "$server"
write_config latest-tag

printf '/app/cache\n/after-ran\n/deploy.log\n' > "$dev/.gitignore"
git -C "$dev" add .gitignore
git -C "$dev" commit --quiet -m 'ignore deploy.log'
commit_tag v1.0.0
commit_tag v1.2.0
commit_tag v1.10.0
commit_tag v2.0.0-rc.1
git -C "$dev" push --quiet --tags origin main

# --- Configuration ---

expect_failure 'deploy.log must be ignored' 'deploy.log is not ignored' --yes
check 'server unchanged without ignored deploy.log' test "$(server_head)" = "$(git -C "$dev" rev-parse main~5)"
git -C "$server" pull --quiet --ff-only

: > "$FAKE_SSH_LOG"
write_config latest-tag '[]' '["../cache/"]'
expect_failure 'clear: no ..' 'must not contain empty, ., .. or .git segments' --yes
write_config latest-tag '[]' '["/tmp/cache/"]'
expect_failure 'clear: no absolute path' 'must be relative to the project root' --yes
write_config latest-tag '[]' '["*/cache/"]'
expect_failure 'clear: first directory named' 'must be named without wildcards' --yes
write_config latest-tag '[]' '["[a]pp/cache/"]'
expect_failure 'clear: first directory without class' 'must be named without wildcards' --yes
write_config latest-tag '[]' '["app/{cache}/"]'
expect_failure 'clear: no braces' 'may only contain' --yes
write_config latest-tag '[]' '[""]'
expect_failure 'clear: no empty glob' 'may only contain' --yes
write_config latest-tag '[]' '["app/c ache/"]'
expect_failure 'clear: no spaces' 'may only contain' --yes
write_config 'latest-tag", "port": "22' '[]'
expect_failure 'unknown key' 'unknown key "port"' --yes
write_config latest-tag '[]'
sed -i.bak 's/"test-server"/"deploy@test-server"/' "$dev/deploy.json"
expect_failure 'host is an alias' 'host must be an SSH alias' --yes
write_config 'main..x'
expect_failure 'defaultRef is a branch name' 'defaultRef must be latest-tag or a branch name' --yes
echo '{' > "$dev/deploy.json"
expect_failure 'invalid JSON' 'deploy.json is not valid JSON' --yes
check 'no SSH call with an invalid configuration' test ! -s "$FAKE_SSH_LOG"

# --- Deployments ---

write_config latest-tag
mkdir -p "$server/app/cache/v1/sub" "$server/app/cache/v2" "$work/outside/v3"
mkdir -p "$server/app/cache/values"
touch "$server/app/cache/v1/sub/file" "$server/app/cache/keep" "$server/app/cache/values/token" "$work/outside/v3/file"
ln -s "$work/outside/v3" "$server/app/cache/v3"

expect_failure 'confirmation without terminal' 'no terminal to confirm; use --yes'
expect_success 'latest tag' --yes
check 'latest tag: highest version, no pre-release' test "$(server_head)" = "$(origin_commit v1.10.0)"
check 'latest tag: detached' test "$(git -C "$server" symbolic-ref -q HEAD || echo detached)" = detached
check 'host from deploy.json' grep -qx 'test-server' "$FAKE_SSH_LOG"
check 'composer install' grep -qx 'composer install --no-dev --optimize-autoloader --no-interaction' \
  "$FAKE_COMPOSER_LOG"
check 'cleared directories' test ! -e "$server/app/cache/v1" -a ! -e "$server/app/cache/v2"
check 'kept other files' test -f "$server/app/cache/keep"
check 'kept FileCache (values)' test -f "$server/app/cache/values/token"
check 'symlink removed, target kept' test ! -e "$server/app/cache/v3" -a -f "$work/outside/v3/file"
check 'after command' test -f "$server/after-ran"
log_line=$'\tok\tv1.10.0\t'"$(origin_commit v1.10.0)"$'\t'"$(origin_commit main)"$'\tTest Developer <developer@example.com>$'
check 'log line' grep -q "$log_line" "$server/deploy.log"
check 'log line: UTC time' grep -qE "^$time_pattern"$'\tok\tv1.10.0\t' "$server/deploy.log"
check 'rollback hint' grep -qF "Roll back with: vendor/bin/deploy $(origin_commit main)" "$work/out"

expect_success 'tag as rollback' --yes v1.2.0
check 'tag as rollback: commit' test "$(server_head)" = "$(origin_commit v1.2.0)"
check 'tag as rollback: removed commits shown' grep -qF 'Removed commits (1):' "$work/out"

expect_success 'commit hash' --yes "$(origin_commit v1.0.0)"
check 'commit hash: commit' test "$(server_head)" = "$(origin_commit v1.0.0)"

# A tag moved on origin: the server and the developer's clone still have the old one
stale_commit=$(origin_commit v1.2.0)
git -C "$dev" tag -f -a v1.2.0 -m v1.2.0 "$(origin_commit v1.10.0)" > /dev/null
git -C "$dev" push --quiet --force origin v1.2.0
git -C "$dev" tag -f -a v1.2.0 -m v1.2.0 "$stale_commit" > /dev/null
expect_success 'moved tag' --yes v1.2.0
check 'moved tag: commit of origin' test "$(server_head)" = "$(origin_commit v1.10.0)"
check 'moved tag: updated locally' test "$(origin_commit v1.2.0)" = "$(origin_commit v1.10.0)"

git -C "$server" remote set-url origin "$work/missing.git"
expect_failure 'fetch error shown' 'does not appear to be a git repository' --yes v1.0.0
git -C "$server" remote set-url origin "$work/origin.git"

echo 'unpushed' > "$dev/unpushed.txt"
git -C "$dev" add unpushed.txt
git -C "$dev" commit --quiet -m 'unpushed'
expect_failure 'unpushed commit' 'is not on origin; push it first' --yes "$(git -C "$dev" rev-parse --short HEAD)"
expect_failure 'unknown ref' 'is no tag or branch on origin and no commit hash' --yes 'no-such-ref'
git -C "$dev" reset --quiet --hard HEAD~1

echo 'changed' >> "$server/composer.json"
expect_failure 'local changes on the server' 'the working tree on the server has local changes' --yes
check 'local changes on the server: unchanged' test "$(server_head)" = "$(origin_commit v1.10.0)"
git -C "$server" checkout --quiet -- composer.json

write_config main
expect_success 'default branch' --yes
check 'default branch: commit' test "$(server_head)" = "$(origin_commit origin/main)"
check 'default branch: on branch' test "$(git -C "$server" symbolic-ref --short HEAD)" = main

# The printf builtin of bash 4.2+ writes the time without date
if bash -c '(( BASH_VERSINFO[0] * 100 + BASH_VERSINFO[1] >= 402 ))'; then
  export FAKE_NO_DATE=1
  expect_success 'without date' --yes v1.2.0
  unset FAKE_NO_DATE
  check 'without date: time logged' grep -qE "^$time_pattern"$'\tok\tv1.2.0\t' <(tail -n 1 "$server/deploy.log")
else
  echo 'skip: without date (bash older than 4.2)'
fi

write_config main '["touch after-ran", "false"]'
expect_failure 'failing after command' 'deployment failed' --yes v1.2.0
check 'failing after command: logged' grep -q $'\tfailed\tv1.2.0\t' "$server/deploy.log"

rm -rf "$server/app/cache"
ln -s "$work/outside" "$server/app/cache"
write_config main '[]'
expect_failure 'symlinked parent outside the project' 'outside the project root' --yes v1.10.0
check 'symlinked parent: nothing deleted' test -f "$work/outside/v3/file"

if [[ $failures != 0 ]]; then
  echo "$failures test(s) failed"
  exit 1
fi
echo 'All tests passed'

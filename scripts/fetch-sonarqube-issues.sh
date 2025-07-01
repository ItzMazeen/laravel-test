#!/bin/sh

# This script fetches all SonarQube issues using paginated API requests

SONAR_HOST_URL="$1"
SONAR_TOKEN="$2"
SONAR_PROJECT_KEY="$3"

# Setup
page=1
page_size=500
output="sonar-report.json"
tmp_output="sonar-page.json"

echo '{"issues":[]}' > "$output"

while true; do
  echo "Fetching page $page..."

  curl -s -u "$SONAR_TOKEN:" "$SONAR_HOST_URL/api/issues/search?componentKeys=$SONAR_PROJECT_KEY&types=BUG,VULNERABILITY,CODE_SMELL&ps=$page_size&p=$page" -o "$tmp_output"

  jq -s '
    {
      issues: (.[0].issues + .[1].issues)
    }
  ' "$output" "$tmp_output" > tmp && mv tmp "$output"

  total=$(jq '.total' "$tmp_output")
  fetched=$(jq '.paging.pageIndex * .paging.pageSize' "$tmp_output")

  if [ "$fetched" -ge "$total" ]; then
    echo "All pages fetched."
    break
  fi

  page=$((page + 1))
done

cat "$output"

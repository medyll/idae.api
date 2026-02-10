#!/usr/bin/env bash
# Examples for idae.api (IDQL)
# Usage: bash examples/curl_examples.sh

BASE_URL="http://localhost:8081"

# 1) Simple find
curl -s -X POST \
  -H "Content-Type: application/json" \
  -d '{"method":"find","scheme":"products","limit":5,"where":{"status":"active"}}' \
  ${BASE_URL}/api/idql/products | jq '.'

# 2) Parallel: find users (limit) + distinct product categories
curl -s -X POST \
  -H "Content-Type: application/json" \
  -d '{"parallel":[{"method":"find","scheme":"users","limit":5,"where":{"active":1}},{"method":"distinct","scheme":"products","distinct":"category","where":{"status":"active"}}],"scheme":"dashboard"}' \
  ${BASE_URL}/api/idql/dashboard | jq '.'

# 3) Group (aggregation example)
curl -s -X POST \
  -H "Content-Type: application/json" \
  -d '{"method":"group","scheme":"orders","group":"iddate","where":{"status":"completed"},"limit":10}' \
  ${BASE_URL}/api/idql/orders | jq '.'

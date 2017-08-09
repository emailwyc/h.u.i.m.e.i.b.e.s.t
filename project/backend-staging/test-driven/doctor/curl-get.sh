#!/bin/bash

host='http://127.0.0.1:8089/rpc'
# host='http://api-staging.huimeibest.com/rpc'

url=$host$1
token=$(cat token)

/usr/bin/curl --silent -i -X GET \
    -H "Content-Type: application/json" \
    -H "X-HM-ID: xsh0g8n2d8a7r8k9" \
    -H "X-HM-Sign: 6401ed4bac3c7ff2d427e8677bb56b97,1234567890123" \
    -H "X-HM-Session-Token: $token" \
    -H "X-Hm-App-Version: 1.0.0" \
    "$url"

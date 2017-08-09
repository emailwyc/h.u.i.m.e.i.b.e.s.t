#!/bin/bash

url='/captcha/verify'

json=$(cat <<JSON
{
    "mobile": "13810261155",
    "captcha": "$1"
}
JSON
)

./curl.sh "$json" "$url"

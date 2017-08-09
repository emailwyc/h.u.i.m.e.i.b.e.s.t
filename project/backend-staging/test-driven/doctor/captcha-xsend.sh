#!/bin/bash

url='/captcha/xsend'

json=$(cat <<JSON
{
    "mobile": "13810261155"
}
JSON
)

./curl.sh "$json" "$url"

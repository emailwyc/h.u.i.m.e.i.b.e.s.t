#!/bin/bash

url='/user/password/reset'

json=$(cat <<JSON
{
    "password": "password-x",
    "csrf_token": "$1"
}
JSON
)

./curl.sh "$json" "$url"

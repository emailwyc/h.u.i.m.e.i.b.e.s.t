#!/bin/bash

url='/user/password/update'

json=$(cat <<JSON
{
    "password_old": "$1",
    "password_new": "$2"
}
JSON
)

./curl.sh "$json" "$url"

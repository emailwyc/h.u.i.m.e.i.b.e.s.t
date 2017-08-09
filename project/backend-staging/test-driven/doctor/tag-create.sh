#!/bin/bash

url='/tag/create'

if [[ $# -lt 1 ]]; then
    echo "usage: $0 name"
    exit 1
fi

json=$(cat <<JSON
{
    "name": "$1"
}
JSON
)

./curl.sh "$json" "$url"

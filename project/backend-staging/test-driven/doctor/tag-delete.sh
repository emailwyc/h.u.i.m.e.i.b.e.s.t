#!/bin/bash

url='/tag/delete'

if [[ $# -lt 1 ]]; then
    echo "usage: $0 id"
    exit 1
fi

json=$(cat <<JSON
{
    "id": "$1"
}
JSON
)

./curl.sh "$json" "$url"

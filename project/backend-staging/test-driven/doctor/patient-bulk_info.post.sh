#!/bin/bash

url='/patient/bulk_info'

if [[ $# -lt 2 ]]; then
    echo "usage: $0 id id"
    exit 1
fi

json=$(cat <<JSON
{
    "ids": ["$1", "$2"]
}
JSON
)

./curl.sh "$json" "$url"

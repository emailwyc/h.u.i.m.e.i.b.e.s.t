#!/bin/bash

url='/template/update'

if [[ $# -lt 2 ]]; then
    echo "usage: $0 id name"
    exit 1
fi

json=$(cat <<JSON
{
    "id": "$1",
    "name": "$2"
}
JSON
)

./curl.sh "$json" "$url"

#!/bin/bash

url='/tag/locate'

if [[ $# -lt 1 ]]; then
    echo "usage: $0 patient_id"
    exit 1
fi

json=$(cat <<JSON
{
    "patient_id": "$1"
}
JSON
)

./curl.sh "$json" "$url"

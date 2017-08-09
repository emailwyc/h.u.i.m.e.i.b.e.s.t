#!/bin/bash

url='/tag/transfer'

if [[ $# -lt 3 ]]; then
    echo "usage: $0 id transfer_id patient_id"
    exit 1
fi

json=$(cat <<JSON
{
    "id": "$1",
    "transfer_id": "$2",
    "patient_id": "$3"
}
JSON
)

./curl.sh "$json" "$url"

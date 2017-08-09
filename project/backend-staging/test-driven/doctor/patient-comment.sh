#!/bin/bash

url='/patient/comment'

json=$(cat <<JSON
{
    "patient_id": "$1",
    "comment": "This is a comment."
}
JSON
)

./curl.sh "$json" "$url"

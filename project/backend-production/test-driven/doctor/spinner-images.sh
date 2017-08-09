#!/bin/bash

url='/spinner/images'

json=$(cat <<JSON
{
    "alias": "schedule-top"
}
JSON
)

./curl.sh "$json" "$url"

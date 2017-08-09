#!/bin/bash

url='/doctor/location/delete'

json=$(cat <<JSON
{
    "id": "$1"
}
JSON
)

./curl.sh "$json" "$url"

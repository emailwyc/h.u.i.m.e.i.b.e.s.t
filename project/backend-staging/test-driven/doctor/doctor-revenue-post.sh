#!/bin/bash

url='/doctor/revenue'

json=$(cat <<JSON
{
    "month": "2016-01"
}
JSON
)

./curl.sh "$json" "$url"

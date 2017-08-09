#!/bin/bash

url='/doctor/timetable/delete'

json=$(cat <<JSON
{
    "id": "$1"
}
JSON
)

./curl.sh "$json" "$url"

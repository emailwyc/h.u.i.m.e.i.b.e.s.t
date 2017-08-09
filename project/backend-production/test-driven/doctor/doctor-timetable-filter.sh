#!/bin/bash

url='/doctor/timetable/filter'

json=$(cat <<JSON
{
    "service": "phonecall"
}
JSON
)

./curl.sh "$json" "$url"

#!/bin/bash

url='/doctor/timetable/edit'

json=$(cat <<JSON
{
    "id": "$1",
    "quantity": "12",
    "interval": "08:00,11:00"
}
JSON
)

./curl.sh "$json" "$url"

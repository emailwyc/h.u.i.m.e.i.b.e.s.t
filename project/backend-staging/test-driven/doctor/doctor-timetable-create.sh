#!/bin/bash

url='/doctor/timetable/create'

json=$(cat <<JSON
{
    "service": "clinic",
    "date": "2015-12-15",
    "interval": "08:00,12:00",
    "weekday": "2",
    "price": "200",
    "quantity": "5",
    "location": "$1"
}
JSON
)

./curl.sh "$json" "$url"

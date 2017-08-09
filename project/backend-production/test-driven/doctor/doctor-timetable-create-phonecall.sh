#!/bin/bash

url='/doctor/timetable/create'

json=$(cat <<JSON
{
    "service": "phonecall",
    "date": "2016-01-15",
    "quantity": "5",
    "interval": "08:00,12:00",
    "weekday": "5"
}
JSON
)

./curl.sh "$json" "$url"

#!/bin/bash

url='/doctor/timetable/edit'

json=$(cat <<JSON
{
    "id": "$1",
    "price": "200",
    "quantity": "5",
    "location": "566e880c83cdf83da47cd808"
}
JSON
)

./curl.sh "$json" "$url"

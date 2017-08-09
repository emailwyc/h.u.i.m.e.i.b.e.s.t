#!/bin/bash

url='/doctor/info/speciality'

json=$(cat <<JSON
{
    "speciality": "这是特长内容-测试"
}
JSON
)

./curl.sh "$json" "$url"

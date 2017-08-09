#!/bin/bash

url='/doctor/info'

json=$(cat <<JSON
{
    "name": "王医生",
    "avatar": "http://www.example.com/avatar.jpg",
    "hospital": "北京 xxx 医院",
    "department": "xxx 科",
    "position": "主任医师",
    "title": "教授",
    "speciality": "这是特长内容",
    "description": "这是描述内容"
}
JSON
)

./curl.sh "$json" "$url"

#!/bin/bash

url='/doctor/location/edit'

json=$(cat <<JSON
{
    "id": "$1",
    "hospital": "积水潭医院",
    "branch": "新街口院区",
    "address": "西城区新街口东街31号",
    "info": "1楼2层3号 -- 编辑测试"
}
JSON
)

./curl.sh "$json" "$url"

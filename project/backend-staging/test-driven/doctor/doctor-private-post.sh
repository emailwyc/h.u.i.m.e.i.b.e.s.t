#!/bin/bash

url='/doctor/private'

json=$(cat <<JSON
{
    "idcard": "1000101234567890",
    "certificate": ["http://www.example.com/abc.png", "http://www.x.com/abc.png"],
    "bank_card": {
        "bank": "中国工商银行",
        "city": "北京",
        "branch": "中国工商银行前门支行营业部",
        "card": "95588123400001234"
    },
    "revenue": 1000000
}
JSON
)

./curl.sh "$json" "$url"

#!/bin/bash 
targetpath='/Server/cron/store'  
nowtime=$(date +%Y%m%d)  
   
start(){  
    mongodump --host 10.172.241.100 --port 27017 --out ${targetpath}/${nowtime}  
}  
execute(){  
    start  
    if [ $? -eq 0 ]   
    then  
        echo "back successfully!"  
    else  
        echo "back failure!"  
    fi  
}  
   
if [ ! -d "${targetpath}/${nowtime}/" ]   
then  
    mkdir ${targetpath}/${nowtime}  
fi  
execute  
echo "============== back end ${nowtime} =============="


nowtime=$(date -d '-7 days' "+%Y%m%d")  
if [ -d "${targetpath}/${nowtime}/" ]   
then  
#    rm -rf "${targetpath}/${nowtime}/"  
    echo "=======${targetpath}/${nowtime}/===删除完毕=="  
fi  
echo "===$nowtime ==="  

##################----soone---2016-05-10-------###################

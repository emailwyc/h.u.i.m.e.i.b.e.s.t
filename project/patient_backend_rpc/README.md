#### patient_backend_rpc
patient_backend_rpc : Patient Backend System RPC Service. 

###测试环境
地址：http://h5test.huimeibest.com:8087(此地址和请求URL进行拼接即可)
appkey:'e139d12d1be57e3dd15p3e4ce34bd975';//str

	session_token验证和sign签名验证,version版本验证,login过期验证，
	目前处于关闭状态(此条信息可忽略，客户端正常发送，正常校验)

###正式环境
地址：http://h5.huimeibest.com:8087(此地址和请求URL进行拼接即可)<br>
appkey:'e139d12d1be57e3dd15p3e4ce34bd975';//str


<br>
<br>
##header与说明： 
对于`POST`请求，请求的主体必须是`JSON`格式，而且`HTTP header`的`Content-Type`需要设置为`application/json`。

参数说明

| 参数      | 说明           | 
|:--------:|:-------------:|
| Content-Type | application/json |
| X-HM-ID    | deviceId    设备ID   |
| X-HM-Session-Token    | 验证登录用户的sessionToken，登录成功后缓存本地      |
| X-HM-Endpoint-Agent    | 设备类型，例如：iOS 9.2 iPhone 6s      |
| X-HM-App-Version    | app版本号       (登录时必须传递，否则参数错误)|
| X-HM-Sign    | 将时间戳加上appKey组成的字符串，在对它做MD5签名后的结果(必须)       |

例如:`timestamp:1389085779854``appKey:n35a5fdhawz56y24pjn3u9d5zp9r1nhpebrxyyu359cq0ddo`需要签名的字符串:`1389085779854n35a5fdhawz56y24pjn3u9d5zp9r1nhpebrxyyu359cq0ddo`进行`MD5`签名得到的字符串:`28ad0513f8788d58bb0f7caa0af23400`
和`timestamp`进行拼接得到最终`X-HM-Sign:28ad0513f8788d58bb0f7caa0af23400,1389085779854`

###response 参数说明

| 参数     | 说明                                           | 
|:-------:|:----------------------------------------------:|
| status  | 网络请求状态。0：成功，402:session过期，重新登录，403：授权失败，请重新登录,其他状态都是失败 |
| message | 消息提示                                        |
| data    | 数据字典                                        |


<br>
<br>
<br>
<br>
##1 登录;登出接口
####1.1 接口说明
用户只能通过微信、微博、QQ登录，登录获取的用户信息提交给服务器

####1.2 接口调用请求说明
| 请求方式 | 请求URL    | 
|:-------:|:---------:| 
| post    | /auth/sign_in  | 

####1.3 请求参数说明
| 参数      | 是否必须 | 说明                | 
|:--------:|:-------:|:------------------:|
| platform | 是      | 平台名称             (手机号码登陆platform='hm',即手机号动态登陆)|
| open_id  | 是      | 第三方登录获取的用户id (当platform='hm'时候，open_id=手机号码)|
| avatar   | 是      | 头像链接地址         |
| nickname | 是      | 昵称                (当platform='hm'时候，nickname=手机号码)|
| yzm      | 否      | 短信验证码(当platform='hm'时，该参数为必须参数)                |

####1.4 请求示例
    {
        "platform": "weichat",
        "open_id": "23508u0090r1235124",
        "avatar": "http://image.baidu.com/6fm%p%3D0.jpg",
        "nickname": "guaker",
        "yzm": "1234"
    }
    
####1.5 返回参数说明
| 字段           | 说明                   | 
|:-------------:|:---------------------:|
| id            | 患者id                 | 
| session_token | 用来进行接口访问和多次登录 |
| nickname      | 昵称                   |

####1.6 响应示例
	{
	    "status": 0,
	    "message": "登录成功！",
	    "data": {
	        "patient": "57457256b7ef6a3d3b8b45bf" ,
	        "session_token": "e815e03f841c3be83eee1c1ef79262e83a68ed31",
	        "nickname": "1234567890"
	    }
	}

###1.7 登出接口调用请求说明
| 请求方式 | 请求URL    | 
|:-------:|:---------:| 
| post    | /auth/sign_out  | 

####1.7.1 请求参数说明
header中正常传递seesion_token即可

####1.7.2 请求示例
    {
    }
####1.7.3 响应示例
	{
		"status": 0,
		"message": "退出成功！",
		"data": {}
	}





<br>
##2 文章列表
####2.1 接口说明
获取文章列表(不验证登录),每页10条数据

####2.2 接口调用请求说明
| 请求方式 | 请求URL    | 
|:-------:|:---------:| 
| post    | article/getList| 

####2.3 请求参数说明
| 参数          | 是否必须 | 说明           | 
|:------------:|:------:|:-------------:|
| classes        | 否      | 文章类型。1：梅奥 2：原创 3：精选 (不传该参数默认搜索所有类型文章)|
| doctor       | 否       | 医生id。 (传该参数搜索该医生文章)|
| page         | 是      | 分页时,当前页数（默认每页10条数据）|
| sort         | 否      | 排序字段 {key:value} value=1正序，value=-1倒序(默认按照id倒序排列)|
| search_val         | 否      | 搜索内容 （可以搜索标题，简介）|

####2.4 请求示例
	{   
		"classes": 1,
		"doctor": '_id',
		"page": 1,
		"sort": {"_id":-1}
	}
    
####2.5 返回参数说明
| 字段         | 说明          | 
|:-----------:|:-------------:|
| _id         | 文章id         | 
| title       | 标题           |
| description | 描述           |
| icon        | 头图           |
| link_url    | 链接地址           |
| classes       | 文章分类。1：梅奥 2：原创 3：精选     |
| type        | 文章类型（1：文本类型，2链接类型）|
| like_num    | 点赞数量        |
| comment_num | 评论数量        |
| created_at  | 创建时间        |
| is_likes    | 是否点赞       |
| read_num    | 阅读量       |
| is_reads     | 是否阅读       |
| pubdate     | 发布日期       |
| author| 作者       |
|doctor| 医生id       |
|price | 收费文章价格       |
|is_vip| 文章是否收费，ｔｒｕｅ为收费，ｆａｌｓｅ为免费       |
|read_access|　用户是否有阅读权限，ｔｒｕｅ为可以阅读，ｆａｌｓｅ为没有权限阅读       |

####2.6 响应示例
	{
	    "status": 0,
	    "message": "ok",
	    "data": [
	        {
	            "_id": "5747b6d0c2f9d446db1e6b13" ,
	            "title": "我们身边的“提灯女士”是谁？ ",
	            "description": "我们过了那么多节日，除了情人节，圣诞节等节日，是否还了解有一个叫“护士节”的节日。每年的5月12日是国际护士节。该节是为纪念现代护理学科的创始人—",
	            "icon": "http://h5test.huimeibest.com/ui/images/logo.png",
	            "link_url": "http://h5test.huimeibest.com",
	            "classes": 1,
	            "type": 1,
	            "like_num": 14,
	            "read_num": 0,
	            "is_likes": true,
	            "is_reads": true,
	            "comment_num": 122,
	            "author": "",
	            "doctor": "",
	            "is_vip": false,
	            "read_access":true,
	            "price": 0,
	            "pubdate": '2013-11-11 22:22:22',
	            "created_at": 1254685635
	        }
	    ]
	}




###2.7 获取某篇文章详情
获取某篇文章详情(不验证登录)

####2.7.2 接口调用请求说明
| 请求方式 | 请求URL    | 
|:-------:|:---------:| 
| post    | article/getDetails| 

####2.7.3 请求参数说明
| 参数          | 是否必须 | 说明           | 
|:------------:|:------:|:-------------:|
| _id          | 是     | 文章ID

####2.7.4 请求示例
	{   
		"_id":"5747b6d0c2f9d446db1e6b13"
	}
    
####2.7.5 返回参数说明
| 字段         | 说明          | 
|:-----------:|:-------------:|
| _id         | 文章id         | 
| title       | 标题           |
| description | 描述           |
| icon        | 头图           |
| link_url    | 链接地址           |
| classes       | 文章分类。1：梅奥 2：原创 3：精选     |
| type        | 文章类型（1：文本类型，2链接类型）|
| like_num    | 点赞数量        |
| comment_num | 评论数量        |
| pubdate     | 发布时间        |
| created_at  | 创建时间        |
|  updated_at | 更新时间        |
| likes       | 点赞者列表        |
| is_likes    | 本人是否点赞        |
| is_reads    | 本人是否阅读        |
| read_num    | 阅读量        |
| body        | 文章内容        |
| author      | 文章作者        |
| doctor      | 文章所属医生(非必须)        |
|price | 收费文章价格       |
|is_vip| 文章是否收费，ｔｒｕｅ为收费，ｆａｌｓｅ为免费       |
|read_access| 是否有阅读权限，ｔｒｕｅ有权限，ｆａｌｓｅ为没有权限       |
|is_collect| 是否收藏，ｔｒｕｅ为收藏，ｆａｌｓｅ为未收藏    |

####2.7.6 响应示例
	{
	    "status": 0,
	    "message": "ok",
	    "data": {
	        "_id": "5747b6d0c2f9d446db1e6b13" ,
	        "title": "我们身边的“提灯女士”是谁？ ",
	        "description": "我们过了那么多节日，除了情人节，圣诞节等节日，是否还了解有一个叫“护士节”的节日。每年的5月12日是国际护士节。该节是为纪念现代护理学科的创始人—",
	        "icon": "http://h5test.huimeibest.com/ui/images/logo.png",
	        "link_url": "http://h5test.huimeibest.com",
	        "body": "我们过了那么多节日，除了情人节，圣诞节等节日，是否还了解有一个叫“护士节”的节日。每年的5月12日是国际护士节。该节是为纪念现代护理学科的创始人—",
	        "classes": 1,
	        "type": 1,
	        "like_num": 14,
	        "read_num": 0,
	        "comment_num": 122,
	        "pubdate": '2013-11-11 22:22:22',
	        "created_at": 1254685635,
	        "updated_at": 1254685635,
	        "likes": [
	            "57457256b7ef6a3d3b8b45bf"
	        ],
	        "is_likes": 1,
	        "is_reads": 1,
			"is_vip": false,
			"is_collect": false,
	        "read_access":true,
			"price": 0,
	        "author": "soone",
	        "doctor": {}
	    }
	}







<br>
##3 评论列表
####3.1 接口说明
用户评论列表

####3.2 接口调用请求说明
| 请求方式 | 请求URL    | 
|:-------:|:---------:| 
| post    | article/getCommentList | 

####3.3 请求参数说明
| 参数        | 是否必须 | 说明                                 | 
|:----------:|:-------:|:-----------------------------------:|
| article_id | 是      | 文章id                               |
| page       | 是      | 分页     |

####3.4 请求示例
	{
		"article_id":"5747b6d0c2f9d446db1e6b13",
		"page": "1"
	}
	    
####3.5 返回参数说明
| 字段           | 说明                    | 
|:-------------:|:----------------------:|
| _id           | 评论id                  | 
| article_id    | 文章id                  | 
| name          | 用户昵称                  | 
| avatar        | 用户头像                |
| content        | 评论内容                 |
| created_at     | 创建时间 |
| role          | 评论者角色              |
| uid           | 评论者id              |
| body          | 回复内容              |

####3.6 响应示例
	{
		"status": 0,
		"message": "ok",
		"data": [
			{
				"_id": "576363cc6803fa740af58d24",
				"article_id": "57554613b7ef6a1a4d8b45a7",
				"body": [
					{
						"_id": "57636cf36803fa0161f58d26",
						"uid": "57457256b7ef6a3d3b8b45bf",
						"pid": "57457256b7ef6a3d3b8b45bf",
						"u_name": "esoa",
						"p_name": "",
						"content": "ziwolaceh,younengzenmeyang",
						"created_at": 1235412341,
						"u_role": "pat_user",
						"p_role": ""
					}
				],
				"name": "",
				"avatar": "",
				"content": "guduhaunzhe,ziwolache",
				"created_at": 2341234,
				"updated_at": 21341234,
				"role": "pat_user",
				"status": 1,
				"uid": "57457256b7ef6a3d3b8b45bf"
			}
		]
	}






<br>
##4 评论
####4.1 接口说明
用户给文章评论,回复评论类型上限目前50条（评论回复<=50条）
role取值：pat_user,doctor两种值

####4.2 接口调用请求说明
| 请求方式 | 请求URL   | 
|:-------:|:--------:| 
| post    | article/sendComment| 

####4.3 请求参数说明
| 参数        | 是否必须 | 说明    | 
|:----------:|:------:|:-------:|
| article_id | 是      | 文章id  |
| content    | 是      | 评论内容 |
| user_id    | 是      | 用户id  |

####4.4 请求示例(按照不同类型)
    {
        "article_id": "5747b6d0c2f9d446db1e6b13",
        "content": "评论内容"
        "user_id": "57457256b7ef6a3d3b8b45bf"
    }

####4.5 返回参数说明
| 字段           | 说明                    | 
|:-------------:|:----------------------:|
| _id           | 评论id                  | 
| article_id    | 文章id                  | 
| uid           | 患者id                  | 
| content       | 评论内容                 |
| created_at    | 评论时间                 |
| name| 昵称|
|avatar| 头像|
|role| 角色|

####4.6 响应示例
	{
		"status": 0,
		"message": "评论成功!",
		"data": {
			"article_id": "57554613b7ef6a1a4d8b4585",
			"uid": "57610381b7ef6a573a8b45c7",
			"name": "soone",
			"avatar": "http://wx.qlogo.cn/mmopen/4Lib1iaSU9oLfcFoeroTbib4eBwPGeYRiaxic8lEW9AE0EWClh5MmvPPWu6yp2MUvmKrRIMTR8rGElf9lLmk7MzicySGyd5lZUQbDA/0",
			"role": "pat_user",
			"content": "你好",
			"body": [],
			"created_at": 1466579829,
			"updated_at": 1466579829,
			"status": 1,
			"_id": "576a3b75b7ef6a3d3b8b473f"
		}
	}


####4.7 回复子评论
回复评论类型上限目前50条（评论回复<=50条）
role取值：pat_user,doctor两种值

####4.7.2 接口调用请求说明
| 请求方式 | 请求URL   | 
|:-------:|:--------:| 
| post    | article/sendChildComment| 

####4.7.3 请求参数说明
| 参数        | 是否必须 | 说明    | 
|:----------:|:------:|:-------:|
| article_id | 是      | 文章id  |
| content    | 是      | 评论内容 |
| user_id    | 是      | 用户id  |
| comment_id| 是      | 一级评论id（当类型为2时必须）  |
| pid| 是      | 被回复者id（当类型为2时必须）  |
| p_name| 是      | 被回复者昵称（当类型为2时必须）  |
| p_role| 是      | 被回复者角色（当类型为2时必须）(pat_user,doctor两种值)  |

####4.7.4 请求示例(按照不同类型)
	{
		"article_id":"57554613b7ef6a1a4d8b4585",
		"user_id"        : "57610381b7ef6a573a8b45c7",
		"content" : "越来越不会恢复了，唉",
		"comment_id" : "576a3b75b7ef6a3d3b8b473f",
		"pid" : "57554613b7ef6a1a4d8b4585",
		"p_name":"soone",
		"p_role":"pat_user"
	}
    
####4.7.5 返回参数说明
| 字段           | 说明                    | 
|:-------------:|:----------------------:|
| _id           | 子评论id                  | 
| uid           | 用户id                  | 
| u_name        | 用户昵称                  | 
| u_role        | 用户角色                 |
| pid           | 被回复者id                 |
| p_name        | 被回复者昵称                 |
| p_role        | 被回复者角色                 |
| content       | 评论内容                 |
| created_at    | 评论时间                 |

####4.7.6 响应示例
	{
		"status": 0,
		"message": "评论成功!",
		"data": {
			"_id": "576a3fb0b7ef6aae3b8b45ee",
			"uid": "57610381b7ef6a573a8b45c7",
			"u_name": "soone",
			"u_role": "pat_user",
			"pid": "57554613b7ef6a1a4d8b4585",
			"p_name": "soone",
			"p_role": "pat_user",
			"content": "越来越不会恢复了，唉",
			"created_at": 1466580912
		}
	}



<br>
##5 删除评论
####5.1 接口说明
删除一条评论，只能删除自己的评论

####5.2 接口调用请求说明
| 请求方式 | 请求URL          | 
|:-------:|:---------------:| 
| post    | article/delComment | 

####5.3 请求说明
| 参数        | 是否必须 | 说明   | 
|:----------:|:-------:|:-----:|
| comment_id | 是      | 评论id |
| body_id    | 否      | 回复id（删除整个评论时不传该参数，删除具体回复时则需要传该参数） |

####5.4 请求示例
	{   
		"comment_id":"57480bb8b7ef6a1a4d8b4572",
		"body_id":"57636cf36803fa0161f58d26"
	}
    
####5.5 返回参数说明
	message返回删除成功和失败
	{
	    "status": 0,
	    "message": "删除成功!",
	    "data": true
	}





<br>
##6 点赞
####6.1 接口说明
用户给文章点赞，点赞不能取消

####6.2 接口调用请求说明
| 请求方式 | 请求URL | 
|:-------:|:------:| 
| post    | article/likes  | 

####6.3 请求参数说明
| 参数        | 是否必须 | 说明   | 
|:----------:|:-------:|:-----:|
| article_id | 是      | 文章id |

####6.4 请求示例
    {
        "article_id": "5747b6d0c2f9d446db1e6b13"
    }
    
####6.5 返回参数说明
	message返回点赞成功或者失败
	{
	    "status": 3,
	    "message": "重复点赞!",
	    "data": false
	}





<br>
##7 阅读文章
####7.1 接口说明
给文章增加阅读量(单个用户目前不允许重复阅读)

####7.2 接口调用请求说明
| 请求方式 | 请求URL | 
|:-------:|:------:| 
| post    | article/read  | 

####7.3 请求参数说明
| 参数        | 是否必须 | 说明   | 
|:----------:|:-------:|:-----:|
| article_id | 是      | 文章id |

####7.4 请求示例
    {
        "article_id": "5747b6d0c2f9d446db1e6b13"
    }
    
####7.5 返回参数说明
	message返回成功或者失败
	{
	    "status": 0,
	    "message": "成功!",
	    "data": 1
	}

###8 获取医生详情
获取医生详情(不验证登录)

####8.1 接口调用请求说明
| 请求方式 | 请求URL    | 
|:-------:|:---------:| 
| post    |  doctor/getDetails| 

####8.2 请求参数说明
| 参数          | 是否必须 | 说明           | 
|:------------:|:------:|:-------------:|
| _id          | 是     | 医生ID

####2.7.4 请求示例
	{   
		"_id":"56fb6e2783cdf8722c8f839b"
	}
    
####8.3 返回参数说明
| 字段         | 说明          | 
|:-----------:|:-------------:|
| _id         | 医生id         | 
| name        | 姓名            |
| avatar| 头像          |
| hospital| 医院           |
| department| 科室|
|position|头衔 |
|speciality| 擅长|
|description| 描述           |
|is_follow| 是否关注该医(0：未关注，1：已关注)        |

####8.4 响应示例
	{
		"status": 0,
		"message": "okok",
		"data": {
				"_id": "56fb6e2783cdf8722c8f839b",
				"name": "徐健",
				"avatar": "http://hm-img.huimeibest.com/avatar/4b/4b713665515b60fe2c43b8b8291b9915.png@!256",
				"hospital": "北大医院",
				"department": "骨科",
				"position": "主任医师",
				"speciality": "",
				"description": "",
				"is_follow": 0
			}
	}



<br>
##9 关注/取消医生
####9.1 接口说明
用户关注/取消医生

####9.2 接口调用请求说明
| 请求方式 | 请求URL | 
|:-------:|:------:| 
| post    |doctor/follow| 

####9.3 请求参数说明
| 参数        | 是否必须 | 说明   | 
|:----------:|:-------:|:-----:|
| doctor  | 是      |医生id |
| status  | 是      | 状态:1关注2取消 |

####9.4 请求示例
	{
		"doctor":"56fb6e2783cdf8722c8f839b",
		"status":"2"
	}
    
####9.5 返回参数说明
	{
		"status": 0,
		"message": "取消成功!",
		"data": true
	}

##9.6 扫码关注/取消医生
####9.6.1 接口说明
用户关注/取消医生

####9.6.2 接口调用请求说明
| 请求方式 | 请求URL | 
|:-------:|:------:| 
| post    |doctor/scanFollow| 

####9.6.3 请求参数说明
| 参数        | 是否必须 | 说明   | 
|:----------:|:-------:|:-----:|
| code    | 是      |医生加密id |

####9.6.4 请求示例
	{
		"code":"d2bfPQH2kwRyXnpdsKWoBvpxemyR3rMGE5PFX973Ihi8NrSpWnmaZjirdIv5InewsnowanewsnowcksNXJys8"
	}
    
####9.6.5 返回参数说明
	{
	  "status": 0,
	  "message": "关注成功!",
	  "data": {
		  "doc_id":"asdfasdf"
	  }
	}


<br>
##10 我关注的医生列表
####10.1 接口说明
获取我关注的医生列表(验证登录),每页10条数据

####10.2 接口调用请求说明
| 请求方式 | 请求URL    | 
|:-------:|:---------:| 
| post    | doctor/followList| 

####10.3 请求参数说明
| 参数          | 是否必须 | 说明           | 
|:------------:|:------:|:-------------:|
| page         | 是      | 分页时,当前页数（默认每页10条数据）|

####10.4 请求示例
	{   
		"page": 1
	}
    
####10.5 返回参数说明
| 字段         | 说明          | 
|:-----------:|:-------------:|
| _id         | id         | 
| pat_id| 患者id           |
| doc_id| 医生id           |
| created_at        | 关注时间           |
| doctor    | 医生信息（后台如果强制删除，该字段有可能为空对象）           |

####10.6 响应示例
	{
		"status": 0,
		"message": "ok",
		"data": [
			{
				"_id": "57690e02b7ef6aad3b8b47ee",
				"pat_id": "5760eeafb7ef6a3d3b8b4723",
				"doc_id": "56fb6e2783cdf8722c8f839b",
				"created_at": 1466502658,
				"doctor": {
					"_id": "56fb6e2783cdf8722c8f839b",
					"name": "徐健",
					"hospital": "北大医院",
					"position": "主任医师",
					"avatar": "头像",
					"speciality": ""
				}
			}
		]
	}


<br>
##11 得到我关注的医生数量
####11.1 接口说明
得到我关注的医生数量

####11.2 接口调用请求说明
| 请求方式 | 请求URL | 
|:-------:|:------:| 
| post    |doctor/followCount| 

####11.3 请求参数说明
| 参数        | 是否必须 | 说明   | 
|:----------:|:-------:|:-----:|

####11.4 请求示例
	{
	}
    
####11.5 返回参数说明
	{
		"status": 0,
		"message": "okok",
		"data": 1
	}

<br>
##12 快速搜索标签
####12.1 接口说明
模糊搜索用户输入标签

####12.2 接口调用请求说明
| 请求方式 | 请求URL |
|:-------:|:------:|
| post    |Article/tagLikes|

####12.3 请求参数说明
| 参数        | 是否必须 | 类型 | 说明   |
|:----------:|:-------:|:-----:|:-----:|
|name       |是      |   字符串 |   搜索内容|

####12.4 请求示例
	{
	    "name":"患者"
	}

####12.5 返回参数说明
	{
      "status": 0,
      "message": "okok",
      "data": [
        {
          "_id": "577c99476803faf511370478",
          "name": "81",
          "type": "tag"
        },
        {
          "_id": "577cbf996803fa263837047a",
          "name": "早上好888",
          "type": "article"
        },
        {
          "_id": "577cec8f6803faf511370483",
          "name": "desu,laile8888",
          "type": "article"
        }
      ]
    }

<br>
##13 获取与与标签相关的文章列表
####13.1 接口说明
用户点击相关标签，获取对应标签文章列表（带分页,默认10条）

####13.2 接口调用请求说明
| 请求方式 | 请求URL |
|:-------:|:------:|
| post    |Article/tagLists|

####13.3 请求参数说明
| 参数        | 是否必须 | 类型 | 说明   |
|:----------:|:-------:|:-----:|:-----:|
|_id       |是      |   字符串 |   标签 或者 文章标题id|
|page       |否      |   int |   标签id|

####13.4 请求示例
	{
	    "_id":"577c73626803faf611370474",
	    "page":1
	}

####13.5 返回参数说明
	{
      "status": 0,
      "message": "okok",
      "data": [
        {
          "_id": "577cec8f6803faf511370483",
          "title": "desu,laile8888",
          "description": "",
          "icon": "12121",
          "link_url": "",
          "body": "",
          "classes": 1,
          "type": 1,
          "like_num": 0,
          "read_num": 0,
          "comment_num": 0,
          "likes": [
            "57457256b7ef6a3d3b8b45bf"
          ],
          "reads": [
            "57457256b7ef6a3d3b8b45bf"
          ],
          "pubdate": 1467907200,
          "created_at": 1467804815,
          "updated_at": 1467804815,
          "status": 1,
          "author": "",
          "push_message": "false",
          "tags": [
            {
              "_id": "577c73626803faf611370474",
              "name": "1"
            },
            {
              "_id": "577ca04b6803faf51137047a",
              "name": "211"
            },
            {
              "_id": "577ca04b6803faf51137047b",
              "name": "411"
            }
          ],
          "is_likes": true,
          "is_reads": true
        }]
    }
<br>
##14 搜索
####14.1 接口说明
搜索关键字，返回5条相关标签和3条文章信息

####14.2 接口调用请求说明
| 请求方式 | 请求URL |
|:-------:|:------:|
| post    |Article/relateSearch|

####14.3 请求参数说明
| 参数        | 是否必须 | 类型 | 说明   |
|:----------:|:-------:|:-----:|:-----:|
|name       |是      |   字符串 |   搜索关键字|


####14.4 请求示例
    {
            "name":"1"
    }
####14.5 返回参数说明
   {
     "status": 0,
     "message": "okok",
     "data": {
       "tags": [],
       "articles": [
         {
           "_id": "577cec8f6803faf511370483",
           "title": "desu,laile8888",
           "description": "",
           "icon": "12121",
           "link_url": "",
           "classes": 1,
           "type": 1,
           "like_num": 0,
           "read_num": 0,
           "comment_num": 0,
           "pubdate": 1467907200,
           "created_at": 1467804815,
           "author": "",
           "tags": [
             {
               "_id": "577c73626803faf611370474",
               "name": "1"
             },
             {
               "_id": "577ca04b6803faf51137047a",
               "name": "211"
             },
             {
               "_id": "577ca04b6803faf51137047b",
               "name": "411"
             }
           ],
           "is_likes": true,
           "is_reads": true
         }
       ]
     }
   }

<br>
##15 获取更多相关内容
####15.1 接口说明
根据标签或者相关文章标题获取跟多相关搜索内容

####15.2 接口调用请求说明
| 请求方式 | 请求URL |
|:-------:|:------:|
| post    |Article/other|

####15.3 请求参数说明
| 参数        | 是否必须 | 类型 | 说明   |
|:----------:|:-------:|:-----:|:-----:|
|name       |是      |   字符串 |   搜索关键字|
|type       |是      |   字符串 |   相关类型tag/article|
|page       |否      |   int |   当前页数|


####15.4 请求示例
    {
    		"name":"1",
    		"type":"article"
    }
####15.5 返回参数说明
    {
      "status": 0,
      "message": "okok",
      "data": {
        "article": [
          {
            "_id": "57554613b7ef6a1a4d8b46a8",
            "title": "11 心脏手术后，您想知道的那些事（上）",
            "description": "心脏手术后，您想知道的那些事（上）",
            "icon": "http://h5test.huimeibest.com:8087/ui/images/article/5754f73e35f2cad0448c9cc8.jpeg",
            "link_url": "http://mp.weixin.qq.com/s?__biz=MzI5MTA0NzU2Ng==&mid=402205288&idx=2&sn=6626d4bdc3248988d7fcd52f2e72914d&scene=4#wechat_redirect",
            "classes": 2,
            "type": 2,
            "like_num": 1,
            "comment_num": 0,
            "created_at": 1465206291,
            "read_num": 1,
            "pubdate": 1449569460,
            "doctor": "55f95a7983cdf8574f5de675",
            "author": "",
            "is_likes": false,
            "is_reads": false,
            "tags": []
          }]
    }
##16 登陆发送验证码
####16.1 接口说明
登陆发送动态验证码，有效期５分钟，每次发送完成等待时间为１２０秒，５分钟内发送次数不得超过４次，服务端有校验；（客户端跟服务端保持同步）
####16.2 接口调用请求说明
| 请求方式 | 请求URL |
|:-------:|:------:|
| post    |msg/regSend|

####15.3 请求参数说明
| 参数        | 是否必须 | 类型 | 说明   |
|:----------:|:-------:|:-----:|:-----:|
|mobile     |是      |   字符串 |   发送验证码的手机号码|


####15.4 请求示例
    {
    		"mobile":"18010021635"
    }
####15.5 返回参数说明
	{
	  "status": 0,
	  "message": "发送成功",
	  "data": true
	}

##16 获取系统消息列表
####16.1 接口说明
系统消息按照发布时间倒序排列，每页10条记录
####16.2 接口调用请求说明
| 请求方式 | 请求URL |
|:-------:|:------:|
| post    |msg/sysMsgList|

####16.3 请求参数说明
| 参数        | 是否必须 | 类型 | 说明   |
|:----------:|:-------:|:-----:|:-----:|
| page         | 是      | 分页时,当前页数（默认每页10条数据）|


####16.4 请求示例
	{
		"page":1
	}
####16.5 返回参数说明
	{
		"status": 0,
			"message": "ok",
			"data": [
			{
				"_id": "57971816c2f9d446db1e6b3f",
				"content": "系统消息，每页10条消息，按照发布时间倒序排列！",//内容
				"isPush": 0,//是否推送
				"created_at": 1431232123,//添加时间
				"pubdate": 1441234122//发布时间
			}
		]
	}

##17 app启动时间更新
####17.1 接口说明
每次运行app请求后端，记录用户使用信息
####17.2 接口调用请求说明
| 请求方式 | 请求URL |
|:-------:|:------:|
| post    |user/runingLog|

####17.3 请求参数说明
| 参数        | 是否必须 | 类型 | 说明   |
|:----------:|:-------:|:-----:|:-----:|



####17.4 请求示例
	无
####17.5 返回参数说明
	无

##18 文章下单接口
####18.1 接口说明
先下->预支付(服务端)-> 发起支付(客户端)->支付回调(服务端)
####18.2 接口调用请求说明
| 请求方式 | 请求URL |
|:-------:|:------:|
| post    |order/article|

####18.3 请求参数说明
| 参数        | 是否必须 | 类型 | 说明   |
|:----------:|:-------:|:-----:|:-----:|
| article_id | 是      |string | 文章ｉｄ|



####18.4 请求示例
	{
		"article_id": "577caa7ab7ef6a7d6f8b4685"//文章ｉｄ
	}
####18.5 返回参数说明
	{
	  "status": 0,
	  "message": "下单成功!",
	  "data": {
		  "_id": "57a0764fb7ef6a3b3b8b4623",//订单ｉｄ
		  "price_pay": 2324.23//需要支付金额
		}
	}

##19 我的文章订单--from ycwang
####19.1 接口说明
我的文章订单,登陆后才会有
####19.2 接口调用请求说明
| 请求方式 | 请求URL |
|:-------:|:------:|
| post    |my/articleOrder|

####18.3 请求参数说明
| 参数        | 是否必须 | 类型 | 说明   |
|:----------:|:-------:|:-----:|:-----:|
| page       | 是      |int    | 分页，默认从第一页开始|



####19.4 请求示例
	{
		"page": 1
	}
####18.5 返回参数说明
	{
	  "status": 0,
	  "message": "ok",
	  "data": [
			  {
					"_id": "57a19dc5b7ef6a796f8b4c70",//订单ｉｄ
					"artId": "577caa7ab7ef6a7d6f8b4685",／／文章ｉｄ
					"userId": "5795c4acb7ef6a796f8b4c4f",／／用户ｉｄ（ｐａｔｉｅｎｔ）
					"art_title": "文章标题",／／文章标题
					"classes": 1,／／文章分类
					"price_ori": "2324.23",／／文章原始价格
					"price_pay": "2324.23",／／实际支付价格
					"created_at": 1470209477,／／下单时间
					"updated_at": 1470209477,
					"pay_type": "",／／支付类型
					"status": 1／／支付状态1支付成功，０未支付
				  }
		]
	}

##20 微信&支付宝预支付接口--from ycwang
####20.1 接口说明
微信预支付接口,服务器发起预支付请求到微信服务器，微信服务器返回成功的prepayid等信息，服务端做签名（客户端所需要的）等数据，返回
支付宝为客户端做签名，最终由客户端发起支付请求
####20.2 接口调用请求说明
| 请求方式 | 请求URL |
|:-------:|:------:|
| post    |order/prePayment|

####18.3 请求参数说明
| 参数        | 是否必须 | 类型 | 说明   |
|:----------:|:-------:|:-----:|:-----:|
| type       | 是      | string    | 支付类型（wechat,alipay）|
| order_id   | 是      | string    |　订单ｉｄ|



####20.4 请求示例
	{
		"type": "wechat",
		"order_id":"57a0764fb7ef6a3b3b8b4623"
	}
####20.5 返回参数说明
	//微信返回示例
	{
	  "status": 0,
	  "message": "预支付成功！",
	  "data": {
		  "wechat": {
				"appid": "wxdbbd77af932a3b5f",
				"noncestr": "XdrOm6P4nZz1vEeD",
				"package1": "Sign=WXPay",
				"partnerid": "1371459102",
				"prepayid": "wx201608041507093f2d7527b60101784203",
				"timestamp": "1470294430",
				"sign": "C4F66AE6B9AD8AF1940609F3D4EF37CC"
			  }
		}
	}
	//支付宝返回示例
	{
		"status": 0,
		"message": "预支付成功！",
		"data": {
			"alipay": "string"
		}
	}

##21 收藏文章
####21.1 接口说明
####21.2 接口调用请求说明
| 请求方式 | 请求URL |
|:-------:|:------:|
| post    |article/collecting|

####21.3 请求参数说明
| 参数        | 是否必须 | 类型 | 说明   |
|:----------:|:-------:|:-----:|:-----:|
| types       | 是      | string    | 1收藏文章，2:取消收藏|
|article_id   | 是      | string    |　文章ｉｄ|



####21.4 请求示例
	{
		"article_id": "57554613b7ef6a1a4d8b4598",
		"types":"1"
	}
####21.5 返回参数说明
	{
	  "status": 0,
	  "message": "收藏成功!",
	  "data": true
	}


##22 收藏文章列表
####22.1 接口说明
	每页十条数据
####21.2 接口调用请求说明
| 请求方式 | 请求URL |
|:-------:|:------:|
| post    |my/articleCollect|

####22.3 请求参数说明
| 参数        | 是否必须 | 类型 | 说明   |
|:----------:|:-------:|:-----:|:-----:|
| page     | 是      | int    | 当前页数|



####22.4 请求示例
	{
		"page":1
	}
####22.5 返回参数说明
	{
	  "status": 0,
	  "message": "ok",
	  "data": [
		      {
			        "_id": "57b28b51b7ef6a2d058b4570",
			        "article_id": "57554613b7ef6a1a4d8b4598",//文章ｉｄ
			        "user_id": "57b16630b7ef6a991e8b4578",／／用户ｉｄ
			        "title": "BMJ：跌眼镜！降低胆固醇反而增加死亡率！",／／文章标题
			        "created_at": 1471318865,／／收藏时间
			        "updated_at": 1471318865,／／数据最新更新更新时间
			        "status": 1,／／状态，当前默认都为１
			        "isdel": false,／／收藏的该文章是否已经删除，ｔｒｕｅ为已经删除，ｆａｌｓｅ为未删除
			        "article": {
					        "_id": "57554613b7ef6a1a4d8b4598",／／文章ｉｄ
					        "title": "BMJ：跌眼镜！降低胆固醇反而增加死亡率！",／／文章标题
					        "like_num": 3,／／点赞数量
					        "comment_num": 0,／／评论数量
					        "read_num": 8,／／阅读数量
					        "icon": "http://...",／／文章头图
					        "pubdate": 1462773240,／／文章发布时间
							"is_likes": false,//是否点赞
							"is_vip": false,//是否是收费文章
							"read_access": true//是否有阅读权限
					      }
			      }
	    ]
	}

##23 我的评论列表
####23.1 接口说明
	每页十条数据
####23.2 接口调用请求说明
| 请求方式 | 请求URL |
|:-------:|:------:|
| post    |my/comment|

####23.3 请求参数说明
| 参数        | 是否必须 | 类型 | 说明   |
|:----------:|:-------:|:-----:|:-----:|
| page     | 是      | int    | 当前页数|



####23.4 请求示例
	{
		"page":1
	}
####22.5 返回参数说明
| 参数        | 类型 | 说明   |
|:----------:|:-----:|:-----:|
| uid     | string    | 评论用户id|
| pid     | string    |  被评论用户id|
| u_role     | string    | 用户角色|
| p_role     | string    | 被评论用户角色|
| u_name     | string    | 评论用户名|
| p_name     | string    | 被评论用户名|
| content     | string    | 评论内容|
| created_at     | string    | 评论时间|
| comment_id     | string    | 以及评论id|
| article_isset     | bool    | 文章是否存在|
| article_title     | string    | 评论文章标题|
| article_icon     | string    | 文章图片|
| user_isset     | bool    | 用户是否存在|
| patient_avatar     | string    |评论患者头像|
####22.6 返回参数说明
	{
      "status": 0,
      "message": "ok",
      "data": [
        {
          "uid": "5770ccbbb7ef6a3d3b8b4746",
          "pid": "575d7f78b7ef6a3c3b8b45da",
          "u_role": "pat_user",
          "p_role": "pat_user",
          "u_name": "xujianrj3",
          "p_name": "Reactive",
          "content": "好的",
          "created_at": 1467092452,
          "article_id": "57554613b7ef6a1a4d8b457e",
          "comment_id": "576d103db7ef6a7a6f8b49fb",
          ##"body_id": "57720de4b7ef6a3d3b8b4748",//子级评论id
          "article_title": "Mayo Clinic 在华合资公司转诊办公室助力海外医疗机构直通梅奥",
          "article_icon": "http://h5test.huimeibest.com:8087/ui/images/article/5754f508a2eff63d48959fc2.jpeg",
          "article_isset": true,
          "patient_avatar": "http://tva3.sinaimg.cn/default/images/default_avatar_male_180.gif",
          "user_isset": true
        },
        {
          "uid": "575d7f78b7ef6a3c3b8b45da",
          "pid": "575d7f78b7ef6a3c3b8b45da",
          "u_role": "pat_user",
          "p_role": "pat_user",
          "u_name": "Reactive",
          "p_name": "Reactive",
          "content": "咯嘛",
          "created_at": 1466675362,
          "article_id": "57554613b7ef6a1a4d8b457d",
          "comment_id": "576baf43b7ef6aad3b8b47f4",
          ##"body_id": "57720de4b7ef6a3d3b8b4748",//子级评论id
          "article_title": "Mayo Clinic 创新中心变革论坛 &amp; Mayo Clinic 国际研讨会 | 9月盛大举行，等待您的参与！",
          "article_icon": "http://h5test.huimeibest.com:8087/ui/images/article/5754f5091ca386a75f74ca4e.jpeg",
          "article_isset": true,
         
          "patient_avatar": "http://wx.qlogo.cn/mmopen/Zg3pbmklJVxyho2VxDx2g4a0Xg3LP2IqWIM6WLb2tbGQAgnVpQBKMgyhWyw0Yu5c2Z9k19sjXQdlUwcUoDRcJP5ZY1U3PYYF/0",
          "user_isset": true
        }
      ]
    }
    
##24 评论推送
####24.1 接口说明
	被评论人根据“别名”收到消息推送
####24.2 接口调用请求说明
| 请求方式 | 请求URL |
|:-------:|:------:|
| 无    |无|

####24.3 请求参数说明
| 参数        | 是否必须 | 类型 | 说明   |
|:----------:|:-------:|:-----:|:-----:|
| 无|

####24.4 请求示例
| 无|
####24.5 返回参数说明
{
     "comment_id":"412fd13qwreqr1234sdfs",
     "type":"comment"
}

    
##25 意见反馈接口 
####25.1 接口说明
* 只有登录用户能够提交意见反馈。
* 每天不超过10条
* 反馈内容不能超过200字
* 联系方式不能超过100字
    
####25.2 接口调用请求说明
| 请求方式 | 请求URL |
|:-------:|:------:|
| post    |suggestion/submit|
   
####25.3 请求参数说明
| 参数       | 是否必须 | 类型   | 说明     |
|:----------:|:-------:|:-----:|:-----:   |
| content    | 是      | string| 反馈内容  |
| contactInfo| 是      | string| 联系方式  | 
   
####25.4 请求示例

```
{
	"content": "你们的App做得太好啦",
    "contactInfo" :"18513612532" 	
}
```

####25.5 返回参数说明

```
{
    "status": 0,
    "message": "提交成功!",
    "data": {
        "uid": "5791b752b7ef6a174d8b45a9",
        "nickname": "kkk",
        "platform": "weixin",
        "content": "577ceb47b7ef6ac26f8b4622",
        "contactInfo": "1",
        "submit_at": 1471494097,
        "state": "0",
        "note": "",
        "last_update_time": 1471494097,
        "_id": "57b537d10dc42bff148b456e"
    }
}

{
    "status": 1,
    "message": "提交内容不能超过200"
}

{
     "status": 2,
     "message": "联系方式不能超过100字"
}

{
    "status": 3,
    "message": "没有此用户"
}

{
    "status": 4,
    "message": "提交反馈过于频繁"
}

{
    "status": -1,
    "message": "提交失败"
}

```

##26 获取顶级评论下的所有评论
####26.1 接口说明
返回顶级评论id下的 评论列表
    
####26.2 接口调用请求说明
| 请求方式 | 请求URL |
|:-------:|:------:|
| post    |Article/getCommentByCommentId|
   
####26.3 请求参数说明
| 参数       | 是否必须 | 类型   | 说明     |
|:----------:|:-------:|:-----:|:-----:   |
| comment_id    |是|string|主评论id   |

####26.4请求示例

    {
        "comment_id":"576b5ddcb7ef6a3b3b8b45d9"
    }
####26.5 响应示例

    {
      "status": 0,
      "message": "ok",
      "data": [
        {
          "_id": "576b5ddcb7ef6a3b3b8b45d9",// 主评论id
          "article_id": "57554613b7ef6a1a4d8b457e",//评论文章id
          "uid": "574d5e5ab7ef6aab3b8b45c9",//评论用户id
          "name": "guaker",//评论用户名字
          "avatar": "http://qzapp.qlogo.cn/qzapp/1105427836/8C06744D9627CA34F15066C8AC777008/100",//评论用户头像
          "role": "pat_user",//评论用户角色
          "content": "看看",//评论内容
          "body": [//子级评论数组
            {
              "_id": "576b9ef9b7ef6aae3b8b45f1",//子级评论自增id
              "uid": "575d7f78b7ef6a3c3b8b45da",//子级评论用户id
              "pid": "574d5e5ab7ef6aab3b8b45c9",//被评论用户id
              "u_role": "pat_user",//子级评论用户角色
              "p_role": "pat_user",//被评论用户角色
              "u_name": "Reactive",//子级评论用户姓名
              "p_name": "guaker",//被评论用户姓名
              "content": "哈巴科技楼",//子级评论内容
              "created_at": 1466670841//评论时间
            },
            {
              "_id": "576b9f00b7ef6a3d3b8b4742",
              "uid": "575d7f78b7ef6a3c3b8b45da",
              "pid": "574d5e5ab7ef6aab3b8b45c9",
              "u_role": "pat_user",
              "p_role": "pat_user",
              "u_name": "Reactive",
              "p_name": "guaker",
              "content": "看了",
              "created_at": 1466670848
            }
          ],
          "created_at": 1466654172,
          "updated_at": 1466674406,
          "status": 1
        }
      ]
     }
}
 
 
##27 消息中心
####27.1 接口说明
根据本地缓存数据，请求服务端是否有新的消息推送，并显示
    
####27.2 接口调用请求说明
| 请求方式 | 请求URL |
|:-------:|:------:|
| post    |msg/msgPrompt|
   
####27.3 请求参数说明
| 参数       | 是否必须 | 类型   | 说明     |
|:----------:|:-------:|:-----:|:-----:   |
| comment_id    |是|string|评论id   |
| system_id|是|string|系统消息id   |

####27.4请求示例

{   
	"comment_id":"576a53b3b7ef6a1a4d8b45c6",
	"system_id":"579970c9b7ef6a573a8b464d"
}
####27.5 响应示例
{
  "status": 0,
  "message": "ok",
  "data": {
    "existence_new_sys_msg": true,
    "existence_new_comment": false
  }
}




 
    



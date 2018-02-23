wepiao service sdk
===================================
出发点
-----------------------------------
        ①: 该框架类似于RPC(远程服务调用), 因实际上是本地调用,所以可以称为LPC.
        ②: 框架的主要用于作为一组SDK, 为其他框架或程序提供公共服务, 通常来说, 使用RPC比较合适, 但是RPC需要额外增加不小数量的服务器, 运维和开发的维护成本也很高.
            所以, 我们做成内部调用, 抽象成为SDK的方式.
            假如我们的SDK服务叫做, 有A项目需要用到一些公共服务, 比如获取影片列表, 那么A与S的关系是:
            首先, A和S位于同一台服务器. 然后, A引入S的sdk文件路径, 最后执行调用即可. 需要说明的是, A和S算作是2个不同的项目,不能把S放入A的内部
            因为A和S分开的优势是: 运维和单独推送A的代码, 也可以单独推送S的代码. 这种部署方式对运维相对来说比较友好.
        ③: SDK的形式, 提供统一的服务, A可以调用S, 如果B项目需要, 也可以把该SDK推送到 B项目所以服务器由B调用, 避免各自为政. 比如, Java更新API地址, 则我们只需要更新SDK即可.

### 组织结构
        .
        ├── README.md
        ├── cgi
        │   ├── READEME
        │   │   └── symfony@http-foundation.md
        │   ├── README.md
        │   ├── app
        │   │   ├── config
        │   │   ├── helper
        │   │   ├── model
        │   │   └── service
        │   ├── composer.json
        │   ├── composer.lock
        │   ├── index.php
        │   └── vendor
        │       ├── autoload.php
        │       ├── composer
        │       ├── container-interop
        │       ├── php-di
        │       ├── symfony
        │       └── wepiao-service
        ├── log
        │   └── 2015-09-08
        └── sdk
            ├── example.php
            └── sdk.class.php

### 结构说明
        vendor: 该框架形似Yii2, 使用composer组件构成, 主框架为 vendor/wepiao-service. 也是以组件之一服务提供. 如果需要其他组件, 请使用composer安装
        app: 项目目录.
        sdk: sdk目录.

SDK调用示例
-----------------------------------
example code: sdk/exmaple.php

        <?php

        require("sdk.class.php");

        $sdk = sdk::Instance();

        //$res = $sdk->call("test/hello", ['aa' => 'aa']);

        \wepiao\wepiao::dump($res);


我们用 cli 模式执行: php sdk/exmaple.php

![输出结果](exmaple.png "输出结果")

app代码说明
-----------------------------------

①: 框架调用逻辑可简单描述

    外部代码(如: exmaple.php) -> sdk/sdk.class.php -> sdk/vendor/wepiao-service -> app/service/serviceTest::hello

②: 调用的实际逻辑:

    我们的示例中: exmaple.php 调用sdk 方式为: $sdk->call("test/hello",['aa'=>'aa']);

    实际中, 是调用到了 app/service 中的 serviceTest 中的hello方法. 倘若我们 serviceTest 方法为 helloWorld, 那么 sdk 调用形式为:

    $sdk->call( 'test/hello-world', ['aa']=>'aa' );

③: 具体 service 如何接受sdk 调用传递的参数

    还是上面的例子, 我们调用 test/hello 的时候, 传递了 一个数组(注意:必须切只能传数组), 那么, serviceTest 接受参数有2种方式

    第一种方式: 在hello方法内部中, 使用 wepiao::$input['aa'] 即可接收.

    第二种方式: 在定义 hello 方法的时候接受, 如: function hello( $input ){ ... 推荐使用这种方式!!!

④: service 调用 service

    也就是如何实例化一个其他服务: $this->service('user')->aa(); 这样我们就可以实例化 serviceUser, 然后调用其中的 aa 方法.

⑤: service 调用 model

    也就是如何实例化一个model: $this->model('user')->getUserName(); 这样我们可以实例化model模型 user, 然后调用其中的 getUserName 方法.

⑤: model 调用 redis

    也就是如何实例化一个model: $this->redis($TPId,某常量)->getDataFromRedis('key');

    注意: model调用redis, $this->redis(), 必须告知渠道编号, 以及常量. 渠道编号和常量的作用, 是定位使用哪个redis的集群!

SDK规范
-----------------------------------test

①: 输出标准

    输出的数据, 为标准的JSON数据, ret, sub, msg, data, 其中 ret为错误码, 只有0为正常的错误码, sub为错误字码, msg为成功或失败的错误信息,
    data为正常的数据内容.
    这样做的主要目的有2个: 第一, 统一sdk输出标准. 第二, 以后sdk的内容改为HTTP调用的话, 各个项目不需要改动
    注意: ret, sub 都需要从-1001 开始

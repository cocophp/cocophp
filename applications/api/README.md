## 项目开发标准及数据流程
目录职责：

- params: 负责校验api参数校验。
- tables: 负责提供params层的校验规则(根据数据表),及数据表的枚举字段的业务值。
- controllers: 负责对外暴露的api 以及业务逻辑
- services: 负责提供基础服务，我的做法是，按领域划分services，
- models：dao层，提供数据库操作接口。

调用时序图流程:
1. controllers调用params校验输入的参数是否满足当前api需求。
2. 若params校验失败，则直接返回错误信息。
3. 若校验成功，则调取业务内需要的service::interface()
4. controller将数据返回， 整个流程如下图：

```
controlls <-----> (params) --> tables --<参数校验>
          <-----> (services)->models        |
          (...more services)                |
          <-----> (services)->models        |
    |                                       |
    |                                      失败
    |                                       |
    L---------->     response   <-----------」
```           
注意事项及后期维护：

- 请小心维护services层，本层对外接口(也就是方法)请认真考虑对整体架构的影响。此处请明确service边界，本人做法是按领域划分，您也可以按模块(或其他方法)划分
- controlls层请尽量不直接调用models层，将技术细节交于service去维护，此处只管业务逻辑（也就是service配合）
- models层尽量单表单文件，向上提供给service层数据流，对于curl等也认为是model的一种，所以请尽量将所有的数据维护都放到这一层。
- params可以忽略，但一个好的，合格的程序员，api参数还是必须要校验的。
- tables层提供了一种基于数据表的校验规则，以供给params层使用。

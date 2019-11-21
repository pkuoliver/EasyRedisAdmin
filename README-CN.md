# EasyRedisAdmin
Php实现的Web版Redis管理系统。

本项目基于[erikdubbelboer/phpRedisAdmin](https://github.com/ErikDubbelboer/phpRedisAdmin) 1.11.5版本改进而来。phpRedisAdmin是一个很棒的项目，在这里表示感谢。

## 本项目主要工作：
* 给每个Key和Folder预估内存；
* 移除了Predis依赖，性能更好，更加高效；
* 支持Json格式化，当数据类型为Json的时候，显示更美观；
* 在首页，对Redis实例添加了更多的信息；
* 美化UI展示；
* 优化代码结构；

## 如何安装

~~~bash
git clone https://github.com/pkuoliver/EasyRedisAdmin.git
~~~

或者

~~~bash
wget https://github.com/pkuoliver/EasyRedisAdmin/archive/master.zip
unzip master.zip
~~~

安装之后，你需要复制一份includes/config.sample.inc.php，然后添加你的Redis实例信息，以及登录信息。

## 未来计划

* 按Key或Group预估内存消耗；
* 优化UI展示，改善CURD操作的便捷性；
* 添加Redis各个实例的实时监控；
* 添加多语言支持。
# EasyRedisAdmin
php web redis admin
本项目基于[erikdubbelboer/phpRedisAdmin](https://github.com/ErikDubbelboer/phpRedisAdmin) 1.11.5版本改进而来。phpRedisAdmin是一个很棒的项目，在这里表示感谢。

本项目主要做了以下工作：
====
* 移除了Predis依赖，性能更好，更加高效；
* 支持Json格式化，当数据类型为Json的时候，显示更美观；
* 在首页，对Redis实例添加了更多的信息；
* 美化UI展示；
* 优化代码结构；
# EasyRedisAdmin
php web redis admin
本项目基于[erikdubbelboer/phpRedisAdmin](https://github.com/ErikDubbelboer/phpRedisAdmin) 1.11.5版本改进而来。phpRedisAdmin是一个很棒的项目，在这里标识感谢。

本项目主要做了以下工作：
====
* 支持Json格式化，当数据类型为Json的时候，显示更美观；
* 在首页，对Redis实例添加了更多的信息；
* 美化UI展示；

本项目基于predis，隐藏在使用前，需要先安装[predis](https://github.com/nrk/predis)
```
composer require mongodb/mongodb
```
![Static Badge](https://img.shields.io/badge/%3E%3Dphp-8.1-green)    ![Static Badge](https://img.shields.io/badge/MIT-License-blue)  ![Static Badge](https://img.shields.io/badge/Symfony_7-green)     [![zread](https://img.shields.io/badge/Ask_Zread-_.svg?style=flat-square&color=00b0aa&labelColor=000000&logo=data%3Aimage%2Fsvg%2Bxml%3Bbase64%2CPHN2ZyB3aWR0aD0iMTYiIGhlaWdodD0iMTYiIHZpZXdCb3g9IjAgMCAxNiAxNiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTQuOTYxNTYgMS42MDAxSDIuMjQxNTZDMS44ODgxIDEuNjAwMSAxLjYwMTU2IDEuODg2NjQgMS42MDE1NiAyLjI0MDFWNC45NjAxQzEuNjAxNTYgNS4zMTM1NiAxLjg4ODEgNS42MDAxIDIuMjQxNTYgNS42MDAxSDQuOTYxNTZDNS4zMTUwMiA1LjYwMDEgNS42MDE1NiA1LjMxMzU2IDUuNjAxNTYgNC45NjAxVjIuMjQwMUM1LjYwMTU2IDEuODg2NjQgNS4zMTUwMiAxLjYwMDEgNC45NjE1NiAxLjYwMDFaIiBmaWxsPSIjZmZmIi8%2BCjxwYXRoIGQ9Ik00Ljk2MTU2IDEwLjM5OTlIMi4yNDE1NkMxLjg4ODEgMTAuMzk5OSAxLjYwMTU2IDEwLjY4NjQgMS42MDE1NiAxMS4wMzk5VjEzLjc1OTlDMS42MDE1NiAxNC4xMTM0IDEuODg4MSAxNC4zOTk5IDIuMjQxNTYgMTQuMzk5OUg0Ljk2MTU2QzUuMzE1MDIgMTQuMzk5OSA1LjYwMTU2IDE0LjExMzQgNS42MDE1NiAxMy43NTk5VjExLjAzOTlDNS42MDE1NiAxMC42ODY0IDUuMzE1MDIgMTAuMzk5OSA0Ljk2MTU2IDEwLjM5OTlaIiBmaWxsPSIjZmZmIi8%2BCjxwYXRoIGQ9Ik0xMy43NTg0IDEuNjAwMUgxMS4wMzg0QzEwLjY4NSAxLjYwMDEgMTAuMzk4NCAxLjg4NjY0IDEwLjM5ODQgMi4yNDAxVjQuOTYwMUMxMC4zOTg0IDUuMzEzNTYgMTAuNjg1IDUuNjAwMSAxMS4wMzg0IDUuNjAwMUgxMy43NTg0QzE0LjExMTkgNS42MDAxIDE0LjM5ODQgNS4zMTM1NiAxNC4zOTg0IDQuOTYwMVYyLjI0MDFDMTQuMzk4NCAxLjg4NjY0IDE0LjExMTkgMS42MDAxIDEzLjc1ODQgMS42MDAxWiIgZmlsbD0iI2ZmZiIvPgo8cGF0aCBkPSJNNCAxMkwxMiA0TDQgMTJaIiBmaWxsPSIjZmZmIi8%2BCjxwYXRoIGQ9Ik00IDEyTDEyIDQiIHN0cm9rZT0iI2ZmZiIgc3Ryb2tlLXdpZHRoPSIxLjUiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIvPgo8L3N2Zz4K&logoColor=ffffff)](https://zread.ai/xuey490/novaphp)



## 官方网站： https://www.phpframe.org/
欢迎访问。

## 简介:
FssPhp is a lightweight Powerful Fast, Simple, secure PHP framework。 This is the best practical project using FssPhp framework.

FssPhp 是一款轻量级强大，快速，简单，安全的php框架，这是FssPhp框架的最佳实践使用项目。

## 使用文档
强烈推荐Zread.Ai，感谢他们提供项目文档分析生成。https://zread.ai/xuey490/project

## 核心特性
🚀 性能与安全
- 	Workerman启动器： 使用workerman 可兼容FPM启动，一样的写法，超传统FPM 10倍以上的性能
- 	Symfony 组件集成：利用 Symfony 7.x 组件实现 HTTP 基础、路由、依赖注入和缓存
- 	轻量级设计：最小开销，快速请求处理
- 	集成中间件：CSRF保护、XSS过滤、速率限制和 IP阻断等中间件
- 	路由缓存：基于文件的缓存系统优化路由加载，注解路由注入中间件
- 	日志检查：基于Monolog的强大的日志功能，日志分片切分
- 	事件管理：自由把控web开发的每个细节
  
🔧 开发体验
- 	多种路由选项：约定的自动路由、支持手动路由配置和基于属性的路由
- 	依赖注入：完整的 Symfony DI 容器集成用于服务管理
- 	模板灵活性：双重模板引擎支持（Twig 和 ThinkTemplate）
- 	ORM 集成：ThinkORM 集成用于数据库操作
- 	数据验证：ThinkValidate 的强大数据集验证
-   权限控制：基于php注解和DOC Comment注释，注解路由的权限设置
  
🛠️ 现代 PHP 特性
- 	PHP 8.0+ 支持：利用现代 PHP 特性，包括属性和联合类型
- 	PSR 标准：遵循 PSR-4 自动加载和其他相关标准
- 	Composer 就绪：基于 Composer 的标准依赖管理

## Casbin 重构说明
- 统一权限模型：仅保留 `config/casbin_rbac_model.conf`，支持 `keyMatch` / `keyMatch2` 与动作通配符。
- 统一适配器实现：仅保留 `app/Services/Casbin/DatabaseAdapter.php`，并实现批量、更新、过滤加载接口。
- 统一权限入口：保留 `framework/Casbin/Permission.php`，移除历史冗余实现。
- 迁移文档：见 `.kiro/specs/casbin-refactoring/MIGRATION.md`。

## 代码质量扫描

本框架的程序代码，非常规范，冗余，重复代码非常少，也适合新手自学扩展。

[![](https://raw.githubusercontent.com/xuey490/project/refs/heads/main/quality.png)](https://gitee.com/fsscms/FssPhp)

## 下载安装:
### 1.传统启动模式
- 本地环境:php8.0 + 及以上，Redis，MySQL5.7, composer 2.x及以上
- 从GitHub下载main版本，解压到本地目录，在根目录下运行

`composer install `

- 等组件包下载完毕后，打开cmd命令行窗口，输入:

`php -S localhost:8000 -t public`

### 2.workerman启动
- 本地环境:php8.0 + 及以上，Redis，MySQL5.7, composer 2.x及以上
- 从GitHub下载main版本，解压到本地目录，在根目录下运行

`composer install `

- 等组件包下载完毕后，打开cmd命令行窗口，输入:

`php watch.php start `

### 3.访问

- 打开浏览器，输入地址http://localhost:8000
- 也可以部署到任何可以运行php的Apache或Nginx服务器上。

## 鸣谢（站在巨人的肩膀上能看得更远）
- Workerman: https://www.workerman.net/ (开源高性能PHP应用容器)
- Symfony: https://www.symfony.com/ (PHP界的Spring，基石)
- Thinkphp: https://thinkphp.cn/ (中文互联网PHP框架顶流)



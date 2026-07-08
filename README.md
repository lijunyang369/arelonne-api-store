# api-store — Arelonne Store API（项目代号 HOPE）

🇺🇸 AWS 部署。为 Arelonne 前台提供商品浏览、下单、配置查询。

## 路由

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | /api/store/products | 商品列表 |
| GET | /api/store/products/{slug} | 商品详情 |
| GET | /api/store/categories | 分类 |
| POST | /api/store/cart/calculate | 购物车计算 |
| POST | /api/store/orders | 创建订单 |
| GET | /api/store/orders/{orderNo} | 订单查询 |
| GET | /api/store/settings | 站点配置 |
| POST | /api/sync/products | 接收商品同步 |
| POST | /api/sync/orders | 接收订单同步 |

## 技术栈

Laravel / MySQL / Sanctum

## 启动

```bash
php artisan serve --port=8081
```

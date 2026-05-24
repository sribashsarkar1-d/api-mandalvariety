# Mandal Variety E-Commerce API Documentation

Base URL: `https://api.mandal-variety.com/`

## Overview
This documentation outlines all available REST APIs for the Mandal Variety platform, covering features like Advanced Search, Mobile Token Authentication, Cart, Orders, Policies, and more. All endpoints accept and return JSON.

---

## System

### Health Check
**Returns API uptime/basic health information.**

- **URL:** `/health`
- **Method:** `GET`
- **Headers:** None required

---

## Categories

### All Categories
**Get all active categories.**

- **URL:** `/categories/list.php`
- **Method:** `GET`
- **Headers:** None required

---

## Products

### All Products
**Get all products.**

- **URL:** `/products/list.php`
- **Method:** `GET`
- **Headers:** None required

---

### Product Detail
**Get one product by id.**

- **URL:** `/products/detail.php`
- **Method:** `GET`
- **Headers:** None required
- **Query Parameters:**
  - `id` (Example: 1)

---

### Manage Product
**Create or update a product. (Admin)**

- **URL:** `/products/manage.php`
- **Method:** `POST`
- **Headers:**
  - `Authorization: Bearer <token>`
- **JSON Body Example:**
```json
{
    "name": "Demo Product",
    "price": 999,
    "stock_quantity": 10
}
```

---

## Cart

### Get Cart
**Get cart items of a user.**

- **URL:** `/cart/cart.php`
- **Method:** `GET`
- **Headers:** None required
- **Query Parameters:**
  - `user_id` (Example: 1)

---

### Add to Cart
**Add product to cart.**

- **URL:** `/cart/add.php`
- **Method:** `POST`
- **Headers:** None required
- **Query Parameters:**
  - `user_id` (Example: 1)
- **JSON Body Example:**
```json
{
    "product_id": 1,
    "quantity": 2
}
```

---

### Update Cart
**Update cart quantity.**

- **URL:** `/cart/update.php`
- **Method:** `PUT`
- **Headers:** None required
- **Query Parameters:**
  - `user_id` (Example: 1)
- **JSON Body Example:**
```json
{
    "cart_item_id": 1,
    "quantity": 3
}
```

---

### Remove Cart Item
**Remove one item from cart.**

- **URL:** `/cart/remove.php`
- **Method:** `DELETE`
- **Headers:** None required
- **Query Parameters:**
  - `user_id` (Example: 1)
  - `cart_item_id` (Example: 1)

---

### Clear Cart
**Remove all cart items.**

- **URL:** `/cart/clear.php`
- **Method:** `DELETE`
- **Headers:** None required
- **Query Parameters:**
  - `user_id` (Example: 1)

---

## Wishlist

### Get Wishlist
**Get wishlist data.**

- **URL:** `/wishlist/list.php`
- **Method:** `GET`
- **Headers:** None required
- **Query Parameters:**
  - `user_id` (Example: 1)

---

### Add Wishlist Item
**Add product to wishlist.**

- **URL:** `/wishlist/add.php`
- **Method:** `POST`
- **Headers:** None required
- **Query Parameters:**
  - `user_id` (Example: 1)
- **JSON Body Example:**
```json
{
    "product_id": 1
}
```

---

### Remove Wishlist Item
**Remove product from wishlist.**

- **URL:** `/wishlist/remove.php`
- **Method:** `DELETE`
- **Headers:** None required
- **Query Parameters:**
  - `user_id` (Example: 1)
  - `product_id` (Example: 1)

---

## Orders

### List Orders
**Get all orders for one user.**

- **URL:** `/orders/list.php`
- **Method:** `GET`
- **Headers:** None required
- **Query Parameters:**
  - `user_id` (Example: 1)

---

### Order Detail
**Get one order detail.**

- **URL:** `/orders/detail.php`
- **Method:** `GET`
- **Headers:** None required
- **Query Parameters:**
  - `user_id` (Example: 1)
  - `order_id` (Example: 1)

---

### Create Order
**Create order from cart.**

- **URL:** `/orders/create.php`
- **Method:** `POST`
- **Headers:** None required
- **Query Parameters:**
  - `user_id` (Example: 1)
- **JSON Body Example:**
```json
{
    "address": "Siliguri",
    "payment_method": "cod"
}
```

---

### Cancel Order
**Cancel pending order.**

- **URL:** `/orders/cancel.php`
- **Method:** `POST`
- **Headers:** None required
- **Query Parameters:**
  - `user_id` (Example: 1)
  - `order_id` (Example: 1)

---

## Auth

### Register
**Register customer account. Sends OTP.**

- **URL:** `/auth/register.php`
- **Method:** `POST`
- **Headers:** None required
- **JSON Body Example:**
```json
{
    "name": "Sribash",
    "email": "sribash@example.com",
    "phone": "9876543210",
    "password": "123456"
}
```

---

### Verify Registration OTP
**Verify OTP and complete registration.**

- **URL:** `/auth/verify.php`
- **Method:** `POST`
- **Headers:** None required
- **JSON Body Example:**
```json
{
    "otp": "123456",
    "email": "sribash@example.com"
}
```

---

### Login (Send OTP)
**Login with email. Sends OTP.**

- **URL:** `/auth/login.php`
- **Method:** `POST`
- **Headers:** None required
- **JSON Body Example:**
```json
{
    "email": "sribash@example.com"
}
```

---

### Verify Login OTP
**Verify OTP and get Auth Token.**

- **URL:** `/auth/verify-login-otp.php`
- **Method:** `POST`
- **Headers:** None required
- **JSON Body Example:**
```json
{
    "email": "sribash@example.com",
    "otp": "123456"
}
```

---

### Get Profile
**Get logged in user profile.**

- **URL:** `/auth/profile.php`
- **Method:** `GET`
- **Headers:**
  - `Authorization: Bearer <token>`

---

### Update Profile
**Update user details.**

- **URL:** `/auth/profile.php`
- **Method:** `PUT`
- **Headers:**
  - `Authorization: Bearer <token>`
- **JSON Body Example:**
```json
{
    "name": "New Name",
    "phone": "1234567890"
}
```

---

### Delete Profile
**Delete user account entirely.**

- **URL:** `/auth/profile.php`
- **Method:** `DELETE`
- **Headers:**
  - `Authorization: Bearer <token>`

---

### Logout
**Logout current user and revoke token.**

- **URL:** `/auth/logout.php`
- **Method:** `POST`
- **Headers:**
  - `Authorization: Bearer <token>`

---

## Reviews

### List Reviews
**Get reviews for a product or user.**

- **URL:** `/reviews/list.php`
- **Method:** `GET`
- **Headers:** None required
- **Query Parameters:**
  - `product_id` (Example: 1)

---

### Add Review
**Submit a new review.**

- **URL:** `/reviews/add.php`
- **Method:** `POST`
- **Headers:**
  - `Authorization: Bearer <token>`
- **JSON Body Example:**
```json
{
    "product_id": 1,
    "rating": 5,
    "title": "Awesome",
    "comment": "Great product!"
}
```

---

## Coupons

### List Coupons
**Get all active coupons**

- **URL:** `/coupons/list.php`
- **Method:** `GET`
- **Headers:** None required

---

### Create Coupon
**Create a new discount coupon (Admin)**

- **URL:** `/coupons/manage.php`
- **Method:** `POST`
- **Headers:**
  - `Authorization: Bearer <token>`
- **JSON Body Example:**
```json
{
    "code": "DISCOUNT10",
    "discount": 10
}
```

---

## Offers

### List Offers
**Get all offers**

- **URL:** `/offers/list.php`
- **Method:** `GET`
- **Headers:** None required

---

### Manage Offers
**Create offers (Admin)**

- **URL:** `/offers/manage.php`
- **Method:** `POST`
- **Headers:**
  - `Authorization: Bearer <token>`
- **JSON Body Example:**
```json
{
    "offer_name": "Summer Sale",
    "offer_value": 20
}
```

---

## Settings

### Get Settings
**Get all global site settings**

- **URL:** `/settings/get.php`
- **Method:** `GET`
- **Headers:** None required

---

### Update Settings
**Update site settings (Admin)**

- **URL:** `/settings/update.php`
- **Method:** `POST`
- **Headers:**
  - `Authorization: Bearer <token>`
- **JSON Body Example:**
```json
{
    "site_name": "My New Store"
}
```

---

## Policies

### List Policies
**Get all dynamic policies**

- **URL:** `/policies/list.php`
- **Method:** `GET`
- **Headers:** None required

---

### Policy Detail
**Get single policy**

- **URL:** `/policies/detail.php`
- **Method:** `GET`
- **Headers:** None required
- **Query Parameters:**
  - `slug` (Example: privacy-policy)

---

### Create Policy
**Create dynamic policies (Admin)**

- **URL:** `/policies/manage.php`
- **Method:** `POST`
- **Headers:**
  - `Authorization: Bearer <token>`
- **JSON Body Example:**
```json
{
    "title": "Privacy",
    "content": "Data..."
}
```

---

### Update Policy
**Update dynamic policies (Admin)**

- **URL:** `/policies/manage.php`
- **Method:** `PUT`
- **Headers:**
  - `Authorization: Bearer <token>`
- **Query Parameters:**
  - `id` (Example: 1)
- **JSON Body Example:**
```json
{
    "title": "Privacy",
    "content": "Data updated..."
}
```

---

## Age Verifications

### List Verifications
**Get verification documents (Admin)**

- **URL:** `/age_verifications/list.php`
- **Method:** `GET`
- **Headers:**
  - `Authorization: Bearer <token>`

---

### Submit Verification
**Submit document for age review**

- **URL:** `/age_verifications/manage.php`
- **Method:** `POST`
- **Headers:**
  - `Authorization: Bearer <token>`
- **JSON Body Example:**
```json
{
    "user_id": 1,
    "full_name": "John Doe"
}
```

---

## Advanced Search

### Global Search
**Search across products and categories with typo handling**

- **URL:** `/search/global.php`
- **Method:** `GET`
- **Headers:** None required
- **Query Parameters:**
  - `q` (Example: milk)

---

### Product Search
**Search only products**

- **URL:** `/search/products.php`
- **Method:** `GET`
- **Headers:** None required
- **Query Parameters:**
  - `q` (Example: milk)

---

### Category Search
**Search only categories**

- **URL:** `/search/categories.php`
- **Method:** `GET`
- **Headers:** None required
- **Query Parameters:**
  - `q` (Example: milk)

---

### Suggestions
**Get autocomplete suggestions**

- **URL:** `/search/suggestions.php`
- **Method:** `GET`
- **Headers:** None required
- **Query Parameters:**
  - `q` (Example: sh)

---

### Related Results
**Fallback related products**

- **URL:** `/search/related.php`
- **Method:** `GET`
- **Headers:** None required

---

### Popular Searches
**Get popular search terms**

- **URL:** `/search/popular.php`
- **Method:** `GET`
- **Headers:** None required

---

### Voice Search
**Process voice query text**

- **URL:** `/search/voice.php`
- **Method:** `POST`
- **Headers:** None required
- **JSON Body Example:**
```json
{
    "query": "milk shake"
}
```

---


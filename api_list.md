# Mandal Variety E-Commerce API List

This document contains a comprehensive list of all APIs available in the `auth-api` project, including the newly added endpoints.

**Base URL:** `https://api.mandal-variety.com/`

## ⚙️ System
- `GET /` - API Home / Docs
- `GET /health` - Health Check

## 📂 Categories
- `GET /categories/list` - Get all active categories
- `GET /categories/details` - Get category details
- `GET /categories/children` - Get children of a category
- `GET /categories/hierarchy` - Get category hierarchy
- `GET /categories/products` - Get products under a category

## 🛍️ Products
- `GET /products/list` - Get all products
- `GET /products/detail/{id}` - Get a specific product by ID
- `POST|PUT /products/manage/{id}` - Create or update a product

## 🖼️ Banners
- `GET /banners/list` - Get all banners
- `GET /banners/details` - Get details of a specific banner

## 🛒 Cart
- `GET /cart/list?user_id={id}` - Get cart items for a user
- `GET /cart/get?user_id={id}` - Get cart state for a user
- `POST /cart/add?user_id={id}` - Add a product to the cart
- `PUT /cart/update?user_id={id}` - Update quantity of a cart item
- `DELETE /cart/remove/{cart_item_id}?user_id={id}` - Remove a specific item from the cart
- `DELETE /cart/clear?user_id={id}` - Clear all items from the cart

## ❤️ Wishlist
- `GET /wishlist/list?user_id={id}` - Get wishlist data for a user
- `POST /wishlist/add?user_id={id}` - Add a product to the wishlist
- `DELETE /wishlist/remove/{product_id}?user_id={id}` - Remove a product from the wishlist

## 📦 Orders
- `GET /orders/list?user_id={id}` - Get all orders for a user
- `GET /orders/detail/{order_id}?user_id={id}` - Get details of a specific order
- `POST /orders/create?user_id={id}` - Create a new order from the cart
- `POST /orders/cancel/{order_id}?user_id={id}` - Cancel a pending order
- `GET /orders/debug` - Debug order processing

## 🔐 Auth (Authentication)
- `POST /auth/register` - Register a new customer account
- `POST /auth/verify` - Verify OTP and complete registration
- `POST /auth/login` - Login with email and password
- `POST /auth/verify-login-otp` - Verify OTP for login
- `POST /auth/logout` - Logout the current user
- `GET /auth/profile` - Get the logged-in user's profile

## ⭐ Reviews
- `GET /reviews/list?product_id={id}` - Get reviews for a product
- `POST /reviews/add?user_id={id}` - Submit a new review

## 🎟️ Coupons
- `GET /coupons/list` - Get all coupons
- `POST|PUT|DELETE /coupons/manage` - Create, update, or delete coupons

## 🎁 Offers
- `GET /offers/list` - Get all offers
- `POST|PUT|DELETE /offers/manage` - Create, update, or delete offers

## ⚙️ Settings
- `GET /settings/get` - Get all site settings
- `POST|PUT /settings/update` - Update site settings

## 📜 Policies
- `GET /policies/list` - Get all policies
- `GET /policies/detail?slug={slug}` - Get a specific policy
- `POST|PUT|DELETE /policies/manage` - Create, update, or delete policies

## 🔞 Age Verifications
- `GET /age_verifications/list` - Get all age verification requests
- `POST|PUT /age_verifications/manage` - Submit or review a verification

## 🔍 Advanced Search
- `GET /search/global` - Global search across products and categories
- `GET /search/products` - Search products only
- `GET /search/categories` - Search categories only
- `GET /search/suggestions` - Get autocomplete suggestions
- `GET /search/related` - Get related products
- `GET /search/popular` - Get popular search terms
- `POST /search/voice` - Process voice search queries

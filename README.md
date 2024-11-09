<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
</head>
<body>

<h1>Hướng dẫn cấu hình chạy dự án</h1>

<h2>Yêu cầu:</h2>
<ul>
    <li>PHP 8.1.10</li>
    <li>Composer version 2.7.2</li>
</ul>

<h2>Bước 1: Tải mã nguồn và file database</h2>
<p>
    Github: <a href="https://github.com/BuiiKNgann/Nhom3-PTPMMNM" target="_blank">https://github.com/BuiiKNgann/Nhom3-PTPMMNM</a>
</p>

<h2>Bước 2: Tạo và Import database</h2>
<ul>
    <li>Tạo database:</li>
    <ul>
        <li>Đặt tên: <code>laravel_vlxd</code></li>
        <li>Bộ ký tự: <code>utf8mb4_unicode_ci</code></li>
    </ul>
    <li>Import dữ liệu từ file database: <code>laravel_vlxd.sql</code></li>
</ul>

<h2>Bước 3:</h2>
<ul>
    <li>Mở thư mục dự án <code>myWeb</code> và mở chạy terminal:</li>
    <li>Chạy lệnh: <code>composer update</code></li>
</ul>

<h2>Bước 4:</h2>
<ul>
    <li>Vào Visual Studio Code và open folder dự án "myWeb"</li>
    <li>Tại file <code>.env.example</code> đổi tên thành <code>.env</code></li>
    <li>Cập nhật thông số như sau:</li>
</ul>
<pre>
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_vlxd
DB_USERNAME=root
DB_PASSWORD=
</pre>
<p>
    Sau đó mở terminal của dự án và gõ lệnh:
</p>
<pre>php artisan key:generate</pre>
<p>Cấu hình trong file <code>.env</code> sẽ như sau:</p>
<pre>
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:cp6J5aDQWw+iTFgyCvEANWqQMk/85A0lqdvt0QdistY=
APP_DEBUG=true
APP_URL=http://localhost
</pre>

<h2>Bước 5: Chạy project</h2>
<ul>
    <li>Mở terminal trong thư mục gốc của dự án.</li>
    <li>Gõ lệnh chạy server: <code>php artisan serve</code> và nhận được địa chỉ: <code>127.0.0.1:8000</code></li>
</ul>

<h2>Đường dẫn truy cập:</h2>
<ul>
    <li>Giao diện trang chủ: <a href="http://127.0.0.1:8000" target="_blank">127.0.0.1:8000</a></li>
    <li>Giao diện danh mục: <a href="http://127.0.0.1:8000/shop" target="_blank">127.0.0.1:8000/shop</a></li>
    <li>Giao diện đăng nhập admin: <a href="http://127.0.0.1:8000/admin/login" target="_blank">127.0.0.1:8000/admin/login</a></li>
    <ul>
        <li>Email: <code>admin@example.com</code></li>
        <li>Password: <code>admin</code></li>
    </ul>
    <li>Giao diện admin: <a href="http://127.0.0.1:8000/admin/dashboard" target="_blank">127.0.0.1:8000/admin/dashboard</a></li>
</ul>

</body>
</html>

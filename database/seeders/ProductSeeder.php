<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Wireless Headphones', 'description' => 'Premium noise-cancelling wireless headphones with 30h battery life.', 'price' => 79.99, 'image' => 'https://plus.unsplash.com/premium_photo-1679513691474-73102089c117?q=80&w=1413&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'],
            ['name' => 'Mechanical Keyboard', 'description' => 'RGB mechanical keyboard with Cherry MX switches.', 'price' => 129.99, 'image' => 'https://plus.unsplash.com/premium_photo-1725075085043-79932bdbcde6?w=700&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8OXx8bWVjaGFuaWNhbCUyMGtleWJvYXJkfGVufDB8fDB8fHww'],
            ['name' => 'USB-C Hub', 'description' => '7-in-1 USB-C hub with HDMI, USB 3.0, and SD card reader.', 'price' => 34.99, 'image' => 'https://plus.unsplash.com/premium_photo-1761043248662-42f371ad31b4?w=700&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MXx8dXNiJTIwYyUyMGh1YnxlbnwwfHwwfHx8MA%3D%3D'],
            ['name' => 'Laptop Stand', 'description' => 'Adjustable aluminum laptop stand for better ergonomics.', 'price' => 49.99, 'image' => 'https://images.unsplash.com/photo-1629317480826-910f729d1709?w=700&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8M3x8bGFwdG9wJTIwc3RhbmR8ZW58MHx8MHx8fDA%3D'],
            ['name' => 'Webcam HD', 'description' => '1080p webcam with built-in microphone and auto-focus.', 'price' => 59.99, 'image' => 'https://images.unsplash.com/photo-1623949556303-b0d17d198863?w=700&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8d2ViY2FtfGVufDB8fDB8fHww'],
            ['name' => 'Mouse Pad XL', 'description' => 'Extra large mouse pad with stitched edges and non-slip base.', 'price' => 19.99, 'image' => 'https://images.unsplash.com/photo-1598978554684-31ee8d544884?w=700&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTJ8fG1vdXNlJTIwcGFkfGVufDB8fDB8fHww'],
            ['name' => 'Monitor Light Bar', 'description' => 'LED monitor light bar with adjustable color temperature.', 'price' => 44.99, 'image' => 'https://images.unsplash.com/photo-1658044552340-42456e3cc071?w=700&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTR8fG1vbml0b3IlMjBsaWdodCUyMGJhcnxlbnwwfHwwfHx8MA%3D%3D'],
            ['name' => 'Portable SSD 1TB', 'description' => 'Ultra-fast portable SSD with USB 3.2 and 1050MB/s read speed.', 'price' => 89.99, 'image' => 'https://images.unsplash.com/photo-1577538926210-fc6cc624fde2?w=700&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8cG9ydGFibGUlMjBzc2R8ZW58MHx8MHx8fDA%3D'],
            ['name' => 'Desk Organizer', 'description' => 'Bamboo desk organizer with compartments for cables and accessories.', 'price' => 24.99, 'image' => 'https://images.unsplash.com/photo-1644463589256-02679b9c0767?w=700&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8ZGVzayUyMG9yZ2FuaXplcnxlbnwwfHwwfHx8MA%3D%3D'],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
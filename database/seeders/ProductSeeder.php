<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Wireless Headphones', 'description' => 'Premium noise-cancelling wireless headphones with 30h battery life.', 'price' => 79.99, 'image' => 'https://picsum.photos/seed/headphones/400/400'],
            ['name' => 'Mechanical Keyboard', 'description' => 'RGB mechanical keyboard with Cherry MX switches.', 'price' => 129.99, 'image' => 'https://picsum.photos/seed/keyboard/400/400'],
            ['name' => 'USB-C Hub', 'description' => '7-in-1 USB-C hub with HDMI, USB 3.0, and SD card reader.', 'price' => 34.99, 'image' => 'https://picsum.photos/seed/usbhub/400/400'],
            ['name' => 'Laptop Stand', 'description' => 'Adjustable aluminum laptop stand for better ergonomics.', 'price' => 49.99, 'image' => 'https://picsum.photos/seed/laptopstand/400/400'],
            ['name' => 'Webcam HD', 'description' => '1080p webcam with built-in microphone and auto-focus.', 'price' => 59.99, 'image' => 'https://picsum.photos/seed/webcam/400/400'],
            ['name' => 'Mouse Pad XL', 'description' => 'Extra large mouse pad with stitched edges and non-slip base.', 'price' => 19.99, 'image' => 'https://picsum.photos/seed/mousepad/400/400'],
            ['name' => 'Monitor Light Bar', 'description' => 'LED monitor light bar with adjustable color temperature.', 'price' => 44.99, 'image' => 'https://picsum.photos/seed/lightbar/400/400'],
            ['name' => 'Portable SSD 1TB', 'description' => 'Ultra-fast portable SSD with USB 3.2 and 1050MB/s read speed.', 'price' => 89.99, 'image' => 'https://picsum.photos/seed/ssd/400/400'],
            ['name' => 'Desk Organizer', 'description' => 'Bamboo desk organizer with compartments for cables and accessories.', 'price' => 24.99, 'image' => 'https://picsum.photos/seed/organizer/400/400'],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
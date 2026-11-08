<?php

/*
 * Demo catalogue used by DemoCatalogSeeder.
 * category => [ subcategory => [ [name, description, old_price, new_price], ... ] ]
 */
return [
    'Electronics' => [
        'Mobile Phones' => [
            ['Galaxy Nova 5G', 'A 6.5 inch AMOLED smartphone with a 50MP triple camera, 8GB RAM and a two-day battery.', 42990, 38990],
            ['Pixel Lite 128GB', 'Compact Android phone with clean software, fast charging and a bright OLED screen.', 31500, 28999],
            ['Redmi Note Max', 'Big-battery phone built for streaming and gaming with a 120Hz display.', 24999, 22499],
        ],
        'Laptops' => [
            ['UltraBook Air 14', 'Thin and light 14 inch laptop with Core i5, 16GB RAM and a 512GB NVMe SSD.', 89990, 82500],
            ['WorkStation Pro 15', 'Powerful 15.6 inch laptop for developers with Ryzen 7, 32GB RAM and a 1TB SSD.', 124000, 115000],
            ['Student Book 11', 'Affordable 11 inch laptop that is perfect for classes, notes and browsing.', 36500, 33900],
        ],
        'Accessories' => [
            ['Wireless Earbuds Z', 'Bluetooth 5.3 earbuds with noise cancellation and a 24 hour charging case.', 4500, 3499],
            ['Fast Charger 65W', 'GaN USB-C wall charger that powers a phone and a laptop at the same time.', 2200, 1799],
            ['Mechanical Keyboard K2', 'Hot-swappable 75 percent mechanical keyboard with tactile switches.', 7800, 6999],
        ],
    ],
    'Fashion' => [
        "Men's Wear" => [
            ['Classic Cotton Panjabi', 'Soft breathable cotton panjabi with fine embroidery, ideal for festive days.', 3200, 2699],
            ['Slim Fit Denim Jeans', 'Stretch denim jeans with a modern slim fit and reinforced stitching.', 2800, 2299],
            ['Polo T-Shirt Navy', 'Premium pique polo shirt that stays fresh all day.', 1500, 1199],
        ],
        "Women's Wear" => [
            ['Handloom Silk Saree', 'Elegant handloom silk saree with a rich zari border and matching blouse piece.', 9800, 8400],
            ['Printed Cotton Kurti', 'Lightweight printed kurti, comfortable for work and casual outings.', 1800, 1450],
            ['Winter Wool Shawl', 'Warm and soft wool shawl with a subtle geometric pattern.', 2600, 2199],
        ],
        'Footwear' => [
            ['Leather Formal Shoes', 'Handcrafted genuine leather shoes with a cushioned insole.', 4800, 4199],
            ['Running Sneakers X', 'Breathable mesh running shoes with a responsive foam sole.', 5200, 4499],
        ],
    ],
    'Home & Living' => [
        'Furniture' => [
            ['Oak Study Table', 'Solid wood study table with a cable hole and two side drawers.', 18500, 16200],
            ['Ergonomic Office Chair', 'Mesh back chair with adjustable lumbar support and 4D armrests.', 14900, 12999],
        ],
        'Kitchen' => [
            ['Non-stick Cookware Set', 'Seven piece non-stick cookware set that works on gas and induction.', 6900, 5799],
            ['Electric Rice Cooker 1.8L', 'Automatic rice cooker with keep-warm function and a steaming tray.', 3900, 3299],
            ['Stainless Water Bottle', 'Double wall insulated bottle that keeps drinks cold for 24 hours.', 1200, 899],
        ],
    ],
];

<?php

/*
 * Demo orders used by DemoOrderSeeder (and the SQL export).
 * [order_number, tracking token, product name, customer, phone, address, qty, payment method, status, payment status]
 */
return [
    ['BS-DEMO-0001', 'DEMO-0001-TRK1', 'Galaxy Nova 5G', 'Rahim Uddin', '01712345678', 'House 12, Road 5, Dhanmondi, Dhaka', 1, 'bkash', 'delivered', 'paid'],
    ['BS-DEMO-0002', 'DEMO-0002-TRK2', 'Wireless Earbuds Z', 'Nusrat Jahan', '01812345678', 'Flat 4B, Agrabad C/A, Chattogram', 2, 'cod', 'shipped', 'unpaid'],
    ['BS-DEMO-0003', 'DEMO-0003-TRK3', 'Handloom Silk Saree', 'Farzana Akter', '01912345678', 'Zindabazar, Sylhet Sadar, Sylhet', 1, 'nagad', 'packaging', 'paid'],
    ['BS-DEMO-0004', 'DEMO-0004-TRK4', 'Ergonomic Office Chair', 'Tanvir Hasan', '01612345678', 'Station Road, Rajshahi Sadar, Rajshahi', 1, 'bank_transfer', 'confirmed', 'unpaid'],
    ['BS-DEMO-0005', 'DEMO-0005-TRK5', 'Mechanical Keyboard K2', 'Sabbir Ahmed', '01512345678', 'Sector 7, Uttara, Dhaka', 3, 'cod', 'pending', 'unpaid'],
    ['BS-DEMO-0006', 'DEMO-0006-TRK6', 'Running Sneakers X', 'Mim Chowdhury', '01312345678', 'Boro Bazar, Khulna Sadar, Khulna', 1, 'bkash', 'cancelled', 'refunded'],
];

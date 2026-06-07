<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class FrontendDocsController extends Controller
{
    public function index()
    {
        $base = url('/api');

        return response()->json([
            'title'   => 'Creakers API — Frontend Reference',
            'version' => '1.0',
            'base_url' => $base,
            'note'    => 'All endpoints are public. No authentication required.',

            'endpoints' => [

                // ── Sites ────────────────────────────────────────────────
                [
                    'group'       => 'Sites',
                    'method'      => 'GET',
                    'url'         => $base . '/sites',
                    'description' => 'List all active sites',
                    'auth'        => false,
                    'params'      => [],
                    'response_example' => [
                        'success' => true,
                        'data' => [[
                            'name' => 'Creakers',
                            'slug' => 'creakers',
                            'logo' => 'https://example.com/logo.png',
                        ]],
                    ],
                ],
                [
                    'group'       => 'Sites',
                    'method'      => 'GET',
                    'url'         => $base . '/{site}',
                    'description' => 'Get details for a single site',
                    'auth'        => false,
                    'params'      => [
                        ['in' => 'path', 'name' => 'site', 'type' => 'string', 'required' => true, 'description' => 'Site slug'],
                    ],
                    'response_example' => [
                        'success' => true,
                        'data' => [
                            'name' => 'Creakers',
                            'slug' => 'creakers',
                            'logo' => 'https://example.com/logo.png',
                        ],
                    ],
                ],

                // ── Categories ───────────────────────────────────────────
                [
                    'group'       => 'Categories',
                    'method'      => 'GET',
                    'url'         => $base . '/{site}/categories',
                    'description' => 'List all active categories for a site',
                    'auth'        => false,
                    'params'      => [
                        ['in' => 'path', 'name' => 'site', 'type' => 'string', 'required' => true, 'description' => 'Site slug'],
                    ],
                    'response_example' => [
                        'success' => true,
                        'site'    => ['name' => 'Creakers', 'slug' => 'creakers'],
                        'data'    => [[
                            'id'           => 1,
                            'name'         => 'Crackers',
                            'slug'         => 'crackers',
                            'image'        => 'https://example.com/cat.jpg',
                            'is_exclusive' => false,
                        ]],
                    ],
                ],

                // ── Products ─────────────────────────────────────────────
                [
                    'group'       => 'Products',
                    'method'      => 'GET',
                    'url'         => $base . '/{site}/products',
                    'description' => 'List products (with optional filters)',
                    'auth'        => false,
                    'params'      => [
                        ['in' => 'path',  'name' => 'site',     'type' => 'string',  'required' => true,  'description' => 'Site slug'],
                        ['in' => 'query', 'name' => 'category', 'type' => 'string',  'required' => false, 'description' => 'Filter by category slug'],
                        ['in' => 'query', 'name' => 'search',   'type' => 'string',  'required' => false, 'description' => 'Search by product name'],
                        ['in' => 'query', 'name' => 'per_page', 'type' => 'integer', 'required' => false, 'description' => 'Items per page (default: 20, max: 100)'],
                    ],
                    'response_example' => [
                        'success' => true,
                        'site'    => ['name' => 'Creakers', 'slug' => 'creakers'],
                        'data'    => [[
                            'id'          => 1,
                            'name'        => 'Sky Shot',
                            'slug'        => 'sky-shot',
                            'per'         => 'box',
                            'description' => 'Description here',
                            'image'       => 'https://example.com/product.jpg',
                            'gallery'     => ['https://example.com/g1.jpg'],
                            'category'    => ['id' => 1, 'name' => 'Crackers', 'slug' => 'crackers'],
                            'pricing'     => [
                                'mrp'            => 500.00,
                                'discount_type'  => 'percentage',
                                'discount_value' => 10.00,
                                'our_price'      => 450.00,
                                'savings'        => 50.00,
                            ],
                        ]],
                        'meta' => [
                            'current_page' => 1,
                            'last_page'    => 5,
                            'per_page'     => 20,
                            'total'        => 100,
                        ],
                    ],
                ],
                [
                    'group'       => 'Products',
                    'method'      => 'GET',
                    'url'         => $base . '/{site}/products/{slug}',
                    'description' => 'Get a single product by slug',
                    'auth'        => false,
                    'params'      => [
                        ['in' => 'path', 'name' => 'site', 'type' => 'string', 'required' => true, 'description' => 'Site slug'],
                        ['in' => 'path', 'name' => 'slug', 'type' => 'string', 'required' => true, 'description' => 'Product slug'],
                    ],
                    'response_example' => [
                        'success' => true,
                        'data'    => [
                            'id'       => 1,
                            'name'     => 'Sky Shot',
                            'slug'     => 'sky-shot',
                            'per'      => 'box',
                            'image'    => 'https://example.com/product.jpg',
                            'gallery'  => ['https://example.com/g1.jpg'],
                            'category' => ['id' => 1, 'name' => 'Crackers', 'slug' => 'crackers'],
                            'pricing'  => [
                                'mrp'            => 500.00,
                                'discount_type'  => 'percentage',
                                'discount_value' => 10.00,
                                'our_price'      => 450.00,
                                'savings'        => 50.00,
                            ],
                        ],
                    ],
                ],
                [
                    'group'       => 'Products',
                    'method'      => 'GET',
                    'url'         => $base . '/{site}/categories/{categorySlug}/products',
                    'description' => 'Get products filtered to a specific category',
                    'auth'        => false,
                    'params'      => [
                        ['in' => 'path',  'name' => 'site',         'type' => 'string',  'required' => true,  'description' => 'Site slug'],
                        ['in' => 'path',  'name' => 'categorySlug', 'type' => 'string',  'required' => true,  'description' => 'Category slug'],
                        ['in' => 'query', 'name' => 'per_page',     'type' => 'integer', 'required' => false, 'description' => 'Items per page (default: 20, max: 100)'],
                    ],
                    'response_example' => [
                        'success'  => true,
                        'category' => ['name' => 'Crackers', 'slug' => 'crackers'],
                        'data'     => [['id' => 1, 'name' => 'Sky Shot', '...' => '...']],
                        'meta'     => ['current_page' => 1, 'last_page' => 2, 'per_page' => 20, 'total' => 30],
                    ],
                ],

                // ── Orders ───────────────────────────────────────────────
                [
                    'group'       => 'Orders',
                    'method'      => 'POST',
                    'url'         => $base . '/{site}/orders',
                    'description' => 'Place a new order',
                    'auth'        => false,
                    'params'      => [
                        ['in' => 'path', 'name' => 'site', 'type' => 'string', 'required' => true, 'description' => 'Site slug'],
                    ],
                    'body' => [
                        ['name' => 'customer_name',     'type' => 'string',  'required' => true,  'description' => 'max: 255'],
                        ['name' => 'customer_phone',    'type' => 'string',  'required' => true,  'description' => 'max: 20'],
                        ['name' => 'customer_email',    'type' => 'string',  'required' => false, 'description' => 'valid email'],
                        ['name' => 'customer_address',  'type' => 'string',  'required' => true,  'description' => ''],
                        ['name' => 'customer_city',     'type' => 'string',  'required' => true,  'description' => 'max: 100'],
                        ['name' => 'customer_district', 'type' => 'string',  'required' => false, 'description' => 'max: 100'],
                        ['name' => 'customer_state',    'type' => 'string',  'required' => false, 'description' => 'max: 100'],
                        ['name' => 'customer_pincode',  'type' => 'string',  'required' => true,  'description' => 'max: 10'],
                        ['name' => 'notes',             'type' => 'string',  'required' => false, 'description' => ''],
                        ['name' => 'items',             'type' => 'array',   'required' => true,  'description' => 'min 1 item'],
                        ['name' => 'items[].product_id','type' => 'integer', 'required' => true,  'description' => 'must exist in products'],
                        ['name' => 'items[].quantity',  'type' => 'integer', 'required' => true,  'description' => 'min: 1'],
                    ],
                    'request_example' => [
                        'customer_name'     => 'John Doe',
                        'customer_phone'    => '9876543210',
                        'customer_email'    => 'john@example.com',
                        'customer_address'  => '123 Main St',
                        'customer_city'     => 'Chennai',
                        'customer_district' => 'Chennai',
                        'customer_state'    => 'Tamil Nadu',
                        'customer_pincode'  => '600001',
                        'notes'             => 'Deliver before 5pm',
                        'items'             => [
                            ['product_id' => 1, 'quantity' => 2],
                            ['product_id' => 5, 'quantity' => 1],
                        ],
                    ],
                    'response_status'  => 201,
                    'response_example' => [
                        'success' => true,
                        'message' => 'Order placed successfully.',
                        'data'    => [
                            'order_number' => 'CRAC-2026-01',
                            'total_amount' => 1350.00,
                            'status'       => 'Pending',
                            'items_count'  => 3,
                        ],
                    ],
                ],
                [
                    'group'       => 'Orders',
                    'method'      => 'GET',
                    'url'         => $base . '/{site}/orders/track',
                    'description' => 'Track orders by phone, email, or order number (at least one required)',
                    'auth'        => false,
                    'params'      => [
                        ['in' => 'path',  'name' => 'site',         'type' => 'string', 'required' => true,  'description' => 'Site slug'],
                        ['in' => 'query', 'name' => 'order_number', 'type' => 'string', 'required' => false, 'description' => 'Exact order number — returns single order'],
                        ['in' => 'query', 'name' => 'customer_email','type' => 'string','required' => false, 'description' => 'Customer email'],
                        ['in' => 'query', 'name' => 'customer_phone','type' => 'string','required' => false, 'description' => 'Customer phone'],
                    ],
                    'response_example' => [
                        'success' => true,
                        'data'    => [
                            'order_number'     => 'CRAC-2026-01',
                            'status'           => 'Pending',
                            'customer_name'    => 'John Doe',
                            'customer_phone'   => '9876543210',
                            'customer_email'   => 'john@example.com',
                            'customer_address' => '123 Main St',
                            'customer_city'    => 'Chennai',
                            'customer_district'=> 'Chennai',
                            'customer_state'   => 'Tamil Nadu',
                            'customer_pincode' => '600001',
                            'total_amount'     => 1350.00,
                            'notes'            => null,
                            'created_at'       => '04 Jun 2026, 10:30 AM',
                            'items'            => [[
                                'product_name'  => 'Sky Shot',
                                'category_name' => 'Crackers',
                                'mrp'           => 500.00,
                                'our_price'     => 450.00,
                                'quantity'      => 2,
                                'subtotal'      => 900.00,
                            ]],
                        ],
                    ],
                ],
                [
                    'group'       => 'Orders',
                    'method'      => 'GET',
                    'url'         => $base . '/{site}/orders/{orderNumber}',
                    'description' => 'Get full details for a specific order',
                    'auth'        => false,
                    'params'      => [
                        ['in' => 'path', 'name' => 'site',        'type' => 'string', 'required' => true, 'description' => 'Site slug'],
                        ['in' => 'path', 'name' => 'orderNumber', 'type' => 'string', 'required' => true, 'description' => 'Order number e.g. CRAC-2026-01'],
                    ],
                    'response_example' => [
                        'success' => true,
                        'data'    => ['order_number' => 'CRAC-2026-01', '...' => '...'],
                    ],
                ],

                // ── Home Banner ──────────────────────────────────────────
                [
                    'group'       => 'Home Banner',
                    'method'      => 'GET',
                    'url'         => $base . '/{site}/home-banner',
                    'description' => 'Get the home page banner content',
                    'auth'        => false,
                    'params'      => [
                        ['in' => 'path', 'name' => 'site', 'type' => 'string', 'required' => true, 'description' => 'Site slug'],
                    ],
                    'response_example' => [
                        'success' => true,
                        'data'    => [
                            'image'                 => 'https://example.com/banner.jpg',
                            'title'                 => 'Vigo Crackers',
                            'second_title'          => 'Light Up Your Celebrations',
                            'description'           => 'Experience the finest selection of premium fireworks and crackers. Safe, certified, and delivered to your doorstep.',
                            'top_small_description' => 'Premium Quality Fireworks Since 1990',
                            'buttons'               => [
                                ['label' => 'Shop Now', 'url' => 'https://...', 'open_in_new_tab' => false],
                                ['label' => 'View Catalog', 'url' => 'https://...', 'open_in_new_tab' => true],
                            ],
                        ],
                    ],
                ],

                // ── Contact ──────────────────────────────────────────────
                [
                    'group'       => 'Contact',
                    'method'      => 'GET',
                    'url'         => $base . '/{site}/contact',
                    'description' => 'Get site contact details',
                    'auth'        => false,
                    'params'      => [
                        ['in' => 'path', 'name' => 'site', 'type' => 'string', 'required' => true, 'description' => 'Site slug'],
                    ],
                    'response_example' => [
                        'success' => true,
                        'data'    => [
                            'address'       => '123 Market Street, Chennai',
                            'phones'        => ['9876543210', '9876543211'],
                            'email'         => 'info@creakers.com',
                            'opening_time'  => 'Mon–Sat: 9am–6pm',
                            'social_links'  => [
                                ['label' => 'Facebook', 'url' => 'https://...', 'icon' => 'https://...'],
                            ],
                            'map_embed_url' => 'https://maps.google.com/...',
                        ],
                    ],
                ],

                // ── Content Pages ────────────────────────────────────────
                [
                    'group'       => 'Content',
                    'method'      => 'GET',
                    'url'         => $base . '/{site}/content',
                    'description' => 'Get all content pages (About, Terms, etc.)',
                    'auth'        => false,
                    'params'      => [
                        ['in' => 'path', 'name' => 'site', 'type' => 'string', 'required' => true, 'description' => 'Site slug'],
                    ],
                    'response_example' => [
                        'success' => true,
                        'data'    => [[
                            'key'        => 'about-us',
                            'title'      => 'About Us',
                            'body'       => '<p>HTML content...</p>',
                            'updated_at' => '2026-06-04T10:00:00.000000Z',
                        ]],
                    ],
                ],
                [
                    'group'       => 'Content',
                    'method'      => 'GET',
                    'url'         => $base . '/{site}/content/{key}',
                    'description' => 'Get a single content page by key',
                    'auth'        => false,
                    'params'      => [
                        ['in' => 'path', 'name' => 'site', 'type' => 'string', 'required' => true, 'description' => 'Site slug'],
                        ['in' => 'path', 'name' => 'key',  'type' => 'string', 'required' => true, 'description' => 'Content key e.g. about-us'],
                    ],
                    'response_example' => [
                        'success' => true,
                        'data'    => [
                            'key'        => 'about-us',
                            'title'      => 'About Us',
                            'body'       => '<p>HTML content...</p>',
                            'updated_at' => '2026-06-04T10:00:00.000000Z',
                        ],
                    ],
                ],

                // ── Client Logos ─────────────────────────────────────────
                [
                    'group'       => 'Client Logos',
                    'method'      => 'GET',
                    'url'         => $base . '/{site}/client-logos',
                    'description' => 'Get all client logos for a site',
                    'auth'        => false,
                    'params'      => [
                        ['in' => 'path', 'name' => 'site', 'type' => 'string', 'required' => true, 'description' => 'Site slug'],
                    ],
                    'response_example' => [
                        'success' => true,
                        'data'    => [
                            ['name' => 'Acme Corp', 'logo' => 'https://example.com/logo.png'],
                        ],
                    ],
                ],

                // ── Order Steps ──────────────────────────────────────────
                [
                    'group'       => 'Order Steps',
                    'method'      => 'GET',
                    'url'         => $base . '/{site}/order-steps',
                    'description' => 'Get "How to Order" steps',
                    'auth'        => false,
                    'params'      => [
                        ['in' => 'path', 'name' => 'site', 'type' => 'string', 'required' => true, 'description' => 'Site slug'],
                    ],
                    'response_example' => [
                        'success' => true,
                        'data'    => [[
                            'step'        => 1,
                            'title'       => 'Browse Products',
                            'description' => 'Choose from our catalog',
                            'icon'        => 'https://example.com/icon.png',
                        ]],
                    ],
                ],

                // ── Safety Tips ──────────────────────────────────────────
                [
                    'group'       => 'Safety Tips',
                    'method'      => 'GET',
                    'url'         => $base . '/{site}/safety-tips',
                    'description' => 'Get safety tip images and text tips',
                    'auth'        => false,
                    'params'      => [
                        ['in' => 'path', 'name' => 'site', 'type' => 'string', 'required' => true, 'description' => 'Site slug'],
                    ],
                    'response_example' => [
                        'success' => true,
                        'data'    => [
                            'images' => [['image' => 'https://example.com/safety.jpg']],
                            'tips'   => [[
                                'title'       => 'Keep away from children',
                                'description' => 'Always supervise...',
                            ]],
                        ],
                    ],
                ],

                // ── Price Lists ──────────────────────────────────────────
                [
                    'group'       => 'Price Lists',
                    'method'      => 'GET',
                    'url'         => $base . '/{site}/price-lists',
                    'description' => 'Get downloadable price list PDFs',
                    'auth'        => false,
                    'params'      => [
                        ['in' => 'path', 'name' => 'site', 'type' => 'string', 'required' => true, 'description' => 'Site slug'],
                    ],
                    'response_example' => [
                        'success' => true,
                        'data'    => [[
                            'title'      => '2026 Price List',
                            'url'        => 'https://example.com/price-list.pdf',
                            'updated_at' => '04 Jun 2026',
                        ]],
                    ],
                ],

            ],

            'error_responses' => [
                ['status' => 200, 'meaning' => 'Success'],
                ['status' => 201, 'meaning' => 'Order created successfully'],
                ['status' => 404, 'meaning' => 'Site or resource not found / inactive'],
                ['status' => 422, 'meaning' => 'Validation error — check errors key for details'],
            ],

            'order_number_format' => [
                'pattern'     => '{SITE}-{YEAR}-{SEQ}',
                'example'     => 'CRAC-2026-01',
                'description' => 'First 4 letters of site name (uppercase) + current year + zero-padded sequence per year per site',
            ],
        ]);
    }
}

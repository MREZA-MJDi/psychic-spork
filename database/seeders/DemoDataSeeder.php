<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\ChequePayment;
use App\Models\ChequePermission;
use App\Models\FinancialTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\WholesalePack;
use App\Models\WholesalePackItem;
use App\Models\WholesaleProfile;
use App\Services\DoubleEntryAccountingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    /**
     * Local/demo business data.
     *
     * Safe to run repeatedly: records are addressed by stable demo emails,
     * order numbers, SKUs and pack slugs instead of creating duplicates.
     */
    public function run(): void
    {
        $this->command?->info('Creating Janan demo customers, wholesale packs and orders...');

        $admin = User::query()
            ->where('is_admin', true)
            ->first();

        if (! $admin) {
            $this->command?->warn('No admin exists yet. Run AdminUserSeeder first.');
        }

        $password = (string) env('JANAN_DEMO_PASSWORD', 'password');

        $customer = $this->customer(
            'demo-customer@janan.local',
            'سارا احمدی',
            '09120000001',
            $password
        );

        $wholesaleCustomer = $this->customer(
            'wholesale@janan.local',
            'مریم کریمی',
            '09120000002',
            $password
        );

        $chequeCustomer = $this->customer(
            'cheque@janan.local',
            'نگار رضایی',
            '09120000003',
            $password
        );

        $this->address($customer, 'منزل', 'سارا احمدی', '09120000001');
        $wholesaleAddress = $this->address(
            $wholesaleCustomer,
            'فروشگاه',
            'مریم کریمی',
            '09120000002',
            'تهران',
            'تهران',
            '1598712345',
            'خیابان ولیعصر، بالاتر از پارک ساعی، پلاک ۱۲۸'
        );
        $chequeAddress = $this->address(
            $chequeCustomer,
            'فروشگاه',
            'نگار رضایی',
            '09120000003',
            'تهران',
            'تهران',
            '1487612345',
            'خیابان شریعتی، خیابان بهار شیراز، پلاک ۴۲'
        );

        // Online wholesale is available to every customer. These profiles are
        // only realistic business metadata and optional minimum-order terms.
        WholesaleProfile::updateOrCreate(
            ['user_id' => $wholesaleCustomer->id],
            [
                'business_name' => 'فروشگاه مریم',
                'business_type' => 'فروشگاه لباس زیر',
                'business_phone' => '02188770001',
                'business_address' => 'تهران، ولیعصر',
                'status' => 'approved',
                'approved_by' => $admin?->id,
                'approved_at' => now(),
                'minimum_order_amount' => 3000000,
                'minimum_order_quantity' => 6,
                'admin_note' => 'Demo wholesale profile',
            ]
        );

        WholesaleProfile::updateOrCreate(
            ['user_id' => $chequeCustomer->id],
            [
                'business_name' => 'بوتیک نگار',
                'business_type' => 'بوتیک',
                'business_phone' => '02122330002',
                'business_address' => 'تهران، شریعتی',
                'status' => 'approved',
                'approved_by' => $admin?->id,
                'approved_at' => now(),
                'minimum_order_amount' => 3000000,
                'minimum_order_quantity' => 6,
                'admin_note' => 'Demo cheque-enabled wholesale customer',
            ]
        );

        // Cheque purchase is the only wholesale path requiring explicit admin permission.
        ChequePermission::updateOrCreate(
            ['user_id' => $chequeCustomer->id],
            [
                'enabled' => true,
                'max_order_amount' => 25000000,
                'approved_by' => $admin?->id,
                'approved_at' => now(),
                'disabled_by' => null,
                'disabled_at' => null,
                'admin_note' => 'مجوز آزمایشی خرید چکی تا سقف ۲۵ میلیون تومان',
            ]
        );

        $variants = ProductVariant::query()
            ->with('product')
            ->whereIn('sku', [
                'JAN-ISA-BRA-001',
                'JAN-PAN-BRA-001',
                'JAN-AVI-PAN-001',
                'JAN-NOS-PAN-001',
                'JAN-LAY-SET-001',
                'JAN-JAN-BOD-001',
            ])
            ->get()
            ->keyBy('sku');

        if ($variants->count() < 6) {
            $this->command?->warn(
                'Some demo variants are missing. Run ProductSeeder before DemoDataSeeder.'
            );
            return;
        }

        $pack = WholesalePack::updateOrCreate(
            ['slug' => 'demo-wholesale-pack-12'],
            [
                'name' => 'پک عمده ۱۲ عددی جانان',
                'description' => 'پک ترکیبی آزمایشی برای تست واقعی کاتالوگ عمده و سفارش.',
                'pack_quantity' => 12,
                'pack_price' => 6900000,
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        $packItems = [
            ['sku' => 'JAN-ISA-BRA-001', 'quantity' => 4],
            ['sku' => 'JAN-PAN-BRA-001', 'quantity' => 3],
            ['sku' => 'JAN-AVI-PAN-001', 'quantity' => 5],
        ];

        foreach ($packItems as $item) {
            WholesalePackItem::updateOrCreate(
                [
                    'wholesale_pack_id' => $pack->id,
                    'product_variant_id' => $variants[$item['sku']]->id,
                ],
                ['quantity' => $item['quantity']]
            );
        }

        $smallPack = WholesalePack::updateOrCreate(
            ['slug' => 'demo-wholesale-pack-6'],
            [
                'name' => 'پک عمده ۶ عددی فانتزی',
                'description' => 'پک کوچک‌تر برای تست حداقل تعداد و انتخاب پک.',
                'pack_quantity' => 6,
                'pack_price' => 5400000,
                'sort_order' => 2,
                'is_active' => true,
            ]
        );

        foreach ([
            ['sku' => 'JAN-NOS-PAN-001', 'quantity' => 3],
            ['sku' => 'JAN-LAY-SET-001', 'quantity' => 3],
        ] as $item) {
            WholesalePackItem::updateOrCreate(
                [
                    'wholesale_pack_id' => $smallPack->id,
                    'product_variant_id' => $variants[$item['sku']]->id,
                ],
                ['quantity' => $item['quantity']]
            );
        }

        // Paid retail order: delivered + successful online payment.
        $retailOrder = $this->order(
            number: 'DEMO-RET-1001',
            user: $customer,
            address: $customer->addresses()->default()->first(),
            type: 'retail',
            status: 'delivered',
            paymentStatus: 'paid',
            paymentMethod: 'online',
            items: [
                [$variants['JAN-LAY-SET-001'], 1, 1590000],
                [$variants['JAN-AVI-PAN-001'], 2, 360000],
            ],
            note: 'سفارش دمو مشتری عادی',
            placedDaysAgo: 8
        );

        $this->paidOnlinePayment($retailOrder, 'DEMO-TXN-RET-1001');
        $this->recordFinancials($retailOrder, 'bank', 'سفارش آنلاین دمو مشتری');

        // Paid wholesale order built from the actual pack components.
        // The pack has its own final price; it is not calculated from variant prices.
        $wholesaleOrder = $this->order(
            number: 'DEMO-WHO-2001',
            user: $wholesaleCustomer,
            address: $wholesaleAddress,
            type: 'wholesale',
            status: 'confirmed',
            paymentStatus: 'paid',
            paymentMethod: 'online',
            items: $this->packOrderItems($pack, $variants),
            note: 'سفارش دمو: خرید آنلاین یک پک عمده ۱۲ عددی',
            placedDaysAgo: 3
        );

        $this->paidOnlinePayment($wholesaleOrder, 'DEMO-TXN-WHO-2001');
        $this->recordFinancials($wholesaleOrder, 'bank', 'فروش آنلاین عمده دمو');

        // Cheque wholesale order: submitted and waiting for admin review.
        $chequeOrder = $this->order(
            number: 'DEMO-CHK-3001',
            user: $chequeCustomer,
            address: $chequeAddress,
            type: 'wholesale',
            status: 'pending',
            paymentStatus: 'pending',
            paymentMethod: 'cheque',
            items: [
                [$variants['JAN-NOS-PAN-001'], 6, 320000],
                [$variants['JAN-JAN-BOD-001'], 4, 1100000],
            ],
            note: 'سفارش دمو خرید عمده با چک؛ منتظر بررسی مدیر',
            placedDaysAgo: 1
        );

        $chequePayment = Payment::updateOrCreate(
            ['idempotency_key' => 'demo-cheque-3001'],
            [
                'order_id' => $chequeOrder->id,
                'gateway' => 'cheque',
                'transaction_id' => null,
                'authority' => null,
                'reference_number' => 'DEMO-CHEQUE-3001',
                'amount' => $chequeOrder->total,
                'status' => 'pending',
                'metadata' => ['demo' => true, 'source' => 'DemoDataSeeder'],
                'paid_at' => null,
            ]
        );

        ChequePayment::updateOrCreate(
            ['payment_id' => $chequePayment->id],
            [
                'order_id' => $chequeOrder->id,
                'sayad_id' => '1234567890123456',
                'cheque_number' => 'DEMO-3001',
                'bank_name' => 'بانک ملت',
                'account_holder' => 'نگار رضایی',
                'amount' => $chequeOrder->total,
                'due_date' => now()->addDays(25)->toDateString(),
                'image_path' => null,
                'status' => 'submitted',
                'reviewed_by' => null,
                'reviewed_at' => null,
                'review_note' => null,
            ]
        );

        // A cancelled order gives the admin/order dashboard another real state to inspect.
        $cancelled = $this->order(
            number: 'DEMO-RET-1002',
            user: $customer,
            address: $customer->addresses()->default()->first(),
            type: 'retail',
            status: 'cancelled',
            paymentStatus: 'refunded',
            paymentMethod: 'online',
            items: [
                [$variants['JAN-ISA-BRA-001'], 1, 1090000],
            ],
            note: 'سفارش دمو لغوشده',
            placedDaysAgo: 12
        );

        Payment::updateOrCreate(
            ['idempotency_key' => 'demo-refund-1002'],
            [
                'order_id' => $cancelled->id,
                'gateway' => 'zarinpal',
                'transaction_id' => 'DEMO-TXN-RET-1002',
                'reference_number' => 'DEMO-REF-1002',
                'amount' => $cancelled->total,
                'status' => 'refunded',
                'metadata' => ['demo' => true, 'source' => 'DemoDataSeeder'],
                'paid_at' => now()->subDays(11),
            ]
        );

        $this->command?->info('Demo data ready: customers, addresses, wholesale profiles, cheque permission, packs, paid orders and cheque order.');
    }

    private function customer(
        string $email,
        string $name,
        string $phone,
        string $password
    ): User {
        return User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'phone' => $phone,
                'password' => Hash::make($password),
                'is_admin' => false,
            ]
        );
    }

    private function address(
        User $user,
        string $title,
        string $recipientName,
        string $phone,
        string $province = 'تهران',
        string $city = 'تهران',
        string $postalCode = '1417712345',
        string $address = 'تهران، خیابان نمونه، پلاک ۱۰'
    ): Address {
        return Address::updateOrCreate(
            [
                'user_id' => $user->id,
                'title' => $title,
            ],
            [
                'recipient_name' => $recipientName,
                'recipient_phone' => $phone,
                'province' => $province,
                'city' => $city,
                'postal_code' => $postalCode,
                'address' => $address,
                'plaque' => '۱۰',
                'unit' => '۲',
                'is_default' => true,
            ]
        );
    }

    private function order(
        string $number,
        User $user,
        ?Address $address,
        string $type,
        string $status,
        string $paymentStatus,
        string $paymentMethod,
        array $items,
        string $note,
        int $placedDaysAgo
    ): Order {
        $subtotal = 0.0;

        foreach ($items as $item) {
            $subtotal += ((float) $item[2]) * ((int) $item[1]);
        }

        $order = Order::updateOrCreate(
            ['order_number' => $number],
            [
                'user_id' => $user->id,
                'address_id' => $address?->id,
                'customer_name' => $user->name,
                'customer_phone' => $user->phone ?? '',
                'customer_email' => $user->email,
                'shipping_address' => $address?->address ?? 'آدرس دمو',
                'shipping_province' => $address?->province,
                'shipping_city' => $address?->city,
                'postal_code' => $address?->postal_code,
                'status' => $status,
                'payment_status' => $paymentStatus,
                'payment_method' => $paymentMethod,
                'order_type' => $type,
                'subtotal' => $subtotal,
                'discount' => 0,
                'shipping_cost' => 0,
                'total' => $subtotal,
                'customer_note' => $note,
                'tracking_code' => $status === 'delivered' ? 'DEMO-TRACK-1001' : null,
                'placed_at' => now()->subDays($placedDaysAgo),
                'paid_at' => $paymentStatus === 'paid' ? now()->subDays($placedDaysAgo)->addHours(2) : null,
                'delivered_at' => $status === 'delivered' ? now()->subDays(max(1, $placedDaysAgo - 3)) : null,
                'cancelled_at' => $status === 'cancelled' ? now()->subDays(max(1, $placedDaysAgo - 1)) : null,
            ]
        );

        $order->items()->delete();

        foreach ($items as $item) {
            /** @var ProductVariant $variant */
            $variant = $item[0];
            $quantity = (int) $item[1];
            $unitPrice = (float) $item[2];

            $order->items()->create([
                'product_id' => $variant->product_id,
                'product_variant_id' => $variant->id,
                'product_name' => $variant->product?->name ?? 'محصول دمو',
                'variant_name' => $variant->display_name,
                'sku' => $variant->sku,
                'product_image' => 'products/' . $variant->product?->slug . '.svg',
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'line_total' => $unitPrice * $quantity,
            ]);
        }

        return $order->fresh('items');
    }

    private function packOrderItems(
        WholesalePack $pack,
        $variants
    ): array {
        $unitPackPrice = (float) $pack->pack_price / max(1, (int) $pack->pack_quantity);

        return $pack->items
            ->map(function (WholesalePackItem $item) use ($variants, $unitPackPrice) {
                $variant = $item->variant ?? $variants->firstWhere('id', $item->product_variant_id);

                return [$variant, (int) $item->quantity, $unitPackPrice];
            })
            ->values()
            ->all();
    }

    private function paidOnlinePayment(Order $order, string $transactionId): Payment
    {
        return Payment::updateOrCreate(
            ['idempotency_key' => 'demo-' . Str::slug($order->order_number)],
            [
                'order_id' => $order->id,
                'gateway' => 'zarinpal',
                'transaction_id' => $transactionId,
                'authority' => 'DEMO-AUTH-' . $order->id,
                'reference_number' => 'DEMO-REF-' . $order->id,
                'amount' => $order->total,
                'status' => 'paid',
                'metadata' => ['demo' => true, 'source' => 'DemoDataSeeder'],
                'paid_at' => $order->paid_at ?? now(),
            ]
        );
    }

    private function recordFinancials(Order $order, string $account, string $description): void
    {
        FinancialTransaction::updateOrCreate(
            [
                'reference_type' => Order::class,
                'reference_id' => $order->id,
                'category' => 'demo-sale',
            ],
            [
                'type' => 'income',
                'amount' => $order->total,
                'description' => $description,
                'transaction_date' => $order->paid_at?->toDateString() ?? now()->toDateString(),
            ]
        );

        app(DoubleEntryAccountingService::class)->recordSale(
            reference: $order,
            amount: (float) $order->total,
            settlementAccount: $account,
            sourceKey: 'demo-order-sale-' . $order->id,
            description: $description
        );
    }
}

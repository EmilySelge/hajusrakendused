<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class OrderController extends Controller
{
    public function checkout()
    {
        return Inertia::render('shop/Checkout', [
            'stripeKey' => config('services.stripe.key'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Calculate total and build line items
        $total = 0;
        $orderItems = [];
        $lineItems = [];

        foreach ($validated['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            $subtotal = $product->price * $item['quantity'];
            $total += $subtotal;

            $orderItems[] = [
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $product->price,
            ];

            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $product->name,
                        'description' => $product->description,
                    ],
                    'unit_amount' => (int) ($product->price * 100), // Stripe uses cents
                ],
                'quantity' => $item['quantity'],
            ];
        }

        // Create order with pending status
        $order = Order::create([
            'user_id' => $request->user()->id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'total' => $total,
            'status' => 'pending',
            'payment_method' => 'stripe',
        ]);

        foreach ($orderItems as $item) {
            $order->items()->create($item);
        }

        // Create Stripe Checkout Session
        Stripe::setApiKey(config('services.stripe.secret'));

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => route('shop.success', $order->id) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('shop.cancel', $order->id),
            'customer_email' => $validated['email'],
            'metadata' => [
                'order_id' => $order->id,
            ],
        ]);

        // Save the Stripe session ID
        $order->update(['payment_id' => $session->id]);

        return Inertia::location($session->url);
    }

    public function success(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        // Verify payment with Stripe
        if ($order->status === 'pending' && $request->query('session_id')) {
            Stripe::setApiKey(config('services.stripe.secret'));

            $session = StripeSession::retrieve($request->query('session_id'));

            if ($session->payment_status === 'paid') {
                $order->update(['status' => 'paid']);
            }
        }

        return Inertia::render('shop/Success', [
            'order' => [
                'id' => $order->id,
                'total' => $order->total,
                'status' => $order->status,
                'payment_id' => $order->payment_id,
                'created_at' => $order->created_at->format('d.m.Y H:i'),
            ],
        ]);
    }

    public function cancel(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->update(['status' => 'failed']);

        return redirect()->route('shop.index')->withErrors([
            'payment' => 'Payment was cancelled. Your items are still in your cart.',
        ]);
    }
}
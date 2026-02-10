<?php

namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Gloudemans\Shoppingcart\Facades\Cart;

class PosController extends Controller
{
    public function index()
    {
        $todayDate = Carbon::now();
        $row = (int) request('row', 10);

        if ($row < 1 || $row > 100) {
            abort(400, 'The per-page parameter must be an integer between 1 and 100.');
        }

        return view('pos.index', [
            'customers' => Customer::all()->sortBy('name'),
            'productItem' => Cart::content(),
            'products' => Product::where('expire_date', '>', $todayDate)->filter(request(['search']))
                ->sortable()
                ->paginate($row)
                ->appends(request()->query()),
        ]);
    }

    public function addCart(Request $request)
    {
        $rules = [
            'id' => 'required|numeric',
            'name' => 'required|string',
            'price' => 'required|numeric',
        ];

        $validatedData = $request->validate($rules);

        // Get product untuk cek stock
        $product = Product::find($validatedData['id']);

        if (!$product) {
            return redirect()->back()->with('error', 'Produk tidak ditemukan!');
        }

        // GANTI 'stock' dengan 'product_store'
        if ($product->product_store < 1) {
            return redirect()->back()->with('error', 'Stok produk habis!');
        }

        $qtyInCart = 0;
        foreach (Cart::content() as $item) {
            if ($item->id == $validatedData['id']) {
                $qtyInCart = $item->qty;
                break;
            }
        }
        if (($qtyInCart + 1) > $product->product_store) {
            return redirect()->back()->with('error', "Tidak bisa menambah lagi! Stok: {$product->product_store}, Di keranjang: {$qtyInCart}");
        }

        // Add to cart
        Cart::add([
            'id' => $validatedData['id'],
            'name' => $validatedData['name'],
            'qty' => 1,
            'price' => $validatedData['price'],
            'options' => [
                'stock' => $product->product_store
            ]
        ]);

        return redirect()->back()->with('success', 'Produk berhasil ditambahkan!');
    }

    public function searchProducts(Request $request)
    {
        $query = $request->get('q', '');

        if (empty($query)) {
            return response()->json([
                'success' => true,
                'products' => []
            ]);
        }

        $products = Product::where(function ($q) use ($query) {
            $q->where('product_name', 'LIKE', "%{$query}%")
                ->orWhere('product_code', 'LIKE', "%{$query}%");
        })
            ->limit(20)
            ->get(['id', 'product_name', 'product_code', 'selling_price']);

        return response()->json([
            'success' => true,
            'products' => $products
        ]);
    }

    public function addCartByBarcode(Request $request)
    {
        $rules = [
            'barcode' => 'required|string',
        ];

        $validatedData = $request->validate($rules);
        $todayDate = Carbon::now();

        $product = Product::where('product_code', $validatedData['barcode'])
            ->where('expire_date', '>', $todayDate)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk dengan barcode tersebut tidak ditemukan atau sudah kadaluarsa.'
            ], 404);
        }

        // Check if product already in cart
        $cartItem = Cart::search(function ($cartItem) use ($product) {
            return $cartItem->id === $product->id;
        });

        if ($cartItem->isNotEmpty()) {
            // Update quantity if already in cart
            $item = $cartItem->first();
            Cart::update($item->rowId, $item->qty + 1);
            $message = 'Jumlah produk di keranjang berhasil ditambahkan!';
        } else {
            // Add new item to cart
            Cart::add([
                'id' => $product->id,
                'name' => $product->product_name,
                'qty' => 1,
                'price' => $product->selling_price,
                'options' => ['size' => 'large']
            ]);
            $message = 'Produk berhasil ditambahkan ke keranjang!';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'product' => [
                'name' => $product->product_name,
                'price' => $product->selling_price,
                'code' => $product->product_code
            ],
            'cart_count' => Cart::count(),
            'cart_total' => Cart::total()
        ]);
    }

    public function updateCart(Request $request, $rowId)
    {
        $rules = [
            'qty' => 'required|numeric',
        ];

        $validatedData = $request->validate($rules);

        Cart::update($rowId, $validatedData['qty']);

        return Redirect::back()->with('success', 'Keranjang berhasil diperbarui!');
    }

    public function deleteCart(String $rowId)
    {
        Cart::remove($rowId);

        return Redirect::back()->with('success', 'Keranjang berhasil dihapus!');
    }

    public function createInvoice(Request $request)
    {
        $rules = [
            'customer_id' => 'nullable'
        ];

        $validatedData = $request->validate($rules);

        if (!empty($validatedData['customer_id'])) {
            $customer = Customer::where('id', $validatedData['customer_id'])->first();
        } else {
            $customer = new Customer();
            $customer->name = 'Pelanggan Umum';
            $customer->email = '-';
            $customer->phone = '-';
            $customer->address = '-';
            $customer->shopname = '-';
            $customer->bank_name = '-';
            $customer->account_number = '-';
            $customer->id = ''; // Ensure ID is empty/null
        }

        $content = Cart::content();

        return view('pos.create-invoice', [
            'customer' => $customer,
            'content' => $content
        ]);
    }

    public function printInvoice(Request $request)
    {
        $rules = [
            'customer_id' => 'nullable'
        ];

        $validatedData = $request->validate($rules);

        if (!empty($validatedData['customer_id'])) {
            $customer = Customer::where('id', $validatedData['customer_id'])->first();
        } else {
            $customer = new Customer();
            $customer->name = 'Pelanggan Umum';
            $customer->email = '-';
            $customer->phone = '-';
            $customer->address = '-';
            $customer->shopname = '-';
            $customer->bank_name = '-';
            $customer->account_number = '-';
            $customer->id = '';
        }

        $content = Cart::content();

        return view('pos.print-invoice', [
            'customer' => $customer,
            'content' => $content
        ]);
    }
}

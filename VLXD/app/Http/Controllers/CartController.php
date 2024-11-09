<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\CustomerAddress;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ShippingCharge;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Colors\Rgb\Channels\Red;
use PhpParser\Node\Expr\FuncCall;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $product = Product::with('product_images')->find($request->id);
        if ($product == null) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found'

            ]);
        }

        if (Cart::count() > 0) {

            //echo "Product already in cart";
            // Products found in cart
            // Check if this product already in the cart
            // Return as message that product already added in your cart
            // if product not found in the cart, then add product in cart

            $cartContent = Cart::content();
            $productAlreadyExist = false;
            foreach ($cartContent as $item) {
                if ($item->id == $product->id) {
                    $productAlreadyExist  = true;
                }
            }
            if ($productAlreadyExist == false) {
                Cart::add($product->id, $product->title, 1, $product->price, ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '']);

                $status = true;
                $message = '<strong>' . $product->title . '</strong> added in your cart successfully.';
                session()->flash('success',  $message);
            } else {
                $status = false;
                $message = $product->title . ' already added in cart';
            }
        } else {


            Cart::add($product->id, $product->title, 1, $product->price, ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '']);
            $status = true;
            $message = '<strong>' . $product->title . '</strong> added in your cart successfully.';

            session()->flash('success', $message);
        }

        return response()->json([
            'status' => $status,
            'message' =>  $message

        ]);
    }
    public function cart()
    {
        $cartContent = Cart::content();
        // dd($cartContent);
        $data['cartContent'] = $cartContent;
        return view('front.cart', $data);
    }
    public function updateCart(Request $request)
    {
        $rowId = $request->rowId;
        $qty = $request->qty;

        $itemInfo =  Cart::get($rowId);
        $product = Product::find($itemInfo->id);
        //check qty available in stock

        if ($product->track_qty == 'Yes') {

            if ($qty <= $product->qty) {
                Cart::update($rowId, $qty);
                $message = 'Cart updated successfully';
                $status = true;
                session()->flash('success', $message);
            } else {
                $message = 'Request qty(' . $qty . ') not available in stock.';
                $status = false;
                session()->flash('error', $message);
            }
        } else {
            Cart::update($rowId, $qty);
            $message = 'Cart updated successfully';
            $status = true;
            session()->flash('success', $message);
        }





        return response()->json([
            'status' => $status,
            'message' => $message
        ]);
    }
    public function deleteItem(Request $request)
    {

        $itemInfo = Cart::get($request->rowId);
        if ($itemInfo == null) {
            $errorMessage = 'Item not found in cart';
            session()->flash('error', $errorMessage);
            return response()->json([
                'status' => false,
                'message' => $errorMessage
            ]);
        }
        Cart::remove($request->rowId);
        $message = 'Item removed from cart successfully';
        session()->flash('error', $message);
        return response()->json([
            'status' => true,
            'message' => $message
        ]);
    }
    public function checkout()
    {
        // -- if carrt iss empty redirect to cart page
        if (Cart::count() == 0) {
            return redirect()->route('front.cart');
        }
        // if user is not logged in then redirect to login page
        if (Auth::check() == false) {
            if (!session()->has('url.intended')) {
                session(['url.intended' => url()->current()]);
            }

            return redirect()->route('account.login');
        }

        $customerAddress = CustomerAddress::where('user_id', Auth::user()->id)->first();


        session()->forget('url.intended');
        $countries = Country::orderBy('name', 'ASC')->get();

        // tính vận chuyển
        if ($customerAddress != '') {
            $userCountry = $customerAddress->country_id;
            $shippingInfo = ShippingCharge::where('country_id', $userCountry)->first();

            // Kiểm tra xem có dữ liệu phí vận chuyển cho quốc gia được cung cấp hay không
            if ($shippingInfo) {
                $totalQty = 0;
                $totalShippingCharge = 0;
                $grandTotal = 0;

                foreach (Cart::content() as $item) {
                    $totalQty += $item->qty;
                }

                $totalShippingCharge = $totalQty * $shippingInfo->amount;
                $grandTotal = Cart::subtotal(2, '.', '') + $totalShippingCharge;
            } else {
                // Nếu không tìm thấy dữ liệu phí vận chuyển, đặt giá trị mặc định là 0
                $totalShippingCharge = 0;
                $grandTotal = Cart::subtotal(2, '.', '');
            }
        } else {
            $grandTotal = Cart::subtotal(2, '.', '');
            $totalShippingCharge = 0;
        }

        return view('front.checkout', [
            'countries' => $countries,
            'customerAddress' => $customerAddress,
            'totalShippingCharge' => $totalShippingCharge,
            'grandTotal' => $grandTotal,
        ]);
    }

    public function processCheckout(Request $request)
    {
        //step -1 apply validation
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|min:5',
            'last_name' => 'required',
            'email' => 'required|email',
            'country' => 'required',
            'address' => 'required|min:30',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'mobile' => 'required',

        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Please fix the errors',
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
        //step -2 save user address
        //   $customerAddress = CustomerAddress::find()


        $user = Auth::user();
        CustomerAddress::updateOrCreate(
            ['user_id' => $user->id],
            [
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'country_id' => $request->country,
                'address' => $request->address,
                'apartment' => $request->apartment,
                'city' => $request->city,
                'state' => $request->state,
                'zip' => $request->zip,

            ]
        );
        //step -3 store data in orders table
        if ($request->payment_method == 'cod') {


            $shipping = 0;
            $discount = 0;
            $subTotal = Cart::subtotal(2, '.', '');
            $grandTotal = $subTotal + $shipping;
            // Calculte Shipping 

            $shippingInfo = ShippingCharge::where('country_id', $request->country)->first();
            $totalQty = 0;

            foreach (Cart::content() as $item) {
                $totalQty += $item->qty;
            }
            if ($shippingInfo != null) {

                $shipping = $totalQty * $shippingInfo->amount;
                $grandTotal =   $subTotal + $shipping;
            } else {
                $shippingInfo = ShippingCharge::where('country_id', 'rest_of_world')->first();

                $shipping = $totalQty * $shippingInfo->amount;
                $grandTotal =   $subTotal + $shipping;
            }






            $order = new Order;
            $order->subtotal =  $subTotal;
            $order->shipping = $shipping;
            $order->payment_status = 'not paid';
            $order->status = 'pending';
            $order->grand_total =  $grandTotal;
            $order->user_id =  $user->id;
            $order->firtst_name =  $request->first_name;
            $order->last_name =   $request->last_name;
            $order->email =  $request->email;
            $order->mobile = $request->mobile;
            $order->address =  $request->address;
            $order->apartment =  $request->apartment;
            $order->state =  $request->state;
            $order->city =  $request->city;
            $order->zip =  $request->zip;
            $order->notes =  $request->order_notes;
            $order->country_id =  $request->country;
            $order->save();

            //step -4 store order items in order items table
            foreach (Cart::content() as $item) {
                $orderItem = new OrderItem;
                $orderItem->product_id = $item->id;
                $orderItem->order_id = $order->id;
                $orderItem->name = $item->name;
                $orderItem->qty = $item->qty;
                $orderItem->price = $item->price;
                $orderItem->total = $item->price * $item->qty;
                $orderItem->save();
            }
            session()->flash('success', 'You have successfully placed your order');
            Cart::destroy();

            return response()->json([
                'message' => 'Order saved successfully ',
                'orderId' => $order->id,
                'status' => true,

            ]);
        } else {
        }
    }
    public function thankyou($id)
    {
        return view('front.thanks', [
            'id' => $id
        ]);
    }
    public function getOrderSummery(Request $request)
    {
        $subTotal = Cart::subtotal(2, '.', '');

        if ($request->country_id > 0) {


            $shippingInfo = ShippingCharge::where('country_id', $request->country_id)->first();

            $totalQty = 0;

            foreach (Cart::content() as $item) {
                $totalQty += $item->qty;
            }

            if ($shippingInfo != null) {

                $shippingCharge = $totalQty * $shippingInfo->amount;
                $grandTotal =   $subTotal + $shippingCharge;

                return response()->json([
                    'status' => true,
                    'grandTotal' => number_format($grandTotal, 2),
                    'shippingCharge' => number_format($shippingCharge, 2),
                ]);
            } else {
                $shippingInfo = ShippingCharge::where('country_id', 'rest_of_world')->first();

                $shippingCharge = $totalQty * $shippingInfo->amount;
                $grandTotal =   $subTotal + $shippingCharge;

                return response()->json([
                    'status' => true,
                    'grandTotal' => number_format($grandTotal, 2),
                    'shippingCharge' => number_format($shippingCharge, 2),
                ]);
            }
        } else {
            return response()->json([
                'status' => true,
                'grandTotal' => number_format($subTotal, 2),
                'shippingCharge' => number_format(0, 2),
            ]);
        }
    }
}

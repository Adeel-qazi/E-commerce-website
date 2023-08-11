<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingCharge;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {

        $product = Product::with('product_images')->find($request->id);

        if ($product == null) {
            return response()->json([
                'status' => false,
                'message' => 'Product Not Found'
            ]);
        }

        if (Cart::count() > 0) { // if product doesnt exist in cart so here product adding in cart

            $cartContent = Cart::content(); // get all cart's content
            $productAlreadyExist = false;

            foreach ($cartContent as $item) {
                if ($item->id == $product->id) {
                    $productAlreadyExist = true;
                }
            }

            if ($productAlreadyExist == false) {
                Cart::add($product->id, $product->title, 1, $product->price, ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '']);
                $status = true;
                $message = '<strong>' . $product->title . '</strong> added in your cart successfully ';
                session()->flash('success', $message);

            } else {
                $status = false;
                $message = $product->title . ' already added in cart';
            }

        } else {
            //cart is empty
            Cart::add($product->id, $product->title, 1, $product->price, ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '']);
            $status = true;
            $message = '<strong>' . $product->title . '</strong> added in your cart successfully ';
            session()->flash('success', $message);
        }

        return response()->json([
            'status' => $status,
            'message' => $message
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
    { // we update any item of update through rowId

        $rowId = $request->rowId;
        $qty = $request->qty;

        $itemInfo = Cart::get($rowId); // product id exists in itemInfo
        $product = Product::find($itemInfo->id);

        // check qty available in stock   if owner has put limited product in stock so he checks 
        if ($product->track_qty == 'Yes') {

            if ($qty <= $product->qty) {
                Cart::update($rowId, $qty);
                $message = 'Cart Updated Successfully';
                $status = true;
                $request->session()->flash('success', $message);

            } else {
                $message = 'Requested qty(' . $qty . ') not available in stock';
                $status = false;
                $request->session()->flash('error', $message);
            }

        } else { // if owner has put unlimited product in stock

            Cart::update($rowId, $qty);
            $message = 'Cart Updated Successfully';
            $status = true;
            $request->session()->flash('success', $message);
        }




        return response()->json([
            'status' => $status,
            'message' => $message
        ]);

    }


    public function deleteItem(Request $request)
    {
        Cart::get($request->rowId);

        if ($request->rowId == null) {
            $messageError = 'Item not found';
            $status = false;
            $request->session()->flash('error', $messageError);

            return response()->json([
                'status' => $status,
                'message' => $messageError
            ]);
        }

        Cart::remove($request->rowId);
        $messageSuccess = 'Item removed from cart successfully';
        $status = true;
        $request->session()->flash('success', $messageSuccess);

        return response()->json([
            'status' => $status,
            'message' => $messageSuccess
        ]);
    }


    public function checkout()
    {

        // if card is empty so redirect to cart page
        if (Cart::count() == 0) {
            return redirect()->route('front.cart');

        }
        
        // if user is not logged in then redirect to login page
        if (Auth::check() == false) { 

            if (!session()->has('url.intendent')) {  //variable has stored current url like checkout
                return session(['url.intendent' => url()->current()]);
            }

            return redirect()->route('account.login');

        }

        session()->forget('url.intendent'); 


         //if address is stored once then showing the address of user order dont fill out again and agian
         $customerAddress = CustomerAddress::where('user_id',Auth::user()->id)->first();

         
         //calculate shipping here
         if($customerAddress != ''){   // as soon as new user is created so user has not its own addres
         $userCountry = $customerAddress->country_id;
         $shippingInfo = ShippingCharge::where('country_id',$userCountry)->first();

        
        $totalQty = 0;
        $totalShippingCharge = 0;
        $grandTotal = 0;
        foreach (Cart::content() as  $item) {   // check the products in cart how many quantities in cart
            $totalQty += $item->qty; 
        }

        if ($shippingInfo) {
            $totalShippingCharge = $totalQty * $shippingInfo->amount;
            $grandTotal = Cart::subtotal(2, '.', '') + $totalShippingCharge;
        } else {
    
            $grandTotal = Cart::subtotal(2, '.', '');
            $totalShippingCharge = 0;
        }

        }else{

        $grandTotal = Cart::subtotal(2,'.','');
        $totalShippingCharge = 0;
            
         }

        


          $countries = Country::orderBy('name','ASC')->get();
          
          $data['countries'] = $countries;
          $data['customerAddress'] = $customerAddress;
          $data['totalShippingCharge'] = $totalShippingCharge;
          $data['grandTotal'] = $grandTotal;
        return view('front.checkout',$data);
    }

     

    public function processCheckout(Request $request){
       
        // step-1 apply validation

        $validator = Validator::make($request->all(),[
          'first_name' => 'required|min:5',
          'last_name'  => 'required',
          'email'      => 'required|email',
          'country'    => 'required',
          'address'    => 'required|min:30',
          'city'       => 'required',
          'state'      => 'required',
          'zip'        => 'required',
          'mobile'     => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'  => false,
                'message' => 'please fix this error',
                'errors'  => $validator->errors(),
            ]); 
        }


        // stpe-2 save user address

        $user = Auth::user();
        
        $customerAddress = CustomerAddress::updateOrCreate(
            ['user_id' => $user->id],   // checks whether user exists or not if user exists then update 
            [
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'country_id' => $request->country,
                'address' => $request->address,
                'appartment' => $request->appartment,
                'city' => $request->city,   
                'state' => $request->state,
                'zip' => $request->zip,

           ],

        );

        // step-3 store the data in orders table

        if($request->payment_method == 'cod'){
             
            $shipping = 0;
            $qtyTotal = 0;
            $discount = 0;
            $subTotal = Cart::subtotal(2,'.','');

            foreach (Cart::content() as $item) {  
                $qtyTotal += $item->qty;
            }

            $shippingInfo = ShippingCharge::where('country_id',$request->country_id)->first();

            if($shippingInfo != null){   // country exists in country list

                $shipping = $qtyTotal*$shippingInfo->amount;
                $grandTotal = $subTotal + $shipping; 

            }else{

            $shippingInfo = ShippingCharge::where('country_id','rest_of_world')->first();
            $shipping = $qtyTotal*$shippingInfo->amount;
            $grandTotal = $subTotal + $shipping; 

            }


            $order = new Order;
            $order->user_id = $user->id;
            $order->subtotal = $subTotal;
            $order->shipping = $shipping;
            $order->discount = $discount;
            $order->grand_total = $grandTotal;
            $order->first_name = $request->first_name;
            $order->last_name = $request->last_name;
            $order->email = $request->email;
            $order->mobile = $request->mobile;
            $order->country_id = $request->country;
            $order->address = $request->address;
            $order->appartment = $request->appartment;
            $order->city = $request->city;
            $order->state = $request->state;
            $order->zip = $request->zip;
            $order->notes = $request->order_notes;
            $order->save();

           // step -3 store the order item in orderItems table
           
           foreach (Cart::content() as $item ) {
               $orderItem = new OrderItem;
               $orderItem->order_id = $order->id;
               $orderItem->product_id = $item->id;
               $orderItem->name = $item->name;
               $orderItem->qty = $item->qty;
               $orderItem->price = $item->price;
               $orderItem->total = $item->price*$item->qty;
               $orderItem->save();

           }

           session()->flash("success","You have successfully placed your order");

            Cart::destroy();   // cart is empty after ordered the product

           return response()->json([
            'status' => true,
            'message' => 'oreder saved successfully',
            'orderId' => $order->id,
           ]);



        }else{

        }



    }


    public function thankyou($id){

        return view('front.thanks',[
            'id' => $id,
        ]);
    }   




    public function getOrderSummary(Request $request){  // during submitting the shipping form when user change country so it would change shipping amount through ajax
          
        if($request->country_id > 0){    // if country exists in list 

            $subTotal = Cart::subtotal(2,'.','');
          $shippingInfo = ShippingCharge::where('country_id',$request->country_id)->first();

          $totalQty = 0;
        foreach (Cart::content() as  $item) {   // check the products in cart how many quantities in cart
            $totalQty += $item->qty; 
        }
          
          if($shippingInfo != null){               // country id exists in database?

              $shippingCharge = $totalQty*$shippingInfo->amount;
              $grandTotal = $subTotal + $shippingCharge;

              return response()->json([
                'status' => true,
                'shippingCharge' => number_format($shippingCharge),
                'grandTotal' => number_format($grandTotal)

              ]);

          }else{

          $shippingInfo = ShippingCharge::where('country_id','rest_of_world')->first();

            $shippingCharge = $totalQty*$shippingInfo->amount;
            $grandTotal = $subTotal + $shippingCharge;
            return response()->json([
                'status' => true,
                'shippingCharge' => number_format($shippingCharge,2),
                'grandTotal' => number_format($grandTotal,2),

            ]);

          }

           }else{   // not selected country
            $grandTotal = Cart::subtotal(2,'.','');
            return response()->json([
                'status' => true,
                'shippingCharge' => number_format(0,2),
                'grandTotal' => number_format($grandTotal,2),

            ]);
           }
    }

}